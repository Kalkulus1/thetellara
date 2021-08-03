<?php

namespace Kalkulus\Thetellara;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Thetellara
{
    /**
     * Your environment mode
     * @var string
     */
    protected $tellerEnv;

    /**
     * Your environment mode initialze url
     * @var string
     */
    protected $tellerInitializeUrl;

    /**
     * Your environment mode payment response url
     * @var string
     */
    protected $tellerPaymentResponseUrl;

    /**
     * Issued Merchant ID from Theteller Dashboard
     * @var string
     */
    protected $merchantID;

    /**
     * Merchant redirect url
     * @var string
     */
    protected $redirectUrl;

    /**
     *  Issued API username from Theteller Dashboard
     * @var mixed
     */
    protected $apiUsername;

    /**
     * Issued API key from Theteller Dashboard
     * @var string
     */
    protected $apiKey;

    /**
     * Based 64 value with is formed from apiKey and apiUsername
     * @var string
     */
    protected $baseValue;

    public function __construct()
    {
        $this->setEnvMode();
        $this->setMerchantId();
        $this->setRedirectUrl();
        $this->setApiUsername();
        $this->setApiKey();
        $this->setBaseValue();
    }

    /**
     * Get Environment Mode from config file
     */
    public function setEnvMode()
    {
        $this->tellerEnv = Config::get('theteller.tellerEnv');

        if ($this->tellerEnv == 'test') {
            $this->tellerInitializeUrl = "https://test.theteller.net/checkout/initiate";
            $this->tellerPaymentResponseUrl = "https://test.theteller.net/v1.1/users/transactions/";
        } else {
            $this->tellerInitializeUrl = "https://checkout.theteller.net/initiate";
            $this->tellerPaymentResponseUrl = "https://prod.theteller.net/v1.1/users/transactions/";
        }
    }

    /**
     * Get Merchant ID from config file
     */
    public function setMerchantId()
    {
        $this->merchantID = Config::get('theteller.merchantId');
    }

    /**
     * Get Client Redirect URL from config file
     */
    public function setRedirectUrl()
    {
        $this->redirectUrl = Config::get('theteller.redirectUrl');
    }

    /**
     * Get API Username from config file
     */
    public function setApiUsername()
    {
        $this->apiUsername = Config::get('theteller.apiUsername');
    }

    /**
     * Get API Key from config file
     */
    public function setApiKey()
    {
        $this->apiKey = Config::get('theteller.apiKey');
    }

    /**
     * Get API Key from config file
     */
    public function setBaseValue()
    {
        $this->baseValue = $this->apiUsername.':'.$this->apiKey;
    }

    /**
     * @param string $transactionId
     * @param string $email
     * @param string $desc
     * @param float $amount
     */
    public function initialize($transactionId, $email, $amount, $desc = null)
    {
        $payload = json_encode(
                                [
                                    "merchant_id" => $this->merchantID,
                                    "transaction_id" => $transactionId,
                                    "desc" => $desc ?? "Payswitch Payment Request",
                                    "amount" => Str::of(str_pad($amount, 12, '0', STR_PAD_LEFT)),
                                    "redirect_url" => $this->redirectUrl,
                                    "email" => $email
                                ]
                            );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->tellerInitializeUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic ".base64_encode($this->baseValue)."",
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);


        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }

    public function getPaymentDetails()
    {
        $transactionId = request()->query('transaction_id');

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->tellerPaymentResponseUrl.$transactionId."/status",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Merchant-Id: ".$this->merchantID,
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            dd(json_decode($response));
            return json_decode($response);
        }
    }
}
