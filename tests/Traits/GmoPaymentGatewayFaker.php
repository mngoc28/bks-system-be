<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Http;

trait GmoPaymentGatewayFaker
{
    private string $sandboxEndpoint = "https://pt01.mul-pay.jp";

    private string $entryTran = "/payment/EntryTran.json";

    private function gmoPaymentRequestFail(): void
    {
        $apiUrl = rtrim($this->sandboxEndpoint . $this->entryTran);

        Http::fake([
            $apiUrl => Http::response([
                'httpStatusCode' => 400,
                'hasError' => true,
                'responseData:' => "",
                'errorMessages:' =>  [
                    "errCode" => "E01",
                    "errInfo" => "E01090001",
                ],
                'appendData' => []
            ], 400),
        ]);
    }
}
