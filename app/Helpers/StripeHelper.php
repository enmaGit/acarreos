<?php

namespace App\Helpers;

/**
 * Created by PhpStorm.
 * User: EnmanuelPc
 * Date: 03/11/2015
 * Time: 3:39
 */
class StripeHelper
{

    public static function generateCharge($token, $amount, $description)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY', 'NO_tE_LA_dIRE'));
        return $charge = \Stripe\Charge::create(array(
          "amount" => $amount,
          "currency" => "usd",
          "description" => $description,
          "source" => $token,
        ));
    }

}
