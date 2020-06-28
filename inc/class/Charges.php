<?php

/**
 * Created by PhpStorm.
 * User: jimmee
 * Date: 4/18/20
 * Time: 9:32 PM
 */
class Charges
{
    static function makeCharge($options = []){
        \Payjp\Payjp::setApiKey(sk);
        $options = array_merge([
            "card" => '',
            "amount" => 0,
            "currency" => "jpy",
            "capture" => false,
            "expiry_days" => 60,
        ], $options);
        if($options['amount'] > 0){
            $data = \Payjp\Charge::create($options);
            return responseSuccess(['charge_id' => base64_encode($data['id'])]);
        }
        return responseFail($options, 'Invalid from create charge');
    }
    static function getCharge($options = []){
        \Payjp\Payjp::setApiKey(sk);
        $options = array_merge(['charge_id' => ''], $options);
        if($options['charge_id']){
            return responseSuccess(\Payjp\Charge::retrieve($options['charge_id']));
        }
        return responseFail($options, 'Invalid from get charge');
    }
    public static function confirmCharge($options = []){
        \Payjp\Payjp::setApiKey(sk);
        $options = array_merge([
            'charge_id' => ''
        ], $options);
        if($options['charge_id']){
            $ch = \Payjp\Charge::retrieve($options['charge_id']);
            $data = $ch->capture();
            return responseSuccess(['charge_id' => base64_encode($data['id'])]);
        }
        return responseFail($options, 'Invalid from confirm charge');
    }
    public static function refundCharge($options = []){
        \Payjp\Payjp::setApiKey(sk);
        $options = array_merge([
            'charge_id' => '',
            'amount' => 0,
            'refund_reason' => 'refund'
        ], $options);

        if($options['charge_id']){
            $ch = \Payjp\Charge::retrieve($options['charge_id']);
            $data = $ch->refund(array("amount" => $options['amount'], "refund_reason" => $options['refund_reason']));
            return responseSuccess(['charge_id' => base64_encode($data['id'])]);
        }
        return responseFail($options, 'Invalid from get charge');
    }
    public static function cancelCharge($options = []){
        \Payjp\Payjp::setApiKey(sk);
        $options = array_merge([
            'charge_id' => '',
        ], $options);

        if($options['charge_id']){
            $ch = \Payjp\Charge::retrieve($options['charge_id']);
            $data = $ch->refund();
            return responseSuccess(['charge_id' => base64_encode($data['id'])]);
        }
        return responseFail($options, 'Invalid from get charge');
    }
}