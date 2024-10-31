<?php   if ( ! defined( 'ABSPATH' ) ) exit; 
class Account_Api_PosMalaysia
{

  public $api_utils = null;
  public $token_api = null;

  public function __construct()
  {
    $this->api_utils = new Api_Utils_PosMalaysia();
    $this->token_api = new Token_Api_PosMalaysia();
   
  }

  public function account()
  {
  
      try {
     
        $token_response = $this->token_api->get_bearer_token();
        if ($token_response['status'] == 'error') {
          return $token_response;
        }
        $token = 'Bearer ' . $token_response['data']['accessToken'];
        return  self::get_tracking_response($token);
      } catch (Exception $e) {
        
          return $this->api_utils->server_error();
        
    
      }
  }


  function get_tracking_response($token)
  {
    $url = Api_Utils_PosMalaysia::get_full_url('/v2/account');
    $header = array(
      'Authorization' => $token,
      'Content-Type' => 'application/json'
    );
    $response = wp_remote_get($url, array('headers' => $header));
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
}