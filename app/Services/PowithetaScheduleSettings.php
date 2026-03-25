<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class PowithetaScheduleSettings
{
    private const FILENAME = 'powitheta_schedule.json';

    public static function defaultConfig(): array
    {
        return [
            'enabled' => true,
            'sync_times' => ['06:00', '18:00'],
            'sap_date_mode' => 'current_year',
            'sap_custom_start' => null,
            'sap_custom_end' => null,
            'staging_modules_enabled' => true,
        ];
    }

    public static function path(): string
    {
        return storage_path('app/'.self::FILENAME);
    }

    public static function get(): array
    {
        $path = self::path();
        if (! File::exists($path)) {
            return self::defaultConfig();
        }
        $data = json_decode(File::get($path), true);
        if (! is_array($data)) {
            return self::defaultConfig();
        }

        return array_merge(self::defaultConfig(), $data);
    }

    public static function save(array $config): void
    {
        $defaults = self::defaultConfig();
        $merged = [
            'enabled' => isset($config['enabled']) ? (bool) $config['enabled'] : $defaults['enabled'],
            'sync_times' => isset($config['sync_times']) && is_array($config['sync_times'])
                ? array_values(array_filter($config['sync_times']))
                : $defaults['sync_times'],
            'sap_date_mode' => isset($config['sap_date_mode']) && $config['sap_date_mode'] === 'custom'
                ? 'custom'
                : 'current_year',
            'sap_custom_start' => isset($config['sap_custom_start']) && $config['sap_custom_start'] !== ''
                ? $config['sap_custom_start']
                : null,
            'sap_custom_end' => isset($config['sap_custom_end']) && $config['sap_custom_end'] !== ''
                ? $config['sap_custom_end']
                : null,
            'staging_modules_enabled' => isset($config['staging_modules_enabled'])
                ? (bool) $config['staging_modules_enabled']
                : $defaults['staging_modules_enabled'],
        ];
        File::put(self::path(), json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Date inputs for scheduled artisan sync only (merged into Request before sync_from_sap).
     * Manual UI sync ignores these and uses modal values only.
     */
    public static function getScheduledSapDatePayload(): array
    {
        $c = self::get();
        $mode = $c['sap_date_mode'] ?? 'current_year';
        if ($mode === 'custom') {
            return [
                'start_date' => $c['sap_custom_start'] ?? null,
                'end_date' => $c['sap_custom_end'] ?? null,
            ];
        }

        return [
            'start_date' => null,
            'end_date' => null,
        ];
    }

    public static function normalizedSyncTimes(): array
    {
        $times = self::get()['sync_times'] ?? [];
        $out = [];
        foreach ($times as $t) {
            if (is_string($t) && preg_match('/^\d{1,2}:\d{2}$/', $t)) {
                $parts = explode(':', $t);
                $out[] = sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
            }
        }

        return count($out) ? $out : self::defaultConfig()['sync_times'];
    }
}
