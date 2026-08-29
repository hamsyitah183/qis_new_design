<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class PermitQrService
{
    public function createEncryptedPayload(string $permitNumber): string
    {
        $plain = json_encode([
            'permit_number' => $permitNumber,
            'ts' => now()->timestamp,
            'v' => 1,
        ], JSON_UNESCAPED_SLASHES);

        if ($plain === false) {
            throw new \RuntimeException('Unable to encode QR payload JSON.');
        }

        $key = $this->resolveQrKeyBytes();
        $iv = random_bytes(16);

        $cipherText = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipherText === false) {
            throw new \RuntimeException('OpenSSL encryption failed.');
        }

        $mac = hash_hmac('sha256', $iv . $cipherText, $key, true);

        $payload = json_encode([
            'iv' => base64_encode($iv),
            'ct' => base64_encode($cipherText),
            'mac' => base64_encode($mac),
        ], JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new \RuntimeException('Unable to encode encrypted QR wrapper.');
        }

        return 'QIS1:' . base64_encode($payload);
    }

    public function createQrDataUri(string $qrPayload, int $size = 300): string
    {
        // Build a PNG data URI so it can be embedded directly inside the PDF template.
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrPayload)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size($size)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        return $result->getDataUri();
    }

    public function decryptPayload(string $qrPayload): array
    {
        if (!str_starts_with($qrPayload, 'QIS1:')) {
            throw new \RuntimeException('Unsupported QR payload version.');
        }

        $decoded = json_decode(base64_decode(substr($qrPayload, 5)), true);
        if (!is_array($decoded) || !isset($decoded['iv'], $decoded['ct'], $decoded['mac'])) {
            throw new \RuntimeException('Malformed QR payload.');
        }

        $iv = base64_decode($decoded['iv'], true);
        $ct = base64_decode($decoded['ct'], true);
        $mac = base64_decode($decoded['mac'], true);

        if ($iv === false || $ct === false || $mac === false || strlen($iv) !== 16) {
            throw new \RuntimeException('Invalid QR payload encoding.');
        }

        $key = $this->resolveQrKeyBytes();
        $expectedMac = hash_hmac('sha256', $iv . $ct, $key, true);
        if (!hash_equals($expectedMac, $mac)) {
            throw new \RuntimeException('QR payload MAC mismatch.');
        }

        $plain = openssl_decrypt($ct, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($plain === false) {
            throw new \RuntimeException('QR payload decryption failed.');
        }

        $data = json_decode($plain, true);
        if (!is_array($data) || !isset($data['permit_number'])) {
            throw new \RuntimeException('Invalid QR payload contents.');
        }

        return $data;
    }

    private function resolveQrKeyBytes(): string
    {
        $secret = (string) config('services.qis.qr_key', env('QIS_QR_KEY', ''));
        if ($secret === '') {
            throw new \RuntimeException('QIS_QR_KEY is not configured.');
        }

        return hash('sha256', $secret, true);
    }
}
