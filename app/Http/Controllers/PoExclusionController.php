<?php

namespace App\Http\Controllers;

use App\Models\PoExclusion;
use Illuminate\Http\Request;

class PoExclusionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('superadmin')) {
                abort(403, 'Unauthorized. Admin access required.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $exclusions = PoExclusion::orderBy('po_no')->get();
        return view('po-exclusions.index', compact('exclusions'));
    }

    public function create()
    {
        return view('po-exclusions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_nos' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $poNos = $this->parsePoNumbers($validated['po_nos']);
        $reason = $validated['reason'] ?? null;

        if (empty($poNos)) {
            return redirect()->route('po-exclusions.create')
                ->with('error', 'No valid PO numbers found. Enter one or more PO numbers separated by comma or newline.');
        }

        $added = 0;
        $skipped = 0;

        foreach ($poNos as $poNo) {
            $poNo = trim($poNo);
            if (empty($poNo)) {
                continue;
            }
            if (PoExclusion::where('po_no', $poNo)->exists()) {
                $skipped++;
                continue;
            }
            PoExclusion::create(['po_no' => $poNo, 'reason' => $reason]);
            $added++;
        }

        $message = "Added {$added} PO number(s) to exclusions.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} duplicate(s).";
        }

        return redirect()->route('po-exclusions.index')->with('success', $message);
    }

    public function destroy(PoExclusion $poExclusion)
    {
        $poExclusion->delete();
        return redirect()->route('po-exclusions.index')->with('success', 'PO number removed from exclusions.');
    }

    private function parsePoNumbers(string $input): array
    {
        $input = preg_replace('/\s+/', "\n", $input);
        $parts = preg_split('/[\s,;]+/', $input, -1, PREG_SPLIT_NO_EMPTY);
        return array_unique(array_map('trim', $parts));
    }
}
