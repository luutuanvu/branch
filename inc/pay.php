<?php
require_once  get_theme_file_path('/payjp-php/init.php');
require_once get_theme_file_path('/inc/class/Charges.php');
require_once get_theme_file_path('/inc/class/Token.php');
function PayJp($action, $data = []){
    try {
        $action = explode('@', $action);
        $class = $action[0];
        $method = $action[1];
        $data = $class::$method($data);
        return $data;
    } catch(\Payjp\Error\Card $e) {
        return responseFail($e->getJsonBody());
        //var_dump($e['error']);
        // Since it's a decline, \Payjp\Error\Card will be caught
    } catch (\Payjp\Error\InvalidRequest $e) {
        // Invalid parameters were supplied to Payjp's API
        return responseFail($e->getJsonBody());
    } catch (\Payjp\Error\Authentication $e) {
        // Authentication with Payjp's API failed
        return responseFail($e->getJsonBody());
    } catch (\Payjp\Error\ApiConnection $e) {
        // Network communication with Payjp failed
        return responseFail($e->getJsonBody());
    } catch (\Payjp\Error\Base $e) {
        // Display a very generic error to the user, and maybe send
        // yourself an email
        return responseFail($e->getJsonBody());
    } catch (Exception $e) {
        // Something else happened, completely unrelated to Payjp
        return responseFail($e->getJsonBody());
    }
}

