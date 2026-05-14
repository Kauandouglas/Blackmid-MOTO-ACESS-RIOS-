<?php

namespace App\Services;

class EnvFileService
{
    public function set(array $values): void
    {
        $path = base_path('.env');
        $contents = file_exists($path) ? (string) file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            $encoded = $this->encode((string) $value);
            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, "{$key}={$encoded}", $contents) ?? $contents;
            } else {
                $contents = rtrim($contents).PHP_EOL."{$key}={$encoded}".PHP_EOL;
            }
        }

        file_put_contents($path, $contents);
    }

    private function encode(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'/', $value)) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }
}
