<?php

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

if (!function_exists('app_env_value')) {
    function app_env_value($key, $default = null) {
        $value = getenv($key);
        return ($value === false || $value === '') ? $default : $value;
    }
}

if (!function_exists('app_mail_settings')) {
    function app_mail_settings() {
        static $config = null;

        if ($config !== null) {
            return $config;
        }

        $settings = function_exists('get_system_settings') ? get_system_settings() : [];
        $host = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $system_name = trim((string) ($settings['system_name'] ?? 'CredenceLend'));

        $config = [
            'transport' => 'phpmailer',
            'host' => trim((string) app_env_value('MAIL_HOST', 'smtp.gmail.com')),
            'port' => intval(app_env_value('MAIL_PORT', 465)),
            'username' => trim((string) app_env_value('MAIL_USERNAME', 'alliah1530@gmail.com')),
            'password' => (string) app_env_value('MAIL_PASSWORD', 'mjnz fexk mofy cgxw'),
            'encryption' => strtolower((string) app_env_value('MAIL_ENCRYPTION', 'ssl')),
            'from_address' => trim((string) app_env_value('MAIL_FROM_ADDRESS', 'alliah1530@gmail.com')),
            'from_name' => trim((string) app_env_value('MAIL_FROM_NAME', $system_name !== '' ? $system_name : 'CredenceLend')),
            'reply_to' => trim((string) app_env_value('MAIL_REPLY_TO', 'alliah1530@gmail.com')),
            'app_url' => rtrim((string) app_env_value('APP_URL', ''), '/'),
        ];

        return $config;
    }
}

if (!function_exists('app_public_url')) {
    function app_public_url($path = '', array $query = []) {
        $config = app_mail_settings();

        if ($config['app_url'] !== '') {
            $base = $config['app_url'];
        } else {
            $https_enabled = (
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || intval($_SERVER['SERVER_PORT'] ?? 0) === 443
                || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
            );
            $scheme = $https_enabled ? 'https' : 'http';
            $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $base = $scheme . '://' . $host . rtrim(APP_BASE, '/');
        }

        $url = $base;
        if ($path !== '') {
            $url .= '/' . ltrim($path, '/');
        }

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }
}

if (!function_exists('app_mail_normalize_body')) {
    function app_mail_normalize_body($body) {
        $body = str_replace(["\r\n", "\r"], "\n", (string) $body);
        return preg_replace("/\n{3,}/", "\n\n", $body);
    }
}

if (!function_exists('app_mail_plain_text')) {
    function app_mail_plain_text($html) {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(app_mail_normalize_body($text));
    }
}

if (!function_exists('app_send_html_mail')) {
    function app_send_html_mail($to, $subject, $html_body, array $options = []) {
        $to = trim((string) $to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Invalid recipient email address.'];
        }

        $config = array_merge(app_mail_settings(), $options);
        $text_body = $options['text_body'] ?? app_mail_plain_text($html_body);

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->Port = intval($config['port']);
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->CharSet = 'UTF-8';

            if ($config['encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->setFrom($config['from_address'], $config['from_name']);
            if (!empty($config['reply_to'])) {
                $mail->addReplyTo($config['reply_to'], $config['from_name']);
            }

            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = app_mail_normalize_body($html_body);
            $mail->AltBody = app_mail_normalize_body($text_body);
            $mail->send();

            return ['ok' => true, 'error' => ''];
        } catch (Exception $e) {
            error_log('Mail send failed: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
