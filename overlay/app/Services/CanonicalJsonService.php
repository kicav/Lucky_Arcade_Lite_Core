<?php

namespace App\Services;

final class CanonicalJsonService
{
    /** @param array<string, mixed> $value */
    public function encode(array $value): string
    {
        return json_encode(
            $this->normalize($value),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    /** @param array<string, mixed> $value */
    public function checksum(array $value): string
    {
        return hash('sha256', $this->encode($value));
    }

    private function normalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->normalize($item), $value);
        }

        ksort($value, SORT_STRING);

        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }
}
