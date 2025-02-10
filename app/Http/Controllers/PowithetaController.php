<?php

namespace App\Http\Controllers;

use App\Exports\PowithetaExport;
use App\Exports\PowithetaExportThisYear;
use App\Imports\PowithetaImport;
use App\Models\Powitheta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PowithetaController extends Controller
{
    public function index()
    {
        $is_data = Powitheta::count() > 0 ? 1 : 0;
        return view('powitheta.index', compact('is_data'));
    }

    public function index_this_year()
    {
        return view('powitheta.index_this_year');
    }

    public function show($id)
    {
        $powitheta = Powitheta::findOrFail($id);

        return view('powitheta.show', compact('powitheta'));
    }

    public function truncate()
    {
        Powitheta::truncate();
        PurchaseOrder::truncate();
        PurchaseOrderItem::truncate();
        // Powitheta::where('batch', 1)->delete();

        return redirect()->route('powitheta.index')->with('success', 'Data has been truncated.');
    }

    public function clean_olddb()
    {
        $po_no_toclean = $this->compare_db()['compare'];
        $pos_toclean = $po_no_toclean->where('batch', 0);
        // delete record that listed in $pos_toclean
        // Powitheta::whereIn('po_no', $pos_toclean)->delete();

        return $pos_toclean;
        // return redirect()->route('powitheta.index')->with('success', 'Old DB has been cleaned.');
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
        // $file->move('file_upload', $nama_file);

        // import data
        Excel::import(new PowithetaImport, public_path('/file_upload/' . $nama_file));

        // alihkan halaman kembali
        return redirect()->route('powitheta.index')->with('success', 'Data Excel Berhasil Diimport!');
    }

    public function import_oldDB(Request $request)
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
        Excel::import(new PowithetaImport, public_path('/file_upload/' . $nama_file));

        // alihkan halaman kembali
        return redirect()->route('powitheta.index')->with('success', 'Data Excel Old DB Berhasil Diimport!');
    }

    public function export_this_month()
    {
        return Excel::download(new PowithetaExport(), 'powitheta_this_month.xlsx');
    }

    public function export_this_year()
    {
        return Excel::download(new PowithetaExportThisYear(), 'powitheta_this_year.xlsx');
    }

    public function data()  //this month data
    {
        $date = Carbon::now();

        $projects = ['011C', '017C', '021C', '022C', '023C', 'APS'];
        $incl_deptcode = ['40', '50', '60', '140', '200'];
        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%']; // , 
        foreach ($excl_itemcode as $e) {
            $excl_itemcode_arr[] = ['item_code', 'not like', $e];
        };

        $list = Powitheta::whereYear('po_delivery_date', $date)
            ->whereMonth('po_delivery_date', $date)
            ->orderBy('po_delivery_date', 'desc')
            ->whereIn('project_code', $projects)
            ->where('po_delivery_status', 'Delivered')
            ->where('po_status', '!=', 'Cancelled')
            ->whereIn('dept_code', $incl_deptcode)
            ->where($excl_itemcode_arr)
            ->get();

        return datatables()->of($list)
            ->editColumn('po_delivery_date', function ($list) {
                return date('d-m-Y', strtotime($list->po_delivery_date));
            })
            ->editColumn('posting_date', function ($list) {
                return date('d-m-Y', strtotime($list->posting_date));
            })
            ->editColumn('item_amount', function ($list) {
                return number_format($list->item_amount, 0);
            })
            ->addIndexColumn()
            ->addColumn('action', 'powitheta.action')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function data_this_year()  //this month data
    {
        $date = Carbon::now();

        $projects = ['011C', '017C', '022C', '023C', 'APS'];
        $incl_deptcode = ['40', '50', '60', '140', '200'];
        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%']; // , 
        foreach ($excl_itemcode as $e) {
            $excl_itemcode_arr[] = ['item_code', 'not like', $e];
        };

        $list = Powitheta::whereYear('po_delivery_date', $date)
            ->orderBy('po_delivery_date', 'desc')
            ->whereIn('project_code', $projects)
            ->where('po_delivery_status', 'Delivered')
            ->where('po_status', '!=', 'Cancelled')
            ->whereIn('dept_code', $incl_deptcode)
            ->where($excl_itemcode_arr)
            ->get();

        return datatables()->of($list)
            ->editColumn('po_delivery_date', function ($list) {
                return date('d-m-Y', strtotime($list->po_delivery_date));
            })
            ->editColumn('posting_date', function ($list) {
                return date('d-m-Y', strtotime($list->posting_date));
            })
            ->editColumn('item_amount', function ($list) {
                return number_format($list->item_amount, 0);
            })
            ->addIndexColumn()
            ->addColumn('action', 'powitheta.action')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function compare_db()
    {
        $old_db = Powitheta::select('po_no')->where('batch', 0)
            ->distinct()
            // ->where('po_status', 'Open')
            ->get();

        $new_db = Powitheta::select('po_no')->where('batch', 1)
            ->distinct()
            ->get();

        $compare = Powitheta::select('po_no')->whereIn('po_no', $old_db)
            ->whereIn('po_no', $new_db)
            ->distinct()
            ->pluck('po_no');

        return [
            // 'old_db' => $old_db->count(),
            // 'new_db' => $new_db->count(),
            // 'compare_count' => count($compare),
            'compare' => $compare,
        ];
    }

    public function convert_to_po()
    {
        try {
            // Get all unique PO records grouped by PO number
            $poGroups = Powitheta::select(
                'po_no',
                'posting_date',
                'create_date',
                'po_delivery_date',
                'vendor_code',
                'vendor_name',
                'po_currency',
                'project_code',
                'dept_code',
                'po_status',
                'unit_no',
                'pr_no',
                'po_eta',
                'po_delivery_status',
                'budget_type'
            )
                ->distinct('po_no')
                ->get();

            if ($poGroups->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data to convert'
                ]);
            }

            $importedCount = 0;
            $createdSuppliers = 0;

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($poGroups as $poGroup) {
                // Find or create supplier
                $supplier = Supplier::firstOrCreate(
                    ['code' => $poGroup->vendor_code],
                    ['name' => $poGroup->vendor_name]
                );

                if ($supplier->wasRecentlyCreated) {
                    $createdSuppliers++;
                }

                // Calculate total PO price
                $totalPoPrice = Powitheta::where('po_no', $poGroup->po_no)
                    ->sum('item_amount');

                // Create Purchase Order
                $purchaseOrder = PurchaseOrder::create([
                    'doc_num' => $poGroup->po_no,
                    'doc_date' => $poGroup->posting_date,
                    'create_date' => $poGroup->create_date,
                    'po_delivery_date' => $poGroup->po_delivery_date,
                    'supplier_id' => $supplier->id,
                    'po_currency' => $poGroup->po_currency,
                    'total_po_price' => $totalPoPrice,
                    'po_with_vat' => $totalPoPrice,
                    'project_code' => $poGroup->project_code,
                    'dept_code' => $poGroup->dept_code,
                    'po_status' => $poGroup->po_status,
                    'unit_no' => $poGroup->unit_no,
                    'pr_no' => $poGroup->pr_no,
                    'po_eta' => $poGroup->po_eta,
                    'budget_type' => $poGroup->budget_type,
                    'po_delivery_status' => $poGroup->po_delivery_status
                ]);

                // Get and create items for this PO
                $poItems = Powitheta::where('po_no', $poGroup->po_no)
                    ->select([
                        'item_code',
                        'description',
                        'qty',
                        'uom',
                        'unit_price',
                        'item_amount'
                    ])
                    ->get();

                foreach ($poItems as $item) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'item_code' => $item->item_code,
                        'description' => $item->description,
                        'qty' => $item->qty,
                        'uom' => $item->uom,
                        'unit_price' => $item->unit_price,
                        'item_amount' => $item->item_amount,
                    ]);
                }

                $importedCount++;
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return response()->json([
                'success' => true,
                'message' => "Successfully converted {$importedCount} Purchase Orders" . 
                            ($createdSuppliers > 0 ? " and created {$createdSuppliers} new suppliers" : "")
            ]);

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return response()->json([
                'success' => false,
                'message' => 'Error converting data: ' . $e->getMessage()
            ]);
        }
    }

    public function get_progress()
    {
        try {
            $progress = intval(file_get_contents(storage_path('app/po_conversion_progress.txt')));
        } catch (\Exception $e) {
            $progress = 0;
        }
        
        return response()->json([
            'progress' => $progress
        ]);
    }
}
