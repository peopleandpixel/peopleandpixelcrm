<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;

/**
 * Minimal email sender with optional SMTP support.
 * If SMTP is not configured, falls back to PHP mail().
 */
final class EmailService
{
    public function __construct(
        private readonly Config $config
    ) {}

    /**
     * Send a plain-text email.
     * @return array{ok:bool,error?:string}
     */
    public function send(string $to, string $subject, string $body, ?string $fromEmail = null, ?string $fromName = null): array
    {
        $to = trim($to);
        if ($to === '') {
            return ['ok' => false, 'error' => 'Missing recipient'];
        }
        $subject = str_replace(["\r","\n"], ' ', $subject);
        $fromEmail = $fromEmail ?: trim($this->config->getEnv('SMTP_FROM')) ?: 'no-reply@localhost';
        $fromName = $fromName ?: trim($this->config->getEnv('SMTP_FROM_NAME')) ?: 'People & Pixel';

        $smtpHost = trim($this->config->getEnv('SMTP_HOST'));
        if ($smtpHost !== '') {
            return $this->sendSmtp($to, $subject, $body, $fromEmail, $fromName);
        }
        return $this->sendMail($to, $subject, $body, $fromEmail, $fromName);
    }

    private function sendMail(string $to, string $subject, string $body, string $fromEmail, string $fromName): array
    {
        $headers = [];
        $headers[] = 'From: ' . ($fromName !== '' ? (sprintf('"%s" <%s>', addslashes($fromName), $fromEmail)) : $fromEmail);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
        if (!$ok) { return ['ok' => false, 'error' => 'mail() failed']; }
        return ['ok' => true];
    }

    private function sendSmtp(string $to, string $subject, string $body, string $fromEmail, string $fromName): array
    {
        $host = trim($this->config->getEnv('SMTP_HOST'));
        $port = (int)($this->config->getEnv('SMTP_PORT') ?: '587');
        $user = $this->config->getEnv('SMTP_USER');
        $pass = $this->config->getEnv('SMTP_PASS');
        $secure = strtolower(trim($this->config->getEnv('SMTP_SECURE'))); // '', 'tls', 'ssl'

        $transport = $host;
        $timeout = 10;
        if ($secure === 'ssl') {
            $transport = 'ssl://' . $host;
            if ($port === 587) { $port = 465; }
        }
        $fp = @fsockopen($transport, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            return ['ok' => false, 'error' => 'SMTP connect failed: ' . $errno . ' ' . $errstr];
        }
        $read = function() use ($fp) {
            $data = '';
            while (!feof($fp)) {
                $line = fgets($fp, 515);
                if ($line === false) break;
                $data .= $line;
                if (strlen($line) >= 4 && $line[3] === ' ') break;
            }
            return $data;
        };
        $write = function(string $cmd) use ($fp) { fwrite($fp, $cmd . "\r\n"); };

        $banner = $read();
        if (strpos($banner, '220') !== 0) { fclose($fp); return ['ok' => false, 'error' => 'SMTP bad banner: ' . trim($banner)]; }
        $ehloHost = 'localhost';
        $write('EHLO ' . $ehloHost); $resp = $read();
        if (strpos($resp, '250') !== 0) { $write('HELO ' . $ehloHost); $resp = $read(); if (strpos($resp, '250') !== 0) { fclose($fp); return ['ok' => false, 'error' => 'HELO/EHLO failed']; } }
        if ($secure === 'tls' && !str_contains($resp, 'STARTTLS')) {
            // try STARTTLS anyway
            $write('STARTTLS'); $tls = $read();
            if (strpos($tls, '220') === 0) {
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return ['ok' => false, 'error' => 'TLS failed']; }
                $write('EHLO ' . $ehloHost); $resp = $read();
            }
        }
        if ($user !== '' && $pass !== '') {
            $write('AUTH LOGIN'); $r = $read(); if (strpos($r, '334') !== 0) { fclose($fp); return ['ok' => false, 'error' => 'AUTH not accepted']; }
            $write(base64_encode($user)); $r = $read(); if (strpos($r, '334') !== 0) { fclose($fp); return ['ok' => false, 'error' => 'USER rejected']; }
            $write(base64_encode($pass)); $r = $read(); if (strpos($r, '235') !== 0) { fclose($fp); return ['ok' => false, 'error' => 'PASS rejected']; }
        }
        $write('MAIL FROM: <' . $fromEmail . '>'); $r = $read(); if (strpos($r, '250') !== 0) { fclose($fp); return ['ok' => false, 'error' => 'MAIL FROM failed']; }
        $write('RCPT TO: <' . $to . '>'); $r = $read(); if (strpos($r, '250') !== 0 && strpos($r, '251') !== 0) { fclose($fp); return ['ok' => false, 'error' => 'RCPT TO failed']; }
        $write('DATA'); $r = $read(); if (strpos($r, '354') !== 0) { fclose($fp); return ['ok' => false, 'error' => 'DATA not accepted']; }

        $headers = [];
        $headers[] = 'From: ' . ($fromName !== '' ? sprintf('"%s" <%s>', addslashes($fromName), $fromEmail) : $fromEmail);
        $headers[] = 'To: ' . $to;
        $headers[] = 'Subject: ' . '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';
        $msg = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        $write($msg); $r = $read(); if (strpos($r, '250') !== 0) { fclose($fp); return ['ok' => false, 'error' => 'Message not accepted']; }
        $write('QUIT');
        fclose($fp);
        return ['ok' => true];
    }
}
