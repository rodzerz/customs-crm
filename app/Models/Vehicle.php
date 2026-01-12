<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CaseModel;

class Vehicle extends Model
{
    protected $fillable = [
        'external_id','plate_no','country','make','model','vin'
    ];

    public function cases()
    {
        return $this->hasMany(CaseModel::class, 'vehicle_id');
    }
}
