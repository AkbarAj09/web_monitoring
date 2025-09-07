<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreatorPartner extends Model
{
    public $timestamps = false;
    protected $table = 'creator_partner';

    protected $fillable = [
        'area',
        'regional',
        'jenis_kol',
        'nama_kol',
        'email_kol',
        'no_hp_kol',
        'created_at',
        'updated_at'
    ];
}
