<?php

namespace App\Models;

use App\Http\Controllers\DEPO\DepoApiController;
use App\Models\DEPO\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ShoptetUserLogin extends Model
{
    protected $table = 'shoptet_user_login';


    protected $fillable = [
        'id',
        'name',
        'password',
        'eshop_id',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at'
    ];
}
