<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapexController extends Controller
{
    public $include_projects = ['017C', '021C', '022C', '023C', '025C', 'APS'];

    public function index()
    {
        $capex = $this->capex_daily();
        return $capex;
    }

    public function capex_daily()
    {
        $date = Carbon::now()->subDay();

        foreach ($this->include_projects as $project) {
            $budget = $this->plant_budget()->where('project_code', $project)
                ->where('budget_type_id', 8)
                ->sum('amount');

            $po_sent_amount = DB::table('powithetas')
                ->whereYear('po_delivery_date', $date)
                ->whereMonth('po_delivery_date', $date)
                ->where('po_status', '!=', 'Cancelled')
                ->where('po_delivery_status', 'Delivered')
                ->where('project_code', $project)
                ->where('budget_type', 'CPX')
                ->sum('item_amount');
            // $po_sent_amount = $this->po_sent_amount()->where('project_code', $project)
            //                 ->where('budget_type', 'CPX')
            //                 ->sum('item_amount');

            // percentage of capex budget vs sent amount, if budget or sent amount is null
            if ($budget == 0 || $po_sent_amount == 0) {
                $percentage = 0;
            } else {
                $percentage = $po_sent_amount / $budget;
            }

            $capex[] = [
                'project' => $project,
                'budget' => $budget,
                'sent_amount' => $po_sent_amount,
                'percentage' => $percentage
            ];
        };

        $total_budget = array_sum(array_column($capex, 'budget'));
        $total_sent = array_sum(array_column($capex, 'sent_amount'));

        // percentage of capex budget vs sent amount, if budget or sent amount is null
        if ($total_budget == 0 || $total_sent == 0) {
            $percentage = 0;
        } else {
            $percentage = $total_sent / $total_budget;
        }

        $result = [
            'capex' => $capex,
            'budget_total' => $total_budget,
            'sent_total' => $total_sent,
            'percentage' => $percentage
        ];

        return $result;
    }

    public function reguler_daily()
    {
        $projects = $this->include_projects;

        foreach ($projects as $project) {
            $budget = $this->plant_budget()->where('project_code', $project)
                ->where('budget_type_id', 2)
                ->sum('amount');

            $pos = $this->po_sent_amount()->where('project_code', $project);

            $po_sent_amount = $pos->where(function ($query) {
                $query->whereNull('budget_type')
                    ->orWhere('budget_type', 'REG');
            })->sum('item_amount');

            // percentage of capex budget vs sent amount, if budget or sent amount is null
            if ($budget == 0 || $po_sent_amount == 0) {
                $percentage = 0;
            } else {
                $percentage = $po_sent_amount / $budget;
            }

            $reguler[] = [
                'project' => $project,
                'budget' => $budget,
                'sent_amount' => $po_sent_amount,
                'percentage' => $percentage
            ];
        };

        $total_budget = array_sum(array_column($reguler, 'budget'));
        $total_sent = array_sum(array_column($reguler, 'sent_amount'));

        // percentage of capex budget vs sent amount, if budget or sent amount is null
        if ($total_budget == 0 || $total_sent == 0) {
            $percentage = 0;
        } else {
            $percentage = $total_sent / $total_budget;
        }

        $result = [
            'reguler' => $reguler,
            'budget_total' => $total_budget,
            'sent_total' => $total_sent,
            'percentage' => $percentage
        ];

        return $result;
    }

    public function plant_budget()
    {
        $date = Carbon::now()->subDay();
        return Budget::select('project_code', 'amount', 'budget_type_id')
            ->whereYear('date', $date)
            ->whereMonth('date', $date);
    }

    public function po_sent_amount()
    {
        $date = Carbon::now()->subDay();
        $incl_deptcode = ['40', '50', '60', '140', '200'];

        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%'];
        $excl_itemcode_arr = array_map(function ($e) {
            return ['item_code', 'not like', $e];
        }, $excl_itemcode);

        $list = DB::table('powithetas')
            ->whereIn('dept_code', $incl_deptcode)
            ->where($excl_itemcode_arr)
            ->whereYear('po_delivery_date', $date)
            ->whereMonth('po_delivery_date', $date)
            ->where('po_status', '!=', 'Cancelled')
            ->where('po_delivery_status', 'Delivered');

        return $list;
    }
}
