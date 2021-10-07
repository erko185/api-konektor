<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HashPasswordController;
use App\Models\Orders;
use App\Models\ShoptetUser;
use App\Models\ShoptetUserLogin;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class DepoApiController extends Controller
{
    private $api = '';
    private $hashPasswordController;

    public function __construct()
    {
        $this->api = env("DEPOAPI");
        $this->hashPasswordController = new HashPasswordController();
    }

    public function places()
    {
        $response = Http::acceptJson()->get($this->api . "/places");
        $places = $response->json()['_embedded']['places'];

        return $places;
    }

    public function send($object, $userData, $again = false)
    {


        if (sizeof($userData) < 1) {
            return response()->json()
                ->setStatusCode(Response::HTTP_UNAUTHORIZED, Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);
        }


        $response = Http::acceptJson()->withBasicAuth($userData[0]->shoptetUserLogin->name, $this->hashPasswordController->rw_hash($userData[0]->shoptetUserLogin->password, false))->post($this->api . "/packages/send", $object);
        $responseJson = json_decode($response);

        if (isset($responseJson->number)) {
            if ($again == false) {
                Orders::insert(['eshop_id' => $userData[0]->eshop_id, 'order_id' => $object['sender_reference'], 'order_id_depo' => $responseJson->number, 'status' => 'send', 'data' => json_encode($object), 'price' => $responseJson->price]);
                return response()->json()->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);;

            } else {
                return response()->json($responseJson)->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);;
            }
        } else if (isset($responseJson->status)) {
            if ($responseJson->status == 401) {
                if ($again == false) {
                    Orders::insert(['eshop_id' => $userData[0]->eshop_id, 'status' => 'unauthorized', 'data' => json_encode($object), 'order_id' => $object['sender_reference']]);
                    return response()->json()->setStatusCode(Response::HTTP_UNAUTHORIZED, Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);
                } else {
                    return response()->json()->setStatusCode(Response::HTTP_UNAUTHORIZED, Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);;
                }
            } else if ($responseJson->status == 406) {
                if ($again == false) {
                    Orders::insert(['eshop_id' => $userData[0]->eshop_id, 'status' => 'Not Acceptable', 'data' => json_encode($object), 'order_id' => $object['sender_reference']]);
                    return response()->json()->setStatusCode(Response::HTTP_NOT_ACCEPTABLE, Response::$statusTexts[Response::HTTP_NOT_ACCEPTABLE]);
                } else {
                    return response()->json()->setStatusCode(Response::HTTP_NOT_ACCEPTABLE, Response::$statusTexts[Response::HTTP_NOT_ACCEPTABLE]);;
                }

            } else if ($responseJson->status == 422) {
                if ($again == false) {
                    Orders::insert(['eshop_id' => $userData[0]->eshop_id, 'status' => 'Unprocessable Entity', 'data' => json_encode($object), 'order_id' => $object['sender_reference']]);
                    return response()->json()->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY]);
                } else {
                    return response()->json()->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY]);;
                }

            }
            else {
                if ($again == false) {
                    Orders::insert(['eshop_id' => $userData[0]->eshop_id, 'status' => 'error', 'data' => json_encode($object), 'order_id' => $object['sender_reference']]);
                    return response()->json()->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
                } else {
                    return response()->json()->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, Response::$statusTexts[Response::HTTP_METHOD_NOT_ALLOWED]);;
                }
            }
        }

    }


    public function getPackagesAuthentification(ShoptetUserLogin $shoptetUserLogin)
    {

        $response = Http::acceptJson()->withBasicAuth($shoptetUserLogin->name, $this->hashPasswordController->rw_hash($shoptetUserLogin->password, false))->get($this->api . "/packages");
        return $response->status();
    }


    public function cancelSend($orderId, $userData)
    {

        $order=Orders::where('order_id',$orderId)->get();
         $object = [
             'number' =>$order[sizeof($order)-1]->order_id_depo
         ];


        $response = Http::acceptJson()->withBasicAuth($userData[0]->shoptetUserLogin->name, $this->hashPasswordController->rw_hash($userData[0]->shoptetUserLogin->password, false))->post($this->api . "/packages/cancel", $object);
        return $response->status();


    }

    public function sendAgain()
    {
        $orders = Orders::where('status', 'error')->get();
        foreach ($orders as $order) {
            $dataUser = ShoptetUser::with("shoptetUserLogin")->where("eshop_id", $order->eshop_id)->get();

            $response=$this->send((array) json_decode($order->data), $dataUser,true);

            if($response->status()==200){
                $responseJson = json_decode($response->content());
               Orders::where("id",$order->id)->update([
                   'status'=>'send',
                   'price'=>$responseJson->price,
                   'order_id_depo'=>$responseJson->number
               ]);
            }
            else if ($response->status()==401){
                Orders::where("id",$order->id)->update([
                    'status'=>'unauthorized',
                ]);
            }
            else if ($response->status()==406){
                Orders::where("id",$order->id)->update([
                    'status'=>'Not Acceptable',
                ]);
            }  else if ($response->status()==422){
                Orders::where("id",$order->id)->update([
                    'status'=>'Unprocessable Entity',
                ]);
            }
            else if ($response->status()==405){
                Orders::where("id",$order->id)->update([
                    'status'=>'error',
                ]);
            }
        }
    }

}
