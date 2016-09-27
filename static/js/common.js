// JavaScript Document
(function($){
	$.fn.extend({
		//层居中
		center: function (settings) {
			settings = $.extend({'fixed':true},settings);
			return this.each(function() {
				var top = ($(window).height() - $(this).outerHeight()) / 2;
				var left = ($(window).width() - $(this).outerWidth()) / 2;
				if(settings.fixed){
					$(this).css({position:'fixed', margin:0, top:top,left:left});
				}else{
					$(this).css({
						position:'absolute', 
						margin:0, 
						top:top+$(document).scrollTop(),
						left:left+$(document).scrollLeft()
					});
				}			
			});
		},
		//层可拖动
		dragable:function(options){
			options = $.extend({},options);
			var mouse = {x:0,y:0};
			var div = $(this);
			var _this = this;
			div.css({'position':'absolute','z-index':1000});
			_this.moveDiv = function(event){
				var e = window.event || event;
				var position = div.offset();
				var top = position.top + (e.clientY - mouse.y);
				var left = position.left + (e.clientX - mouse.x);
				div.css({top:top,left:left});
				mouse.x = e.clientX;
				mouse.y = e.clientY;
			}
			var handle = options.handle ? $(options.handle) : div;
			handle.mousedown(function(event){
				var e = window.event || event;
				mouse.x = e.clientX;
				mouse.y = e.clientY;
				$(document).bind('mousemove',_this.moveDiv);
			});
			$(document).mouseup(function(){
				$(document).unbind('mousemove',_this.moveDiv);
			});
		},
		//当前位置插入内容
		insertContent: function(myValue, t) {
            var $t = $(this)[0];
            if (document.selection) { //ie
                this.focus();
                var sel = document.selection.createRange();
                sel.text = myValue;
                sel.moveStart('character', -l);
                var wee = sel.text.length;
                if (arguments.length == 2) {
                    var l = $t.value.length;
                    sel.moveEnd("character", wee + t);
                    t <= 0 ? sel.moveStart("character", wee - 2 * t - myValue.length) : sel.moveStart("character", wee - t - myValue.length);
                    sel.select();
                }
            } else if ($t.selectionStart || $t.selectionStart == '0') {
                var startPos = $t.selectionStart;
                var endPos = $t.selectionEnd;
                var scrollTop = $t.scrollTop;
                $t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
                this.focus();
                $t.selectionStart = startPos + myValue.length;
                $t.selectionEnd = startPos + myValue.length;
                $t.scrollTop = scrollTop;
                if (arguments.length == 2) {
                    $t.setSelectionRange(startPos - t, $t.selectionEnd + t);
                    this.focus();
                }
            }
            else {
                this.value += myValue;
                this.focus();
            }
        },
		//表单验证
		validate:function(option){
			var opt = $.extend({
				submitType:'',
				submitButton:'',
				settings:{}
			},option);
			
			var form  = $(this);
			var that  = this;		
			var tips  = $('<div/>').addClass("ui-tips-box ui-form-tips").css({'z-index':'10005'});
			var arrow = $('<div/>').addClass("ui-tips-arrow ui-form-tips").css({'z-index':'10005'});
			var validateItems = $(this).find("[required]");
			this.flag = null;
			this.showPrompt = function(o,text){
				that.hidePrompt();
				$("body").append(tips);
				$("body").append(arrow);
				tips.empty().text(text);
				var offset = $(o).offset();
				arrow.css({top:offset.top-14, left:offset.left + 10});
				tips.css({top:offset.top-$(tips).outerHeight()-8, left:offset.left});
			}
			this.hidePrompt = function(){
				$(".ui-form-tips").remove();
			}
			
			this.validateItem = function(o){				
				var value = $.trim($(o).val());
				var regular  = $(o).attr("regular");
				var errormsg = $(o).attr('error');
				if(value == undefined) value = '';
				if(regular == undefined || regular == ''){
					if(value.length < 1){
						if(errormsg != undefined) that.showPrompt(o,errormsg);
						that.flag = false;
					}else {
						that.hidePrompt();
					}
				}else {
					regular = eval(regular);
					if(!regular.test(value)){
						if(errormsg != undefined) that.showPrompt(o,errormsg);
						that.flag = false;
					}else {
						that.hidePrompt();
					}
				}
			}
			
			this.validateForm = function(){
				that.flag = true;
				$(that).find("[required]").each(function(index, element) {
                    if(that.flag) that.validateItem(element);
                });
			}
			
			this.bind = function(){
				$(that).find("[required]").each(function(index, element) {
					if($(element).attr('prompt') != undefined){
						$(element).focus(function(e) {
							that.showPrompt(element,$(element).attr('prompt'));
						});
					}
					$(element).blur(function(e) {
						that.hidePrompt();
					});
				});
			}
			
			this.bind();
			if(opt.submitType == 'ajax'){
				form.find(opt.submitButton).click(function(e) {
                    that.validateForm();
					if(that.flag) form.ajaxSubmit(opt.settings);
                });
			}else {
				$(this).submit(function(e) {
                    that.validateForm();
					return that.flag;
                });
			}
		}
	});
})(jQuery);

var DSXUtil = {
	mb_cutstr : function(str, maxlen, dot) {
		var len = 0;
		var ret = '';
		var dot = !dot ? '...' : '';
		maxlen = maxlen - dot.length;
		for(var i = 0; i < str.length; i++) {
			len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
			if(len > maxlen) {
				ret += dot;
				break;
			}
			ret += str.substr(i, 1);
		}
		return ret;
	},
	IsURL : function(url){ 
		return /^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\:+!]*([^<>])*$/.test(url);
	},
	IsChineseName:function(username){
		return /^[\u4e00-\u9fa5]{2,12}$/.test(username);
	},
	IsUserName:function(username){
		return /^[a-zA-Z0-9_\u4e00-\u9fa5]{2,20}$/.test(username);
	},
	IsEmail : function(email){
		return /^[-._A-Za-z0-9]+@([A-Za-z0-9]+\.)+[A-Za-z]{2,3}$/.test(email);
	},
	IsMobile : function(num){
		return /^1[3|5|8]\d{9}$/.test(num);
	},
	IsPassword : function(str){
		return /^.{6,20}$/.test(str);
	},
	paramToJSON : function(str){
		if(!str) return;
		var json = {};
		var arr = str.split('&');
		$.each(arr,function(i,o){
			var arr2 = o.split('=');
			json[arr2[0]] = arr2[1] ? arr2[1] : '';
		});
		return json;
	},
	randomString : function (len) {
	　　len = len || 32;
	　　var $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';    
	   /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
	　　var maxPos = $chars.length;
	　　var pwd = '';
	　　for (i = 0; i < len; i++) {
	　　　　pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
	　　}
	　　return pwd;
	},
	getQueryString : function(item){
		var svalue = location.search.match(new RegExp("[\?\&]" + item + "=([^\&]*)(\&?)","i"));
		return svalue ? svalue[1] : svalue;
	},
	checkAll : function(o,input){return $(input).attr('checked',$(o).is(":checked"));},
	setCookie : function(name, value, expiresHours) {
		var cookieString = name + "=" + escape(value); 
		//判断是否设置过期时间 
		if(expiresHours > 0){ 
			var date = new Date(); 
			date.setTime(date.getTime() + expiresHours * 3600 * 1000); 
			cookieString = cookieString + "; expires=" + date.toGMTString(); 
		} 
		document.cookie = cookieString; 
	},
	getCookie : function(strName){
		var strCookie = document.cookie.split("; ");
		for (var i=0; i < strCookie.length; i++) {
			var strCrumb = strCookie[i].split("=");
			if (strName == strCrumb[0]) {
				return strCrumb[1] ? unescape(strCrumb[1]) : null;
			}
		}
		return null;
	},
	reFresh:function(){
		window.location = window.location.href;
	},
	bindDistrict:function(selector, fid, defaultval, tips, onChange){
		if(!tips) tips = '请选择';
		var optionhtml = '<option value="">'+tips+'</option>';
		$.ajax({
			url:'/?m=common&c=district&a=getoption&fid='+fid+'&selected='+defaultval,
			dataType:'html',
			success: function(html){
				if (html) {
					optionhtml+= html;
					$(selector).html(optionhtml); 
					if(onChange) {
						$(selector).on('change',function(){
							var idvalue = $(selector).find(":selected").attr("data-id"); 
							onChange(idvalue);
						}).change();
					}
				}
			}
		});
	}
}

var DSXUI = {
	success : function(content,callback,error){
		callback = callback||function(){}
		var div = error ? '<div class="ui-error">'+content+'</div>' : '<div class="ui-success">'+content+'</div>';
		var el = $('<div/>').addClass("ui-message-box").html(div).appendTo(window.top.document.body)
		.fadeIn('fast').center();
		setTimeout(function(){el.remove();callback();},1500);
	},
	error : function(content,callback){
		DSXUI.success(content,callback,1);
	},
	confirm : function(o,text,ok,cancel){
		text = text || '确定要做此项操作吗？';
		$('#ui-confirm-box').remove();
		var dlg = $('<div id="ui-confirm-box" class="ui-confirm-box">'+
		'<dl><dt class="txt">'+text+'</dt><dd><span class="button submit" tabindex="1">确定</span>'+
		'<span class="button cancel" tabindex="1">取消</span></dd></dl></div>').appendTo("body");
		var position = $(o).offset();
		var top = parseInt((position.top+$(o).outerHeight()));
		dlg.css({"top":top+"px","display":"none",'position':'absolute'})
		.on('mouseleave',function(){$(this).remove();}).fadeIn("fast");
		if(position.left<($(document).width()/2)){
			dlg.css({'left':position.left+'px'});
		}else{
			dlg.css({'left':parseInt(position.left-dlg.outerWidth()+30)+'px'});
		}
		dlg.find(".submit").one('click',function(){
			dlg.remove();
			if(ok) ok();
		});
		dlg.find(".cancel").click(function(){
			dlg.remove();
			if(cancel) cancel();
		});
	},
	showloading : function(text){
		text = text||'正在加载数据....';
		var overlayer = $("<div/>").addClass("ui-overlayer").css({'height':'100%','position':'fixed'})
		.appendTo(document.body);
		var loading = $('<div class="ui-loading-box"><span class="ico"></span>'+text+'</div>')
		.appendTo(window.top.document.body).center();
		this.close = function(){
			overlayer.remove();
			loading.remove();
		}
		return this;
	},
	
	showConfirm : function(settings){
		settings = $.extend({
			title:'删除确认',
			width:450,
			text:'确定要删除此项目吗？'
		},settings);
		var dlg = dialog('<div class="content-confirm">'+settings.text+'</div>',
		{
			title:settings.title,
			width:settings.width,
			showButtons:true,
			onConfirm:function(){
				dlg.close();
				if(settings.onConfirm) settings.onConfirm();
			},
			onCancel:function(){
				if(settings.onCancel) settings.onCancel();
			}
		});
		return dlg;
	},
	checkLogin : function(){
		var logged = false;
		$.ajax({
			url:'/?m=member&c=login&a=getstatus',
			async:false,
			dataType:"json",
			success: function(json){
				if(json.errno == 0) logged = true;
			}
		});
		if(!logged){
			var dlg = dialog({url:'/?m=member&c=login&a=ajaxlogin&inajax=1'},
			{
				title:'登录',
				width:600
			});
		}
		return logged;
	},
	//创建相册
	showCreateAlbum : function(callback){
		var dlg = dialog({url:'/?m=photo&c=album&a=create'},
		{
			title:'创建相册',
			width:'550',
			onReady:function(){
				$("#J-create-album-button").click(function(e) {
                    var form = $("#J-create-album-form");
					var title = form.find("input[name=title]").val();
					if(!/^[a-zA-Z0-9_\u4e00-\u9fa5]{1,20}$/.test(title)){
						DSXUI.error('相册名称输入错误');
						return false;
					}
					var password = form.find("input[name=password]").val();
					if(form.find("input[name=isopen]:checked").val() == '0'){
						if(!DSXUtil.IsPassword(password)){
							DSXUI.error('相册密码输入错误');
							return false;
						}
					}
					$(form).ajaxSubmit({
						dataType:'json',
						success:function(json){
							if(json.errno == 0){
								dlg.close();
								DSXUI.success('相册创建成功',function(){
									if(callback) callback(json.data);
								});								
							}else {
								DSXUI.error(json.error);
							}
						}
					});
                });
			}
		});
		return dlg;
	},
	
	//图片选择器
	showImagePickView : function(settings){
		var opts = $.extend({
			multi:false
		},settings);
		var dlg = dialog({url:'/?m=photo&c=jsapi&a=pickimage'},
		  {
			  title:'选择图片',
			  width:'750',
			  onReady:function(d){
				  var getImageData = function(){
					  var imagedata = [];
					  dlg.dialog.find("li.checked").each(function(i, li) {
						  var id = $(li).attr("data-id");
						  var thumb = $(li).attr("data-thumb");
						  var image = $(li).attr("data-image");
						  var thumburl = $(li).attr("data-thumburl");
						  var imageurl = $(li).attr("data-imageurl");
						  imagedata.push({id:id,thumb:thumb,image:image,thumburl:thumburl,imageurl:imageurl});
					  });
					  return imagedata;
				  }
				  
				  var bindEvent = function(){
					  if(opts.multi) {
						  dlg.dialog.find(".pickitem").click(function(e) {
							  $(this).toggleClass('checked');
						  });
					  }else {
						  dlg.dialog.find(".pickitem").click(function(e) {
							  $(this).addClass('checked').siblings().removeClass('checked');
						  });
					  }
					  dlg.dialog.find(".pickview-pages a").each(function(i,el){
						  var url = $(el).attr('href');
						  $(el).attr('href', 'javascript:;');
						  $(el).click(function(e) {
                              loadItems(url);
                          });
					  });
					  //dlg.dialog.find(".pickview-pages a").click(function(e){
					  //	  loadItems($(this).attr("href"));
					  //	  return false;
					  //});
				  }
				  
				  var loadItems = function(url,data){
					  if(!url) url = '/?m=photo&c=jsapi&a=pickimage&inajax=1';
					  if(!data) data = {};
					  $.ajax({
						  url:url,
						  data:data,
						  success: function(c){
							  dlg.dialog.find(".pickview-div").html(c);
							  bindEvent();
						  }
					  });
				  }
				  
				  bindEvent();
				  dlg.dialog.find(".pickview-button").click(function(e) {
					  var data = getImageData();
					  if(data.length == 0){
						  alert('请选择图片');
					  }else {
						  dlg.close();
						  if(opts.multi) {
							  if(opts.onPicked) opts.onPicked(data);
						  }else {
							  if(opts.onPicked) opts.onPicked(data[0]);
						  }
					  }
				  });
				  
				  dlg.dialog.find(".pickview-select-album").change(function(){
					  loadItems('',{albumid:$(this).val()});
				  });
				  
				  dlg.dialog.find(".pickview-create-album").click(function(e) {
					  DSXUI.showCreateAlbum(function(album){
						  dlg.dialog.find(".pickview-choose-album").prepend('<option value="'+album.albumid+'" selected="selected">'+album.title+'</option>');
					  });
				  });
				  
				  dlg.dialog.find(".pickview-input-file").change(function(){
					  var loading = null;
					  $("#pickview-upload-form").ajaxSubmit({
						  dataType:'json',
						  beforeSend:function(){
							  loading = DSXUI.showloading('正在上传图片...');
							  dlg.dialog.find(".pickview-upload").hide();
						  },
						  success:function(json){
							  if(json.errno == 0){
								  dlg.close();
								  loading.close();
								  if(opts.onPicked) {
									  if(opts.multi){
										var imagedata = [];
										imagedata.push(json.data);
										opts.onPicked(imagedata);
									  }else{
									  	opts.onPicked(json.data);
									  }
								  }
							  }else {
								  DSXUI.error(json.error);
							  }
						  }
					  });
				  });
			  }
		  }
	  	);
		return dlg;
	}
}