<?php
global $wpdb, $ews_table;
$ews_table = isset($table_prefix) ? ($table_prefix . 'ews_logs') : ($wpdb->prefix . 'ews_logs');

function ews_scripts(){
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'ews',  EWS_URL.'/assets/ews.js', array(), EWS_VERSION ,true);
    wp_localize_script( 'ews', 'ews_ajax_url', EWS_ADMIN_URL . "admin-ajax.php");
}
add_action('wp_enqueue_scripts', 'ews_scripts', 20, 1);

function ews_install(){
    global $wpdb, $ews_table;
    if( $wpdb->get_var("show tables like '{$ews_table}'") != $ews_table ) {
        $wpdb->query("CREATE TABLE {$ews_table} (
            id      BIGINT(20) NOT NULL AUTO_INCREMENT,
            scene_id VARCHAR(50),
            openid VARCHAR(200),
            unionid VARCHAR(200),
            access_token VARCHAR(200),
            nickname VARCHAR(100),
            avatar VARCHAR(200),
            create_time datetime NOT NULL,
            update_time datetime NOT NULL,
            UNIQUE KEY id (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
    }

    $var2 = $wpdb->query("SELECT weixinid FROM $wpdb->users");
    if(!$var2){
        $wpdb->query("ALTER TABLE $wpdb->users ADD weixinid varchar(200)");
    }
}

add_action('admin_menu', 'ews_menu');
function ews_menu() {
    add_menu_page('公众号登录', '公众号登录', 'activate_plugins', 'ews_setting_page', 'ews_setting_page','dashicons-smiley');
    add_submenu_page('ews_setting_page', '设置', '设置', 'activate_plugins', 'ews_setting_page','ews_setting_page');
    add_submenu_page('ews_setting_page', '记录', '记录', 'activate_plugins', 'ews_log_page','ews_log_page');
}

function ews_setting_page(){
    @include EWS_PATH.'/admin/setting.php';
}

function ews_log_page(){
    @include EWS_PATH.'/admin/logs.php';
}

function ews_login($code){
    date_default_timezone_set('Asia/Shanghai');
    global $wpdb, $ews_table;
    if($code){
        $result = $wpdb->get_row("select openid, nickname, avatar from $ews_table where scene_id='".esc_sql($code)."' and update_time >= SUBDATE(NOW(), INTERVAL 5 MINUTE)");
        if($result){
            $openid = $result->openid;
            $nickname = $result->nickname;
            $avatar = $result->avatar;
            if($openid){
                $user_ID = $wpdb->get_var("SELECT ID FROM $wpdb->users WHERE weixinid='".$openid."'");
                if($user_ID){
                    $user = get_user_by('id',$user_ID);
                    if($user){
                        $user_login = $user->user_login;
                        wp_set_auth_cookie($user_ID,true,is_ssl());
                        wp_signon( array(), is_ssl() );
                        do_action('wp_login', $user_login);
                        return true;
                    }else{
                        $login_name = "u".mt_rand(1000,9999).mt_rand(1000,9999).mt_rand(1000,9999).mt_rand(1000,9999);
                        $userdata=array(
                          'user_login' => $login_name,
                          'display_name' => $nickname?$nickname:$login_name,
                          'nickname' => $nickname?$nickname:$login_name,
                          'user_pass' => $code
                        );
                        $user_ID = wp_insert_user( $userdata );
                        if ( !is_wp_error( $user_ID ) ) {
                            $ff = $wpdb->query("UPDATE $wpdb->users SET weixinid = '".$openid."' WHERE ID = '$user_ID'");
                            if($ff){
                                if($avatar){
                                    add_user_meta($user_ID,'photo',$avatar);
                                }
                                wp_set_auth_cookie($user_ID,true,is_ssl());
                                wp_signon( array(), is_ssl() );
                                do_action('wp_login', $login_name);
                                return true;
                            }
                        }
                    }
                }else{
                    $login_name = "u".mt_rand(1000,9999).mt_rand(1000,9999).mt_rand(1000,9999).mt_rand(1000,9999);
                    $userdata=array(
                      'user_login' => $login_name,
                      'display_name' => $nickname?$nickname:$login_name,
                      'nickname' => $nickname?$nickname:$login_name,
                      'user_pass' => $code
                    );
                    $user_ID = wp_insert_user( $userdata );
                    if ( !is_wp_error( $user_ID ) ) {
                        $ff = $wpdb->query("UPDATE $wpdb->users SET weixinid = '".$openid."' WHERE ID = '$user_ID'");
                        if($ff){
                            if($avatar){
                                add_user_meta($user_ID,'photo',$avatar);
                            }
                            wp_set_auth_cookie($user_ID,true,is_ssl());
                            wp_signon( array(), is_ssl() );
                            do_action('wp_login', $login_name);
                            return true;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function ews_bind($code){
    if(is_user_logged_in()){
        date_default_timezone_set('Asia/Shanghai');
        global $wpdb, $ews_table, $current_user;
        $userid = $current_user->ID;
        if($code){
            $result = $wpdb->get_row("select openid from $ews_table where scene_id='".esc_sql($code)."' and update_time >= SUBDATE(NOW(), INTERVAL 5 MINUTE)");
            if($result){
                $openid = $result->openid;
                if($openid){
                    $user_ID = $wpdb->get_var("SELECT ID FROM $wpdb->users WHERE weixinid='".$openid."'");
                    if($user_ID){
                        return '2';
                    }else{
                        $wpdb->query("UPDATE $wpdb->users SET weixinid = '".$openid."' WHERE ID = $userid");
                        return '1';
                    }
                }
            }
        }
    }
    return '0';
}

function ews_login_callback(){
    $code = $_POST['code'];
    $status = 0;
    if(ews_login($code)){
        $status = 1;
    }

    $result = array(
        'status' => $status
    );

    header('Content-type: application/json');
    echo json_encode($result);
    exit;
}
add_action( 'wp_ajax_nopriv_ews_login', 'ews_login_callback');

function ews_bind_callback(){
    $code = $_POST['code'];
    $status = ews_bind($code);

    $result = array(
        'status' => $status
    );

    header('Content-type: application/json');
    echo json_encode($result);
    exit;
}
add_action( 'wp_ajax_ews_bind', 'ews_bind_callback');

add_shortcode('erphp_weixin_scan_bind','ews_bind_shortcode');
function ews_bind_shortcode($atts, $content){
    $ews_qrcode = get_option("ews_qrcode");
    $html = '<style>
        .erphp-weixin-scan{margin:0 auto;position:relative;max-width: 250px;text-align:center;}
        .erphp-weixin-scan .ews-title{text-align:center;font-size:18px;}
        .erphp-weixin-scan img{max-width: 100%;height: auto;margin:5px 0}
        .erphp-weixin-scan .ews-box{text-align: center;}
        .erphp-weixin-scan .ews-box .ews-input{border:1px solid #eee;border-radius:3px;padding:6px 12px;width:120px;height: 35px;box-sizing: border-box;}
        .erphp-weixin-scan .ews-box .ews-bind-button{background: #07C160;border:none;padding:7px 12px;color:#fff;border-radius: 3px;font-size:14px;cursor: pointer;height: 35px;box-sizing: border-box;}
        .erphp-weixin-scan .ews-tips{text-align:center;font-size:13px;color:#999;margin-top:10px;}
        </style>
        <div class="erphp-weixin-scan">
            <img src="'.$ews_qrcode.'" />
            <div class="ews-box">
                <input type="text" id="ews_code" class="ews-input" placeholder="验证码"/>
                <button type="button" class="ews-bind-button">验证绑定</button>
            </div>
            <div class="ews-tips">
            如已关注，请回复“绑定”二字获取验证码
            </div>
        </div>';
    return $html;
}

add_shortcode('erphp_weixin_scan','ews_shortcode');
function ews_shortcode($atts, $content){
    if(!is_user_logged_in()){
    $ews_qrcode = get_option("ews_qrcode");
    $html = '<style>
        .erphp-weixin-scan{margin:0 auto;position:relative;max-width: 300px;}
        .erphp-weixin-scan .ews-title{text-align:center;font-size:18px;}
        .erphp-weixin-scan img{max-width: 100%;height: auto;}
        .erphp-weixin-scan .ews-box{text-align: center;}
        .erphp-weixin-scan .ews-box .ews-input{border:1px solid #eee;border-radius:3px;padding:6px 12px;width:150px;height: 35px;box-sizing: border-box;}
        .erphp-weixin-scan .ews-box .ews-button{background: #07C160;border:none;padding:7px 12px;color:#fff;border-radius: 3px;font-size:14px;cursor: pointer;height: 35px;box-sizing: border-box;}
        .erphp-weixin-scan .ews-tips{text-align:center;font-size:13px;color:#999;margin-top:10px;}
        </style>
        <div class="erphp-weixin-scan">
            <div class="ews-title">微信扫一扫关注</div>
            <img src="'.$ews_qrcode.'" />
            <div class="ews-box">
                <input type="text" id="ews_code" class="ews-input" placeholder="验证码"/>
                <button type="button" class="ews-button">验证登录</button>
            </div>
            <div class="ews-tips">
            如已关注，请回复“登录”二字获取验证码
            </div>
        </div>';
    }else{
        $html = '<script>location.href="'.home_url().'";</script>';
    }
    return $html;
}

function ews_pagination($total_count, $number_per_page=15){

    $current_page = isset($_GET['paged'])?$_GET['paged']:1;

    if(isset($_GET['paged'])){
        unset($_GET['paged']);
    }

    $base_url = add_query_arg($_GET,admin_url('admin.php'));

    $total_pages    = ceil($total_count/$number_per_page);

    $first_page_url = $base_url.'&amp;paged=1';
    $last_page_url  = $base_url.'&amp;paged='.$total_pages;
    
    if($current_page > 1 && $current_page < $total_pages){
        $prev_page      = $current_page-1;
        $prev_page_url  = $base_url.'&amp;paged='.$prev_page;

        $next_page      = $current_page+1;
        $next_page_url  = $base_url.'&amp;paged='.$next_page;
    }elseif($current_page == 1){
        $prev_page_url  = '#';
        $first_page_url = '#';
        if($total_pages > 1){
            $next_page      = $current_page+1;
            $next_page_url  = $base_url.'&amp;paged='.$next_page;
        }else{
            $next_page_url  = '#';
        }
    }elseif($current_page == $total_pages){
        $prev_page      = $current_page-1;
        $prev_page_url  = $base_url.'&amp;paged='.$prev_page;
        $next_page_url  = '#';
        $last_page_url  = '#';
    }
    ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num">每页 <?php echo $number_per_page;?> 共 <?php echo $total_count;?></span>
            <span class="pagination-links">
                <a class="first-page button <?php if($current_page==1) echo 'disabled'; ?>" title="前往第一页" href="<?php echo $first_page_url;?>">«</a>
                <a class="prev-page button <?php if($current_page==1) echo 'disabled'; ?>" title="前往上一页" href="<?php echo $prev_page_url;?>">‹</a>
                <span class="paging-input">第 <?php echo $current_page;?> 页，共 <span class="total-pages"><?php echo $total_pages; ?></span> 页</span>
                <a class="next-page button <?php if($current_page==$total_pages) echo 'disabled'; ?>" title="前往下一页" href="<?php echo $next_page_url;?>">›</a>
                <a class="last-page button <?php if($current_page==$total_pages) echo 'disabled'; ?>" title="前往最后一页" href="<?php echo $last_page_url;?>">»</a>
            </span>
        </div>
        <br class="clear">
    </div>
    <?php
}

function ews_http_post($url, $data) {  
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL,$url);  
    curl_setopt($ch, CURLOPT_HEADER,0);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
    curl_setopt($ch, CURLOPT_POST, 1);  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //不验证 SSL 证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证 SSL 证书域名
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
    $res = curl_exec($ch);  
    curl_close($ch);  
    return $res;  
}

function ews_filter_nickname($nickname){
    $nickname = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $nickname);
    $nickname = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $nickname);
    $nickname = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $nickname);
    $nickname = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $nickname);
    $nickname = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $nickname);
    $nickname = str_replace(array('"','\''), '', $nickname);
    return addslashes(trim($nickname));
}
