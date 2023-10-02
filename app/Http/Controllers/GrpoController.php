<?php

namespace App\Http\Controllers;

use App\Exports\GrpoExport;
use App\Exports\GrpoExportThisYear;
use App\Imports\GrpoImport;
use App\Models\Grpo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GrpoController extends Controller
{
    public function index()
    {
        $is_data = Grpo::exists() ? 1 : 0;

        return view('grpo.index', compact('is_data'));
    }

    public function index_this_year()
    {
        return view('grpo.index_this_year');
    }

    public function show($id)
    {
        $grpo = Grpo::findOrFail($id);

        return view('grpo.show', compact('grpo'));
    }

    public function truncate()
    {
        Grpo::truncate();

        return redirect()->route('grpo.index')->with('success', 'Table has been truncated.');
    }

    public function import_excel(Request $request)
    {
        // validasi
        $this->validate($request, [
            'file_upload' => 'required|mimes:xls,xlsx'
        ]);

        // menangkap file excel
        $file = $request->file('file_upload');

        // membuat nama file unik
        $nama_file = rand().$file->getClientOriginalName();

        // upload ke folder file_upload
        $file->move('file_upload', $nama_file);

        // import data
        Excel::import(new GrpoImport, public_path('/file_upload/'.$nama_file));

        // alihkan halaman kembali
        return redirect()->route('grpo.index')->with('success', 'Data Excel Berhasil Diimport!');
    }

    public function export_this_month()
    {
        return Excel::download(new GrpoExport(), 'grpo_this_month.xlsx');
    }

    public function export_this_year()
    {
        return Excel::download(new GrpoExportThisYear(), 'grpo_this_year.xlsx');
    }

    public function data()
    {
        $list = Grpo::whereYear('grpo_date', Carbon::now())
                ->whereMonth('grpo_date', Carbon::now())
                ->orderBy('grpo_date', 'desc')
                ->get();

        return datatables()->of($list)
            ->editColumn('grpo_date', function ($list) {
                return date('d-m-Y', strtotime($list->grpo_date));
            })
            ->editColumn('item_amount', function (Grpo $model) {
                return number_format($model->item_amount, 0);
            })
            ->addIndexColumn()
            ->addColumn('action', 'grpo.action')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function data_this_year()
    {
        $list = Grpo::whereYear('grpo_date', Carbon::now())
                ->orderBy('grpo_date', 'desc')
                ->get();

        return datatables()->of($list)
            ->editColumn('grpo_date', function ($list) {
                return date('d-m-Y', strtotime($list->grpo_date));
            })
            ->editColumn('item_amount', function (Grpo $model) {
                return number_format($model->item_amount, 0);
            })
            ->addIndexColumn()
            ->addColumn('action', 'grpo.action')
            ->rawColumns(['action'])
            ->toJson();
    }
}
