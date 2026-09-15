<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DOMDocument;
use Exception;

class CoalPriceController extends Controller
{
    // Cache TTL in minutes
    protected $cacheTtl = 60; // 1 hour

    public function getCoalPrices()
    {
        try {
            // Try to get from cache first
            if (Cache::has('coal_prices')) {
                return response()->json(Cache::get('coal_prices'));
            }

            // Get Indonesia Coal Price Data
            $indonesiaCoalPrice = $this->getIndonesiaCoalPrice();

            // Get Newcastle Coal Price Data
            $newcastleCoalPrice = $this->getNewcastleCoalPrice();

            // Get USD/IDR exchange rate
            $exchangeRate = $this->getExchangeRate();

            $responseData = [
                'status' => 'success',
                'data' => [
                    'indonesia' => $indonesiaCoalPrice,
                    'newcastle' => $newcastleCoalPrice,
                    'exchange_rate' => $exchangeRate
                ]
            ];

            // Cache the response
            Cache::put('coal_prices', $responseData, $this->cacheTtl);

            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch coal prices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the latest Indonesia coal price from the government website
     */
    protected function getIndonesiaCoalPrice()
    {
        try {
            $url = 'https://www.minerba.esdm.go.id/harga_acuan';

            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                Log::warning('Failed to fetch Indonesia coal price: HTTP request failed with status ' . $response->status());
                return null;
            }

            $html = $response->body();

            $dom = new DOMDocument();
            @$dom->loadHTML($html);

            $tables = $dom->getElementsByTagName('table');

            if ($tables->length === 0) {
                Log::warning('Failed to fetch Indonesia coal price: price table not found in HTML response');
                return null;
            }

            $table = $tables->item(0);
            $rows = $table->getElementsByTagName('tr');

            $price = null;
            $date = null;
            $cells = null;

            foreach ($rows as $row) {
                $cells = $row->getElementsByTagName('td');
                if ($cells->length > 0) {
                    $firstCell = $cells->item(0);
                    if ($firstCell && strpos($firstCell->nodeValue, 'Batubara (USD/ton)') !== false) {
                        $price = $cells->item($cells->length - 1)->nodeValue;
                        $date = date('Y-m-d');
                        break;
                    }
                }
            }

            if ($price === null) {
                Log::warning('Failed to fetch Indonesia coal price: Batubara (USD/ton) price not found in table');
                return null;
            }

            $previousPrice = null;
            if ($cells->length > 2) {
                $previousPrice = $cells->item($cells->length - 2)->nodeValue;
            }

            $change = 0;
            if ($previousPrice !== null) {
                $change = round($price - $previousPrice, 2);
            }

            return [
                'price' => (float) $price,
                'change' => $change,
                'unit' => 'USD/ton',
                'date' => $date
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Indonesia coal price: ' . $e->getMessage());
            return null;
        }
    }

    protected function getNewcastleCoalPrice()
    {
        Log::warning('Failed to fetch Newcastle coal price: no reliable data source available');
        return null;
    }

    /**
     * Get USD/IDR exchange rate
     */
    protected function getExchangeRate()
    {
        try {
            $response = Http::get('https://api.exchangerate-api.com/v4/latest/USD');

            if (!$response->successful()) {
                Log::warning('Failed to fetch exchange rate: HTTP request failed with status ' . $response->status());
                return null;
            }

            $data = $response->json();
            if (!isset($data['rates']['IDR'])) {
                Log::warning('Failed to fetch exchange rate: rates.IDR not found in API response');
                return null;
            }

            return [
                'rate' => $data['rates']['IDR'],
                'date' => $data['date'],
                'last_updated' => $data['time_last_updated']
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to fetch exchange rate: ' . $e->getMessage());
            return null;
        }
    }
}
