<?php

namespace App\Http\Controllers;

use App\Exports\MigiExport;
use App\Exports\MigiExportThisYear;
use App\Imports\MigiImport;
use App\Models\Migi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MigiController extends Controller
{
    public function index()
    {
        // $is_data = Migi::exists() ? 1 : 0;
        $is_data = Migi::where('batch', 1)->exists() ? 1 : 0;

        return view('migi.index', compact('is_data'));
    }

    public function index_this_year()
    {
        return view('migi.index_this_year');
    }

    public function show($id)
    {
        $migi = Migi::findOrFail($id);

        return view('migi.show', compact('migi'));
    }

    public function truncate()
    {
        // delete records with batch = 1
        Migi::where('batch', 1)->delete();

        return redirect()->route('migi.index')->with('success', 'Table has been truncated.');
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
        $nama_file = rand() . $file->getClientOriginalName();

        // upload ke folder file_upload
        $file->move('public/file_upload', $nama_file);

        // import data
        Excel::import(new MigiImport, public_path('/file_upload/' . $nama_file));

        // alihkan halaman kembali
        return redirect()->route('migi.index')->with('success', 'Data Excel Berhasil Diimport!');
    }

    public function export_this_month()
    {
        return Excel::download(new MigiExport(), 'migi_this_month.xlsx');
    }

    public function export_this_year()
    {
        return Excel::download(new MigiExportThisYear(), 'migi_this_year.xlsx');
    }

    public function data()
    {
        $list = Migi::whereYear('posting_date', Carbon::now())
            ->whereMonth('posting_date', Carbon::now())
            ->get();

        return datatables()->of($list)
            ->editColumn('posting_date', function ($list) {
                return date('d-m-Y', strtotime($list->posting_date));
            })
            ->addIndexColumn()
            ->toJson();
    }

    public function data_this_year()
    {
        $list = Migi::whereYear('posting_date', Carbon::now())
            ->orderby('posting_date')
            ->get();

        return datatables()->of($list)
            ->editColumn('posting_date', function ($list) {
                return date('d-m-Y', strtotime($list->posting_date));
            })
            ->addIndexColumn()
            ->toJson();
    }
}
