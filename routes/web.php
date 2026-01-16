<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Vehicle;
use App\Models\Party;
use App\Models\CaseModel;
use App\Models\CaseCargoItem;
use App\Models\Inspection;
use App\Models\Document;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Data routes with permission-based access
    Route::get('/vehicles', function () {
        $vehicles = Vehicle::all();
        return view('vehicles.index', compact('vehicles'));
    })->middleware('permission:view vehicles');

    Route::get('/parties', function () {
        $parties = Party::all();
        return view('parties.index', compact('parties'));
    })->middleware('permission:view parties');

    Route::get('/cases', function () {
        $cases = CaseModel::with('vehicle', 'parties', 'cargoItems', 'inspections', 'documents')->get();
        return view('cases.index', compact('cases'));
    })->middleware('permission:view cases');

    Route::get('/cases/create', function () {
        $vehicles = Vehicle::all();
        return view('cases.create', compact('vehicles'));
    })->middleware('permission:create cases')->name('cases.create');

    Route::post('/cases', function () {
        $validated = request()->validate([
            'external_id' => 'required|unique:cases',
            'vehicle_id' => 'required|exists:vehicles,id',
            'route' => 'nullable|string',
            'origin_country' => 'nullable|string',
            'destination_country' => 'nullable|string',
            'declared_value' => 'nullable|numeric',
        ]);
        
        $case = CaseModel::create(array_merge($validated, [
            'status' => 'new',
            'arrived_at' => now(),
        ]));
        
        return redirect("/cases/{$case->id}/edit")->with('success', 'Case created successfully');
    })->middleware('permission:create cases')->name('cases.store');

    Route::get('/cases/{id}/edit', function ($id) {
        $case = CaseModel::with('vehicle', 'parties', 'cargoItems', 'inspections', 'documents')->findOrFail($id);
        return view('cases.edit', compact('case'));
    })->middleware('permission:update cases')->name('cases.edit');

    Route::put('/cases/{id}', function ($id) {
        $case = CaseModel::findOrFail($id);
        $case->update(request()->validate([
            'status' => 'nullable|in:new,screening,in_inspection,on_hold,released,rejected,closed',
            'route' => 'nullable|string',
            'origin_country' => 'nullable|string',
            'destination_country' => 'nullable|string',
            'declared_value' => 'nullable|numeric',
            'actual_value' => 'nullable|numeric',
        ]));
        return redirect('/cases')->with('success', 'Case updated successfully');
    })->middleware('permission:update cases')->name('cases.update');

    Route::get('/cargo-items', function () {
        $cargoItems = CaseCargoItem::with('case')->get();
        return view('cargo_items.index', compact('cargoItems'));
    })->middleware('permission:view cases');

    Route::get('/inspections', function () {
        $inspections = Inspection::with('case')->get();
        return view('inspections.index', compact('inspections'));
    })->middleware('permission:view inspections');

    Route::get('/cases/{case_id}/inspections/create', function ($case_id) {
        $case = CaseModel::findOrFail($case_id);
        return view('inspections.create', compact('case'));
    })->middleware('permission:perform inspections')->name('inspections.create');

    Route::post('/cases/{case_id}/inspections', function ($case_id) {
        $case = CaseModel::findOrFail($case_id);
        $inspection = $case->inspections()->create(request()->validate([
            'type' => 'required|in:document,RTG,physical',
            'comment' => 'nullable|string',
        ]));
        $inspection->update(['performed_by_user_id' => auth()->id()]);
        return redirect("/cases/{$case_id}/inspections/{$inspection->id}/decision")->with('success', 'Inspection created');
    })->middleware('permission:perform inspections')->name('inspections.store');

    Route::get('/cases/{case_id}/inspections/{inspection_id}/decision', function ($case_id, $inspection_id) {
        $inspection = Inspection::findOrFail($inspection_id);
        return view('inspections.decision', compact('inspection'));
    })->middleware('permission:perform inspections')->name('inspections.decision');

    Route::post('/cases/{case_id}/inspections/{inspection_id}/decision', function ($case_id, $inspection_id) {
        $inspection = Inspection::findOrFail($inspection_id);
        $validated = request()->validate([
            'decision' => 'required|in:release,hold,reject',
            'decision_reason' => 'nullable|string',
        ]);
        $inspection->recordDecision($validated['decision'], $validated['decision_reason'] ?? null);
        return redirect('/inspections')->with('success', 'Inspection decision recorded');
    })->middleware('permission:perform inspections')->name('inspections.record');

    Route::get('/documents', function () {
        $documents = Document::with('case')->get();
        return view('documents.index', compact('documents'));
    })->middleware('permission:view documents');

    Route::get('/cases/{case_id}/documents/create', function ($case_id) {
        $case = CaseModel::findOrFail($case_id);
        return view('documents.create', compact('case'));
    })->middleware('permission:submit declarations')->name('documents.create');

    Route::post('/cases/{case_id}/documents', function ($case_id) {
        $case = CaseModel::findOrFail($case_id);
        $validated = request()->validate([
            'type' => 'required|in:declaration,invoice,packing_list,certificate,other',
            'description' => 'nullable|string',
        ]);
        
        // Generate unique external_id
        $externalId = 'DOC-' . $case->id . '-' . time() . '-' . rand(100, 999);
        
        $document = $case->documents()->create(array_merge($validated, [
            'external_id' => $externalId,
            'uploaded_at' => now(),
        ]));
        
        // Handle file upload if provided
        if (request()->hasFile('file')) {
            $file = request()->file('file');
            $path = $file->store('documents', 'private');
            $document->files()->create([
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'signed_url_token' => \Illuminate\Support\Str::random(60),
                'expires_at' => now()->addHours(24),
            ]);
        }
        
        return redirect("/cases/{$case_id}/documents/create")->with('success', 'Document uploaded successfully');
    })->middleware('permission:submit declarations')->name('documents.store');

    Route::get('/analytics', function () {
        // Get statistics
        $totalCases = CaseModel::count();
        $casesByStatus = CaseModel::select('status')->selectRaw('count(*) as count')->groupBy('status')->get();
        $averageRiskScore = CaseModel::avg('risk_score') ?? 0;
        $highRiskCases = CaseModel::where('risk_score', '>=', 100)->count();
        $totalInspections = Inspection::count();
        $completedInspections = Inspection::where('status', 'completed')->count();
        $totalDocuments = Document::count();
        
        // Get recent cases
        $recentCases = CaseModel::orderBy('created_at', 'desc')->limit(10)->get();
        
        // Get inspection types
        $inspectionsByType = Inspection::select('type')->selectRaw('count(*) as count')->groupBy('type')->get();
        
        // Get inspection decisions
        $inspectionDecisions = Inspection::select('decision')->selectRaw('count(*) as count')->whereNotNull('decision')->groupBy('decision')->get();
        
        return view('analytics.index', compact(
            'totalCases',
            'casesByStatus',
            'averageRiskScore',
            'highRiskCases',
            'totalInspections',
            'completedInspections',
            'totalDocuments',
            'recentCases',
            'inspectionsByType',
            'inspectionDecisions'
        ));
    })->middleware('permission:view analytics')->name('analytics');

    // Admin routes
    Route::middleware('permission:manage users')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->only(['index', 'edit', 'update']);
    });
});

require __DIR__.'/auth.php';