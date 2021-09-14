<?php

namespace App\Models;

use App\Http\Controllers\DEPO\DepoApiController;
use App\Models\DEPO\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ShoptetUser extends Model
{
    protected $table = 'shoptet_user';


    protected $fillable = [
        'id',
        'name',
        'eshop_id',
        'eshop_url',
        'contact_email',
        'access_token',
        'scope',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at'
    ];

    public function shoptetUserLogin()
    {
        return $this->belongsTo(ShoptetUserLogin::class,'eshop_id','eshop_id');
    }

}
