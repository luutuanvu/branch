<?php

/**
 * Created by PhpStorm.
 * User: jimmee
 * Date: 4/10/20
 * Time: 1:49 PM
 */
class Token
{
    public static function genToken($options = []){
        \Payjp\Payjp::setApiKey(pk);
        $params = [
            'card' => array_merge([
                "number" => '',
                "exp_month" => '',
                "exp_year" => '',
            ], $options)
        ];
        if($params['card']['number']){
            $data = \Payjp\Token::create($params, $options = []);
            return responseSuccess(['token_id' => base64_encode($data['id'])]);
        }
        $options['message'] = 'Please check again some card field is invalid.';
        return responseFail($options);
    }
}