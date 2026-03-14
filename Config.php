<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/PHPMailer-master/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nome'])) {

    $nome     = htmlspecialchars($_POST['nome']);
    $email    = htmlspecialchars($_POST['email']);
    $mensagem = htmlspecialchars($_POST['mensagem']);
    $captcha_input = trim($_POST['captcha'] ?? '');

    if (!isset($_SESSION['captcha_code']) || $captcha_input !== $_SESSION['captcha_code']) {
        $erro_captcha = 'Código de verificação incorreto. Tenta novamente.';
        unset($_SESSION['captcha_code']);
    } else {
        unset($_SESSION['captcha_code']);

        $email_body_admin = '
        <!DOCTYPE html>
        <html lang="pt-pt">
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#050505;font-family:Poppins,Arial,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#050505;padding:40px 20px;">
            <tr><td align="center">
                <table width="580" cellpadding="0" cellspacing="0" style="background:#0d0d0d;border-radius:16px;border:1px solid rgba(255,0,128,0.25);overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#1a001a,#0d0d0d);padding:32px 40px;border-bottom:1px solid rgba(255,0,128,0.2);">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <p style="margin:0;color:#ff0080;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;">K-POP UNIVERSE</p>
                                        <h1 style="margin:6px 0 0;color:#fff;font-size:22px;font-weight:800;letter-spacing:1px;">Nova Mensagem de Contacto</h1>
                                    </td>
                                    <td align="right">
                                        <div style="background:rgba(255,0,128,0.15);border:1px solid rgba(255,0,128,0.4);border-radius:50px;padding:8px 18px;display:inline-block;">
                                            <span style="color:#ff0080;font-size:11px;font-weight:800;letter-spacing:2px;">ADMIN</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Info -->
                    <tr>
                        <td style="padding:32px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="50%" style="padding-bottom:16px;">
                                        <p style="margin:0 0 4px;color:#555;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Nome</p>
                                        <p style="margin:0;color:#fff;font-size:15px;font-weight:600;">' . $nome . '</p>
                                    </td>
                                    <td width="50%" style="padding-bottom:16px;">
                                        <p style="margin:0 0 4px;color:#555;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Email</p>
                                        <p style="margin:0;color:#00d4ff;font-size:15px;font-weight:600;">' . $email . '</p>
                                    </td>
                                </tr>
                            </table>

                            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-left:3px solid #ff0080;border-radius:10px;padding:20px 24px;margin-top:8px;">
                                <p style="margin:0 0 8px;color:#555;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Mensagem</p>
                                <p style="margin:0;color:#ccc;font-size:14px;line-height:1.7;">' . nl2br($mensagem) . '</p>
                            </div>

                            <div style="margin-top:28px;text-align:center;">
                                <a href="mailto:' . $email . '" style="display:inline-block;background:linear-gradient(45deg,#ff0080,#ff6ec7);color:#fff;font-size:12px;font-weight:800;letter-spacing:2px;text-transform:uppercase;text-decoration:none;padding:12px 28px;border-radius:50px;box-shadow:0 0 20px rgba(255,0,128,0.3);">
                                    Responder a ' . $nome . '
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 40px;border-top:1px solid rgba(255,255,255,0.04);">
                            <p style="margin:0;color:#333;font-size:11px;text-align:center;">&copy; 2026 K-Pop Universe &mdash; Projeto Final TGPSI</p>
                        </td>
                    </tr>

                </table>
            </td></tr>
        </table>
        </body>
        </html>';

        $email_body_user = '
        <!DOCTYPE html>
        <html lang="pt-pt">
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#050505;font-family:Poppins,Arial,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#050505;padding:40px 20px;">
            <tr><td align="center">
                <table width="580" cellpadding="0" cellspacing="0" style="background:#0d0d0d;border-radius:16px;border:1px solid rgba(0,212,255,0.2);overflow:hidden;">

                    <!-- Header com gradiente -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#001a1a,#0d0d0d);padding:32px 40px;border-bottom:1px solid rgba(0,212,255,0.15);">
                            <p style="margin:0;color:#00d4ff;font-size:11px;font-weight:700;letter-spacing:4px;text-transform:uppercase;">&#9733; K-POP UNIVERSE</p>
                            <h1 style="margin:8px 0 0;color:#fff;font-size:24px;font-weight:800;">Mensagem Recebida!</h1>
                            <p style="margin:6px 0 0;color:#555;font-size:12px;letter-spacing:1px;">Confirmação de Contacto</p>
                        </td>
                    </tr>

                    <!-- Corpo -->
                    <tr>
                        <td style="padding:36px 40px;">
                            <p style="margin:0 0 6px;color:#888;font-size:14px;">Olá <strong style="color:#fff;">' . $nome . '</strong>,</p>
                            <p style="margin:0 0 28px;color:#555;font-size:13px;line-height:1.6;">Recebemos a tua mensagem com sucesso e vamos responder o mais breve possível. Aqui está uma cópia do que nos enviaste:</p>

                            <div style="background:rgba(0,212,255,0.04);border:1px solid rgba(0,212,255,0.12);border-left:3px solid #00d4ff;border-radius:10px;padding:20px 24px;margin-bottom:28px;">
                                <p style="margin:0 0 8px;color:#00d4ff;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">A tua mensagem</p>
                                <p style="margin:0;color:#ccc;font-size:14px;line-height:1.7;">' . nl2br($mensagem) . '</p>
                            </div>

                            <!-- Badge de estado -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(0,212,100,0.06);border:1px solid rgba(0,212,100,0.2);border-radius:10px;">
                                <tr>
                                    <td style="padding:14px 20px;">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-right:12px;font-size:20px;">&#x2705;</td>
                                                <td>
                                                    <p style="margin:0;color:#00d464;font-size:12px;font-weight:800;letter-spacing:1px;">MENSAGEM ENVIADA COM SUCESSO</p>
                                                    <p style="margin:2px 0 0;color:#555;font-size:11px;">Responderemos para ' . $email . '</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 40px;border-top:1px solid rgba(255,255,255,0.04);">
                            <p style="margin:0;color:#333;font-size:11px;text-align:center;">
                                Se não enviaste este formulário, ignora este email.<br>
                                &copy; 2026 K-Pop Universe &mdash; Projeto Final TGPSI
                            </p>
                        </td>
                    </tr>

                </table>
            </td></tr>
        </table>
        </body>
        </html>';

        try {
            // Email 1: admin
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'kpopuniverse.pap@gmail.com';
            $mail->Password   = 'vvgigfkwdzxnkgzp';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom('kpopuniverse.pap@gmail.com', 'Site - K-Pop Universe');
            $mail->addAddress('kpopuniverse.pap@gmail.com');
            $mail->addReplyTo($email, $nome);
            $mail->isHTML(true);
            $mail->Subject = 'Nova mensagem de ' . $nome . ' - K-Pop Universe';
            $mail->Body    = $email_body_admin;
            $mail->send();

            // Email 2: confirmação ao utilizador
            $mail2 = new PHPMailer(true);
            $mail2->isSMTP();
            $mail2->Host       = 'smtp.gmail.com';
            $mail2->SMTPAuth   = true;
            $mail2->Username   = 'kpopuniverse.pap@gmail.com';
            $mail2->Password   = 'vvgigfkwdzxnkgzp';
            $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail2->Port       = 587;
            $mail2->CharSet    = 'UTF-8';
            $mail2->setFrom('kpopuniverse.pap@gmail.com', 'K-Pop Universe');
            $mail2->addAddress($email, $nome);
            $mail2->isHTML(true);
            $mail2->Subject = 'Recebemos a tua mensagem - K-Pop Universe';
            $mail2->Body    = $email_body_user;
            $mail2->send();

            $enviado = true;

        } catch (Exception $e) {
            $erro_envio = $mail->ErrorInfo;
        }
    }
}
?>