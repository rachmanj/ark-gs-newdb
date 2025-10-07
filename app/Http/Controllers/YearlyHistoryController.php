<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Budget;
use Illuminate\Http\Request;

class YearlyHistoryController extends Controller
{
    public $include_projects = ['017C', '021C', '022C', '023C', '025C', 'APS'];

    public function index($year)
    {
        $yearly = [
            'year' => $year,
            'reguler' => $this->reguler_history_yearly($year),
            'capex' => $this->capex_history_yearly($year),
            'grpo' => $this->grpo_history_yearly($year),
            'npi' => $this->npi_history_yearly($year),
        ];

        return $yearly;
    }

    public function reguler_history_yearly($year)
    {
        foreach ($this->include_projects as $project) {
            $budget =  $this->plant_budget_history_yearly($year)->where('project_code', $project)
                ->where('budget_type_id', 2)
                ->sum('amount');

            $po_sent_amount = History::select('amount', 'project_code')
                ->where('periode', 'yearly')
                ->where('project_code', $project)
                ->where('gs_type', 'po_sent')
                ->whereYear('date', $year)
                ->first();

            if ($po_sent_amount) {
                $po_sent_amount = $po_sent_amount->amount;
            } else {
                $po_sent_amount = 0;
            }

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
        }

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

    public function capex_history_yearly($year)
    {
        foreach ($this->include_projects as $project) {
            $budget =  $this->plant_budget_history_yearly($year)->where('project_code', $project)
                ->where('budget_type_id', 8)
                ->sum('amount');

            $po_sent_amount = History::select('amount', 'project_code')
                ->where('periode', 'yearly')
                ->where('project_code', $project)
                ->where('gs_type', 'capex')
                ->whereYear('date', $year)
                ->first();

            if ($po_sent_amount) {
                $po_sent_amount = $po_sent_amount->amount;
            } else {
                $po_sent_amount = 0;
            }

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
        }

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

    public function plant_budget_history_yearly($year)
    {
        return Budget::select('project_code', 'amount', 'budget_type_id')
            ->whereYear('date', $year);
    }

    public function grpo_history_yearly($year)
    {
        foreach ($this->include_projects as $project) {
            $gs_types_includes = ['po_sent', 'capex'];
            $po_sent_amount = History::select('amount', 'project_code')
                ->where('periode', 'yearly')
                ->where('project_code', $project)
                ->whereIn('gs_type', $gs_types_includes)
                ->whereYear('date', $year)
                ->sum('amount');

            $grpo_amount = History::where('periode', 'yearly')
                ->where('project_code', $project)
                ->whereYear('date', $year)
                ->where('gs_type', 'grpo_amount')
                ->first();

            if ($grpo_amount) {
                $grpo_amount = $grpo_amount->amount;
            } else {
                $grpo_amount = 0;
            }

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

    public function npi_history_yearly($year)
    {
        foreach ($this->include_projects as $project) {
            $incoming_qty = History::where('gs_type', 'incoming_qty')
                ->where('periode', 'yearly')
                ->where('project_code', $project)
                ->whereYear('date', $year)
                ->first();

            if ($incoming_qty) {
                $incoming_qty = $incoming_qty->amount;
            } else {
                $incoming_qty = 0;
            }

            $outgoing_qty = History::where('gs_type', 'outgoing_qty')
                ->where('periode', 'yearly')
                ->where('project_code', $project)
                ->whereYear('date', $year)
                ->first();

            if ($outgoing_qty) {
                $outgoing_qty = $outgoing_qty->amount;
            } else {
                $outgoing_qty = 0;
            }

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

    public function incoming_history_yearly()
    {
        $incoming = History::where('periode', 'yearly')
            ->where('gs_type', 'incoming_qty')
            ->get();

        return $incoming;
    }

    public function outgoing_history_yearly()
    {
        $outgoing = History::where('periode', 'yearly')
            ->where('gs_type', 'outgoing_qty')
            ->get();

        return $outgoing;
    }
}
