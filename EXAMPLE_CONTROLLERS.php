<?php

// Example Controllers - Copy and customize for your needs

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\Inspection;
use App\Models\Document;
use App\Rules\HSCodeRule;
use App\Rules\ISOCountryCodeRule;
use App\Rules\CurrencyFormatRule;
use App\Services\FileUploadService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

/**
 * CaseController
 * Handles case management, risk analysis, and transitions
 */
class CaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new case
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'external_id' => 'required|string|unique:cases',
            'vehicle_id' => 'required|exists:vehicles,id',
            'origin_country' => ['required', new ISOCountryCodeRule()],
            'destination_country' => ['required', new ISOCountryCodeRule()],
            'declared_value' => ['required', new CurrencyFormatRule()],
            'route' => 'nullable|string',
            'arrived_at' => 'required|date_format:Y-m-d\TH:i:sZ',
        ]);

        $case = CaseModel::create(array_merge($validated, [
            'status' => 'new',
        ]));

        // Log the creation
        AuditLogService::log('create', $case);

        return response()->json($case, 201);
    }

    /**
     * Show case with all relationships
     */
    public function show(CaseModel $case)
    {
        $this->authorize('view', $case);

        return response()->json($case->load([
            'vehicle',
            'parties',
            'cargoItems',
            'inspections',
            'documents.files',
            'events' => fn($q) => $q->latest()->limit(50),
        ]));
    }

    /**
     * Perform risk analysis
     */
    public function analyze(Request $request, CaseModel $case)
    {
        $this->authorize('update', $case);

        // Ensure cargo items exist before analysis
        if ($case->cargoItems->isEmpty()) {
            return response()->json([
                'error' => 'Cannot analyze case without cargo items'
            ], 422);
        }

        $analysis = $case->performRiskAnalysis();

        return response()->json([
            'risk_score' => $analysis['risk_score'],
            'risk_level' => $analysis['risk_level'],
            'should_inspect' => $analysis['should_inspect'],
            'reasons' => $analysis['reasons'],
        ]);
    }

    /**
     * Transition case status
     */
    public function transition(Request $request, CaseModel $case)
    {
        $this->authorize('update', $case);

        $validated = $request->validate([
            'new_status' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        if (!$case->canTransitionTo($validated['new_status'])) {
            return response()->json([
                'error' => "Cannot transition from {$case->status} to {$validated['new_status']}"
            ], 422);
        }

        $case->transitionTo($validated['new_status'], $validated['reason']);

        return response()->json([
            'message' => 'Case status updated',
            'case' => $case,
        ]);
    }

    /**
     * Get case event history
     */
    public function history(CaseModel $case)
    {
        return response()->json($case->getEventHistory());
    }

    /**
     * Get audit log for case
     */
    public function audit(CaseModel $case)
    {
        $logs = AuditLogService::getLogs('CaseModel', $case->id);

        return response()->json($logs);
    }

    /**
     * Update case values (for inspector corrections)
     */
    public function update(Request $request, CaseModel $case)
    {
        $this->authorize('update', $case);
        $this->authorize('perform inspections');

        $validated = $request->validate([
            'actual_value' => ['nullable', new CurrencyFormatRule()],
            'previous_violations' => 'nullable|integer|min:0',
        ]);

        $original = $case->getOriginal();
        $case->update($validated);

        // Log the changes
        AuditLogService::log('update', $case, array_keys($validated));

        // Re-run risk analysis with new values
        $case->performRiskAnalysis();

        return response()->json($case);
    }
}

/**
 * InspectionController
 * Handles inspection creation and decision recording
 */
class InspectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:perform inspections');
    }

    /**
     * Create inspection for case
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'type' => 'required|in:document,RTG,physical',
        ]);

        $case = CaseModel::findOrFail($validated['case_id']);

        // Ensure case is in inspection-eligible state
        if (!in_array($case->status, ['screening', 'in_inspection'])) {
            return response()->json([
                'error' => 'Case must be in screening or in_inspection state'
            ], 422);
        }

        // Transition case to in_inspection if still in screening
        if ($case->status === 'screening') {
            $case->transitionTo('in_inspection', 'Inspection initiated');
        }

        $inspection = $case->inspections()->create([
            'type' => $validated['type'],
            'status' => 'pending',
        ]);

        return response()->json($inspection, 201);
    }

    /**
     * Record inspection decision
     */
    public function recordDecision(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'decision' => 'required|in:release,hold,reject',
            'reason' => 'required|string',
        ]);

        $inspection->recordDecision(
            $validated['decision'],
            $validated['reason']
        );

        return response()->json([
            'message' => 'Inspection decision recorded',
            'inspection' => $inspection,
            'case_status' => $inspection->case->status,
        ]);
    }

    /**
     * Get inspections for case
     */
    public function forCase(CaseModel $case)
    {
        return response()->json($case->inspections);
    }
}

/**
 * DocumentController
 * Handles document upload and signed URL download
 */
class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Upload document for case
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'document_type' => 'required|string|max:50',
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $case = CaseModel::findOrFail($validated['case_id']);
        $this->authorize('update', $case);

        // Create document record
        $document = $case->documents()->create([
            'type' => $validated['document_type'],
            'uploaded_at' => now(),
        ]);

        // Store file with signed URL
        $documentFile = FileUploadService::storeDocument(
            $request->file('file'),
            $document->id
        );

        return response()->json([
            'document' => $document,
            'file' => $documentFile,
            'signed_url' => FileUploadService::getSignedUrl($documentFile),
        ], 201);
    }

    /**
     * Download document by signed URL token
     */
    public function download($token)
    {
        $documentFile = FileUploadService::getFileByToken($token);

        if (!$documentFile) {
            return response()->json(['error' => 'File not found or link expired'], 404);
        }

        return FileUploadService::downloadFile($documentFile);
    }

    /**
     * Get documents for case
     */
    public function forCase(CaseModel $case)
    {
        $this->authorize('view', $case);

        return response()->json(
            $case->documents()->with('files')->get()
        );
    }

    /**
     * Generate new signed URL for document file
     */
    public function regenerateUrl(DocumentFile $documentFile)
    {
        $this->authorize('update', $documentFile->document->case);

        $url = FileUploadService::getSignedUrl($documentFile);

        return response()->json(['signed_url' => $url]);
    }
}

/**
 * CargoItemController
 * Handles cargo item management
 */
class CargoItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:update cases');
    }

    /**
     * Add cargo item to case
     */
    public function store(Request $request, CaseModel $case)
    {
        $this->authorize('update', $case);

        $validated = $request->validate([
            'hs_code' => ['required', new HSCodeRule()],
            'description' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0.01',
            'value' => ['required', new CurrencyFormatRule()],
        ]);

        $cargoItem = $case->cargoItems()->create($validated);

        // Re-analyze risk with new cargo
        $case->performRiskAnalysis();

        return response()->json($cargoItem, 201);
    }

    /**
     * Update cargo item
     */
    public function update(Request $request, CaseCargoItem $cargoItem)
    {
        $this->authorize('update', $cargoItem->case);

        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0.01',
            'value' => ['nullable', new CurrencyFormatRule()],
        ]);

        $cargoItem->update($validated);

        // Re-analyze risk with updated values
        $cargoItem->case->performRiskAnalysis();

        return response()->json($cargoItem);
    }

    /**
     * Delete cargo item
     */
    public function destroy(CaseCargoItem $cargoItem)
    {
        $this->authorize('update', $cargoItem->case);

        $case = $cargoItem->case;
        $cargoItem->delete();

        // Re-analyze risk with removed cargo
        if ($case->cargoItems->isNotEmpty()) {
            $case->performRiskAnalysis();
        }

        return response()->json(['message' => 'Cargo item deleted']);
    }
}

/**
 * WebhookController
 * Handles webhook management for admins
 */
class WebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage users');
    }

    /**
     * List webhooks
     */
    public function index()
    {
        return response()->json(Webhook::all());
    }

    /**
     * Create webhook
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'event' => 'required|string',
            'active' => 'boolean',
        ]);

        $webhook = Webhook::create(array_merge($validated, [
            'secret' => Str::random(32),
            'active' => $validated['active'] ?? false,
        ]));

        return response()->json($webhook, 201);
    }

    /**
     * Get webhook delivery logs
     */
    public function logs(Webhook $webhook)
    {
        $logs = $webhook->logs()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($logs);
    }

    /**
     * Disable/enable webhook
     */
    public function toggle(Webhook $webhook)
    {
        $webhook->update(['active' => !$webhook->active]);

        return response()->json($webhook);
    }

    /**
     * Delete webhook
     */
    public function destroy(Webhook $webhook)
    {
        $webhook->delete();

        return response()->json(['message' => 'Webhook deleted']);
    }
}

/**
 * AnalyticsController
 * Provides analytics and reporting
 */
class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view analytics');
    }

    /**
     * Case status distribution
     */
    public function casesByStatus()
    {
        $data = CaseModel::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json($data);
    }

    /**
     * Risk score distribution
     */
    public function riskDistribution()
    {
        $data = [
            'low' => CaseModel::whereBetween('risk_score', [0, 29])->count(),
            'medium' => CaseModel::whereBetween('risk_score', [30, 99])->count(),
            'high' => CaseModel::where('risk_score', '>=', 100)->count(),
        ];

        return response()->json($data);
    }

    /**
     * Average processing time
     */
    public function processingTime()
    {
        $avgTime = CaseModel::where('status', 'closed')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - created_at))) as avg_seconds')
            ->value('avg_seconds');

        return response()->json([
            'average_seconds' => $avgTime,
            'average_hours' => round($avgTime / 3600, 2),
        ]);
    }

    /**
     * Webhook delivery statistics
     */
    public function webhookStats()
    {
        $total = WebhookLog::count();
        $successful = WebhookLog::where('success', true)->count();

        return response()->json([
            'total_deliveries' => $total,
            'successful' => $successful,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
        ]);
    }

    /**
     * Inspection completion rate
     */
    public function inspectionStats()
    {
        $total = Inspection::count();
        $completed = Inspection::where('status', 'completed')->count();

        return response()->json([
            'total_inspections' => $total,
            'completed' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'by_type' => Inspection::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type'),
        ]);
    }
}
