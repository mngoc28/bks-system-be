<?php

namespace Tests\Traits;

use App\Services\Gmo\GmoPaymentGateway;
use App\Services\Gmo\Core\Shop\Encryption\CreditCardTokenizer;
use Exception;
use Illuminate\Support\Facades\Log;

trait TransactionGmo
{
    private $cardNumber = '4111111111111111';

    private $expriedDate;

    private $securityNumber;

    private $cardHoderName;

    private $memberID;

    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
    }

    public function setExpriedDate($expriedDate)
    {
        $this->expriedDate = $expriedDate;
    }

    public function setSecurityNumber($securityNumber)
    {
        $this->securityNumber = $securityNumber;
    }

    public function setCardHoderName($cardHoderName)
    {
        $this->cardHoderName = $cardHoderName;
    }
    public function setMemberID($memberID)
    {
        $this->memberID = $memberID;
    }


    public function addCreditCard(): void
    {
        try {
            $gmo = new GmoPaymentGateway();

            $data = [
                "cardNo" => $this->cardNumber,
                "expire" => $this->expriedDate,
                "holderName" => $this->cardHoderName,
                "securityCode" => $this->securityNumber,
            ];

            $token = new CreditCardTokenizer($data);
            $genCardToken = $gmo->creditCard()->getCreditCardToken($token);

            if (! $genCardToken->hasError()) {
                $arr = $genCardToken->getResult();

                $data = [
                    "memberID" => $this->memberID,
                    "token" => $arr["tokenObject"]["token"][0],
                ];

                $gmo->useSiteApi()->saveCard($data);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
