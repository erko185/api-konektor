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

    public function __construct()
    {
        $this->api = 'https://admin.depo.sk/v2/api';
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

    public function send()
    {


        $object = [
            "target" => "31703542",
            "recipient_name" => "Erik",
            "recipient_phone" => "421910106954",
            "recipient_street" => "Karola",
            "recipient_number" => "5",
            "recipient_zip" => "06001",
            "recipient_city" => "Kezmarok",
            "recipient_country" => "Slovensko",
            "recipient_email" => "erko185@gmail.com",
            "cod" => "255",
            "insurance" => "255",
            "size_a" => "",
            "service_18plus" => "0",
            "deliver_to_address" => "Doručiť na adresu zákazníka",
            "pickup_from_address" => "Vyzdvihnúť z adresy klienta"
        ];

        $userData=ShoptetUserLogin::where("client_id",'154545')->get();

        if (sizeof($userData)<1){
            return response()->json()
                ->setStatusCode(Response::HTTP_UNAUTHORIZED, Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);
        }

        $user = $userData[0]->name;
        $password = $userData[0]->password;

        $hashPassword = new HashPasswordController();
        $apiPassword = $password;
//        $response = '{"number":"2000002822820","price":3.65,"region":""}';

        $response = Http::acceptJson()->withBasicAuth( $user, $apiPassword )->post($this->api."/packages/send", $object );
        $responseJson = json_decode($response);

        if (isset($responseJson->number)) {
            Orders::insert(['shop_id' => 'aaa', 'client_id' => '11555', 'status' => 'send', 'data' => $response]);
            return response()->json()
                ->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
        } else if (isset($responseJson->status)) {
            if ($responseJson->status == 401) {
                Orders::insert(['shop_id' => 'aaa', 'client_id' => '11555', 'status' => 'unauthorized', 'data' => $response]);
                return response()->json()
                    ->setStatusCode(Response::HTTP_UNAUTHORIZED, Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);
            } else if ($responseJson->status == 406) {
                Orders::insert(['shop_id' => 'aaa', 'client_id' => '11555', 'status' => 'Not Acceptable', 'data' => $response]);

                return response()->json()
                    ->setStatusCode(Response::HTTP_NOT_ACCEPTABLE, Response::$statusTexts[Response::HTTP_NOT_ACCEPTABLE]);
            } else {
                Orders::insert(['shop_id' => 'aaa', 'client_id' => '11555', 'status' => 'error', 'data' => $response]);
                return response()->json()
                    ->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
            }
        }

    }

//    public function package(Kiosk $kiosk, string $number) {
//        $hashPassword = new HashPasswordController();
//        $apiPassword = $hashPassword->unHashPassword($kiosk->depo_api_password);
//
//        $response = Http::acceptJson()->withBasicAuth( $kiosk->depo_api_user, $apiPassword )->get( "$this->api/packages/$number" );
//        return $response;
//    }
//
//    public function packages(Kiosk $kiosk) {
//        $hashPassword = new HashPasswordController();
//        $apiPassword = $hashPassword->unHashPassword($kiosk->depo_api_password);
//
//        $response = Http::acceptJson()->withBasicAuth( $kiosk->depo_api_user, $apiPassword )->get( "$this->api/packages" );
//        return $response;
//    }

}
