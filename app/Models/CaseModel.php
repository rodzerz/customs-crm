<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\RiskAnalysisService;
use App\Services\CaseEventService;

class CaseModel extends Model
{
    // Tabulas nosaukums (jo "cases" ir mūsu DB tabula)
    protected $table = 'cases';

    // Masveida pievienojami lauki
    protected $fillable = [
        'external_id',
        'vehicle_id',
        'status',
        'risk_score',
        'arrived_at',
        'route',
        'origin_country',
        'destination_country',
        'declared_value',
        'actual_value',
        'previous_violations',
        'risk_reason',
        'status_updated_at',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'status_updated_at' => 'datetime',
    ];

    // Valid status transitions
    protected const VALID_TRANSITIONS = [
        'new' => ['screening'],
        'screening' => ['in_inspection', 'released'],
        'in_inspection' => ['on_hold', 'released', 'rejected'],
        'on_hold' => ['in_inspection', 'released', 'rejected'],
        'released' => ['closed'],
        'rejected' => ['closed'],
        'closed' => [],
    ];

    // RELĀCIJAS

    // Piesaistīts transportlīdzeklim
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Saistītās puses (deklarants, saņēmējs, pārvadātājs)
    public function parties()
    {
        return $this->belongsToMany(
            Party::class,
            'case_parties',
            'case_id',
            'party_id'
        )->withPivot('role');
    }

    // Kravas pozīcijas
    public function cargoItems()
    {
        return $this->hasMany(CaseCargoItem::class, 'case_id');
    }

    // Pārbaudes (inspection)
    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'case_id');
    }

    // Event history
    public function events()
    {
        return $this->hasMany(CaseEvent::class, 'case_id');
    }

    // Documents
    public function documents()
    {
        return $this->hasMany(Document::class, 'case_id');
    }

    // Status transition methods
    public function canTransitionTo($newStatus)
    {
        $allowedTransitions = self::VALID_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowedTransitions);
    }

    public function transitionTo($newStatus, $reason = null)
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \Exception("Cannot transition from {$this->status} to {$newStatus}");
        }

        $oldStatus = $this->status;
        $this->update([
            'status' => $newStatus,
            'status_updated_at' => now(),
        ]);

        // Log the status change
        CaseEventService::logStatusChange($this, $oldStatus, $newStatus, $reason);
    }

    public function performRiskAnalysis()
    {
        return RiskAnalysisService::analyzeCase($this);
    }

    public function getEventHistory()
    {
        return $this->events()->orderBy('created_at', 'desc')->get();
    }
}
