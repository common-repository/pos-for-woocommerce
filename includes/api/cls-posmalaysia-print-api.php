<?php  if ( ! defined( 'ABSPATH' ) ) exit; 
class Print_Api_PosMalaysia
{

  public $api_utils = null;
  public $token_api = null;

  public function __construct()
  {
    $this->api_utils = new Api_Utils_PosMalaysia();
    $this->token_api = new Token_Api_PosMalaysia();
  }

  function print_label($items)
  {
    $backOff = false;
    $backOffLimit = 3;
    $backOffRetry = 0;
    do {
      try {
        if (!$this->api_utils->has_setup_shipping_details()) {
          return $this->api_utils->no_shipping_details_error_msg();
        }
        $token_response = $this->token_api->get_bearer_token();
        if ($token_response['status'] == 'error') {
          return $token_response;
        }
        $token = 'Bearer ' . $token_response['data']['accessToken'];
        $response = self::get_connoteno_bulk_print_reponse($token, $items);
        return $response;
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
  function get_connoteno_bulk_print_reponse($token, $items)
  {
    $body = array('orders' => $items);
    $body = $this->api_utils->get_json_string($body);
    $url = Api_Utils_PosMalaysia::get_full_url('/v2/connote-bulkv2');
    $header = array(
      'Authorization' => $token,
      'Content-Type' => 'application/json'
    );
    $response = wp_remote_post($url, array('headers' => $header, 'body' => $body, 'timeout' => 180,));

    $responseCode = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $body = json_decode($body, true);

    if ($responseCode == 200) {
      $body['status'] = 'success';
      $data = $body['data'];
      if ($data) {
        $connote_nos = $data['connotes'];
        if (sizeof($connote_nos) != sizeof($items)) {
          $body['status'] = 'fail';
        }
        $index = 0;
        $new_connote_nos = array();
        foreach ($connote_nos as $connote_no) {
          $connote_no['id'] = $items[$index]['id'];
          array_push($new_connote_nos, $connote_no);
          $index++;
        }
        $body['data']['connotes'] = $new_connote_nos;
      }
    } else {
      $body['status'] = 'fail';
    }
   
    return $body;
  }
}