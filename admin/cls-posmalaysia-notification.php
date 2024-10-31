<?php  if ( ! defined( 'ABSPATH' ) ) exit; 

class posmalaysia_Notification
{
    public function __construct()
    {
        if (isset($_GET['acti']) && ($_GET['acti'] == 'err' || $_GET['acti'] == 'success')) {
            add_action('admin_notices', [$this, 'display_notice']);
        }
    }

    public function display_notice($type)
    {
        $type = $_GET['acti'];
        $message = $_GET['msg'];
        $class = "notice notice-$type is-dismissible";
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }
}