<?php
require_once __DIR__ . '/app_settings.php';
require_once __DIR__ . '/PHPMailer-master/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

if (!function_exists('create_configured_mailer')) {
    function create_configured_mailer(?string $fromName = null): PHPMailer
    {
        $settings = load_app_settings();
        $mailHost = app_setting($settings, 'mail.host');
        $mailPort = (int) app_setting($settings, 'mail.port', 587);
        $mailSecurity = strtolower((string) app_setting($settings, 'mail.security', 'tls'));
        $mailUsername = app_setting($settings, 'mail.username');
        $mailPassword = app_setting($settings, 'mail.password');
        $fromEmail = app_setting($settings, 'mail.from_email');
        $defaultFromName = app_setting($settings, 'mail.from_name', 'K-Pop Universe');

        if (!$mailHost || !$mailUsername || $mailPassword === null || !$fromEmail) {
            throw new RuntimeException('Configuração de email incompleta.');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $mailHost;
        $mail->SMTPAuth = true;
        $mail->Username = $mailUsername;
        $mail->Password = $mailPassword;
        $mail->Port = $mailPort;
        $mail->CharSet = 'UTF-8';

        if ($mailSecurity === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($mailSecurity === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom($fromEmail, $fromName ?: $defaultFromName);

        return $mail;
    }

    function app_admin_email(): ?string
    {
        return app_setting(load_app_settings(), 'mail.admin_email');
    }

    function send_password_reset_email(string $email, string $name, string $resetUrl, int $ttlMinutes): void
    {
        $mail = create_configured_mailer('K-Pop Universe');
        $mail->addAddress($email, $name !== '' ? $name : $email);
        $mail->isHTML(true);
        $mail->Subject = 'Recuperacao de password - K-Pop Universe';
        $mail->Body = '
        <!DOCTYPE html>
        <html lang="pt-pt">
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#050505;font-family:Poppins,Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#050505;padding:40px 20px;">
                <tr><td align="center">
                    <table width="580" cellpadding="0" cellspacing="0" style="background:#0d0d0d;border-radius:16px;border:1px solid rgba(0,212,255,0.2);overflow:hidden;">
                        <tr>
                            <td style="background:linear-gradient(135deg,#001a1a,#0d0d0d);padding:32px 40px;border-bottom:1px solid rgba(0,212,255,0.15);">
                                <p style="margin:0;color:#00d4ff;font-size:11px;font-weight:700;letter-spacing:4px;text-transform:uppercase;">K-POP UNIVERSE</p>
                                <h1 style="margin:8px 0 0;color:#fff;font-size:24px;font-weight:800;">Recuperacao de Password</h1>
                                <p style="margin:6px 0 0;color:#555;font-size:12px;letter-spacing:1px;">Pedido seguro de acesso</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:36px 40px;">
                                <p style="margin:0 0 14px;color:#ccc;font-size:14px;line-height:1.7;">Foi pedido um reset de password para a tua conta. Se foste tu, usa o botao abaixo para escolheres uma nova password.</p>
                                <div style="margin:28px 0;text-align:center;">
                                    <a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:linear-gradient(45deg,#00c9ff,#00d4ff);color:#000;font-size:12px;font-weight:800;letter-spacing:2px;text-transform:uppercase;text-decoration:none;padding:14px 28px;border-radius:50px;box-shadow:0 0 20px rgba(0,212,255,0.3);">
                                        Definir Nova Password
                                    </a>
                                </div>
                                <p style="margin:0;color:#666;font-size:12px;line-height:1.7;">Este link expira em ' . $ttlMinutes . ' minutos e so pode ser usado uma vez.</p>
                                <p style="margin:16px 0 0;color:#444;font-size:11px;line-height:1.6;">Se nao pediste esta alteracao, podes ignorar este email.</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>';
        $mail->AltBody = "Foi pedido um reset de password para a tua conta. Usa este link nos proximos {$ttlMinutes} minutos: {$resetUrl}";
        $mail->send();
    }
}
