<?php

namespace App\Http\Controllers;

use App\Exports\IncomingExport;
use App\Exports\IncomingExportThisYear;
use App\Imports\IncomingImport;
use App\Models\Incoming;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IncomingController extends Controller
{
    public function index()
    {
        // $is_data = Incoming::exists() ? 1 : 0;
        $is_data = Incoming::where('batch', 1)->exists() ? 1 : 0;

        return view('incoming.index', compact('is_data'));
    }

    public function index_this_year()
    {
        return view('incoming.index_this_year');
    }

    public function show($id)
    {
        $incoming = Incoming::findOrFail($id);

        return view('incoming.show', compact('incoming'));
    }

    public function truncate()
    {
        // delete records with batch = 1
        // Incoming::where('batch', 1)->delete();
        Incoming::truncate();

        return redirect()->route('incoming.index')->with('success', 'Data has been truncated.');
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

        // Create directory if it doesn't exist
        $uploadPath = public_path('file_upload');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // upload ke folder file_upload
        $file->move($uploadPath, $nama_file);

        // Full path to the uploaded file
        $filePath = $uploadPath . '/' . $nama_file;

        try {
            // import data
            Excel::import(new IncomingImport, $filePath);
            
            // Delete the temporary file after successful import
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // alihkan halaman kembali
            return redirect()->route('incoming.index')->with('success', 'Data Excel Berhasil Diimport dan File Temporary Dihapus!');
        } catch (\Exception $e) {
            // Delete the file even if import fails
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            return redirect()->route('incoming.index')->with('error', 'Error saat import: ' . $e->getMessage());
        }
    }

    public function export_this_month()
    {
        return Excel::download(new IncomingExport(), 'incoming_this_month.xlsx');
    }

    public function export_this_year()
    {
        return Excel::download(new IncomingExportThisYear(), 'incoming_this_year.xlsx');
    }

    public function data()
    {
        $list = Incoming::whereYear('posting_date',  Carbon::now())
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
        $list = Incoming::whereYear('posting_date',  Carbon::now())
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
