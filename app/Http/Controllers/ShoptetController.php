<?php

namespace App\Http\Controllers;


use App\Models\Logs;
use App\Models\ShoptetUser;
use App\Models\ShoptetUserLogin;
use App\Models\Tokens;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class ShoptetController extends Controller
{

    private $clientId = "";
    private $clientCode = "";
    private $oauthServerTokenUrl = "";
    private $apiAccessTokenUrl = "";
    private $urlShop = "";

    public function __construct()
    {
        $this->clientId = env('CLIENTID');
        $this->clientCode = env('CLIENTCODE');
        $this->oauthServerTokenUrl = env('OAUTHSERVERTOKENURL');
        $this->apiAccessTokenUrl = env('SHOPTETURLAPI');
        $this->urlShop = env('URLSHOP');
    }

    public function install(Request $request)
    {

        $oAuthRequest = [
            'code' => $request->code,
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientCode,
            'redirect_uri' => $request->getSchemeAndHttpHost() . env('INSTALLURL'),
            'scope' => 'api'
        ];


        $oAuthResponse = json_decode($this->requestSend($oAuthRequest,$this->oauthServerTokenUrl), true);
        ShoptetUser::insert(['scope' => $oAuthResponse['scope'], 'eshop_id' => $oAuthResponse['eshopId'], 'eshop_url' => $oAuthResponse['eshopUrl'], 'access_token' => $oAuthResponse['access_token'], 'created_at' => date('Y-m-d H:i:s', strtotime("-2 hours"))]);

        //install shipping method
        $data = [
            'data' => [

                'name' => "Depo.sk",
                'description' => "Delivery within 48 hours",
                'shippingCompanyCode' => "intime",
                'visibility' => true,
                'wholesale' => false,

            ]
        ];

        $this->getApiEndpointData('/shipping-methods', $this->getToken($oAuthResponse['eshopId'], $oAuthResponse['access_token']), json_encode($data));
        $this->hookRegistration($request, $this->getToken($oAuthResponse['eshopId'], $oAuthResponse['access_token']));
    }


    public function createOrder(Request $request)
    {

        $log = new Logs();
        $log->access_token = 'pokus';
        $log->eshop_id = 'asdasdasdasd';
        $log->save();


        exit;
        $dataUser = ShoptetUser::with("shoptetUserLogin")->where("eshop_id", $request->eshopId)->get();

        $data = $this->getApiEndpointData('/orders/2021000005', $this->getToken($request->eshopId, $dataUser[0]->access_token));


        if (strtolower($data['data']['order']['shipping']['name']) == "depo.sk") {
            $recipentName = $recipentPhone = $recipentStreet = $recipentZip = $recipentCity = $recipentCountry = $recipentEmail = "";

            if ($data['data']['order']['deliveryAddress'] != null) {
                $recipentName = $data['data']['order']['deliveryAddress']['fullName'];
                $recipentPhone = str_replace("+", "", preg_replace('/\s+/', '', $data['data']['order']['phone']));
                $recipentStreet = $data['data']['order']['deliveryAddress']['street'];
                $recipentZip = $data['data']['order']['deliveryAddress']['zip'];
                $recipentCity = $data['data']['order']['deliveryAddress']['city'];
                $recipentCountry = $data['data']['order']['deliveryAddress']['countryCode'];
                $recipentEmail = $data['data']['order']['email'];
            } else {
                $recipentName = $data['data']['order']['billingAddress']['fullName'];
                $recipentPhone = str_replace("+", "", preg_replace('/\s+/', '', $data['data']['order']['phone']));
                $recipentStreet = $data['data']['order']['billingAddress']['street'];
                $recipentZip = $data['data']['order']['billingAddress']['zip'];
                $recipentCity = $data['data']['order']['billingAddress']['city'];
                $recipentCountry = $data['data']['order']['billingAddress']['countryCode'];
                $recipentEmail = $data['data']['order']['email'];
            }


            if($dataUser[0]->shoptetUserLogin->address!=""){
                $object = [
                    "target" => "31703542",
                    "recipient_name" => $recipentName,
                    "recipient_phone" => $recipentPhone,
                    "recipient_street" => $recipentStreet,
                    "recipient_zip" => $recipentZip,
                    "recipient_city" => $recipentCity,
                    "recipient_country" => $recipentCountry,
                    "recipient_email" => $recipentEmail,
                    "insurance_currency" => $data['data']['order']['price']['currencyCode'],
                    "insurance" => $data['data']['order']['price']['withVat'],
                    'sender_reference' => '2021000004',
                    "deliver_to_address" => "Doručiť na adresu zákazníka",
                    "pickup_from_address" => "Vyzdvihnúť z adresy klienta",
                ];
            }
            else{
                $object = [
                    "target" => "31703542",
                    "recipient_name" => $recipentName,
                    "recipient_phone" => $recipentPhone,
                    "recipient_street" => $recipentStreet,
                    "recipient_zip" => $recipentZip,
                    "recipient_city" => $recipentCity,
                    "recipient_country" => $recipentCountry,
                    "recipient_email" => $recipentEmail,
                    "insurance_currency" => $data['data']['order']['price']['currencyCode'],
                    "insurance" => $data['data']['order']['price']['withVat'],
                    'sender_reference' => '2021000004',
                ];
            }


            $depoApiController=new DepoApiController();
            $depoApiController->send($object,$dataUser);

        }



    }


    public function cancelOrder(Request $request){

        $dataUser = ShoptetUser::with("shoptetUserLogin")->where("eshop_id", $request->eshopId)->get();


        $object=[
            'number'=>'2000002839576'
        ];

        $depoApiController=new DepoApiController();
        dump($depoApiController->cancelSend($object,$dataUser));
        exit();
    }



    public function hookRegistration(Request $request, $token)
    {
        $data = [
            'data' => [
                [
                    'event' => "order:create",
                    'url' => $request->getSchemeAndHttpHost() . env('CREATEORDER')
                ],
            ]
        ];


        $apiResponse = $this->getApiEndpointData('/webhooks', $token, json_encode($data));

        return $apiResponse;
    }

    public function unistall(Request $request)
    {


        $body = file_get_contents('php://input');
        $webhook = json_decode($body, TRUE);
        $log = new Logs();
        $log->access_token = 'nnnnn';
        $log->eshop_id = 'asdasdasdasd';
        $log->save();


        ShoptetUser::where("eshop_id", $webhook['eshopId'])->delete();
    }


    public function setting(Request $request)
    {

        if (!isset($request->access)) {
            return $this->authetificationUser($request);
        } else if($request->access=='true') {
            if ($request->session()->get('access_token') == Logs::getToken($request->eshopId)) {

                $userData = ShoptetUserLogin::where('eshop_id', $request->eshopId)->get();
                if (sizeof($userData) > 0) {
                    $decrypted = new HashPasswordController();
                    return view('shoptet_setting', ['access'=>true,'name' => $userData[0]->name, 'password' => $decrypted->rw_hash($userData[0]->password, false), 'address' => $userData[0]->address]);

                } else {
                    return view('shoptet_setting', ['access' => true,'name' => '', 'password' => '', 'address' => '']);
                }

            }
        }
        else{
            return view('shoptet_setting', ['access' => false]);

        }
    }


    public function authetificationUser(Request $request)
    {
        $dataUser = ShoptetUser::select('access_token', 'scope')->where("eshop_id", $request->eshopId)->get();

        if (sizeof($dataUser) < 1) {
            return 'false';
        } else {

            if (!isset($request->code)) {
                $data = $this->getApiEndpointData('/eshop', $this->getToken($request->eshopId, $dataUser[0]->access_token));

                $baseOAuthUrl = null;

                foreach ($data['data']['urls'] as $value) {
                    if ($value['ident'] == 'oauth') {
                        $baseOAuthUrl = $value['url'];
                    }
                }

                $url = $baseOAuthUrl . 'authorize';
                $state = uniqid();

                $request->session()->put('baseOAuthUrl', $baseOAuthUrl);
                $request->session()->put('scope', $dataUser[0]->scope);
                $request->session()->put('eshopId', $request->eshopId);
                $request->session()->put('state', $state);


                $param['client_id'] = $this->clientId;
                $param['response_type'] = 'code';
                $param['scope'] = $dataUser[0]->scope;
                $param['state'] = $state;
                $param['redirect_uri'] = $request->getSchemeAndHttpHost() . env('AUTHORIZATIONURL');
                $url = $url . "?" . http_build_query($param);

                return redirect($url);
            }
        }

    }


    public function code(Request $request)
    {

        if( $request->state==$request->session()->get('state') ){
        $data = [
            'code' => $request->code,
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientCode,
            'redirect_uri' => $request->getSchemeAndHttpHost().env('AUTHORIZATIONURL'),
            'scope' => $request->session()->get('scope')
        ];
        $url = $request->session()->get('baseOAuthUrl') . 'token';

        $response = json_decode($this->requestSend($data,$url), TRUE);
        $accessToken = $response['access_token'];

        if ($accessToken!=="" || $accessToken!=null) {

            $accessTokenMy = uniqid();
            $log = new Logs();
            $log->access_token = $accessTokenMy;
            $log->eshop_id = $request->session()->get('eshopId');
            $log->save();

            $request->session()->put('access_token', $accessTokenMy);

            return redirect($request->getSchemeAndHttpHost() . "/public/settings?eshopId=" . $request->session()->get('eshopId') . '&access=true')->header("aaa", 'sadasd');


            return response()->json(['code' => $request->code])
                ->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
        } else {
            return redirect($request->getSchemeAndHttpHost() . "/public/settings?eshopId=" . $request->session()->get('eshopId') . '&access=false');

            return response()->json(['code' => null])
                ->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
        }
        }{
        return redirect($request->getSchemeAndHttpHost() . "/public/settings?eshopId=" . $request->session()->get('eshopId') . '&access=false');

        return response()->json(['code' => null])
            ->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
    }


    }

    public function getToken($eshopId, $oAuthToken)
    {

        $token = Tokens::getToken($eshopId);

        if ($token == null) {
            $token = $this->fetchNewApiAccessTokenData($oAuthToken);

//            $token=['access_token'=>"295925-a-703-0t75kg5xax5szai2oc6rcjf7mm83da06"];
            $tokenData = new Tokens();
            $tokenData->eshop_id = $eshopId;
            $tokenData->token = $token['access_token'];
            $tokenData->save();

            return $token['access_token'];

        } else {

            return $token;
        }


    }


    public function getApiEndpointData($endpoint, $accessToken, $data = null)
    {
        $curl = curl_init("https://api.myshoptet.com/api" . $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Shoptet-Access-Token: " . $accessToken . "",
            "Content-Type: application/vnd.shoptet.v1.0"
        ]);

        if ($data !== null) {

            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $data = json_decode($response, true);
        curl_close($curl);

        return $data;
    }


    public function fetchNewApiAccessTokenData($oAuthToken)
    {

        $curl = curl_init($this->apiAccessTokenUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $oAuthToken]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    public function requestSend($data,$url){
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $returnData = curl_exec($curl);
        curl_close($curl);

        return $returnData;
    }


}
