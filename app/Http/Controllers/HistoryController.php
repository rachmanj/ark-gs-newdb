<?php

namespace App\Http\Controllers;

use App\Models\History;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HistoryController extends Controller
{
    public function index()
    {
        $projects = ['017C', '021C', '022C', '023C', '025C', 'APS'];

        return view('history.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated_data = $request->validate([
            'date' => 'required',
            'periode' => 'required',
            'gs_type' => 'required',
            'project_code' => 'required',
            'amount' => 'required',
        ]);

        History::create(array_merge($validated_data, ['remarks' => $request->remarks]));

        return redirect()->route('history.index')->with('success', 'History created successfully.');
    }

    public function edit($id)
    {
        $history = History::findOrFail($id);
        // $projects = Http::get('http://localhost:5000/projects')->json()['data'];
        $projects = ['017C', '021C', '022C', '023C', '025C', 'APS'];

        return view('history.edit', compact('history', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $validated_data = $request->validate([
            'date' => 'required',
            'periode' => 'required',
            'gs_type' => 'required',
            'project_code' => 'required',
            'amount' => 'required',
        ]);

        History::findOrFail($id)->update(array_merge($validated_data, ['remarks' => $request->remarks]));

        return redirect()->route('history.index')->with('success', 'History updated successfully.');
    }

    public function destroy($id)
    {
        History::findOrFail($id)->delete();

        return redirect()->route('history.index')->with('success', 'History deleted successfully.');
    }

    public function data()
    {
        $list = History::orderBy('periode', 'asc')
            ->orderBy('date', 'desc')
            ->orderBy('gs_type', 'asc')
            ->orderBy('project_code', 'asc')
            ->get();

        return datatables()->of($list)
            ->editColumn('date', function ($list) {
                if ($list->periode === 'monthly') {
                    return date('F Y', strtotime($list->date));
                } else {
                    return date('Y', strtotime($list->date));
                }
            })
            ->editColumn('amount', function ($list) {
                return number_format($list->amount, 0);
            })
            ->addIndexColumn()
            ->addColumn('action', 'history.action')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function generate_monthly(Request $request)
    {
        $request->validate([
            'capture_date' => 'required',
        ]);

        $general_data = app(DashboardDailyController::class)->getDailyData();

        $capex_data = $general_data['capex_daily']['capex'];

        // YYYYMMDD
        $batch_no = Carbon::parse($request->capture_date)->format('Ymd');

        foreach ($capex_data as $capex) {
            History::create([
                'date' => $request->capture_date,
                'periode' => 'monthly',
                'gs_type' => 'capex',
                'project_code' => $capex['project'],
                'amount' => $capex['sent_amount'],
                'remarks' => 'BATCH ' . $batch_no
            ]);
        }

        $reguler_data = $general_data['reguler_daily']['reguler'];

        foreach ($reguler_data as $reguler) {
            History::create([
                'date' => $request->capture_date,
                'periode' => 'monthly',
                'gs_type' => 'po_sent',
                'project_code' => $reguler['project'],
                'amount' => $reguler['sent_amount'],
                'remarks' => 'BATCH ' . $batch_no
            ]);
        }

        $grpo_data = $general_data['grpo_daily']['grpo_daily'];

        foreach ($grpo_data as $grpo) {
            History::create([
                'date' => $request->capture_date,
                'periode' => 'monthly',
                'gs_type' => 'grpo_amount',
                'project_code' => $grpo['project'],
                'amount' => $grpo['grpo_amount'],
                'remarks' => 'BATCH ' . $batch_no
            ]);
        }

        $npi_data = $general_data['npi_daily']['npi'];

        foreach ($npi_data as $incoming) {
            History::create([
                'date' => $request->capture_date,
                'periode' => 'monthly',
                'gs_type' => 'incoming_qty',
                'project_code' => $incoming['project'],
                'amount' => $incoming['incoming_qty'],
                'remarks' => 'BATCH ' . $batch_no
            ]);
        }

        foreach ($npi_data as $outgoing) {
            History::create([
                'date' => $request->capture_date,
                'periode' => 'monthly',
                'gs_type' => 'outgoing_qty',
                'project_code' => $outgoing['project'],
                'amount' => $outgoing['outgoing_qty'],
                'remarks' => 'BATCH ' . $batch_no
            ]);
        }

        return redirect()->route('history.index')->with('success', 'History generate successfully.');
    }
}
