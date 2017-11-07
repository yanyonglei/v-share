$(".avatar-change,.avatar-change-sub").on("click",function(){
            
    $(".avatar-change-cover").removeClass("dn");
    $("body").addClass("ovh");

})

$(".avatar-change-cover-close").on("click",function(){
    $(".avatar-change-cover").addClass("dn");
    $("body").removeClass("ovh");
})

// $(".nickname-edit").on("click",function(){
//     $(".nickname-verify-item").addClass("dn");
//     $(".nickname-item").removeClass("dn");

// })

$(".field").on("click",function(){

    var _this = $(this),
        currentFieldUl = _this.find("ul");

    // currentFieldUl.addClass("dn");
    if($(".province").data("value") == 830000 && ($(this).hasClass("city") || $(this).hasClass("area"))){
        return false;
    }

    if(currentFieldUl.hasClass("dn")){

        $('.field>ul').addClass("dn");
        currentFieldUl.removeClass("dn");
        _this.addClass("spread")
    }else{
        currentFieldUl.addClass("dn");
        _this.removeClass("spread")
    }
    

})




$(".field").on("click","li",function(){

    var _this = $(this),
        _list =  _this.parents("ul"),
        _field =  _this.parents(".field"),
        value = _this.data("value"),
        type = _field.data("type");
    _this.parents("ul").find("li").removeClass("select");
    _this.addClass("select");
    _this.parents("ul").addClass("dn");
    $(".spread").removeClass("spread");
    _field.data("value",value).addClass("color333");
    _field.find("em").text(_this.text());

    if(type == "province"){
        abroadDisabled(_this, value);
        $(".city,.area").data("value",0)
        $(".city,.area").find("em").text("请选择");
        $(".city-list,.area-list").empty().addClass("dn");
    }else if(type == "city"){
        $(".area").data("value",0)
        $(".area").find("em").text("请选择");
        $(".area-list").empty().addClass("dn");
    }

    // console.log();
    if(type && value){

        areaLevelSet(type,value);

    }

    return false;
})

$(".city,.province").each(function(){
    var _this = $(this),
        value = _this.data("value");
    if(value){
        abroadDisabled(_this, value);
        // console.log(_this.data("type"),_this.data("value"));
        areaLevelSet(_this.data("type"),_this.data("value"))
    }
})


function areaLevelSet(type,value){

    var levelMap = ["province","city","area"];

    var actionLevel = levelMap[levelMap.indexOf(type) + 1];
    // console.log(levelMap.indexOf(type));
    $.get(basePath + "/" + type + "/" + value,function(res){

        
        if(res.data){
            var str = "";
            for(var i = 0;i < res.data.length;i++){

                str = "<li data-value='" + res.data[i]["id"] + "'>" + res.data[i]["name"] + "</li>" + str;

            } 
            var _actionLevelList = $("." + actionLevel + "-list");
            if(res.data.length < 5){
                _actionLevelList.css("height","auto");
            }

            _actionLevelList.append(str); 
        }
        
    })

}


function abroadDisabled(elem, value) {
    var _lineItem = elem.closest(".set-item"),
        _cityElem = _lineItem.find(".city"),
        _areaElem = _lineItem.find(".area");

    if (value == 830000) {
        if (!_cityElem.hasClass("disabled")) {
            _cityElem.addClass("disabled");
            _areaElem.addClass("disabled");
        }

    } else {
        if (_cityElem.hasClass("disabled")) {
            _cityElem.removeClass("disabled");
            _areaElem.removeClass("disabled");
        }
    }
}


$(".set-save,.jump").on("click",function(){
    
    var _this = $(this);

    var obj = {
        nickname : "",
        school : "",
        profile : ""
    };

    if($(this).data("jump")){

        obj['jump'] = 1;

    }else{

        for(var p in obj){

            obj[p] = $("input[name='" + p + "'],textarea[name='" + p + "']").val();
        }

        obj["user_sex"] = $(".sex").data("value");
        obj["province"] = $(".province").data("value");
        obj["city"] = $(".city").data("value");
        obj["area"] = $(".area").data("value");
        
        obj["birthday"] = $(".year").data("value").toString() + $(".month").data("value").toString() + $(".day").data("value").toString();
        if(!obj.nickname){

            addEmptyAction("nickname","昵称不可为空");
            return;
        }else if(getStrLength(obj.nickname) > 40){

            addEmptyAction("nickname","昵称过长，不允许超过40个字符");
            return;
        }
        

    }

    
    if(!_this.hasClass('submit-loading')){

        if(!obj['jump']){
            _this.addClass('submit-loading');
        }
        
        $.post(basePath + "/user/improve",obj,function(res){
            _this.removeClass('submit-loading');
            if(res.status == 0){
                
                if($(".set-detail").data("link")){
                    location.href = res.data.callback;
                }else{
                    showDialogTip({
                        content : "保存成功",
                        type : "success"
                    })
                }
                
                // location.reload();
            }else{
                showDialogTip({
                    content : res.msg
                })
            }
        })
    }
    
    

})