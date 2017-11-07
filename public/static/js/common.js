// $(function(){
	$.fn.MicroRotate = function(obj){
		var def = $.extend({speed:2000,fadeTime:'slow'},obj);
		var _main = $(this).find('ul').eq(0),_control = $(this).find('ul').eq(1),_len = _control.find('li').length;
		function _rotateNext(next){
			var _cur = _control.find('.sel').index(),_next = (next!=undefined?next:(_cur+1)%_len),_lis = _main.find('li');
			_lis.eq(_next).css('z-index',2).addClass('sel');
			_lis.eq(_cur).css('z-index',3).fadeOut(def.fadeTime,function(){
				_lis.removeClass('sel').eq(_next).addClass('sel');
			});
			_control.find('li.sel').removeClass('sel').end().find('li').eq(_next).addClass('sel');
		}
		var _timer = setInterval(_rotateNext,def.speed);
		_control.find('li').on('click',function(){
			clearInterval(_timer);
			var _next = $(this).index();
			_rotateNext(_next);
			_timer = setInterval(_rotateNext,def.speed);
		});
	}
	$.extend({
		conf:{
			//保存网站各种配置参数
			starNum:5,		//星的个数，也可通过DOM的data-num设置，后者优先级高。
			centerPage: 5	//分页中间显示个数
		},
		str:{
			// 保存网站各种字符串
			emailReg:/\w+((-w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+/
		},
		getLen:function(str){
			return str.replace(/[^\x00-\xff]/g,"aa").length;
		},
		alert:function(obj){
			var def = {fade:200,delay:1500,tip:"操作成功"};
			var o = $.extend({},def,obj);
			var imgUrl = (o.error==undefined)?"xm_tip_ok.png":"xm_tip_error.png";
			$('#xm-tip-dialog').remove();
			var dom = '<div class="xm-tip-dialog" id="xm-tip-dialog">'+
						'<img src="'+Think.IMG+'/' + imgUrl + '"/><span>'+ o.tip +'</span>'+
					  '</div>';
			$(dom).appendTo('body');
			$('#xm-tip-dialog').css({
				marginLeft:-$('#xm-tip-dialog').width()/2
			}).fadeIn(o.fade).delay(o.delay).fadeOut(o.fade,function(){
				$(this).remove();
				if( o.complete != undefined && typeof o.complete == "function" ) 
					o.complete();
			});
		},
		confirm:function(obj){
			var pace = 200;
			var def = {};
			var o = $.extend({},def,obj);
			var conDom = '';
			if( o.buttons != undefined ){
				conDom += (o.buttons.length==0)?'':'<div class="xm-con-btn">';
				for(var i=0;i<o.buttons.length;i++){
					var tmp = 	'<a href="javascript:void(0);" class="rl-btn">'+
									o.buttons[i].name +
								'</a>';
					conDom += tmp;
				}
				conDom += (o.buttons.length==0)?'':'</div>';
			}
			var dom = 	'<div class="xm-mask" id="xm-mask"></div>'+
						'<div class="xm-con-dialog" id="xm-con-dialog">'+
							'<div class="xm-con-title">'+
								'<a href="javascript:void(0);" class="dialog-close">×</a>'+
							'</div>'+
							'<div class="xm-con-main">'+o.content+'</div>'+conDom+
					  	'</div>';
			$(dom).appendTo('body');
			if( o.init != undefined && typeof o.init == 'function' )
				o.init();
			if( obj.buttons.length != 0 ){
				$('#xm-con-dialog .xm-con-btn>a').each(function(i){
					if( obj.buttons[i].callback != undefined ){
						$(this).bind('click',function(){
							if( obj.buttons[i].callback() == false )
								return;
							$('#xm-con-dialog,#xm-mask').remove();
						});
					}
				});
			}
			$('#xm-con-dialog a.dialog-close').bind('click',function(){
				$('#xm-con-dialog,#xm-mask').remove();
			});
			$('#xm-con-dialog').css({
				marginLeft:-$('#xm-con-dialog').width()/2,
				marginTop:-$('#xm-con-dialog').height()/2
			}).fadeIn(pace);
		},
		comment:function(obj){
		/*
		*	评论默认配置
		*	type:标识请求哪里的评论，type=0为文章评论，type=1为网剧评论。
		*	page:默认请求第一页的评论。
		*	postid:请求的 文章|网剧 的ID。
		*/
			var def = {page:1,type:0,postid:1,login:0,referid:0};		
			var o = $.extend({},def,obj),cur = 0,ajaxIng = 0;
			var replyuid = replycommentid = 0;
			ajax_get(o);
			pager_bind();
function ajax_get(o){
	$.post('/Comment/getComment',o,function(res){
		// console.log(res);
		if( res.status == 1 ){
			var pager = {
				tt	:	parseInt(res.ftotalCount),
				ft	:	parseInt(res.totalCount),
				pn 	:	parseInt(res.pageNumber),
				ps 	:	parseInt(res.pageSize)
			}
			if(commentType == 5 ){
				var commentNumText = "条留言";
			}else{
				var commentNumText = "条点评";
			}
			$('#comment .comment-title .num').text('已有'+pager.ft+commentNumText);

			$('#post-comment').text(pager.ft);
			if( pager.tt != 0 ){
				init(res.data);
			}else{
				$('#com-list').html('');
			}
			if(pager.tt>pager.pn){
				initPager(pager);
			}
			if( o.commentid ){
				series_noscroll();
				location.href="#c_"+o.commentid;
			}
			if (o.page>1) {
				location.href="#comment";
			};
		}
	},'json');
}
/*	绑定评论列表各种操作	*/
function pager_bind(){
	/*	绑定分页操作事件	*/
	$('#comment').on('click','#comment-pager  span[data-p]',function(){
		o.page = $(this).data('p');
		ajax_get(o);
		$(window).scrollTop($("#comment")[0].offsetTop-90);
	});
	$('#comment input[data-sync]').attr('disabled',false);
	
/*	return操作前写未登录可以做的逻辑操作	*/
	if( o.login == 0)
		return;
	if( Think.LOGIN_USER.bind_weibo == 0 ){
		$('#comment').on('click','input[data-sync]',function(){
			var pObj = $(this).parent(),off = pObj.offset();
			if( $('#comment-sync').length == 0 ){
				$('<div class="comment-sync" id="comment-sync"></div>').appendTo($('body'));
				var str = '<div class="info">去新浪微博授权后才可以回复，点击确定前往相应页面。<a href="javascript:void(0);" id="wb_bd">确定</a><a href="javascript:void(0);" id="wb_cancel" class="info_close">取消</a></div>'+
					'<div class="arrow"><div class="arrow-before"></div><div class="arrow-after"></div></div>';
				$('#comment-sync').html(str).css({
					left:off.left,top:off.top+28
				});
			}else{
				$('#comment-sync').remove();
			}
			return false;
		});
		$(document).on('click',function(evt){
			if( $(evt.target).closest('.comment-sync').length == 0 ){
				$('#comment-sync').remove();
			}
		});
		$(document).on('click','#comment-sync a:last-child',function(){
			$('#comment-sync').remove();
		});
		var wbChild;
		$(document).on('click','#comment-sync a:first-child',function(){
			wbChild = window.open('/Oauth/weibo','weiboChild','width=800,height=600,left=100,top=100');
		});
	}
	/*	绑定回复操作事件	*/
	$('#com-list').on('click','.reply',function(){
		var referid = $(this).data('referid');
		replycommentid = $(this).data('replycommentid');
		replyuid = userid = $(this).data('replyuid');
		var toObj = $(this).parents('li').eq(0);
		if( toObj.next('li#comment-reply').length != 0 ){
 			$('#comment-reply').slideUp(300,function(){
 				$(this).remove();
 			});
 			return false;
 		}
 		$('#comment-reply').remove();
		$('#comment .comment-text:eq(0)').clone().insertAfter(toObj).wrap('<li class="comment-reply dn" id="comment-reply"></li>');
		$('#comment-reply').find('.comment-textarea').removeClass('input-error').data('len',1);
		$('#comment-reply').find('.comment-btn').data({'referid':referid,'replyuid':userid, 'replycommentid':replycommentid}).text('回复');
		$('#comment-reply').find('.expression').addClass('reply-expression').removeClass('expression');
		$('#comment-reply').find('.comment-tip i').text($('.comment-textarea').attr('maxlength')).end()
			.slideDown(300,function(){
			$(this).removeClass('dn');
		});
	});
	$('#comment').on('keyup','.comment-textarea',function(){
		var perLen = $(this).attr('maxlength');
		var len = $.trim($(this).val()).length;
		$(this).next('.comment-ope').find('.comment-tip i').text(perLen-len);
		if( len > 0 )
			$(this).removeClass('input-error');
	});
	$('#comment').on('click','.comment-btn',function(){
		if( ajaxIng == 1 )
			return;
		var obj = $(this).parents('.comment-input').eq(0),
			textarea = obj.find('.comment-textarea'),
			val = $.trim(textarea.val()),minLen = textarea.data('len'),
			_this = $(this);
			var reg = /(\[[^\]]{1,9}?\])/g;
			var newVal = val.match(reg);
			if (newVal) {
				for (var i = 0 ;i < newVal.length; i++) {
					var a = newVal[i].substring(1, newVal[i].length - 1);
					var index = publicExpressText.indexOf(a);
					if (index != -1) {
						val = val.replace(/(\[[^\]]{1,9}?\])/, realEmoji[index]);
					}
				}
			}
		if( val.length < minLen ){
			$.alert({tip:(minLen!=1?'最少输入2个字':'回复不能为空'),error:true});
			textarea.addClass('input-error');
			return;
		}
		if ($(this).data('replyuid')==0) {
			replyuid = 0;
		};
		var postObj = {
			postid:o.postid,
			content:val,
			type:o.type,
			referid:$(this).data('referid'),
			replyuid:replyuid,
			replycommentid:replycommentid,
			weibo:$(this).siblings('label').eq(0).find('input[data-sync]').prop('checked')==true?1:0
		};
		ajaxIng = 1;
		_this.text(minLen!=1?'发表中':'回复中');
		$.post('/Comment/index',postObj,function(res){
			ajaxIng = 0;
			if( minLen!=1 )
				_this.text('发表点评');
			else
				_this.text('回复');
			if( res.status == 0 ){
				if (typeof articleInfo != "undefined") {
					zhuge.track("评论成功", {
			            "来源": "pc",
			            "影片id": articleInfo.id,
			            "影片名": articleInfo.title,
			            "影片分类": articleInfo.cate,
			            "影片时长(秒)": articleInfo.duration
			        });
				}
				
				var href = location.href;
				if( minLen != 1 ){
					// $(init_item(res.data,0)).prependTo($('#com-list'));
				}else{
					var reply = $('#comment-reply');
					var pObj = reply.parent();
					if(pObj.attr('id') == 'com-list'){
						if(reply.next('.com-sub').length==0){
							reply.after('<li class="com-sub clearfix"><ul class="com-list"></ul></li>');
						}
						reply.next('.com-sub').find('.com-list').append($(init_item(res.data,0)));
					}else{
						pObj.append($(init_item(res.data,1)))
					}
					$('#comment-reply').remove();
				}
				$('#comment textarea').val('');
				$('#comment .comment-tip i').text($('.comment-textarea').attr('maxlength'));
				if(res['data']['referid']==0){
					o.page = res.page;
					o.commentid = res['data']['commentid'];
					ajax_get(o);
				}
			}else{
				//错误提示
				$.alert({
					error:true,
					tip:res.msg
				});
			}
			},'json');
	});
	/*	评论条数展开	*/
	$("ul li.com-sub").each(function(){
		$(this).get(0).isHide=false;
	});
	$('#comment').on('click','.count-reply',function(){
		var more = $(this).data('more');
		if( more == 0 ){
			var sub = $(this).parents('li').next('li.com-sub');
			if(!sub.get(0).isHide){
				sub.slideUp();
				sub.get(0).isHide=true;
			}		
			else{
				sub.slideDown();
				sub.get(0).isHide=false;
			}
		}else{
			var _this = $(this),
				comId = _this.data('commentid'),
				_thisLi = $(this).parents('li').eq(0);
			$.post('/Comment/getSubComment',{commentid:comId},function(res){
				var str = '';
				for( var i = 0;i<res.data.length;i++){
					str += init_item(res.data[i],0);
				}
				_thisLi.after($('<li class="com-sub clearfix"><ul class="com-list">'+str+'</ul></li>'));
				_this.data('more',0);
			},'json');
		}
	});
	/*	管理员||自己删除评论	*/
	$('#comment').on('click','.delete',function(){
		var _pLi = $(this).parents('li').eq(0),
			_sib = _pLi.next('.com-sub');
		var comId = $(this).data('commentid');
		$.confirm({
			content:"是否确定删除？",buttons:[
			  {
			    name:"确定",callback:function(){
					$.post('/Comment/delete',{commentid:comId},function(res){
						if( res.status == 0 ){
							_pLi.remove();
							_sib.remove();
						}else{
							$.alert({tip:res.msg,error:true});
						}
					},'json');
			    }
			  },
			  {name:"取消",callback:function(){}}
			]
		});
	});
	$('#comment').on('click','.setwonderful',function(){
		var _this = $(this);
			recommend = _this.data('recommend'),
			commentid = _this.data('commentid');
		if( recommend == 0 ){
			$.post('/Comment/recommend',{commentid:commentid},function(res){
				if( res.status == 0 ){
					$.alert({tip:'操作成功'});
					_this.data('recommend',1);
					_this.text('取消精彩');
					// $('<span class="com-amazing">精彩点评</span>').prependTo(_this.parent().siblings('.title'));
				}else{
					$.alert({tip:res.msg,error:true});
				}
			},'json');
		}else{
			$.post('/Comment/recommend',{commentid:commentid},function(res){
				if( res.status == 0 ){
					$.alert({tip:'操作成功'});
					_this.data('recommend',0);
					_this.text('设为精彩');
					_this.parent().siblings('.title').find('.com-amazing').remove();
				}else{
					$.alert({tip:res.msg,error:true});
				}
			},'json');
		}
	});
	// 评论点赞
	var img = 0
	$('#comment').on('click','.js-com-prove',function(){
		if(img==1){
			return;
		}
		img = 1;
		var _thisObj = $(this),
		commentid = _thisObj.data('commentid');
		if(_thisObj.hasClass('approve')){
			// 二级评论
			var _thisNum = _thisObj.find('span').length ? parseInt(_thisObj.find('span').text()) : 0;
			$.post('/Comment/approve', {commentid : commentid}, function(re){
				if(re.status === 1){
					// 点赞成功
					if(_thisNum === 0){
						_thisObj.append('(<span>1</span>)');
					}else{
						_thisObj.find('span').text(++_thisNum);
					}
					img = 0;
				}else if(re.status === 0){
					// 取消点赞
					(_thisNum - 1) > 0 ? _thisObj.find('span').text(--_thisNum) : _thisObj.text('赞');
					$.alert({tip:'取消成功'});
					img = 0;
				}else{
					// 异常
					$.alert({tip:re.msg,error:true});
					img = 0;				}
			},'json');
		}else{
			// 一级评论
			var _thisNum = _thisObj.text() === '赞同' ? 0 : parseInt(_thisObj.text());
			if(!_thisObj.hasClass("com-proved")){
				// 点赞成功
				_thisObj.text(++_thisNum).addClass('com-proved');
				var _str = '<div class="add-one">+1</div>';
				_thisObj.append(_str);
				$('.add-one').animate({top:'-40px',opcity:0},function(){
					$(this).remove();
				});
			}else{
				// 取消点赞
				_thisNum = (_thisNum - 1) > 0 ? (--_thisNum) : '赞同';
				_thisObj.text(_thisNum).removeClass('com-proved');
				$.alert({tip:'取消成功'});
			}
			$.post('/Comment/approve', {commentid:commentid}, function(re) {
				img = 0;
				if(re.status != 0 && re.status!= 1){
					// 异常
					$.alert({tip:re.msg,error:true});
				}
			}, 'json');
		}
		
	});
}
/*	生成分页数据	*/
function initPager(p){
	var str = '',cn = $.conf.centerPage,sp = Math.floor(cn/2);
	var tp = Math.ceil(p.tt/p.ps);
	str += (p.pn>1?'<span class="prev" data-p="'+(p.pn-1)+'">上一页</span>':'');
	str += ((p.pn-sp)>2?'<span data-p="1">1</span><span class="dot">...</span>':'');
	var start = (p.pn-sp)==2?1:(p.pn-sp),end = (p.pn+sp)==(tp-1)?tp:(p.pn+sp);
	for(var i=1;i<=tp;i++){
		if( start <= i && i <= end ){
			if( i == p.pn )
				str += '<span class="cur" data-p="'+i+'">'+i+'</span>';
			else
				str += '<span data-p="'+i+'">'+i+'</span>';
		}else{

		}
	}
	str += ((p.pn+sp)<(tp-1)?'<span class="dot">...</span><span data-p="'+tp+'">'+tp+'</span>':'');
	str += ((p.pn<tp)?'<span class="next" data-p="'+(p.pn+1)+'">下一页</span>':'');
	if($('#comment-pager').length==0)
		$('#com-list').after('<div class="comment-pager" id="comment-pager"></div>');
	$('#comment-pager').html(str);
}
/*	初始化评论列表	*/
function init(res){
	$('#com-list').html('');
	for(var i = 0,len=res.length;i<len;i++){
		$(init_item(res[i],0)).appendTo($('#com-list'));
		if(res[i].hassub == 1 && res[i].ref.length != 0 ){
			var _dom = $('<li class="com-sub clearfix"></li>');
			_dom.appendTo($('#com-list'));
			$('<ul class="com-list"></ul>').appendTo(_dom);
			for(var j=0;j<res[i].ref.length;j++){
				$(init_item(res[i].ref[j],1)).appendTo(_dom.find('.com-list'));
			}
		}
	}	
}
/*	初始化单条评论	*/
function init_item(res,hassub){

	if (emojiReplaceMode != "unified") {
		var reg = "\ud83c[\udf00-\udfff]|\ud83d[\udc00-\ude4f]|\ud83d[\ude80-\udeff]";
		function findEmoji(content){
		   	return content.match(new RegExp(reg, "g"));
		}
		var contentTemp = findEmoji(res.content);
		if (contentTemp) {
			for (var i = 0; i < contentTemp.length; i++) {
				var index = realEmoji.indexOf(contentTemp[i]);
				res.content = res.content.replace(new RegExp(reg), '<img src="http://www.vmovier.com/Public/Home/images/emoji/'+index+'.png?20170224" alt="'+ publicExpressText[index] +'" width="20" title="'+ publicExpressText[index] +'">');
			}
		}
	}
	
	if(controller=='Series'){
		controller='series_post';
	}
var str='<li class="clearfix" id="c_'+res.commentid+'">';
			if(res.referid==0){
				if(res.count_approve>0){
					str += '<div class="'+(o.login==0?' need-login':'js-com-prove')+' com-prove '+(res.isapprove==0?'':' com-proved')+' zg" data-commentid="'+res.commentid+'" approved="'+res.approved+'" data-zg="点赞">'+res.count_approve+'</div>';
				}else{
					str += '<div class="'+(o.login==0?' need-login':'js-com-prove')+' com-prove '+(res.isapprove==0?'':' com-proved')+' zg" data-commentid="'+res.commentid+'" approved="'+res.approved+'" data-zg="点赞">赞同</div>';
				}
			}
			str += '<div class="com-img zg" data-zg="点击评论头像">'+
					'<a target="_blank" href="'+res.userinfo.homepage+'?from='+controller.toLowerCase()+'_comment" class="full">'+
						'<img class="full" src="'+res.userinfo.avatar_180+'" alt="头像">'+
					'</a>'+
			'</div>';
			if(res.referid>0){
				str += '<div class="com-text sub-text">';
			}else{
				str += '<div class="com-text">';
			}
			str +='<h4 class="title zg" data-zg="点击评论用户名">'+
						'<a target="_blank" href="'+res.userinfo.homepage+'?from='+controller.toLowerCase()+'_comment">'+res.userinfo.username+'</a>'+
						(res.userinfo.isadmin == 3?'<a target="_blank" href="'+res.userinfo.vpage+'" class="plus-v-p-14" title="新片场认证创作人"></a>':res.userinfo.isadmin==4?'<a target="_blank" href="'+res.userinfo.vpage+'" class="plus-v-c-14" title="新片场认证机构"></a>':'')+
						(res.replyuid == 0?'':(' 回复 <a target="_blank" href="'+res.replyuser.homepage+'">'+res.replyuser.username+'</a>')+
						(res.replyuser.isadmin == 3?'<a target="_blank" href="'+res.replyuser.vpage+'" class="plus-v-p-14" title="新片场认证创作人"></a>':res.replyuser.isadmin==4?'<a target="_blank" href="'+res.replyuser.vpage+'" class="plus-v-c-14" title="新片场认证机构"></a>':''))+

					'</h4>'+
					'<div class="intro">'+res.content+'</div>'+
					'<div class="ope">'+
					(Think.admin==1?('<a href="javascript:;" data-commentid="'+res.commentid+'" class="fr setwonderful" data-recommend='+res.isrecommend+'>'+(res.isrecommend==1?'取消精彩':'设为精彩')+'</a>'):'')+
					(Think.admin==1||Think.LOGIN_USER.userid==res.userid?'<a href="javascript:;" data-commentid="'+res.commentid+'" class="fr delete">删除</a>':'');
					if(res.referid>0){
						str += '<a href="javascript:;" data-commentid="'+res.commentid+'" class="fr approve '+(o.login==0?' need-login':' js-com-prove')+'">赞'+
						(res.count_approve==0?'':'(<span>'+res.count_approve+'</span>)')+'</a>';
					}
					str += 
					(res.forbidden_comment == 1?'':('<a href="javascript:;" data-zg="点击评论回复"  data-referid='+res.p_commentid+' data-replyuid="'+res.userid+'" data-replycommentid="'+res.commentid+'" class="zg fr reply'+(o.login==0?' need-login':'')+'">回复</a>'))+
					((hassub==0)?res.count_reply!=0?'<a href="javascript:;" data-commentid="'+res.commentid+'" data-more="'+res.more_than+'" class="fr count-reply">'+res.count_reply+'条评论</a>':'':'')+
						'<span class="time">'+res.addtime+'</span>'+		
						(res.plat>=4?'来自<a href="/app" class="from-plat">'+(res.plat==5?'V电影Android客户端':'V电影iOS客户端')+'</a>':'')+		
					'</div>'+
			'</div>'+
		'</li>';
	return str;
}
		},
		/*	首页图片轮转	*/
		rotate:function(opt){
			var def = {
				domId: '#rotate',
				pace: 7600,
				nextBtn: '#index-next',
				preBtn: '#index-prev',
				wrap: '#index-header'
			};
			def = $.extend({},def,opt);
			if( $(def.domId).length == 0 )
				return false;
			var _dom = $(def.domId),
				num = _dom.find('.rotate-item').length,
				_con = _dom.find('.rotate-nav');
			var timer = setInterval(function(){_animate();},def.pace);
			function _animate(i){
				var cur = _con.find('.sel').index();
				var next = i!=undefined?i:(++cur%num);
				_dom.find('.rotate-active').removeClass('rotate-active');
				_dom.find('.rotate-item').eq(next).addClass('rotate-active');
				_con.find('.sel').removeClass('sel');
				_con.find('a').eq(next).addClass('sel');
			};
			_con.find('a').each(function(){
				$(this).on('click',function(){
					clearInterval(timer);
					_i = $(this).index();
					_animate(_i);
					timer = setInterval(function(){_animate();},def.pace);
					return false;
				});
			});
			$(def.wrap).hover(function(){
				$(def.nextBtn+','+def.preBtn).removeClass('dn');
			},function(){
				$(def.nextBtn+','+def.preBtn).addClass('dn');
			});
			$(def.preBtn).on('click',function(){
				clearInterval(timer);
				_i = _con.find('.sel').index();
				var next = (_i+num-1)%num;
				_animate(next);
				timer = setInterval(function(){_animate();},def.pace);
			});
			$(def.nextBtn).on('click',function(){
				clearInterval(timer);
				_i = _con.find('.sel').index();
				var next = (_i+1)%num;
				_animate(next);
				timer = setInterval(function(){_animate();},def.pace);
			});
		}
	});
	/*	星级渲染JS @小毛*/
	function ratingInit(){
		$('.rating').each(function(){
			var str = $(this).data('score');
			var score = parseFloat($(this).data('score')/2);
			var floor = Math.floor(score);
			var len = floor*90/5 + 3 + (score-floor)*12;
			$(this).html('<div class="rating-holder"><span style="width:'+len+'px;"></span></div><span class="num">'+str+'</span>');
		});
	}
	ratingInit();
	$.rotate({});

	/*	搜索	*/
	$('#index-search').on('keyup','input[type="text"]',function(evt){
		if( evt.keyCode == 13 ){
			$('#index-search .index-search-btn').trigger('click');			
		}
	});
	$('#index-search').on('click','.index-search-btn',function(){
		var val = $.trim($('#index-search input[type="text"]').val());
		if( val == '' ){
			return false;
		}else{
			location.href = $(this).prop('href');
		}
	});
	/*	导航条运动效果	@小毛*/
	var nav_sel = $('#fix-list .fix-sel').eq(0);
	if( nav_sel.length==0)
		nav_sel = $('#fix-list .fix-item').eq(0);
	var nav_ref = $('#fix-list .fix-move');
	var nav_pace = 30,nav_s_p = 70,nav_l = 10;
	if( nav_sel.length != 0 )
		nav_ref.fadeIn(10).css({'left':nav_sel.position().left,'width':nav_sel.width()});
	$('#fix-list .fix-item:not(".fix-sel")').each(function(){
		var this_l = $(this).position().left,
			sel_l = (nav_sel.length==0?0:nav_sel.position().left),
			offset = (this_l>sel_l)?nav_l:-nav_l;
		$(this).hover(function(){
			var left = $(this).position().left,width = $(this).width();
			nav_ref.stop(true,false).animate({left:left+offset,width:width},nav_pace)
						.animate({'left':left-offset},nav_s_p)
						.animate({'left':left},nav_s_p);
		},function(){
			var left = nav_sel.position().left,width = nav_sel.width();
			nav_ref.stop(true,false).animate({left:left+offset,width:width},nav_pace)
						.animate({'left':left-offset},nav_s_p)
						.animate({'left':left},nav_s_p);
		});
	});
	/*	end导航条运动效果*/
	/*人人关注*/
	$('#follow-renren').on('click',function(){
		if( $('#follow-renren-img').length != 0 ){
			$('#follow-renren-img').remove();
			return false;
		}
		var _off = $(this).offset();
		$('<div id="follow-renren-img" class="follow-renren-img"></div>').css({
			position:'absolute',
			left:_off.left-260,top:_off.top-120,
			textAlign:'center',
			zIndex:1
		}).html('<div class="follow-renren-title" style="position:absolute; top:10px; left:37px; font-size:14px;"><p>用人人客户端扫描二维码</p><p>订阅V电影公众号</p></div><img src="/Public/Home/images/follow-renren.png?20140321" alt="" /><span class="dialog-close">×</span>').appendTo($('body'));
	});
	$(document).on('click','#follow-renren-img .dialog-close',function(){
		$('#follow-renren-img').remove();
	});
	
// });


