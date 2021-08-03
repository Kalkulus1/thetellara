# thetellara

[![Latest Stable Version](https://poser.pugx.org/kalkulus/thetellara/v/stable.svg)](https://packagist.org/packages/kalkulus/thetellara)
[![License](https://poser.pugx.org/kalkulus/thetellara/license.svg)](LICENSE.md)
[![Build Status](https://travis-ci.com/Kalkulus1/thetellara.svg?branch=main)](https://travis-ci.com/github/Kalkulus1/thetellara)
[![Quality Score](https://img.shields.io/scrutinizer/g/kalkulus1/thetellara.svg?style=flat-square)](https://scrutinizer-ci.com/g/Kalkulus1/thetellara/)
[![Total Downloads](https://img.shields.io/packagist/dt/kalkulus/thetellara.svg?style=flat-square)](https://packagist.org/packages/kalkulus/thetellara)

>A laravel package for theteller payment gateway

## Inspiration
I got inspiration from [Unideveloper](https://github.com/unicodeveloper/laravel-paystack)

## Installation

[PHP](https://php.net) 5.4+ and [Composer](https://getcomposer.org) are required.

If you have your own server, make sure php-curl is installed.

```bash
apt-get install php-curl
```

To get the latest version of Thetallara, simply require it

```bash
composer require kalkulus/thetallara
```

Or add the following line to the require block of your `composer.json` file.

```
"kalkulus/thetallara": "1.0.*"
```

You'll then need to run `composer install` or `composer update` to download it and have the autoloader updated.


Once Thetellara is installed, you need to register the service provider. Open up `config/app.php` and add the following to the `providers` key.

```php
'providers' => [
    ...
    Kalkulus\Thetellara\ThetellaraServiceProvider::class,,
    ...
]
```

> If you use **Laravel >= 5.5** you can skip this step and go to [**`configuration`**](https://github.com/kalkulus1/thetellara#configuration)

* `Kalkulus\Thetellara\ThetellaraServiceProvider::class,`

Also, register the Facade like so:

```php
'aliases' => [
    ...
    'Thetellara' => Kalkulus\Thetellara\ThetellaraServiceProvider::class,
    ...
]
```

## Configuration

You can publish the configuration file using this command:

```bash
php artisan vendor:publish --provider="Kalkulus\Thetellara\ThetellaraServiceProvider"
```

A configuration-file named `theteller.php` with some sensible defaults will be placed in your `config` directory:

```php
<?php

return [

    /**
     * Your environment. Either Production (prod) or Test (test)
     *
     */
    'tellerEnv' => getenv('THETELLER_ENV', 'test'),

    /**
     * Merchant ID From Theteller Dashboard
     *
     */
    'merchantId' => getenv('THETELLER_MERCHANT_ID'),

    /**
     * Theteller API Username
     *
     */
    'apiUsername' => getenv('THETELLER_API_USERNAME'),

    /**
     * Theteller API Key
     *
     */
    'apiKey' => getenv('THETELLER_API_KEY'),

    /**
     * Theteller Redirect Url
     *
     */
    'redirectUrl' => getenv('THETELLER_REDIRECT_URL'),
];

```

## General payment flow

Though there are multiple ways to pay an order, most payment gateways expect you to follow the following flow in your checkout process:

### 1. The customer is redirected to the payment provider
After the customer has gone through the checkout process and is ready to pay, the customer must be redirected to the site of the payment provider.

The redirection is accomplished by submitting a form with some hidden fields. The form must send a POST request to the site of the payment provider. The hidden fields minimally specify the amount that must be paid, the order id and a hash.

The hash is calculated using the hidden form fields and a non-public secret. The hash used by the payment provider to verify if the request is valid.


### 2. The customer pays on the site of the payment provider
The customer arrives on the site of the payment provider and gets to choose a payment method. All steps necessary to pay the order are taken care of by the payment provider.

### 3. The customer gets redirected back to your site
After having paid the order the customer is redirected back. In the redirection request to the shop-site some values are returned. The values are usually the order id, a payment result and a hash.

The hash is calculated out of some of the fields returned and a secret non-public value. This hash is used to verify if the request is valid and comes from the payment provider. It is paramount that this hash is thoroughly checked.


## Usage

Open your .env file and add your public key, secret key, merchant email and payment url like so:

```php
THETELLER_ENV=xxxx
THETELLER_MERCHANT_ID=xxxxxxxxxxxx
THETELLER_REDIRECT_URL=http://baseurl/payment/callback
THETELLER_API_USERNAME=xxxxxxxxxxxxxx
THETELLER_API_KEY=xxxxxxxxxxxxxxxxxx
```
*Set the `THETELLER_ENV` to `test` if your just testing and `prod` if you are going on production.*

*If you are using a hosting service like heroku, or digitalocean app platform, etc ensure to add the above details to your configuration variables.*


Set up routes and controller methods like so:

```php
// Laravel 8
Route::post('/pay', [App\Http\Controllers\PaymentController::class, 'redirectToGateway'])->name('pay');
```


```php
Route::get('/payment/callback', 'PaymentController@handleGatewayCallback');
```
*Make sure to register the callback url in your env*

```php

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kalkulus\Thetellara\Thetellara;
use Illuminate\Support\Facades\Redirect;

class PaymentController extends Controller
{
    /**
     * Redirect the User to Theteller Payment Page
     * @return Url
     */

    public function redirectToGateway()
    {
        $transactionId = mt_rand(100000000000,999999999999); // Must be unique for all transactions... 
        // You may use the function I wrote below for unique transaction id `genTransactionId()` 
        // In that case you keep track of the transaction IDs in payments table in the database. 
        // Hence call it as $transactionId = $this->genTransactionId();
        $email = request()->input('email'); //Customer email
        $amount = request()->input('amount');; //Amount with is in pesewas. So 200 is actually GHS 2.00
        $desc = request()->input('desc');
        try{
            $thetellar = new Thetellara();
            $response =  $thetellar->initialize($transactionId, $email, $amount, $desc);
            return redirect($response->checkout_url);

        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['error'=>'The transaction has expired']);
        }
    }

    /**
     * Get Theteller payment information
     * @return void
     */
    public function success()
    {
        $thetellar = new Thetellara();
        $response = $thetellar->getPaymentDetails();
        dd($response);

        // Now you have the payment details,
        // you can then redirect or do whatever you want
    }


    /**
     * Get unique transaction ID
     * @return string
     */
    private function genTransactionId(){
        $this->transactionId = [
            'transactionId' => mt_rand(100000000000,999999999999)
        ];

        $rules = ['transactionId' => 'unique:payments'];

        $validate = Validator::make($this->transactionId, $rules)->passes();

        return $validate ? $this->transactionId['transactionId'] : $this->genTransactionId();
    }
}

```

```html
<form action="{{ route('pay') }}" method="POST">
    @csrf
     <input type="hidden" name="amount" value="200"> {{-- required in pesewas. Set it up here and request it in your controller --}}
     <input type="hidden" name="email" value="customer@email.com"> {{-- Required --}}
     <input type="hidden" name="desc" value="Theteller Payment Request"> {{-- Required --}}
    <button type="submit" class="btn btn-primary">Pay</button>
</form>
```

## Todo
* Direct payments -> Card and Mobile Money
* Fund transfer
* Add Comprehensive Tests
* Implement Transaction Dashboard to see all of the transactions in your laravel app

## Contributing

Please feel free to fork this package and contribute by submitting a pull request to enhance the functionalities.

## How can I thank you?

Why not star the github repo? I'd love the attention! Why not share the link for this repository on Twitter or any where? Spread the word!

Don't forget to [follow me on twitter](https://twitter.com/kalkulus_1)!

Thanks!
Mumuni Mohammed.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
