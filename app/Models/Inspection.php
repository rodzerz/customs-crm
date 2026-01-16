<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [
        'external_id',
        'case_id',
        'type',       // document, RTG, physical
        'status',     // pending, completed
        'decision',   // release, hold, reject
        'comment',
        'performed_at',
        'decision_reason',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    // Valid inspection types
    public const TYPES = ['document', 'RTG', 'physical'];

    // Valid decisions
    public const DECISIONS = ['release', 'hold', 'reject'];

    // Relācija uz case
    public function case()
    {
        return $this->belongsTo(\App\Models\CaseModel::class, 'case_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    public function isValid()
    {
        return in_array($this->type, self::TYPES);
    }

    public function recordDecision($decision, $reason = null)
    {
        if (!in_array($decision, self::DECISIONS)) {
            throw new \Exception("Invalid inspection decision: {$decision}");
        }

        $this->update([
            'decision' => $decision,
            'decision_reason' => $reason,
            'status' => 'completed',
            'performed_at' => now(),
        ]);

        // Handle case status based on decision
        $case = $this->case;
        if ($decision === 'release') {
            $case->transitionTo('released', "Released after {$this->type} inspection");
        } elseif ($decision === 'hold') {
            $case->transitionTo('on_hold', "On hold pending {$this->type} inspection review");
        } elseif ($decision === 'reject') {
            $case->transitionTo('rejected', "Rejected after {$this->type} inspection");
        }
    }
}
