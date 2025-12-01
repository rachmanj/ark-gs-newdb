<?php

namespace App\Http\Controllers;

use App\Exports\GrpoExport;
use App\Exports\GrpoExportThisYear;
use App\Imports\GrpoImport;
use App\Models\Grpo;
use App\Services\SapService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class GrpoController extends Controller
{
    public function index()
    {
        // $is_data = Grpo::exists() ? 1 : 0;
        $is_data = Grpo::where('batch', 1)->exists() ? 1 : 0;


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
        // Grpo::where('batch', 1)->delete();
        Grpo::truncate();

        return redirect()->route('grpo.index')->with('success', 'Table has been truncated.');
    }

    public function sync_from_sap(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Convert empty strings to null
            $startDate = !empty($startDate) ? $startDate : null;
            $endDate = !empty($endDate) ? $endDate : null;

            $sapService = new SapService();
            $results = $sapService->executeGrpoSqlQuery($startDate, $endDate);

            if (empty($results)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No data found for the selected date range.'
                    ], 200);
                }
                return redirect()->route('grpo.index')->with('error', 'No data found for the selected date range.');
            }

            $importedCount = 0;
            $skippedCount = 0;
            $totalRecords = count($results);

            DB::beginTransaction();

            try {
                foreach ($results as $row) {
                    $grpoData = [
                        'po_no' => $row->po_no ?? null,
                        'po_date' => $this->convertSqlDate($row->po_date ?? null),
                        'po_delivery_date' => $this->convertSqlDate($row->po_delivery_date ?? null),
                        'grpo_date' => $this->convertSqlDate($row->grpo_date ?? null),
                        'po_delivery_status' => $row->po_delivery_status ?? null,
                        'grpo_no' => $row->grpo_no ?? null,
                        'vendor_code' => $row->vendor_code ?? null,
                        'unit_no' => $row->unit_no ?? null,
                        'item_code' => $row->item_code ?? null,
                        'uom' => $row->uom ?? null,
                        'description' => $row->description ?? null,
                        'qty' => $row->qty ?? null,
                        'grpo_currency' => $row->grpo_currency ?? null,
                        'unit_price' => $row->unit_price ?? null,
                        'item_amount' => $row->item_amount ?? null,
                        'project_code' => $row->project_code ?? null,
                        'dept_code' => $row->dept_code ?? null,
                        'remarks' => $row->remarks ?? null,
                        'batch' => 1,
                    ];

                    Grpo::create($grpoData);
                    $importedCount++;
                }

                DB::commit();

                $message = "Successfully synced {$importedCount} GRPO records from SAP.";

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'imported_count' => $importedCount,
                        'total_records' => $totalRecords
                    ], 200);
                }

                return redirect()->route('grpo.index')->with('success', $message);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $errorMessage = 'Error syncing from SAP: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->route('grpo.index')->with('error', $errorMessage);
        }
    }

    private function convertSqlDate($date)
    {
        if (!$date) {
            return null;
        }

        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d');
        }

        if (is_string($date)) {
            try {
                return Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
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
            Excel::import(new GrpoImport, $filePath);

            // Delete the temporary file after successful import
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // alihkan halaman kembali
            return redirect()->route('grpo.index')->with('success', 'Data Excel Berhasil Diimport dan File Temporary Dihapus!');
        } catch (\Exception $e) {
            // Delete the file even if import fails
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return redirect()->route('grpo.index')->with('error', 'Error saat import: ' . $e->getMessage());
        }
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
