<?php

namespace App\Http\Controllers;

use App\Models\Grpo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GrpoIndexController extends Controller
{
    public $include_projects = ['017C', '021C', '022C', '023C', '025C', 'APS'];

    public function index()
    {
        $projects = $this->include_projects;
        $grpos = [];

        foreach ($projects as $project) {
            $po_sent_amount = $this->getPoSentAmount($project);
            $grpo_amount = $this->getGrpoAmount($project);

            $percentage = ($po_sent_amount == 0 || $grpo_amount == 0) ? 0 : $grpo_amount / $po_sent_amount;

            $grpos[] = [
                'project' => $project,
                'grpo_amount' => $grpo_amount,
                'po_sent_amount' => $po_sent_amount,
                'percentage' => $percentage
            ];
        }

        $total_grpo_amount = array_sum(array_column($grpos, 'grpo_amount'));
        $total_po_sent_amount = array_sum(array_column($grpos, 'po_sent_amount'));
        $total_percentage = ($total_grpo_amount == 0 || $total_po_sent_amount == 0) ? 0 : $total_grpo_amount / $total_po_sent_amount;

        return [
            'grpo_daily' => $grpos,
            'total_grpo_amount' => $total_grpo_amount,
            'total_po_sent_amount' => $total_po_sent_amount,
            'total_percentage' => $total_percentage
        ];
    }

    private function getPoSentAmount($project)
    {
        return app(CapexController::class)->po_sent_amount()
            ->where('project_code', $project)
            ->sum('item_amount');
    }

    private function getGrpoAmount($project)
    {
        return $this->buildGrpoQuery()
            ->where('project_code', $project)
            ->sum('item_amount');
    }

    private function buildGrpoQuery()
    {
        $date = Carbon::now()->subDay();
        $incl_deptcode = ['40', '50', '60', '140', '200'];
        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%'];

        $query = Grpo::whereMonth('po_delivery_date', $date->month)
            ->whereMonth('grpo_date', $date->month)
            ->where('po_delivery_status', 'Delivered')
            ->whereIn('dept_code', $incl_deptcode);

        foreach ($excl_itemcode as $e) {
            $query->where('item_code', 'not like', $e);
        }

        return $query;
    }
}
