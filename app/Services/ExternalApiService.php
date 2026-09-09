<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    private string $url;
    private bool $verifySsl;

    public function __construct()
    {
        $this->url = config('services.external_api.url') ?? 'https://ogienurdiana.com/career/ecc694ce4e7f6e45a5a7912cde9fe131';
        $this->verifySsl = (bool) config('services.external_api.verify_ssl', false);
    }

    /**
     * Mengambil data dari API eksternal dan mem-parsing text pipe-delimited menjadi array asosiatif.
     *
     * @return array<int, array<string, string>>
     */
    public function getData(): array
    {
        try {
            $client = Http::timeout(15);

            if (! $this->verifySsl) {
                $client = $client->withoutVerifying();
            }

            $response = $client->get($this->url);

            if ($response->failed()) {
                throw new \Exception(
                    'Failed to fetch external API: HTTP ' . $response->status()
                );
            }

            $raw = $response->json();

            // Jika response memiliki key 'DATA' bertipe string (berisi teks pipe-delimited)
            if (isset($raw['DATA']) && is_string($raw['DATA'])) {
                return $this->parsePipeDelimitedData($raw['DATA']);
            }

            // Jika response sudah berupa array list
            if (is_array($raw) && ! isset($raw['RC'])) {
                return $raw;
            }

            return [];
        } catch (\Throwable $e) {
            Log::error('ExternalApiService error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mem-parsing teks string berformat pipe-delimited (header YMD|NAMA|NIM).
     *
     * @return array<int, array<string, string>>
     */
    private function parsePipeDelimitedData(string $rawText): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($rawText));
        if (empty($lines)) {
            return [];
        }

        $headerLine = array_shift($lines);
        $headers = array_map('trim', explode('|', $headerLine));

        $parsed = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $columns = array_map('trim', explode('|', $line));
            if (count($columns) === count($headers)) {
                $parsed[] = array_combine($headers, $columns);
            }
        }

        return $parsed;
    }
}