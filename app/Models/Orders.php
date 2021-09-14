<?php

namespace App\Models;

use App\Http\Controllers\DEPO\DepoApiController;
use App\Models\DEPO\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Orders extends Model
{
    protected $table = 'orders';


    protected $fillable = [
        'id',
        'eshop_id',
        'order_id',
        'order_id_depo',
        'data',
        'status',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at'
    ];
}
