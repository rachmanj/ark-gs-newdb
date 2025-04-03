<?php

namespace App\Http\Controllers;

use App\Models\Incoming;
use App\Models\Migi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NpiController extends Controller
{
    public $include_projects = ['017C', '021C', '022C', '023C', '025C', 'APS'];

    public function index()
    {
        $projects = $this->include_projects;
        $npi = [];

        foreach ($projects as $project) {
            $incoming_qty = $this->getQuantity(Incoming::class, $project);
            $outgoing_qty = $this->getQuantity(Migi::class, $project);

            $percentage = ($incoming_qty == 0 || $outgoing_qty == 0) ? 0 : $incoming_qty / $outgoing_qty;

            $npi[] = [
                'project' => $project,
                'incoming_qty' => $incoming_qty,
                'outgoing_qty' => $outgoing_qty,
                'percentage' => $percentage
            ];
        }

        $total_incoming_qty = array_sum(array_column($npi, 'incoming_qty'));
        $total_outgoing_qty = array_sum(array_column($npi, 'outgoing_qty'));
        $total_percentage = ($total_incoming_qty == 0 || $total_outgoing_qty == 0) ? 0 : $total_incoming_qty / $total_outgoing_qty;

        return [
            'npi' => $npi,
            'total_incoming_qty' => $total_incoming_qty,
            'total_outgoing_qty' => $total_outgoing_qty,
            'total_percentage' => $total_percentage
        ];
    }

    private function getQuantity($model, $project)
    {
        return $this->buildQuery($model)
            ->where('project_code', $project)
            ->sum('qty');
    }

    // public function incomings()
    // {
    //     return $this->getFilteredList(Incoming::class);
    // }

    // public function outgoing()
    // {
    //     return $this->getFilteredList(Migi::class);
    // }

    // private function getFilteredList($model)
    // {
    //     return $this->buildQuery($model)->get();
    // }

    private function buildQuery($model)
    {
        $date = Carbon::now()->subDay();
        $incl_deptcode = ['40', '50', '60', '140', '200'];
        $excl_itemcode = ['CO%', 'EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%'];

        $query = $model::whereMonth('posting_date', $date->month)
            ->whereYear('posting_date', $date->year)
            ->whereIn('dept_code', $incl_deptcode);

        foreach ($excl_itemcode as $e) {
            $query->where('item_code', 'not like', $e);
        }

        return $query;
    }
}
