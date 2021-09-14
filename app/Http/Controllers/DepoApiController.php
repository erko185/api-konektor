<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HashPasswordController;
use App\Models\Orders;
use App\Models\ShoptetUserLogin;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class DepoApiController extends Controller
{
    private $api = '';
    private $hashPasswordController ;

    public function __construct()
    {
        $this->api = env("DEPOAPI");
        $this->hashPasswordController=new HashPasswordController();
    }

    public function places()
    {

        $response = Http::acceptJson()->get($this->api . "/places");
        $places = $response->json()['_embedded']['places'];

        foreach ($places as &$place) {
            $place['depo_id'] = $place['id'];
            $place['open_hours'] = json_encode($place['open_hours']);

            unset($place['id']);
            unset($place['_links']);
        }

//        Place::upsert( $places, ['depo_id'] );
    }

    public function send($object,$userData)
    {


        if (sizeof($userData)<1){
            return response()->json()
                ->setStatusCode(Response::HTTP_UNAUTHORIZED, Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);
        }


        $response = Http::acceptJson()->withBasicAuth( $userData[0]->shoptetUserLogin->name, $this->hashPasswordController->rw_hash($userData[0]->shoptetUserLogin->password,false) )->post($this->api."/packages/send", $object );
        $responseJson = json_decode($response);

        if (isset($responseJson->number)) {
            Orders::insert(['eshop_id' => $userData[0]->eshop_id,'order_id'=>$object['sender_reference'],'order_id_depo'=>$responseJson->number, 'status' => 'send', 'data' => $response]);
            return response()->json()
                ->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
        } else if (isset($responseJson->status)) {
            if ($responseJson->status == 401) {
                Orders::insert(['eshop_id' => $userData[0]->eshop_id, 'status' => 'unauthorized', 'data' => json_encod($object)]);
                return response()->json()
                    ->setStatusCode(Response::HTTP_UNAUTHORIZED, Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);
            } else if ($responseJson->status == 406) {
                Orders::insert(['eshop_id' => $userData[0]->eshop_id, 'status' => 'Not Acceptable', 'data' =>  json_encod($object)]);

                return response()->json()
                    ->setStatusCode(Response::HTTP_NOT_ACCEPTABLE, Response::$statusTexts[Response::HTTP_NOT_ACCEPTABLE]);
            } else {
                Orders::insert(['eshop_id' => $userData[0]->eshop_id, 'status' => 'error', 'data' =>  json_encod($object)]);
                return response()->json()
                    ->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
            }
        }

    }


    public function getPackagesAuthentification(ShoptetUserLogin $shoptetUserLogin) {

        $response = Http::acceptJson()->withBasicAuth( $shoptetUserLogin->name, $this->hashPasswordController->rw_hash($shoptetUserLogin->password,false) )->get( $this->api."/packages" );
        return $response->status();
    }


    public function cancelSend($object,$userData){

        $response = Http::acceptJson()->withBasicAuth(  $userData[0]->shoptetUserLogin->name, $this->hashPasswordController->rw_hash( $userData[0]->shoptetUserLogin->password,false) )->post( $this->api."/packages/cancel",$object);
        return $response->status();


    }

}
