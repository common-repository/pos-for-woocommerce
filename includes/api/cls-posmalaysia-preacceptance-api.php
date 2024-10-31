<?php  if ( ! defined( 'ABSPATH' ) ) exit; 
class Preacceptance_Api_PosMalaysia
{

  public $api_utils = null;
  public $token_api = null;

  public function __construct()
  {
    $this->api_utils = new Api_Utils_PosMalaysia();
    $this->token_api = new Token_Api_PosMalaysia();
  }

  function pickup($connote_nos)
  {
    $backOff = false;
    $backOffLimit = 3;
    $backOffRetry = 0;
    do {
      try {
        $token_response = $this->token_api->get_bearer_token();
        if ($token_response['status'] == 'error') {
          return $token_response;
        }
        $token = 'Bearer ' . $token_response['data']['accessToken'];
   
        return self::get_pick_up_response($token, $connote_nos);
        print_r($token);
        die();
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

  function dropoff($connote_nos)
  {
    $backOff = false;
    $backOffLimit = 3;
    $backOffRetry = 0;
    do {
      try {
        $token_response = $this->token_api->get_bearer_token();
        if ($token_response['status'] == 'error') {
          return $token_response;
        }
        $token = 'Bearer ' . $token_response['data']['accessToken'];
        return self::get_drop_off_response($token, $connote_nos);
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

  function get_pick_up_response($token, $data)
  {
    $url = Api_Utils_PosMalaysia::get_full_url('/v2/pickup');
    $header = array(
      'Authorization' => $token,
      'Content-Type' => 'application/json'
    );

    $data = $this->api_utils->get_json_string($data);
    
    $response = wp_remote_post($url, array('headers' => $header, 'body' => $data, 'timeout' => 120,));
    $responseCode = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $body = json_decode($body, true);
    if ($responseCode == 200) {
      $body['status'] = 'success';
    } else {
      $body['status'] = 'fail';
    }
    return $body;
  }

  function get_drop_off_response($token, $data)
  {
    $url = Api_Utils_PosMalaysia::get_full_url('/v2/dropoff');
    $header = array(
      'Authorization' => $token,
      'Content-Type' => 'application/json'
    );
    $data = $this->api_utils->get_json_string($data);
    $response = wp_remote_post($url, array('headers' => $header, 'body' => $data, 'timeout' => 120,));

    $responseCode = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $body = json_decode($body, true);

    if ($responseCode == 200) {
      $body['status'] = 'success';
    } else {
      $body['status'] = 'fail';
    }
    return $body;
  }

  static function get_json_string($data)
  {
    foreach (array('data'  => json_encode($data)) as $key => $value) {
    }
    return $value;
  }
}