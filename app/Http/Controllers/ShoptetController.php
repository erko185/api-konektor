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
    private $oauthServerTokenUrl  = "";
    private $apiAccessTokenUrl = "";
    private $urlShop = "";

    public function __construct()
    {
        $this->clientId = env('clientId');
        $this->oauthServerTokenUrl  = env('oauthServerTokenUrl');
        $this->apiAccessTokenUrl = env('SHOPTETURLAPI');
        $this->urlShop = env('URLSHOP');
    }

    public function install(Request $request)
    {

        $oAuthRequest = [
            'code' => $request->code,
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'redirect_uri' =>  $request->getSchemeAndHttpHost().env('INSTALLURL'),
            'scope' => 'api'
        ];


        $curl = curl_init($this->oauthServerTokenUrl );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $oAuthRequest);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $jsonOAuthResponse = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);


        $oAuthResponse = json_decode($jsonOAuthResponse, true);
        ShoptetUser::insert(['scope' => $oAuthResponse['scope'],'eshop_id' => $oAuthResponse['eshopId'], 'eshop_url' => $oAuthResponse['eshopUrl'], 'access_token' => $oAuthResponse['access_token'], 'created_at' => date('Y-m-d H:i:s',strtotime("-2 hours"))]);
    }


    public function setting(Request $request)
    {

        if(!isset($request->access)){
            return $this->authetificationUser($request);
        }
        else{
            if(  $request->session()->get('access_token')==Logs::getToken($request->eshopId)){

                $userData=ShoptetUserLogin::where('eshop_id',$request->eshopId)->get();
                if(sizeof($userData)>0){
                    $decrypted=new HashPasswordController();
                    return view('shoptet_setting', ['name' => $userData[0]->name,'password'=>$decrypted->rw_hash($userData[0]->password,false),'address'=>$userData[0]->address]);

                }
                else{
                    return view('shoptet_setting', ['name' =>'','password'=>'','address'=>'']);
                }

            }
        }
    }


    public function authetificationUser(Request $request)
    {
        $dataUser = ShoptetUser::select('access_token','scope')->where("eshop_id", $request->eshopId)->get();

        if (sizeof($dataUser) < 1) {
            return 'false';
        } else {

            if(!isset($request->code)){
            $data = $this->getApiEndpointData('/eshop', $this->getToken($request->eshopId, $dataUser[0]->access_token));

            $baseOAuthUrl = null;

            foreach ($data['data']['urls'] as $value) {
                if ($value['ident'] == 'oauth') {
                    $baseOAuthUrl = $value['url'];
                }
            }

            $url = $baseOAuthUrl . 'authorize';

                $oAuthRequest = [
                    'client_id' =>  $this->clientId,
                    'response_type' => 'code',
                    'redirect_uri' =>"https://instal.techband.io/public/authorization",
                    'scope' => $dataUser[0]->scope,

                ];

                $request->session()->put('baseOAuthUrl', $baseOAuthUrl);
                $request->session()->put('scope', $dataUser[0]->scope);
                $request->session()->put('eshopId', $request->eshopId);



                $param['client_id'] = $this->clientId;
                $param['response_type'] = 'code';
                $param['scope'] = $dataUser[0]->scope;
                $param['redirect_uri'] = $request->getSchemeAndHttpHost().env('AUTHORIZATIONURL');
                $url = $url . "?" . http_build_query($param);

            return redirect($url);
            }
        }

    }


    public function code(Request $request){

//        $data = [
//            'code' => $request->code,
//            'grant_type' => 'authorization_code',
//            'client_id' => $this->clientId,
//            'redirect_uri' => $request->getSchemeAndHttpHost().env('AUTHORIZATIONURL'),
//            'scope' => 'api'
//        ];
//        $url = $request->session()->get('baseOAuthUrl') . 'token';
//
//        $curl = curl_init($url);
//        curl_setopt($curl, CURLOPT_POST, true);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//        curl_close($curl);
//
//        dump($response);
//
//        $data = json_decode($response, true);
//        $accessToken = $data['access_token'];
//
//        exit;
        if(isset($request->code)){

            $accessTokenMy=uniqid();
            $log=new Logs();
            $log->access_token=$accessTokenMy;
            $log->eshop_id=$request->session()->get('eshopId');
            $log->save();

            $request->session()->put('access_token',$accessTokenMy);

            return redirect( $request->getSchemeAndHttpHost()."/public/settings?eshopId=".$request->session()->get('eshopId').'&access=true')->header("aaa",'sadasd');


            return response()->json(['code'=> $request->code])
                ->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
        }
        else{
            return redirect( $request->getSchemeAndHttpHost()."/public/settings?eshopId=".$request->session()->get('eshopId').'&access=false');

            return response()->json(['code'=> null])
                ->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);
        }


    }

    public function getToken($eshopId, $oAuthToken)
    {

        $token = Tokens::getToken($eshopId);

        if ($token == null) {
            $token = $this->fetchNewApiAccessTokenData($oAuthToken);

//            $token=['access_token'=>"295925-a-703-0t75kg5xax5szai2oc6rcjf7mm83da06"];
            $tokenData=new Tokens();
            $tokenData->eshop_id=$eshopId;
            $tokenData->token= $token['access_token'];
            $tokenData->save();

            return $token['access_token'];

        } else {

            return $token;
        }


    }


    public function getApiEndpointData($endpoint, $accessToken)
    {
        $curl = curl_init("https://api.myshoptet.com/api" . $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Shoptet-Access-Token: " . $accessToken . "",
            "Content-Type: application/vnd.shoptet.v1.0"
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $data = json_decode($response, true);
        curl_close($curl);

        return $data;
    }


    public
    function fetchNewApiAccessTokenData($oAuthToken)
    {

        $curl = curl_init($this->apiAccessTokenUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $oAuthToken]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }


}
