<?php
declare(strict_types=1);

// --- PHPMailer manual include (no Composer) ---
require_once __DIR__ . '/libs/PHPMailer/Exception.php';
require_once __DIR__ . '/libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * send_mail
 * A tiny mailer wrapper for IONOS SMTP via PHPMailer.
 *
 * @param string $to       Recipient email
 * @param string $subject  Subject (UTF-8)
 * @param string $htmlBody HTML body
 * @param string $textBody Plain text alternative (optional)
 * @param array  $opts     Optional headers: ['reply_to' => 'x@y', 'reply_to_name'=>'...']
 *
 * @return bool true if accepted by SMTP, false otherwise (errors are logged via error_log)
 */
function send_mail(string $to, string $subject, string $htmlBody, string $textBody = '', array $opts = []): bool {
    // Read from env/constants (loaded in config.php)
    $host   = $_ENV['SMTP_HOST']   ?? 'smtp.ionos.de';
    $port   = (int)($_ENV['SMTP_PORT'] ?? 587);          // 587 STARTTLS (or 465 SSL)
    $secure = strtolower($_ENV['SMTP_SECURE'] ?? 'tls'); // 'tls' => STARTTLS, 'ssl' => SMTPS
    $user   = $_ENV['SMTP_USER']   ?? null;              // full mailbox (e.g., noreply@rudolph.cologne)
    $pass   = $_ENV['SMTP_PASS']   ?? null;
    $from   = $_ENV['MAIL_FROM']   ?? $user;
    $fromN  = $_ENV['MAIL_FROM_NAME'] ?? 'Rudolph Cologne';

    if (!$user || !$pass || !$from) {
        error_log('send_mail: SMTP config missing (SMTP_USER/SMTP_PASS/MAIL_FROM).');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        // PRODUCTION: keep debug off (0). For troubleshooting, set to 2 and log via callback.
        $mail->SMTPDebug  = 0;
        // $mail->Debugoutput = static function($str, $lvl){ error_log("SMTP[$lvl]: $str"); };

        $mail->Host       = $host;
        $mail->Port       = $port;
        $mail->SMTPAuth   = true;
        $mail->Username   = $user;
        $mail->Password   = $pass;
        $mail->SMTPSecure = ($secure === 'ssl')
            ? PHPMailer::ENCRYPTION_SMTPS    // Port 465
            : PHPMailer::ENCRYPTION_STARTTLS; // Port 587

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($from, $fromN);

        if (!empty($opts['reply_to'])) {
            $mail->addReplyTo($opts['reply_to'], $opts['reply_to_name'] ?? '');
        }

        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody !== '' ? $textBody : html_entity_decode(strip_tags($htmlBody), ENT_QUOTES, 'UTF-8');

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('send_mail: ' . $mail->ErrorInfo);
        return false;
    }
}