<?php

namespace App\Models;

use App\Http\Controllers\DEPO\DepoApiController;
use App\Models\DEPO\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Tokens extends Model
{
    protected $table = 'tokens';


    protected $fillable = [
        'id',
        'eshop_id',
        'token',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at'
    ];



    public static function getToken($eshopId){


        $token=Tokens::select('token')->where([["eshop_id",$eshopId],['created_at','<=',date("Y-m-d H:i:s", strtotime("-30 minutes"))]])->orderBy('created_at','desc')->get();

        if(sizeof($token)>0){
            return $token[0]->token;
        }
        else{
            return null;
        }


    }

}
