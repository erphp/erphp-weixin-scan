<?php
if ( !defined('ABSPATH') ) {exit;}
if(isset($_POST['erphp_weixin_scan'])){
    update_option('ews_token', trim($_POST['ews_token']));
    update_option('ews_appid', trim($_POST['ews_appid']));
    update_option('ews_appsecret', trim($_POST['ews_appsecret']));
    update_option('ews_qrcode', trim($_POST['ews_qrcode']));
    update_option('ews_reply', trim($_POST['ews_reply']));
    update_option('ews_reply_auto', $_POST['ews_reply_auto']);
    echo'<div class="updated settings-error"><p>更新成功！</p></div>';
}
$ews_token = get_option("ews_token");
$ews_appid = get_option("ews_appid");
$ews_appsecret = get_option("ews_appsecret");
$ews_qrcode = get_option("ews_qrcode");
$ews_reply = get_option("ews_reply");
$ews_reply_auto = get_option("ews_reply_auto");
wp_enqueue_media ();
?>

<div class="wrap">
    <h1>设置</h1>
    <form method="post" action="<?php echo admin_url('admin.php?page=ews_setting_page');?>">
        <table class="form-table">
            <tr>
                <th valign="top">插件作者</th>
                <td>
                    <a href="http://www.mobantu.com/8259.html" target="_blank">模板兔</a>
                </td>
            </tr>
            <tr>
                <th valign="top">服务器地址(URL)</th>
                <td>
                    <code><?php echo EWS_URL.'/valid.php';?></code>
                    <p class="description">进公众号，开发 - 基础配置，设置好IP白名单，启用服务器配置，配置好信息即可。</p>
                </td>
            </tr>
            <tr>
                <th valign="top">令牌(Token)</th>
                <td>
                    <input type="text" id="ews_token" name="ews_token" value="<?php echo $ews_token;?>" class="regular-text" required=""/> 
                </td>
            </tr>
            <tr>
                <th valign="top">开发者ID(AppID)</th>
                <td>
                    <input type="text" id="ews_appid" name="ews_appid" value="<?php echo $ews_appid;?>" class="regular-text" required=""/> 
                </td>
            </tr>
            <tr>
                <th valign="top">开发者密码(AppSecret)</th>
                <td>
                    <input type="text" id="ews_appsecret" name="ews_appsecret" value="<?php echo $ews_appsecret;?>" class="regular-text" required=""/> 
                </td>
            </tr>
            <tr>
                <th valign="top">公众号二维码</th>
                <td>
                    <input type="text" id="ews_qrcode" name="ews_qrcode" value="<?php echo $ews_qrcode;?>" class="regular-text" required=""/> <button class="set_ews_qrcode button" type="button">上传二维码</button>
                </td>
            </tr>
            <tr>
                <th valign="top">默认自动回复</th>
                <td>
                    <input type="text" id="ews_reply" name="ews_reply" value="<?php echo $ews_reply;?>" class="regular-text" required=""/> 
                    <p class="description">匹配不到关键字时自动回复的信息。</p>
                </td>
            </tr>
            <tr>
                <th valign="top">匹配自动回复</th>
                <td>
                    <?php if($ews_reply_auto){ $cnt = count($ews_reply_auto['key']); if($cnt){?>
                    <div class="replys">
                        <?php for($i=0; $i<$cnt;$i++){?>
                        <p><input type="text" name="ews_reply_auto[key][]" value="<?php echo $ews_reply_auto['key'][$i]?>" class="regular-text" style="width:150px;" placeholder="关键字"/> ➸ <input type="text" name="ews_reply_auto[value][]" value="<?php echo $ews_reply_auto['value'][$i]?>" class="regular-text" placeholder="回复内容"/> <a href="javascript:;" class="del-price">删除</a></p>
                        <?php }?>
                    </div>
                    <?php }}else{?>
                    <div class="replys"></div>
                    <?php }?>
                    <button class="button add-more-reply" type="button" style="margin-top: 5px">+添加规则</button>
                    <p class="description">基于用户发送的关键字精准回复的信息，关键字不要设置“登录”、“登陆”、“绑定”。</p>
                </td>
            </tr>
            <tr>
                <th valign="top">使用方法</th>
                <td>
                    新建页面，输入短代码[erphp_weixin_scan]即可。如需深度集成到主题自带的登录里，可联系我们二次开发。
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="erphp_weixin_scan" value="保存设置" class="button-primary"/>
        </p>  
    </form>
</div>
<script>
jQuery(document).ready(function() {
    var $ = jQuery;
    if ($('.set_ews_qrcode').length > 0) {
        if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
        $(document).on('click', '.set_ews_qrcode', function(e) {
        e.preventDefault();
        var button = $(this);
        var id = button.prev();
        wp.media.editor.send.attachment = function(props, attachment) {
        id.val(attachment.url);
        };
        wp.media.editor.open(button);
        return false;
        });
        }
    }

    $(".add-more-reply").click(function(){
        $(".replys").append('<p><input type="text" name="ews_reply_auto[key][]" class="regular-text" style="width:150px;" placeholder="关键字"/> ➸ <input type="text" name="ews_reply_auto[value][]" class="regular-text" placeholder="回复内容"/> <a href="javascript:;" class="del-reply">删除</a></p>');
        $(".del-reply").click(function(){
            $(this).parent().remove();
        });
        return false;
    });

    $(".del-reply").click(function(){
        $(this).parent().remove();
    });
});
</script>