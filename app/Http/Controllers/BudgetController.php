<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BudgetController extends Controller
{
    public $include_projects = ['017C', '021C', '022C', '025C', 'APS', '023C'];

    public function index()
    {
        // $projects = Http::get('http://localhost:5000/projects')->json()['data'];
        $projects = ['017C', '021C', '022C', '023C', '025C', 'APS'];
        $budget_types = BudgetType::orderBy('display_name')->get();

        return view('budget.index', compact('projects', 'budget_types'));
    }

    public function show($id)
    {
        $budget = Budget::find($id);

        return view('budget.show', compact('budget'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required',
            'budget_type_id' => 'required',
            'project_code' => 'required',
            'amount' => 'required',
            // 'remarks' => 'string',
        ]);

        // return $test;
        // die;

        Budget::create([
            'date' => $request->date . '-01',
            'budget_type_id' => $request->budget_type_id,
            'project_code' => $request->project_code,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('budget.index')->with('success', 'Budget has been added');
    }

    public function edit($id)
    {
        $budget = Budget::find($id);
        // $projects = Http::get('http://localhost:5000/projects')->json()['data'];
        $projects = ['017C', '021C', '022C', '023C', '025C', 'APS'];
        $budget_types = BudgetType::orderBy('display_name')->get();

        return view('budget.edit', compact('budget', 'projects', 'budget_types'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date'              => 'required',
            'budget_type_id'    => 'required',
            'project_code'      => 'required',
            'amount'            => 'required',
            'remarks'           => 'string',
        ]);

        $budget = Budget::find($id);
        $budget->date = $request->date . '-01';
        $budget->budget_type_id = $request->budget_type_id;
        $budget->project_code = $request->project_code;
        $budget->amount = $request->amount;
        $budget->remarks = $request->remarks;
        $budget->save();

        return redirect()->route('budget.index')->with('success', 'Budget has been updated');
    }

    public function destroy($id)
    {
        $budget = Budget::findOrFail($id);
        $budget->delete();

        return redirect()->route('budget.index')->with('success', 'Budget has been deleted');
    }

    public function data()
    {
        $list = Budget::orderBy('date', 'desc')
            ->orderBy('budget_type_id', 'asc')
            ->get();

        return datatables()->of($list)
            ->editColumn('date', function ($list) {
                return date('F Y', strtotime($list->date));
            })
            ->editColumn('amount', function ($list) {
                return number_format($list->amount, 0);
            })
            ->editColumn('budget_type', function ($list) {
                return $list->budget_type->display_name;
            })
            ->addIndexColumn()
            ->addColumn('action', 'budget.action')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function getPlantBudgetOfMonth($search_date)
    {
        $month = substr($search_date, 5, 2);
        $year = substr($search_date, 0, 4);

        $reguler = Budget::select('budget_type_id', 'project_code', 'date', 'amount')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('budget_type_id', 2)
            ->whereIn('project_code', $this->include_projects)
            ->get();

        $capex = Budget::select('budget_type_id', 'project_code', 'date', 'amount')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('budget_type_id', 8)
            ->whereIn('project_code', $this->include_projects)
            ->get();

        return [
            'regulers' => $reguler,
            'capexs' => $capex
        ];
    }

    public function copy_budget(Request $request)
    {
        $request->validate([
            'from_month' => 'required', // cannot same with dest_month
            'to_month' => 'required',
        ]);

        if ($request->from_month == $request->to_month) {
            return redirect()->route('budget.index')->with('error', 'Cannot copy budget to same month');
        }

        $original_month = $request->from_month . '-01';
        $dest_month = $request->to_month . '-01';

        $budget_data = $this->getPlantBudgetOfMonth($original_month);

        foreach ($budget_data['regulers'] as $budget) {
            Budget::create([
                'date' => $dest_month,
                'budget_type_id' => 2,
                'project_code' => $budget->project_code,
                'amount' => $budget->amount,
            ]);
        }

        foreach ($budget_data['capexs'] as $budget) {
            Budget::create([
                'date' => $dest_month,
                'budget_type_id' => 8,
                'project_code' => $budget->project_code,
                'amount' => $budget->amount,
            ]);
        }

        return redirect()->route('budget.index')->with('success', 'Budget has been copied');
    }
}
