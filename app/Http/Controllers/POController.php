<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
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
        $unit_nos = PurchaseOrder::distinct()
            ->whereNotIn('unit_no', ['NON UNIT', 'NON_UNIT', 'NONUNIT', 'NON _UNIT'])
            ->pluck('unit_no');

        return view('dashboard.daily.search_po', compact('suppliers', 'project_codes', 'unit_nos'));
    }

    public function search_po_results(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->doc_num) {
            $query->where('doc_num', 'like', '%' . $request->doc_num . '%');
        }
        if ($request->supplier_name) {
            $query->whereHas('supplier', function ($q) use ($request) {
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
            ->addColumn('supplier_name', function ($row) {
                return $row->supplier ? $row->supplier->name : '';
            })
            ->addColumn('formatted_date', function ($row) {
                return $row->doc_date ? date('d M Y', strtotime($row->doc_date)) : '-';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-xs btn-info show-po" data-id="' . $row->id . '">
                            <i class="fas fa-eye"></i> Detail
                        </button>';
            })
            ->rawColumns(['action'])
            ->orderColumn('DT_RowIndex', function ($query, $order) {
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

    public function item_price_history()
    {
        // Get distinct values for dropdowns
        $unit_nos = PurchaseOrder::distinct()
            ->whereNotIn('unit_no', ['NON UNIT', 'NON_UNIT', 'NONUNIT', 'NON _UNIT'])
            ->orderBy('unit_no', 'asc')
            ->pluck('unit_no');

        return view('dashboard.daily.item_price_history', compact('unit_nos'));
    }

    public function item_price_history_results(Request $request)
    {
        $query = PurchaseOrderItem::getPriceHistory(
            $request->item_code,
            $request->description,
            $request->unit_no
        );

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('po_number', function ($row) {
                return $row->purchaseOrder ? $row->purchaseOrder->doc_num : '-';
            })
            ->addColumn('po_date', function ($row) {
                return $row->purchaseOrder && $row->purchaseOrder->doc_date
                    ? date('d M Y', strtotime($row->purchaseOrder->doc_date))
                    : '-';
            })
            ->addColumn('supplier_name', function ($row) {
                return $row->purchaseOrder && $row->purchaseOrder->supplier
                    ? $row->purchaseOrder->supplier->name
                    : '-';
            })
            ->addColumn('unit_no', function ($row) {
                return $row->purchaseOrder ? $row->purchaseOrder->unit_no : '-';
            })
            ->addColumn('formatted_price', function ($row) {
                return number_format($row->unit_price, 2);
            })
            ->addColumn('formatted_total', function ($row) {
                return number_format($row->item_amount, 2);
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-xs btn-info show-po" data-id="' . $row->purchase_order_id . '">
                            <i class="fas fa-eye"></i> View PO
                        </button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function po_sent_details(Request $request)
    {
        $project = $request->get('project');
        $year = $request->get('year');
        $month = $request->get('month');
        $budgetType = $request->get('budget_type', 'CPX');

        $incl_deptcode = ['40', '50', '60', '140', '200'];
        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%'];
        
        $query = DB::table('powithetas')
            ->whereYear('po_delivery_date', $year)
            ->whereMonth('po_delivery_date', $month)
            ->where('po_status', '!=', 'Cancelled')
            ->where('po_delivery_status', 'Delivered');
        
        if ($project !== 'ALL') {
            $query->where('project_code', $project);
        }
        
        if ($budgetType === 'REG') {
            $excl_itemcode_arr = array_map(function ($e) {
                return ['item_code', 'not like', $e];
            }, $excl_itemcode);
            
            $query->whereIn('dept_code', $incl_deptcode)
                ->where($excl_itemcode_arr)
                ->where(function ($q) {
                    $q->whereNull('budget_type')
                      ->orWhere('budget_type', 'REG');
                });
        } else {
            $query->where('budget_type', $budgetType);
        }
        
        $allItems = $query->select([
                'po_no',
                'create_date',
                'posting_date',
                'vendor_name',
                'pr_no',
                'item_code',
                'description',
                'uom',
                'qty',
                'unit_no',
                'project_code',
                'dept_code',
                'po_currency',
                'unit_price',
                'item_amount',
                'total_po_price',
                'po_status',
                'po_delivery_status',
                'po_delivery_date',
                'po_eta',
                'budget_type',
                'id'
            ])
            ->orderBy('po_delivery_date', 'desc')
            ->orderBy('po_no', 'asc')
            ->get();

        $groupedData = $allItems->groupBy('po_no')->map(function ($items, $poNo) {
            $firstItem = $items->first();
            return [
                'po_no' => $poNo,
                'create_date' => $firstItem->create_date,
                'posting_date' => $firstItem->posting_date,
                'vendor_name' => $firstItem->vendor_name,
                'po_delivery_date' => $firstItem->po_delivery_date,
                'po_eta' => $firstItem->po_eta,
                'unit_no' => $firstItem->unit_no,
                'project_code' => $firstItem->project_code,
                'po_status' => $firstItem->po_status,
                'po_delivery_status' => $firstItem->po_delivery_status,
                'budget_type' => $firstItem->budget_type,
                'item_count' => $items->count(),
                'total_amount' => $items->sum('item_amount'),
                'items' => $items->toArray()
            ];
        })->values();

        return DataTables::of($groupedData)
            ->addIndexColumn()
            ->addColumn('formatted_delivery_date', function ($row) {
                return $row['po_delivery_date'] ? date('d M Y', strtotime($row['po_delivery_date'])) : '-';
            })
            ->addColumn('formatted_create_date', function ($row) {
                return $row['create_date'] ? date('d M Y', strtotime($row['create_date'])) : '-';
            })
            ->addColumn('formatted_eta', function ($row) {
                return $row['po_eta'] ? date('d M Y', strtotime($row['po_eta'])) : '-';
            })
            ->addColumn('formatted_total_amount', function ($row) {
                return number_format($row['total_amount'], 2);
            })
            ->addColumn('items_detail', function ($row) {
                return json_encode($row['items']);
            })
            ->rawColumns(['formatted_total_amount'])
            ->make(true);
    }

    public function po_sent_details_page(Request $request)
    {
        $project = $request->get('project');
        $year = $request->get('year');
        $month = $request->get('month');
        $budgetType = $request->get('budget_type', 'CPX');
        
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        
        $projectDisplay = $project === 'ALL' ? 'All Projects' : $project;

        return view('dashboard.daily.po_sent_details', compact('project', 'year', 'month', 'monthName', 'budgetType', 'projectDisplay'));
    }
}
