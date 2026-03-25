<?php

namespace App\Http\Controllers;

use App\Models\PowithetaSyncHistory;
use App\Services\PowithetaScheduleSettings;
use Illuminate\Http\Request;

class PowithetaScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (! $request->user() || ! $request->user()->hasRole('superadmin')) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function edit()
    {
        $config = PowithetaScheduleSettings::get();
        $times = $config['sync_times'] ?? PowithetaScheduleSettings::defaultConfig()['sync_times'];
        $histories = PowithetaSyncHistory::query()
            ->with('user:id,name')
            ->orderByDesc('started_at')
            ->limit(40)
            ->get();

        return view('admin.powitheta-schedule', [
            'enabled' => $config['enabled'] ?? true,
            'sync_time_1' => $times[0] ?? '06:00',
            'sync_time_2' => $times[1] ?? '18:00',
            'sap_date_mode' => $config['sap_date_mode'] ?? 'current_year',
            'sap_custom_start' => $config['sap_custom_start'] ?? '',
            'sap_custom_end' => $config['sap_custom_end'] ?? '',
            'histories' => $histories,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'sync_time_1' => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'sync_time_2' => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'sap_date_mode' => 'required|in:current_year,custom',
            'sap_custom_start' => 'nullable|required_if:sap_date_mode,custom|date',
            'sap_custom_end' => 'nullable|required_if:sap_date_mode,custom|date|after_or_equal:sap_custom_start',
        ]);

        PowithetaScheduleSettings::save([
            'enabled' => $request->boolean('enabled'),
            'sync_times' => [
                $validated['sync_time_1'],
                $validated['sync_time_2'],
            ],
            'sap_date_mode' => $validated['sap_date_mode'],
            'sap_custom_start' => $validated['sap_date_mode'] === 'custom'
                ? ($validated['sap_custom_start'] ?? null)
                : null,
            'sap_custom_end' => $validated['sap_date_mode'] === 'custom'
                ? ($validated['sap_custom_end'] ?? null)
                : null,
        ]);

        return redirect()->route('admin.powitheta-schedule.edit')->with('success', 'POWITHETA sync schedule saved.');
    }
}
