<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseCargoItem extends Model
{
    protected $table = 'case_cargo_items';

    protected $fillable = [
        'external_id',
        'case_id',
        'hs_code',
        'description',
        'weight',
        'value',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }
}