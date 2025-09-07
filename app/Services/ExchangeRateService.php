<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.exchange.url'); // use EXCHANGE_API_URL
        $this->apiKey = config('services.exchange.key');
    }


    public function getAllRates(string $baseCurrency = 'GBP'): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/{$baseCurrency}");
               

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro na API de câmbio', ['body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error('Falha ao conectar na API de câmbio', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getRates(string $base): array
    {
        $response = Http::get("{$this->baseUrl}/{$base}");

        if ($response->failed()) {
            throw new \Exception("Erro ao consultar taxa de câmbio.");
        }

        return $response->json('rates');
    }

    public function convert(float $amount, string $from, string $to): array
    {
        $rates = $this->getRates($from);

        if (!isset($rates[$to])) {
            throw new \Exception("Taxa de conversão de {$from} para {$to} não encontrada.");
        }

        return [
            'amount' => round($amount * $rates[$to], 2),
            'currency' => $to,
            'rate' => $rates[$to],
        ];
    }
}