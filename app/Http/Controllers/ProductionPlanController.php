<?php

namespace App\Http\Controllers;

use App\Models\ProductionPlan;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ProductionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = Project::where('is_active', true)->orderBy('code')->get();
        return view('production-plans.index', compact('projects'));
    }

    /**
     * Get production plans data for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data()
    {
        $productionPlans = ProductionPlan::latest()->get();
        
        return DataTables::of($productionPlans)
            ->addIndexColumn()
            ->addColumn('period', function ($productionPlan) {
                return sprintf('%02d-%d', $productionPlan->month, $productionPlan->year);
            })
            ->addColumn('actions', function ($productionPlan) {
                return '
                <div class="btn-group" style="gap: 0.25rem;">
                    <a href="' . route('production-plan.show', $productionPlan->id) . '" class="btn btn-xs btn-sm btn-primary" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-xs btn-sm btn-warning" onclick="editItem(' . $productionPlan->id . ')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-sm btn-danger" onclick="deleteItem(' . $productionPlan->id . ')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'product' => 'required|string',
            'uom' => 'required|string',
            'qty' => 'required|numeric',
            'project' => 'required|string|exists:projects,code',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ProductionPlan::create($request->all());

        return response()->json(['success' => 'Production plan created successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductionPlan  $productionPlan
     * @return \Illuminate\Http\Response
     */
    public function show(ProductionPlan $productionPlan)
    {
        return view('production-plans.show', compact('productionPlan'));
    }

    /**
     * Get the specified resource for editing via AJAX.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getForEdit($id)
    {
        $productionPlan = ProductionPlan::findOrFail($id);
        return response()->json($productionPlan);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductionPlan  $productionPlan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductionPlan $productionPlan)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'product' => 'required|string',
            'uom' => 'required|string',
            'qty' => 'required|numeric',
            'project' => 'required|string|exists:projects,code',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $productionPlan->update($request->all());

        return response()->json(['success' => 'Production plan updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductionPlan  $productionPlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductionPlan $productionPlan)
    {
        $productionPlan->delete();

        return response()->json(['success' => 'Production plan deleted successfully.']);
    }

    /**
     * Export production plans to Excel.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // You can implement export functionality here if needed
        // For example, using Laravel Excel package
    }
} 