<?php  if ( ! defined( 'ABSPATH' ) ) exit; 
class Status_Api_PosMalaysia
{

    public $api_utils = null;
    public $token_api = null;

    public function __construct()
    {
        $this->api_utils = new Api_Utils_PosMalaysia();
        $this->token_api = new Token_Api_PosMalaysia();
    }

    function status($connote_nos)
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
                return self::status_response($connote_nos, $token);
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

    function status_response($data, $token)
    {
        $url = Api_Utils_PosMalaysia::get_full_url('/tracking/connote');
        $header = array(
            'Authorization' => $token,
            'Content-Type' => 'application/json'
        );

        $json_data = json_encode($data);
        $response = wp_remote_post($url, array('headers' => $header, 'body' => $json_data, 'timeout' => 30));
        $responseCode = wp_remote_retrieve_response_code($response);

        $body['data'] = json_decode(wp_remote_retrieve_body($response), true);
        $body['responseCode'] = $responseCode;
        return $body;
    }
}