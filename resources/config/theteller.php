<?php

/*
 * This file is part of Thetellara package.
 *
 * (c) Mumuni Mohammed <mumunim10@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
