<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
            'case_parties'
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

    // Dokumenti
    public function documents()
    {
        return $this->hasMany(Document::class, 'case_id');
    }
}
