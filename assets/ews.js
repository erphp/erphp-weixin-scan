jQuery(function($){
    $(".ews-button").click(function(){
        var that = $(this),
            code = $("#ews_code").val();
        if(code){
            if(!that.hasClass("disabled")){
                that.text("验证中...");
                that.addClass("disabled");
                $.post(ews_ajax_url, {
                    "action": "ews_login",
                    "code": code
                }, function(data) {
                    if(data.status == "1"){
                        location.reload();
                    }else{
                        that.removeClass("disabled");
                        that.text("验证登录");
                        alert("登录失败！");
                    }
                });
            }
        }else{
            alert("请输入验证码");
        }
        return false;
    });
});
