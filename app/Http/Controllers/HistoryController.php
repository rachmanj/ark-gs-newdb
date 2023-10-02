<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HistoryController extends Controller
{
    public function index()
    {
        // $projects = Http::get('http://localhost:5000/projects')->json()['data'];
        $projects = ['011C', '017C', '021C', '022C', '023C', 'APS'];

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
        $projects = ['011C', '017C', '021C', '022C', '023C', 'APS'];

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
                if($list->periode === 'monthly') {
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
}
