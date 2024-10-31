<?php  if ( ! defined( 'ABSPATH' ) ) exit; 
class posmalaysia_woocommerce_API
{
    private $user_id = null;
    private  $desc = null;
    private $perm = null;
    private  $table = null;
    private  $account = null;
    private  $shop = null;

    public function __construct($define_hooks = true)
    {
        global $wpdb;
        $this->desc = 'POS Order Status API for Webhook';
        $this->perm = 'write';
        $this->table = $wpdb->prefix . 'woocommerce_api_keys';
        $this->account = get_option('PosAccountNo');
        $this->shop = get_option('posmalaysia_sender_name');
        if ($define_hooks) $this->define_hooks();
    }

    public function define_hooks()
    {
        add_action('init', [$this, 'enable']);
        add_action('init', [$this, 'generate_api_key']);
        add_action('admin_notices', [$this, 'webhook_notice']);
    }

    public function enable()
    {
        update_option('woocommerce_api_enabled', 'yes');
    }

    public function generate_api_key()
    {
        global $pagenow, $typenow;
        if ($pagenow != 'edit.php' && $typenow  != 'shop_order') return;

        if (get_option('wc_api_subscription_status') == 'done') {
            delete_option('wc_api_key_enc');
            delete_option('wc_api_secret_enc');
        }

        $cur_code = substr('wc.' . $this->account . '.' . $this->shop, 0, 512);
        $sub_code = get_option('wc_api_subscription_code');

        $key_id = $this->get_api_key_id();
        if (empty($this->account) || empty($this->shop) || (!empty($key_id) && $cur_code === $sub_code)) return;
        $this->_generate_api_key($this->account, $this->shop);
    }

    public function _generate_api_key($account, $shop)
    {
        $key_id = $this->get_api_key_id();
        if (!empty($key_id)) $this->delete_api_key($key_id);

        $this->user_id = get_current_user_id();

        $consumer_key    = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();

        $data = array(
            'user_id'         => $this->user_id,
            'description'     => $this->desc,
            'permissions'     => $this->perm,
            'consumer_key'    => wc_api_hash($consumer_key),
            'consumer_secret' => $consumer_secret,
            'truncated_key'   => substr($consumer_key, -7),
        );

        global $wpdb;
        $wpdb->insert(
            $this->table,
            $data,
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );

        $code = substr('wc.' . $account . '.' . $shop, 0, 512);
        update_option('wc_api_subscription_code', $code);
        update_option('wc_api_key_enc', $this->encrypt_key($consumer_key, $account, $shop));
        update_option('wc_api_secret_enc', $this->encrypt_key($consumer_secret, $account, $shop));
        update_option('wc_api_subscription_status', 'pending');
    }

    public function get_api_key_id()
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT key_id FROM {$this->table} WHERE description=%s AND permissions=%s;", $this->desc, $this->perm);
        return $wpdb->get_var($query);
    }

    public function delete_api_key($key_id)
    {
        global $wpdb;
        $delete = $wpdb->delete($wpdb->prefix . 'woocommerce_api_keys', array('key_id' => $key_id), array('%d'));
        return $delete;
    }

    public function get_info($account_no, $shop_name)
    {
        $site_url = site_url();
        $code = substr('wc.' . $account_no . '.' . $shop_name, 0, 512);

        $WC_info = array(
            'subscriptionCode' => $code,
            'postBackAddress' => $site_url . '/index.php/wp-json/wc/v3/orders/batch',
            'clientKey' => get_option('wc_api_key_enc'),
            'clientSecret' => get_option('wc_api_secret_enc'),
        );

        return $WC_info;
    }

    function encrypt_key($data, $account, $shop)
    {
        $method = 'aes-256-cbc';
        $encryption_key = hash('sha256', $account);
        $iv = md5($shop);
        $options = 0;

        $encrypted = openssl_encrypt(
            $data,
            $method,
            $encryption_key,
            $options,
            $iv
        );

        return $encrypted;
    }

    function update_webhook_subscription_result($res)
    {
        $response = json_decode($res, true);
       
        $data = $response['data'];
        $response_status = $data['status'];
        $webhook_status =  $data['webhook'];
        $success_code = 'E0200';

        if (get_option('wc_api_subscription_status') == 'processing') {
            if ($response_status && $webhook_status == $success_code) {
                update_option('wc_api_subscription_status', 'done');
            } else {
                update_option('wc_api_subscription_status', 'error');
                global $pagenow, $typenow;
                if ($pagenow === 'edit.php' && $typenow  === 'shop_order' && get_option('pos_last_triggered_action') !== 'get_pos_status')
                    update_option('wc_api_display_subscription_status', 1);
            }
        }
    }

    public function webhook_notice()
    {
        global $pagenow, $typenow;
        if (
            $pagenow === 'edit.php' && $typenow  === 'shop_order' &&
            get_option('wc_api_subscription_status') == 'error' &&
            get_option('wc_api_display_subscription_status') == 1
        ) {
            $class = "notice notice-error is-dismissible";
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html(self::get_webhook_err_msg()));
            update_option('wc_api_display_subscription_status', 0);
        }
    }

    function get_webhook_err_msg()
    {
        return "Error occurs in updating Pos Malaysia status. Please reach out to Pos Malaysia support team.";
    }
}