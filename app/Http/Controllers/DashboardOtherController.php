<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Services\PoExclusionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardOtherController extends Controller
{
    public function index()
    {
        $projects = ['011C', '017C', '021C', '022C', '023C', 'APS'];
         
        return view('dashboard.other.index', [
            'projects' => $projects,
            'months' => $this->get_month(),
            'static_posent' => $this->static_posent()->get(),
            'dynamic_posent' => $this->dynamic_posent()->get(),
        ]);
    }

    public function static_posent()
    {
        $date = Carbon::now();
        
        $static_posent = History::select('project_code', 'amount')
            ->selectRaw('substring(date, 6, 2) as month')
            ->whereYear('date', $date)
            ->where('periode', 'monthly')
            ->where('gs_type', 'po_sent');

        return $static_posent;
    }

    public function dynamic_posent()
    {
        $incl_deptcode = ['40', '50', '60', '140'];

        // $excl_itemcode = ['CO%', 'EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%'];
        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%'];
        foreach ($excl_itemcode as $e) {
            $excl_itemcode_arr[] = ['item_code', 'not like', $e];
        };

        $dynamic_posent = DB::table('powithetas')
            ->whereIn('dept_code', $incl_deptcode)
            ->where($excl_itemcode_arr)
            ->selectRaw('project_code, item_amount, substring(po_delivery_date, 6, 2) as month, po_status, po_delivery_status')
            ->where('po_status', '!=', 'Cancelled')
            ->where('po_delivery_status', 'Delivered');
        app(PoExclusionService::class)->applyExclusion($dynamic_posent);

        return $dynamic_posent;
    }

    public function get_month()
    {
        return History::selectRaw('substring(date, 6, 2) as month')
                ->whereYear('date', Carbon::now())
                ->where('periode', 'monthly')
                ->distinct('month')
                ->get();
    }

    public function grpo()
    {
        $projects = ['011C', '017C', '021C', '022C', '023C', 'APS'];

        return view('dashboard.other.index_grpo', [
            'projects' => $projects,
            'months' => $this->get_month(),
            'dynamic_posent' => $this->dynamic_posent()->get(),
        ]);
    }

    public function test()
    {
        $projects = ['011C', '017C', '021C', '022C', '023C', 'APS'];
        $hasil =  $this->dynamic_posent()
        ->where('project_code', '011C')
        ->get()
        ->where('month', 12);

        return $hasil;
    }
}
