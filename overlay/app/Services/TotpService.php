<?php

namespace App\Services;

use App\Models\User;
use InvalidArgumentException;

class TotpService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes(max(10, $bytes)));
    }

    public function currentCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $counter = intdiv($timestamp, 30);
        $key = $this->base32Decode($secret);
        $binaryCounter = pack('N2', 0, $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $value = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad((string) ($value % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    public function verify(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';
        if (strlen($code) !== 6) {
            return false;
        }

        $timestamp ??= time();
        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->currentCode($secret, $timestamp + ($offset * 30)), $code)) {
                return true;
            }
        }

        return false;
    }

    public function otpAuthUri(User $user, string $secret): string
    {
        $issuer = rawurlencode((string) config('app.name', 'Lucky Arcade'));
        $label = rawurlencode(config('app.name', 'Lucky Arcade').':'.$user->email);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /** @return array{plain: array<int, string>, hashed: array<int, string>} */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $plain = [];
        $hashed = [];

        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(substr(bin2hex(random_bytes(6)), 0, 4).'-'.substr(bin2hex(random_bytes(6)), 4, 6));
            $plain[] = $code;
            $hashed[] = password_hash($code, PASSWORD_DEFAULT);
        }

        return compact('plain', 'hashed');
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        foreach ($codes as $index => $hash) {
            if (password_verify(strtoupper(trim($code)), $hash)) {
                unset($codes[$index]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();
                return true;
            }
        }

        return false;
    }

    private function base32Encode(string $data): string
    {
        $bits = '';
        foreach (str_split($data) as $character) {
            $bits .= str_pad(decbin(ord($character)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $encoded;
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret) ?? '');
        if ($secret === '') {
            throw new InvalidArgumentException('Invalid TOTP secret.');
        }

        $bits = '';
        foreach (str_split($secret) as $character) {
            $position = strpos(self::ALPHABET, $character);
            if ($position === false) {
                throw new InvalidArgumentException('Invalid TOTP secret.');
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }

        return $decoded;
    }
}
