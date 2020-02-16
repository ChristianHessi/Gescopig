<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecondSession extends Model
{
    protected $fillable = [
        'contrat_id',
        'enseignement_id',
    ];

    public function contrat(){
        return $this->belongsTo(Contrat::class);
    }

    public function enseignement(){
        return $this->belongsTo(Enseignement::class);
    }
}
