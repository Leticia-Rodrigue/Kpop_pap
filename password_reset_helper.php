<?php
require_once __DIR__ . '/app_settings.php';
require_once __DIR__ . '/mail_helper.php';

if (!function_exists('ensure_password_reset_table')) {
    function ensure_password_reset_table(mysqli $conn): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            token_hash CHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_password_reset_user_id (user_id),
            INDEX idx_password_reset_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        return mysqli_query($conn, $sql) === true;
    }

    function cleanup_password_reset_tokens(mysqli $conn): void
    {
        mysqli_query($conn, "DELETE FROM password_reset_tokens WHERE used_at IS NOT NULL OR expires_at < NOW()");
    }

    // A resposta e generica mesmo quando o email nao existe para nao revelar que contas estao registadas.
    function request_password_reset(mysqli $conn, string $email): bool
    {
        if (!ensure_password_reset_table($conn)) {
            error_log('Password reset table is unavailable: ' . mysqli_error($conn));
            return false;
        }

        cleanup_password_reset_tokens($conn);

        $email = trim($email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        $stmt = mysqli_prepare($conn, 'SELECT id_utilizador, nome, email FROM utilizadores WHERE email = ? LIMIT 1');
        if (!$stmt) {
            error_log('Password reset select prepare failed: ' . mysqli_error($conn));
            return false;
        }

        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $userId, $userName, $userEmail);
        $found = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!$found) {
            return true;
        }

        $deleteStmt = mysqli_prepare($conn, 'DELETE FROM password_reset_tokens WHERE user_id = ?');
        if ($deleteStmt) {
            mysqli_stmt_bind_param($deleteStmt, 'i', $userId);
            mysqli_stmt_execute($deleteStmt);
            mysqli_stmt_close($deleteStmt);
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $settings = load_app_settings();
        $ttlMinutes = max(5, (int) app_setting($settings, 'app.reset_token_ttl_minutes', 30));
        $expiresAt = date('Y-m-d H:i:s', time() + ($ttlMinutes * 60));

        $insertStmt = mysqli_prepare($conn, 'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
        if (!$insertStmt) {
            error_log('Password reset insert prepare failed: ' . mysqli_error($conn));
            return false;
        }

        mysqli_stmt_bind_param($insertStmt, 'iss', $userId, $tokenHash, $expiresAt);
        $inserted = mysqli_stmt_execute($insertStmt);
        mysqli_stmt_close($insertStmt);

        if (!$inserted) {
            error_log('Password reset insert failed: ' . mysqli_error($conn));
            return false;
        }

        try {
            $baseUrl = app_base_url($settings);
            $resetUrl = app_join_url($baseUrl, 'recuperar_password.php?token=' . urlencode($token));
            send_password_reset_email($userEmail, $userName ?? '', $resetUrl, $ttlMinutes);
            return true;
        } catch (Throwable $e) {
            error_log('Password reset email failed: ' . $e->getMessage());
            mysqli_query($conn, "DELETE FROM password_reset_tokens WHERE token_hash = '" . mysqli_real_escape_string($conn, $tokenHash) . "'");
            return false;
        }
    }

    function get_password_reset_token_record(mysqli $conn, string $token): ?array
    {
        if ($token === '' || !ensure_password_reset_table($conn)) {
            return null;
        }

        cleanup_password_reset_tokens($conn);
        $tokenHash = hash('sha256', $token);
        $stmt = mysqli_prepare($conn, 'SELECT id, user_id, expires_at, used_at FROM password_reset_tokens WHERE token_hash = ? LIMIT 1');
        if (!$stmt) {
            error_log('Password reset token lookup failed: ' . mysqli_error($conn));
            return null;
        }

        mysqli_stmt_bind_param($stmt, 's', $tokenHash);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $userId, $expiresAt, $usedAt);

        if (!mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        mysqli_stmt_close($stmt);

        return [
            'id' => (int) $id,
            'user_id' => (int) $userId,
            'expires_at' => $expiresAt,
            'used_at' => $usedAt,
        ];
    }

    function is_password_reset_token_valid(?array $tokenRecord): bool
    {
        if (!$tokenRecord) {
            return false;
        }

        if (!empty($tokenRecord['used_at'])) {
            return false;
        }

        return strtotime($tokenRecord['expires_at']) > time();
    }

    function reset_password_with_token(mysqli $conn, string $token, string $newPassword): bool
    {
        $tokenRecord = get_password_reset_token_record($conn, $token);
        if (!is_password_reset_token_valid($tokenRecord)) {
            return false;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Password e token sao atualizados na mesma transacao para evitar estados inconsistentes.
        mysqli_begin_transaction($conn);

        try {
            $updatePassword = mysqli_prepare($conn, 'UPDATE utilizadores SET senha_hash = ? WHERE id_utilizador = ?');
            if (!$updatePassword) {
                throw new RuntimeException('Falha a preparar update de password.');
            }

            mysqli_stmt_bind_param($updatePassword, 'si', $passwordHash, $tokenRecord['user_id']);
            if (!mysqli_stmt_execute($updatePassword)) {
                throw new RuntimeException('Falha a gravar a nova password.');
            }
            mysqli_stmt_close($updatePassword);

            $markUsed = mysqli_prepare($conn, 'UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?');
            if (!$markUsed) {
                throw new RuntimeException('Falha a marcar o token como usado.');
            }

            mysqli_stmt_bind_param($markUsed, 'i', $tokenRecord['id']);
            if (!mysqli_stmt_execute($markUsed)) {
                throw new RuntimeException('Falha a fechar o token de reset.');
            }
            mysqli_stmt_close($markUsed);

            $cleanup = mysqli_prepare($conn, 'DELETE FROM password_reset_tokens WHERE user_id = ? AND id <> ?');
            if ($cleanup) {
                mysqli_stmt_bind_param($cleanup, 'ii', $tokenRecord['user_id'], $tokenRecord['id']);
                mysqli_stmt_execute($cleanup);
                mysqli_stmt_close($cleanup);
            }

            mysqli_commit($conn);
            return true;
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            error_log('Password reset update failed: ' . $e->getMessage());
            return false;
        }
    }
}
