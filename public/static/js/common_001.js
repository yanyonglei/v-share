
var basePath = "/api/v1",
    baseHost = "http://passport.xinpianchang.com"

function checkPhone(phone){
    return /^1[3|4|5|7|8][0-9]{9}$/.test(phone);
}

function omitPhone(phone){
    return phone.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2')
}

function getUa(){
    var ua = navigator.userAgent.toLowerCase();
    return {
        isAndroid : /android/i.test(ua),
        isIOS : /iphone|ipad|ipod/i.test(ua),
        isQq : /qq/i.test(ua),
        isWeibo : /weibo/i.test(ua),
        isWeixin : /MicroMessenger/i.test(ua)
    }
}

function getRequest(){
    var url = location.search;
    var theRequest = new Object();   
    if (url.indexOf("?") != -1) {   
        var str = url.substr(1);   
        var strs = str.split("&");   
        for(var i = 0; i < strs.length; i ++) {   
            theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);   
        }   
    }   
    return theRequest;
}

function getStrLength(str){
    var len = 0;
    var code = -1;
    for(var i=0;i<str.length;i++){
        code = str.charCodeAt(i);
        if( code >= 0 && code <= 128 )
            len++;
        else
            len+=2;
    }
    return len;
}

function showFormAction(index){
    $(".form-action").addClass("dn");
    $(".form-action").eq(index).removeClass("dn");
}

function addEmptyAction(p,notice){
    var _currentActionItem = $(".form-action:not(.dn)").find("input[name='" + p + "']").parents(".action-item");

    if(!_currentActionItem.find(".empty-notice").length){

        _currentActionItem.append("<i class='empty-notice'><em>" + (notice ? notice : "请填写此字段")  + "</em></i>");
        _currentActionItem.find("input").focus();
        // $("input[name='" + p + "']").parents(".action-item").addClass("empty-action");
    }

}

function removeEmptyAction(){
    $(".empty-notice").remove();
}

function sendCode(elem,phoneObj,sendPath,sendCallback){
    sendCallback = sendCallback || function(){};
    // console.log(phoneObj);
    if(!phoneObj.phone){
        // showDialogTip({
        //     content : "请输入电话号码"
        // })
        addEmptyAction("phone");
        return;
    }else{
        var phoneTip = propertyMap.criminal["phone"](phoneObj.phone,phoneObj.prefix_code);
        if(phoneTip){
            addEmptyAction("phone",phoneTip);
            return;
        } 
        
    }
    if(phoneObj.prefix_code && phoneObj.prefix_code.toString().indexOf("+") == -1){
        phoneObj.prefix_code = "+" + phoneObj.prefix_code;
    }
    if(!elem.data("sendFlag")){
        $.ajax({
            url: basePath + (sendPath ? sendPath : "/mobile/send"),
            data:phoneObj,
            type: "POST",
            dataType:'json',
            success:function(data){
                if(data.status == 0){
                    // elem.data("sendFlag",true);
                    // var i = 60;
                    // elem.text(i + "秒");
                    // var timer = setInterval(function(){
                    //     elem.text(--i + "秒");
                    //     if(i == 0){
                    //         clearInterval(timer);
                    //         elem.data("sendFlag",false);
                    //         elem.text("重新发送");
                    //     }
                    // },1000);
                    countdown(elem);
                    sendCallback(data);
                    $("input[name='code']").focus();
                }else{

                    showDialogTip({
                        content : data.msg
                    })
                    $(".send-identifying").removeClass("submit-loading");
                }
            },
            error:function(xhr,errorStr,error){
                // console.log(xhr,errorStr,error);
                $(".send-identifying").removeClass("submit-loading");
                showDialogTip({
                    content : "网络出错"
                })
            }
        });
    }
}

function countdown(elem){
    elem.data("sendFlag",true);
    var i = 60;
    elem.text(i + "秒");
    var timer = setInterval(function(){
        elem.text(--i + "秒");
        if(i == 0){
            clearInterval(timer);
            elem.data("sendFlag",false);
            elem.text("重新发送");
        }
    },1000);
}

var propertyMap = {

    prisonIn  : ["phone","email","code","nickname","password","reset_password"],

    prisonBreak : ["user_sex","province","city","area","year","month","day","prefix_code","callback"],
    criminal : {
        password : function(password){
            if(/^[~`!@#$%\^&\*\(\)_+\-=\{\}|\[\]\\:";'<>\?,\.\/\da-zA-Z]{6,16}$/.test(password)){
                return false;
            }else{
                return "请输入6-16位数字字母或常用特殊字符";
            }
            
        },
        email : function(email){
            if(/^[a-z_0-9.-]{1,64}@([a-z0-9-]{1,200}.){1,5}[a-z]{1,6}$/.test(email)){
                return false;
            }else{
                return "邮箱格式错误";
            }
             
        },
        phone : function(phone,mobileCode){
            if(phone && mobileCode){
                var phoneReg;
                for(p in phoneRegMap){
                    if(mobileCode == ("+" + phoneRegMap[p]["mobileCode"])){
                        // console.log(phoneRegMap[p]["RE"]);
                        phoneReg = phoneRegMap[p]["RE"];
                        phone = phoneRegMap[p]["mobileCode"] + phone;
                    }
                }
                
                // console.log(phoneReg,phone);
                if(phoneReg.test(phone)){
                    return false;
                }else{
                    return "输入手机号的格式错误";
                }
            }
            
        },
        nickname : function(nickname){
            if(getStrLength(nickname) <= 40){
                return false;
            }else{
                return "昵称过长，不允许超过40个字符";
            }
        }
    },

    wardenry : ["callback"]



}


function formBridgeServer(_target,ajaxPath,ajaxObj,ajaxCallback,ajaxFailCallback){

    var _currentFormAction = _target.parents(".form-action");
    var emptyNoticeFlag = false;
    removeEmptyAction();

    for(var p in ajaxObj){

        // 登陆邮箱电话方式切换兼容
        dp = p == "value" ? ajaxObj["type"] : p;
        
        // input-value数据获取
        if(propertyMap["prisonIn"].indexOf(dp) > -1){
            
            if(_currentFormAction.hasClass("broadcast")){
                
                ajaxObj[p] = $("input[name='" + (dp) + "']").val();
                
            }else{
                if(_currentFormAction.find("input[name='" + (dp) + "']").length){
                    ajaxObj[p] = _currentFormAction.find("input[name='" + (dp) + "']").val();
                }
            }
        // data-value数据获取
        }else if(propertyMap["prisonBreak"].indexOf(dp) > -1){
            

            ajaxObj[p] = $("." + p).attr("data-value");

        }

    }

    // 数据校验
    for(var p in ajaxObj){

        // 登陆邮箱电话方式切换兼容
        dp = p == "value" ? ajaxObj["type"] : p;

        if(propertyMap["wardenry"].indexOf(p) == -1 && !emptyNoticeFlag){

             // 非空校验
            if(!ajaxObj[p]){
                if(p == "nickname"){
                    addEmptyAction(dp,"昵称不能为空");
                }else{
                    addEmptyAction(dp);
                }
                
                emptyNoticeFlag = true;

            }

            //有效性校验
            var verifyFunction = propertyMap.criminal[dp],
                verifyStr;

            if(verifyFunction){

                if(dp == "phone"){
                    console.log(ajaxObj["prefix_code"]);
                    verifyStr = verifyFunction(ajaxObj[p],ajaxObj["prefix_code"]);
                }else{
                    verifyStr = verifyFunction(ajaxObj[p]);
                }
                if(verifyStr){
                    addEmptyAction(dp,verifyStr);
                    emptyNoticeFlag = true;
                }

            }
        }  
    }
    
    if(ajaxObj.prefix_code && ajaxObj.prefix_code.toString().indexOf("+") == -1){
        ajaxObj.prefix_code = "+" + ajaxObj.prefix_code;
    }
    // 发起ajax请求
    if(!_target.hasClass('submit-loading') && !emptyNoticeFlag){

        _target.addClass('submit-loading');
        $.ajax({
            url: basePath + ajaxPath,
            data:ajaxObj,
            type: "POST",
            dataType:'json',
            success:function(data){
                if(data.status == 0){
                    ajaxCallback(data);
                }else{

                    _target.removeClass('submit-loading');

                    showDialogTip({
                        content : data.msg
                        // type : "success"
                    },function(){
                        (ajaxFailCallback || function(){})(data);
                    })

                }
            },
            error:function(xhr,errorStr,error){
                console.log(xhr,errorStr,error);
                _target.removeClass('submit-loading');
                showDialogTip({
                    content : "网络出错"
                })
            }
        });
    }   
}
    
    
 
function placeholder(input){
    

    var _currentActionItem = input.parents(".action-item"),
        placeholderText = input.attr('placeholder'),
        defaultValue = input.val();
    
    var _placeholderEm = $("<em></em>").attr("class","placeholder").text(placeholderText);
    if(_currentActionItem.find(".phone-action").length){
        _placeholderEm.css("left","94px");
    }
    
    if(!defaultValue){
        _currentActionItem.append(_placeholderEm);
    }
 
    input.focus(function(){
 
        if(input.val()){
            _currentActionItem.find(_placeholderEm).remove();
        }

    });
    
    _placeholderEm.on("click",function(){
        input.focus();
    })
  
    input.blur(function(){
 
        if(!input.val()){
            _currentActionItem.append(_placeholderEm);
        }

    });
 
    input.keydown(function(){
        _currentActionItem.find(_placeholderEm).remove();
    });

};
 
  

 
$(function(){
    //判断浏览器是否支持placeholder属性
    var supportPlaceholder='placeholder'in document.createElement('input');

    if(!supportPlaceholder){
        $('input').each(function(){
            
            placeholder($(this));

        });
    }
    
    $(document).on('click',function(e){
        var target = $(e.target);
        if( target.parents('.field').length == 0 && e.target.className.indexOf("field") == -1 && e.target.nodeName != "EM"){
            $('.field>ul').addClass("dn");
            $(".spread").removeClass("spread");
        }
    });

    $(document).keyup(function(event){
        if($(".set-save").hasClass("dn")){
            return;
        }
        if(event.keyCode ==13){
            $(".form-action:not(.dn)").find(".submit-btn").trigger("click");
        }
    });

    // $(".nickname").blur(function(){
    //     if($(this).val()){
    //         $.get(basePath + "/user/check/nickname/" + $(this).val(),function(res){
    //             // console.log(res);
    //             if(res.status == 0){
    //                 if(res.data.is_exist){
    //                     addEmptyAction("nickname","这个昵称已经有人使用，换个昵称试试");
    //                     // $(".error-notice").removeClass("dn");
    //                     $(".nickname").addClass("border-red");
    //                 }else{
    //                     removeEmptyAction();
    //                     // $(".error-notice").addClass("dn");
    //                     $(".nickname").removeClass("border-red");
    //                 } 
    //             }else{
    //                 showDialogTip({
    //                     content : res.msg
    //                 })
                    
    //             }
                
    //         })
    //     }
    // })
    
    var emailPreValue;
    $("input").blur(function(){
        var _this = $(this);
        if(_this.val()){
            _this.parents(".action-item").find(".empty-notice").remove();

        }

        if(_this.attr("name") == "email"){

            if(_this.val() && _this.val() != emailPreValue){
                emailPreValue = _this.val();
                var emailTip = propertyMap.criminal["email"](_this.val());
                if(emailTip){
                    addEmptyAction("email",emailTip);
                } 
            }
        }

        if(_this.attr("name") == "nickname"){

            if(_this.val()){
                var trimNickname = $.trim(_this.val());
                _this.val(trimNickname);
            }
        }

        
    })


    

    // $("body").on("click",function(event){
    
    //     if(event.target.className.indexOf("field") == -1 && event.target.nodeName != "EM"){

    //         $(".field ul").addClass("dn");
            
    //     }

    // })


})

