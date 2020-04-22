<?php
if ( !defined('ABSPATH') ) {exit;}
if(!is_user_logged_in()){
    exit;
}
global $wpdb, $ews_table;

$total   = $wpdb->get_var("SELECT COUNT(id) FROM $ews_table");
$perpage = 20;
$pages = ceil($total / $perpage);
$page=isset($_GET['paged']) ?intval($_GET['paged']) :1;
$offset = $perpage*($page-1);
$list = $wpdb->get_results("SELECT * FROM $ews_table ORDER BY update_time DESC limit $offset,$perpage");
?>
<style>
.wp-list-table td{display: table-cell !important;}
@media (max-width: 768px){
    .wp-list-table td.pc, .wp-list-table th.pc{display: none !important;}
}
</style>
<div class="wrap">
    <h2>所有记录</h2>
    <table class="wp-list-table widefat fixed striped posts">
        <thead>
            <tr>
                <th class="pc">验证码</th>
                <th>openid</th>
                <th>创建时间</th>
                <th>更新时间</th>
            </tr>
        </thead>
        <tbody>
    <?php
        if($list) {
            foreach($list as $value){
                echo "<tr>\n";
                echo "<td class='pc'>$value->scene_id</td>\n";
                echo "<td>$value->openid</td>";
                echo "<td>$value->create_time</td>";
                echo "<td>$value->update_time</td>\n";
                echo "</tr>";
            }
        }
        else{
            echo '<tr><td colspan="4" align="center"><strong>暂无记录</strong></td></tr>';
        }
    ?>
    </tbody>
    </table>
    <?php ews_pagination($total,$perpage);?>
</div>
