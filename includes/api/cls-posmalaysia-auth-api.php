<?php  if ( ! defined( 'ABSPATH' ) ) exit; 
class Auth_Api_PosMalaysia
{
  public $api_utils = null;
  public $woocommerce_API = null;

  public function __construct()
  {
    $this->api_utils = new Api_Utils_PosMalaysia();
    $this->woocommerce_API = new posmalaysia_woocommerce_API(false);
  }

  public function validation($reg_detail)
  {
    $backOff = false;
    $backOffLimit = 3;
    $backOffRetry = 0;
    do {
      try {
        $value = $this->api_utils->get_json_string($reg_detail);
        $result = self::get_validation_response($value);
        $this->woocommerce_API->update_webhook_subscription_result($result);
        return $result;
      } catch (Exception $e) {
        if ($backOffRetry >= $backOffLimit) {
          return $this->api_utils->server_error();
        }
        $backOff = true;
        $backOffRetry++;
        sleep($backOffRetry * 2);
      }
    } while ($backOff);
  }

  public function register_shop()
  {
    $backOff = false;
    $backOffLimit = 3;
    $backOffRetry = 0;
    do {
      try {
        $register_request_body = $this->api_utils->get_register_shop_request_body();
        $body = $this->api_utils->get_json_string($register_request_body);
         $result = self::get_register_shop_response($body);
        $this->woocommerce_API->update_webhook_subscription_result($result);
        return $result;
      } catch (Exception $e) {
        if ($backOffRetry >= $backOffLimit) {
          return $this->api_utils->server_error();
        }
        $backOff = true;
        $backOffRetry++;
        sleep($backOffRetry * 2);
      }
    } while ($backOff);
  }

  // APIs 
  static function get_register_shop_response($body)
  {
    $url = Api_Utils_PosMalaysia::get_full_url('/v2/shop/register');
    $header = array('Content-Type' => 'application/json');
    $response = wp_remote_post($url, array('headers' => $header, 'body' => $body, 'timeout' => 60,));
    $result = wp_remote_retrieve_body($response);
    return $result;
  }

  static function get_validation_response($body)
  {
    $url = Api_Utils_PosMalaysia::get_full_url('/v2/pos/validation');
    $header = array('Content-Type' => 'application/json');
    $response = wp_remote_post($url, array('headers' => $header, 'body' => $body, 'timeout' => 60,));
    $result = wp_remote_retrieve_body($response);
    return $result;
  }
}