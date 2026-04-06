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

        $settings    = function_exists('get_system_settings') ? get_system_settings() : [];
        $system_name = trim((string) ($settings['system_name'] ?? 'CredenceLend'));

        $config = [
            'transport'    => 'phpmailer',
            'host'         => trim((string) app_env_value('MAIL_HOST', 'smtp-relay.brevo.com')),
            'port'         => intval(app_env_value('MAIL_PORT', 587)),
            'username'     => trim((string) app_env_value('MAIL_USERNAME', '')),
            'password'     => (string) app_env_value('MAIL_PASSWORD', ''),
            'encryption'   => strtolower((string) app_env_value('MAIL_ENCRYPTION', 'tls')),
            'from_address' => trim((string) app_env_value('MAIL_FROM_ADDRESS', '')),
            'from_name'    => trim((string) app_env_value('MAIL_FROM_NAME', $system_name !== '' ? $system_name : 'CredenceLend')),
            'reply_to'     => trim((string) app_env_value('MAIL_REPLY_TO', '')),
            'app_url'      => rtrim((string) app_env_value('APP_URL', ''), '/'),
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
            $host   = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $base   = $scheme . '://' . $host . rtrim(APP_BASE, '/');
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

        $config    = array_merge(app_mail_settings(), $options);
        $text_body = $options['text_body'] ?? app_mail_plain_text($html_body);
        $api_key   = (string) app_env_value('BREVO_API_KEY', '');

        if ($api_key === '') {
            return ['ok' => false, 'error' => 'BREVO_API_KEY is not set.'];
        }

        $payload = json_encode([
            'sender'      => [
                'name'  => $config['from_name'],
                'email' => $config['from_address'],
            ],
            'to'          => [['email' => $to]],
            'replyTo'     => ['email' => $config['reply_to'] ?: $config['from_address']],
            'subject'     => $subject,
            'htmlContent' => app_mail_normalize_body($html_body),
            'textContent' => app_mail_normalize_body($text_body),
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'content-type: application/json',
                'api-key: ' . $api_key,
            ],
        ]);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            error_log('Mail send failed: ' . $curl_error);
            return ['ok' => false, 'error' => $curl_error];
        }

        if ($http_code >= 200 && $http_code < 300) {
            return ['ok' => true, 'error' => ''];
        }

        $decoded = json_decode($response, true);
        $error   = $decoded['message'] ?? ('HTTP ' . $http_code);
        error_log('Mail send failed: ' . $error);
        return ['ok' => false, 'error' => $error];
    }
}
