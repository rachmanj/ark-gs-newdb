<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Grpo;
use App\Models\Incoming;
use App\Models\Migi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YearlyIndexController extends Controller
{
    public $include_projects = ['017C', '021C', '022C', '025C', 'APS', '023C'];
    private $included_dept_codes = ['40', '50', '60', '140', '200'];
    private $excluded_item_codes = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%'];
    private $excluded_item_codes_with_co; // Will include 'CO%' for specific methods

    public function __construct()
    {
        $this->excluded_item_codes_with_co = array_merge(['CO%'], $this->excluded_item_codes);
    }

    public function periode()
    {
        return Carbon::now()->year;
    }

    public function index()
    {
        $yearly = [
            'reguler' => $this->reguler_yearly(),
            'capex' => $this->capex_yearly(),
            'grpo' => $this->grpo_index(),
            'npi' => $this->npi_index(),
        ];

        return $yearly;
    }

    public function reguler_yearly()
    {
        foreach ($this->include_projects as $project) {
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
            'reguler_yearly' => $reguler,
            'budget_total' => $total_budget,
            'sent_total' => $total_sent,
            'percentage' => $percentage
        ];

        return $result;
    }

    public function capex_yearly()
    {
        // $date = Carbon::now()->subYear();

        foreach ($this->include_projects as $project) {
            $budget = $this->plant_budget()->where('project_code', $project)
                ->where('budget_type_id', 8) // capex
                ->sum('amount');

            $po_sent_amount = DB::table('powithetas')
                ->whereYear('po_delivery_date', $this->periode())
                ->where('po_status', '!=', 'Cancelled')
                ->where('po_delivery_status', 'Delivered')
                ->where('project_code', $project)
                ->where('budget_type', 'CPX')
                ->sum('item_amount');

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

    public function plant_budget()
    {
        return Budget::select('project_code', 'amount', 'budget_type_id')
            ->whereYear('date', $this->periode());
    }

    public function po_sent_amount()
    {
        $excl_itemcode_arr = $this->getExcludedItemCodesArray($this->excluded_item_codes);

        return DB::table('powithetas')
            ->whereIn('dept_code', $this->included_dept_codes)
            ->where($excl_itemcode_arr)
            ->whereYear('po_delivery_date', $this->periode())
            ->where('po_status', '!=', 'Cancelled')
            ->where('po_delivery_status', 'Delivered');
    }


    // GRPO INDEX
    public function grpo_index()
    {
        foreach ($this->include_projects as $project) {
            $po_sent_amount = $this->po_sent_amount()->where('project_code', $project)
                ->sum('item_amount');

            $grpo_amount = $this->grpo_amount()->where('project_code', $project)
                ->sum('item_amount');

            // percentage of capex budget vs sent amount, if budget or sent amount is null
            if ($po_sent_amount == 0 || $grpo_amount == 0) {
                $percentage = 0;
            } else {
                $percentage = $grpo_amount / $po_sent_amount;
            }

            $grpos[] = [
                'project' => $project,
                'grpo_amount' => $grpo_amount,
                'po_sent_amount' => $po_sent_amount,
                'percentage' => $percentage
            ];
        };

        $total_grpo_amount = array_sum(array_column($grpos, 'grpo_amount'));
        $total_po_sent_amount = array_sum(array_column($grpos, 'po_sent_amount'));

        // percentage of capex budget vs sent amount, if budget or sent amount is null
        if ($total_grpo_amount == 0 || $total_po_sent_amount == 0) {
            $total_percentage = 0;
        } else {
            $total_percentage = $total_grpo_amount / $total_po_sent_amount;
        }

        $result = [
            'grpo_yearly' => $grpos,
            'total_grpo_amount' => $total_grpo_amount,
            'total_po_sent_amount' => $total_po_sent_amount,
            'total_percentage' => $total_percentage
        ];

        return $result;
    }

    public function grpo_amount()
    {
        $excl_itemcode_arr = $this->getExcludedItemCodesArray($this->excluded_item_codes);

        return Grpo::whereYear('po_delivery_date', $this->periode())
            ->where('po_delivery_status', 'Delivered')
            ->whereIn('project_code', $this->include_projects)
            ->whereIn('dept_code', $this->included_dept_codes)
            ->where($excl_itemcode_arr);
    }

    //NPI INDEX
    public function npi_index()
    {
        foreach ($this->include_projects as $project) {
            $incoming_qty = $this->incomings()->where('project_code', $project)
                ->sum('qty');

            $outgoing_qty = $this->outgoing()->where('project_code', $project)
                ->sum('qty');

            // percentage of incoming qty vs outgoing qty, if incoming qty or outgoing qty is null
            if ($incoming_qty == 0 || $outgoing_qty == 0) {
                $percentage = 0;
            } else {
                $percentage = $incoming_qty / $outgoing_qty;
            }


            $npi[] = [
                'project' => $project,
                'incoming_qty' => $incoming_qty,
                'outgoing_qty' => $outgoing_qty,
                'percentage' => $percentage
            ];
        };

        $total_incoming_qty = array_sum(array_column($npi, 'incoming_qty'));
        $total_outgoing_qty = array_sum(array_column($npi, 'outgoing_qty'));

        // percentage of total incoming qty vs total outgoing qty, if incoming qty or outgoing qty is null
        if ($total_incoming_qty == 0 || $total_outgoing_qty == 0) {
            $percentage = 0;
        } else {
            $percentage = $total_incoming_qty / $total_outgoing_qty;
        }

        $result = [
            'npi' => $npi,
            'total_incoming_qty' => $total_incoming_qty,
            'total_outgoing_qty' => $total_outgoing_qty,
            'total_percentage' => $percentage
        ];

        return $result;
    }

    public function incomings()
    {
        $excl_itemcode_arr = $this->getExcludedItemCodesArray($this->excluded_item_codes_with_co);

        return Incoming::whereYear('posting_date', $this->periode())
            ->whereIn('dept_code', $this->included_dept_codes)
            ->where($excl_itemcode_arr)
            ->get();
    }

    public function outgoing()
    {
        $excl_itemcode_arr = $this->getExcludedItemCodesArray($this->excluded_item_codes_with_co);

        return Migi::whereYear('posting_date', $this->periode())
            ->whereIn('dept_code', $this->included_dept_codes)
            ->where($excl_itemcode_arr)
            ->get();
    }

    private function getExcludedItemCodesArray($codes)
    {
        return array_map(function ($code) {
            return ['item_code', 'not like', $code];
        }, $codes);
    }
}
