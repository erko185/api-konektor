<?php

namespace App\Http\Controllers;


use App\Models\ShoptetUserLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{



    public function password(Request $request,HashPasswordController $hashPasswordController,DepoApiController $depoApiController){


        if(sizeof(ShoptetUserLogin::where("eshop_id",$request->eshop_id)->get())>0){
            $state=  ShoptetUserLogin::where("eshop_id",$request->eshop_id)->update([
                'name'=>$request->email,
                'password'=> $hashPasswordController->rw_hash($request->password),
                'address'=> $request->address,


            ]);
        }
        else{


            $ShoptetUserLogin=new ShoptetUserLogin();
            $ShoptetUserLogin->name=$request->email;
            $ShoptetUserLogin->password=$hashPasswordController->rw_hash($request->password);
            $ShoptetUserLogin->eshop_id=$request->eshop_id;
            $ShoptetUserLogin->address=$request->address;

            if($depoApiController->getPackagesAuthentification($ShoptetUserLogin)==200){
                $state= $ShoptetUserLogin->save();            }
            else{
                return 'wrong data';
            }

        }


        if($state==1){
            return 'success';
        }
        else{
            return "error";
        }

    }




}
