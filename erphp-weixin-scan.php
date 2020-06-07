<?php
/*
Plugin Name: 关注公众号登录
Plugin URI: http://www.mobantu.com/8259.html
Author: 模板兔
Author URI: http://www.mobantu.com
Description: WordPress关注公众号登录，支持未认证的订阅号～
Version: 2.0
*/

define('EWS_VERSION', '2.0');
define('EWS_URL', plugins_url('', __FILE__));
define('EWS_PATH', dirname( __FILE__ ));
define('EWS_ADMIN_URL', admin_url());

global $ews_weixin_appid, $ews_weixin_appsecret, $ews_token;
$ews_weixin_appid = get_option("ews_appid");
$ews_weixin_appsecret = get_option("ews_appsecret");
$ews_token = get_option("ews_token");

require EWS_PATH . '/inc/base.php';
register_activation_hook(__FILE__, 'ews_install');
