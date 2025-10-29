<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Budget;
use Illuminate\Http\Request;

class MonthlyHistoryController extends Controller
{
    public $include_projects = ['017C', '021C', '022C', '025C', '026C', 'APS', '023C'];

    public function index($date)
    {
        $montly = [
            'date' => $date,
            'reguler' => $this->reguler_history_monthly($date),
            'capex' => $this->capex_history_monthly($date),
            'grpo' => $this->grpo_history_monthly($date),
            'npi' => $this->npi_history_monthly($date),
        ];

        return $montly;
    }

    public function reguler_history_monthly($date)
    {
        $month = substr($date, 5, 2);
        $year = substr($date, 0, 4);

        foreach ($this->include_projects as $project) {
            $budget =  $this->plant_budget_history_monthly($date, $project)
                ->where('budget_type_id', 2)
                ->first();

            if ($budget) {
                $budget = $budget->amount;
            } else {
                $budget = 0;
            }

            $po_sent_amount = History::select('amount', 'project_code')
                ->where('periode', 'monthly')
                ->where('project_code', $project)
                ->where('gs_type', 'po_sent')
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
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
            'reguler_monthly' => $reguler,
            'budget_total' => $total_budget,
            'sent_total' => $total_sent,
            'percentage' => $percentage
        ];


        return $result;
    }

    public function capex_history_monthly($date)
    {
        $month = substr($date, 5, 2);
        $year = substr($date, 0, 4);

        foreach ($this->include_projects as $project) {
            $budget =  $this->plant_budget_history_monthly($date, $project)
                ->where('budget_type_id', 8)
                ->first();

            if ($budget) {
                $budget = $budget->amount;
            } else {
                $budget = 0;
            }

            $po_sent_amount = History::select('amount', 'project_code')
                ->where('periode', 'monthly')
                ->where('project_code', $project)
                ->where('gs_type', 'capex')
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
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

    public function plant_budget_history_monthly($date, $project)
    {
        $month = substr($date, 5, 2);
        $year = substr($date, 0, 4);

        return Budget::select('project_code', 'amount', 'budget_type_id')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('project_code', $project);
    }

    public function grpo_history_monthly($date)
    {
        $month = substr($date, 5, 2);
        $year = substr($date, 0, 4);

        foreach ($this->include_projects as $project) {
            $gs_types_includes = ['po_sent', 'capex'];
            $po_sent_amount = History::select('amount', 'project_code')
                ->where('periode', 'monthly')
                ->where('project_code', $project)
                ->whereIn('gs_type', $gs_types_includes)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $grpo_amount = History::where('periode', 'monthly')
                ->where('project_code', $project)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
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
            'grpo' => $grpos,
            'total_grpo_amount' => $total_grpo_amount,
            'total_po_sent_amount' => $total_po_sent_amount,
            'total_percentage' => $total_percentage
        ];

        return $result;
    }

    public function npi_history_monthly($date)
    {
        $month = substr($date, 5, 2);
        $year = substr($date, 0, 4);

        foreach ($this->include_projects as $project) {
            $incoming_qty = History::where('gs_type', 'incoming_qty')
                ->where('periode', 'monthly')
                ->where('project_code', $project)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->first();

            if ($incoming_qty) {
                $incoming_qty = $incoming_qty->amount;
            } else {
                $incoming_qty = 0;
            }

            $outgoing_qty = History::where('gs_type', 'outgoing_qty')
                ->where('periode', 'monthly')
                ->where('project_code', $project)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
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
}
