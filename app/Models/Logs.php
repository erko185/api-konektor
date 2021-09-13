<?php

namespace App\Models;

use App\Http\Controllers\DEPO\DepoApiController;
use App\Models\DEPO\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Logs extends Model
{
    protected $table = 'logs';


    protected $fillable = [
        'id',
        'pokus'
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at'
    ];
}
