<?php

namespace Tests\Feature;;

//use App\Services\Gmo\Core\Shop\Encryption\CreditCardToken;
use App\Models\User;
use App\Services\Gmo\Contracts\Shop\CreditCard\Basic;
use App\Services\Gmo\Core\Shop\Encryption\CreditCardTokenizer;
use App\Services\Gmo\GmoPaymentGateway;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GmoPaymentGatewayTest extends TestCase
{

//    public function test_initialize_package()
//    {
//        $gmo = new GmoPaymentGateway();
//        $this->assertTrue($gmo->useShopApi()->creditCard()->entryTransaction([])->hasError());
//    }


//    public function test_save_member()
//    {
//        $user = User::firstOrCreate([
//            'email' => 'test@example.com',
//            'password' => Hash::make('password123'),
//            'ekyc_status' => 4
//        ]);
////        $user = User::first();
//        $data = [
//            "MemberID" => $user->id,
//            'MemberName' => "test",
//        ];
//        $gmo = new GmoPaymentGateway();
//        $response = $gmo->useSiteApi()->saveMember($data);
////        dump($response);
//        $this->assertTrue($response == $data);
//    }
//    public function test_delete_member()
//    {
//        $data = [
//            "MemberID" => '1',
//        ];
//        $gmo = new GmoPaymentGateway();
//        $response = $gmo->useSiteApi()->deleteMember($data);
//        $this->assertTrue($response == [ "MemberID" => "1" ]);
//    }
//    public function test_save_card()
//    {
//        $user = User::first();
//        $gmo = new GmoPaymentGateway();
//        $data = [
//            'cardNo' => '4111111111111111',
//            'expire' => '3412',
//            'securityCode' => '123',
//            'holderName' => 'NGUYEN VAN A',
//        ];
//        $token = new CreditCardTokenizer($data);
//        $genCardToken = $gmo->creditCard()->getCreditCardToken($token);
//        $arr = $genCardToken->getResult();
//        dd($arr);
//        $data = [
////            "siteID" => $user->id,
////            'sitePass' => $arr["tokenObject"]["token"][0],
//            "memberID" => $user->id,
//            'token' => $arr["tokenObject"]["token"][0],
//        ];
//        $response = $gmo->useSiteApi()->saveCard($data);
//        $this->assertTrue($response["CardNo"] == $data["CardNo"]);
//    }

//    public function test_search_card()
//    {
//        $user = User::first();
//        $data = [
//            "memberID" => 2,
//            "validFlag" => 0
//        ];
//        $gmo = new GmoPaymentGateway();
//        $response = $gmo->useSiteApi()->searchCard($data);
//        dd($response->getResult());
////        $this->assertTrue($response == [ "MemberID" => "1" ]);
//    }
//
//    public function test_credit()
//    {
//
//        $gmo = new GmoPaymentGateway();
//        $orderId = uniqid();
//        $data = ['OrderID' => $orderId, 'JobCd' => 'AUTH', 'Amount' => 1000, 'Method' => '1'];
//        $response = $gmo->creditCard()
//            ->entryTransaction($data, function (Basic $gmo) use (&$data) {
//                $data['Method'] = 1;
//                $data['PayTimes'] = 1;
//                $data['CardNo'] = '4111111111111111';
//                $data['Expire'] = '3412';
//                return $gmo->execTransaction($data);
//            });
//
//        $this->assertTrue($orderId == $response->getResult()['OrderID']);
//    }
//
//    public function test_create_token()
//    {
//
//        $gmo = new GmoPaymentGateway();
//        $data = [
//            'cardNo' => '4111111111111111',
//            'expire' => '3412',
//            'securityCode' => '123',
//            'holderName' => 'NGUYEN VAN A',
//        ];
//        $token = new CreditCardTokenizer($data);
//        $genCardToken = $gmo->creditCard()->getCreditCardToken($token);
//        $arr = $genCardToken->getResult();
//
//        $this->assertTrue($genCardToken->getHttpStatusCode() == "200");
//        $this->assertTrue($arr["resultCode"][0] == "000");
//    }
}
