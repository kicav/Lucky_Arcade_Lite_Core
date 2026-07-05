<?php

namespace App\Services;

use InvalidArgumentException;

final class ProvablyFairService
{
    public function hashServerSeed(string $serverSeed): string
    {
        return hash('sha256', $serverSeed);
    }

    public function digest(
        string $serverSeed,
        string $clientSeed,
        int $nonce,
        int $cursor = 0,
    ): string {
        $message = $clientSeed.':'.$nonce.':'.$cursor;

        return hash_hmac('sha256', $message, $serverSeed);
    }

    public function uniformInt(
        string $serverSeed,
        string $clientSeed,
        int $nonce,
        int $min,
        int $max,
    ): int {
        if ($min > $max) {
            throw new InvalidArgumentException('Minimum must not exceed maximum.');
        }

        $range = $max - $min + 1;
        $maxUint32 = 0xFFFFFFFF;
        $limit = $maxUint32 - (($maxUint32 + 1) % $range);

        for ($cursor = 0; $cursor < 1024; $cursor++) {
            $digest = $this->digest($serverSeed, $clientSeed, $nonce, $cursor);
            $value = unpack('N', hex2bin(substr($digest, 0, 8)))[1];

            if ($value <= $limit) {
                return $min + ($value % $range);
            }
        }

        throw new \RuntimeException('Unable to derive an unbiased random integer.');
    }
}
