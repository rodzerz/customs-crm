<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function index(Request $request)
    {
        $query = CaseModel::with([
            'vehicle',
            'parties',
            'cargoItems',
            'inspections'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plate_no')) {
            $query->whereHas('vehicle', function ($q) use ($request) {
                $q->where('plate_no', 'like', "%{$request->plate_no}%");
            });
        }

        if ($request->filled('risk_min')) {
            $query->where('risk_score', '>=', $request->risk_min);
        }

        return $query->paginate(20);
    }

    public function show($id)
    {
        return CaseModel::with([
            'vehicle',
            'parties',
            'cargoItems',
            'inspections',
            'documents'
        ])->findOrFail($id);
    }
}
