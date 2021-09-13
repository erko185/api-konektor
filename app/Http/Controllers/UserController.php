<?php

namespace App\Http\Controllers;


use App\Models\ShoptetUserLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function password(Request $request){


        if(sizeof(ShoptetUserLogin::where("eshop_id",$request->eshop_id)->get())>0){
            $state=  ShoptetUserLogin::where("eshop_id",$request->eshop_id)->update([
                'name'=>$request->email,
                'password'=> Hash::make($request->password),
                'address'=> $request->address,


            ]);
        }
        else{
            $state= ShoptetUserLogin::insert(["name"=>$request->email,"password"=>Hash::make($request->password),"eshop_id"=>$request->eshop_id, 'address'=> $request->address,]);

        }


        if($state==1){
            return 'success';
        }
        else{
            return "error";
        }

    }




}
