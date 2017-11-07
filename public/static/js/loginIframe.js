function LoginIframe() {
  
    // document.domain = 'xinpianchang.com';

    // 弹层
    this._elemLoginCover = createElemIframe("div",{
        class : "login-cover"
    },{

        left : 0,
        top : 0,
        zIndex : 1000,
        width : "100%",
        height : "100%",
        position : "fixed",
        display :"none",
        background : "rgba(0,0,0,0.5)"

    });


    // 登陆广场
    this._elemLoginGround = createElemIframe("div",{
        class : "login-ground"
    },{

        width : 382,
        height : 420,
        marginTop:-210,
        marginLeft:-191,
        position : "absolute",
        left:"50%",
        top:"50%",

    });

    // 登陆iframe
    this._elemIframe = createElemIframe("iframe",{

        src : PASSPORT_ORIGIN + "/login_iframe?from=vmovier",
        frameborder : "0"

    },{

        width : 382,
        height : 420

    });

    // 登陆取消
    this._elemClose = createElemIframe("span",{
        class : "login-iframe-close",
    },{
 
        top:20,
        right:20,
        fontWeight:"bold",
        fontSize:"20px",
        position : "absolute",
        cursor:"pointer"

    }).html("╳").on("click",function(){
        this.hide();
    }.bind(this))



    this._elemLoginGround.append(this._elemIframe,this._elemClose);
    this._elemLoginCover.append(this._elemLoginGround);
    $("body").append(this._elemLoginCover);
  
    
    function createElemIframe(tagName,attrMap,cssMap,innerHtml){
        return $("<" + tagName + "></" + tagName + ">").attr(attrMap).css(cssMap);
    }



    window.addEventListener('message', function(e){

        if (e.data.act == 'reload') {

            location.reload();

        } else if(e.data.act == 'skip'){

            location.href = e.data.msg.url;

        }

    }, false);
  
}


LoginIframe.prototype.reload = function(){
    location.reload();
}

LoginIframe.prototype.show = function(){
    this._elemLoginCover.show();
}

LoginIframe.prototype.hide = function(){
    this._elemLoginCover.hide();
}

LoginIframe.prototype.skip = function(url){
    location.href = url;
}
