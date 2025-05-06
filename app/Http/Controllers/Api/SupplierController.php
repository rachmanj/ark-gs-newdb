<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Get suppliers data with customer and vendor counts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuppliers()
    {
        // Get all suppliers
        $suppliers = Supplier::all()->map(function ($supplier) {
            // Determine type based on code prefix
            $type = strpos($supplier->code, 'V') === 0 ? 'vendor' : 'customer';
            
            return [
                'id' => $supplier->id,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'type' => $type,
                'project' => null
            ];
        });

        // Count vendors and customers
        $vendorCount = $suppliers->where('type', 'vendor')->count();
        $customerCount = $suppliers->where('type', 'customer')->count();

        // Format response
        $response = [
            'customer_count' => $customerCount,
            'vendor_count' => $vendorCount,
            'customers' => $suppliers->values()->all()
        ];

        return response()->json($response);
    }
} 