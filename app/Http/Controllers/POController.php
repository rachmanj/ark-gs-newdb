<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class POController extends Controller
{
    public function search_po()
    {
        // Get distinct values for dropdowns
        $suppliers = Supplier::pluck('name', 'id');
        $project_codes = PurchaseOrder::distinct()->pluck('project_code');
        $unit_nos = PurchaseOrder::distinct()->pluck('unit_no');

        return view('dashboard.daily.search_po', compact('suppliers', 'project_codes', 'unit_nos'));
    }

    public function search_po_results(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->doc_num) {
            $query->where('doc_num', 'like', '%' . $request->doc_num . '%');
        }
        if ($request->supplier_name) {
            $query->whereHas('supplier', function($q) use ($request) {
                $q->where('id', $request->supplier_name);
            });
        }
        if ($request->pr_no) {
            $query->where('pr_no', 'like', '%' . $request->pr_no . '%');
        }
        if ($request->unit_no) {
            $query->where('unit_no', $request->unit_no);
        }
        if ($request->project_code) {
            $query->where('project_code', $request->project_code);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('supplier_name', function($row) {
                return $row->supplier ? $row->supplier->name : '';
            })
            ->addColumn('formatted_date', function($row) {
                return $row->doc_date ? date('d M Y', strtotime($row->doc_date)) : '-';
            })
            ->addColumn('action', function($row) {
                return '<button type="button" class="btn btn-xs btn-info show-po" data-id="'.$row->id.'">
                            <i class="fas fa-eye"></i> Detail
                        </button>';
            })
            ->rawColumns(['action'])
            ->orderColumn('DT_RowIndex', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->make(true);
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['supplier', 'items'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => [
                'po_details' => [
                    'Document Number' => $po->doc_num,
                    'Document Date' => $po->doc_date ? date('d M Y', strtotime($po->doc_date)) : '-',
                    'Create Date' => $po->create_date ? date('d M Y', strtotime($po->create_date)) : '-',
                    'Delivery Date' => $po->po_delivery_date ? date('d M Y', strtotime($po->po_delivery_date)) : '-',
                    'Supplier' => $po->supplier->name,
                    'ETA' => $po->po_eta ? date('d M Y', strtotime($po->po_eta)) : '-',
                    'PR Number' => $po->pr_no,
                    'Unit Number' => $po->unit_no,
                    'Currency' => $po->po_currency,
                    'Total Price' => number_format($po->total_po_price, 2),
                    // 'Price with VAT' => number_format($po->po_with_vat, 2),
                    'Project Code' => $po->project_code,
                    'Department Code' => $po->dept_code,
                    'PO Status' => $po->po_status,
                    'Delivery Status' => $po->po_delivery_status,
                    'Budget Type' => $po->budget_type,
                ],
                'po_items' => $po->items
            ]
        ]);
    }
}
