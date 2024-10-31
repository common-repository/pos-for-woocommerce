<?php
/**
 Plugin Name: Pos Malaysia for WooCommerce
 Plugin URI: http://pos.com.my
 Description: Pos Malaysia plugin for Woocommerce extension's.
 Requires at least: 5.1
 Tested up to: 5.9
 Requires PHP: 7.4
 Version: 1.1.5
 Author: Pos Digital Team
 Author URI: http://pos.com.my/
 Developer: Digital Backend Team
 Developer URI: http://pos.com.my/
 Text Domain: Pos Malaysia
 License: License: GPLv2 or later
 License URI: http://www.gnu.org/licenses/gpl-3.0.html
 Contributors: posmalaysiaberhad
 Donate link: https://www.pos.com.my/
 Tags: Pos4you,PosMalaysia
 
 */
/**
 * @package posmalaysia
 */

if (!defined('ABSPATH')) {
	die;
}

define('PosMalaysiaVERSION', '1.1.4');
define('PosMalaysiaPLUGINDIR', plugin_dir_path(__FILE__));

function activate_posmalaysia()
{
	require_once PosMalaysiaPLUGINDIR . 'includes/cls-posmalaysia-activator.php';
	PosMalaysia_Activator::activator();
}
register_activation_hook(__FILE__,  'activate_posmalaysia');

function deactivate_posmalaysia()
{
	require_once PosMalaysiaPLUGINDIR . 'includes/cls-posmalaysia-deactivator.php';
	PosMalaysia_Deactivator::deactivator();
}
register_deactivation_hook(__FILE__, 'deactivate_posmalaysia');

require PosMalaysiaPLUGINDIR . 'includes/cls-posmalaysia.php';

$plugin = PosMalaysia::init();
$plugin->InitPlugin();