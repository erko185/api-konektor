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
        'access_token',
        'eshop_id',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at'
    ];

    public static function getToken($eshopId){


        $token=Logs::select('access_token')->where("eshop_id",$eshopId)->orderBy('created_at','desc')->get();

        if(sizeof($token)>0){
            return $token[0]->access_token;
        }
        else{
            return null;
        }


    }

}
