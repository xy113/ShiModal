// JavaScript Document
/**
 * jQuery的Dialog插件。
 *
 * @param object content
 * @param object options 选项。
 * @return 
 */
function Dialog(content, options)
{
    var defaults = { // 默认值。 
        title:'WINDOW',       // 标题文本，若不想显示title请通过CSS设置其display为none 
        showTitle:true,     // 是否显示标题栏。
		showButtons:false,	// 是否显示底部按钮
        draggable:true,     // 是否移动 
        modal:true,         // 是否是模态对话框 
        center:true,        // 是否居中。 
        fixed:true,         // 是否跟随页面滚动。
        time:0,             // 自动关闭时间，为0表示不会自动关闭。 
        id:false,            // 对话框的id，若为false，则由系统自动产生一个唯一id。 
		width:500,
		height:0
    };
    var options = $.extend(defaults, options);
    options.id = options.id ? options.id : 'dialog-' + Dialog.__count; // 唯一ID
	
	this.Id = options.id;
    this.overlayId = options.id + '-overlay'; // 遮罩层ID
    this.timeId = null;  // 自动关闭计时器 
    this.isShow = false;
    this.isIe = $.browser.msie;
	var that = this;
    /* 对话框的布局及标题内容。*/
	var headHtml = !options.showTitle ? '' : '<div class="dialog-title"><strong>'+options.title+'</strong><span class="icon close">&#xf00b3;</span></div>';
	var footerHtml = !options.showButtons ? '' : '<div class="dialog-footer"><div class="ui-button onconfirm" tabindex="1">确定</div><div class="ui-button oncancel" tabindex="1">取消</div></div>';
	this.dialog = $('<div id="' + options.id + '" class="ui-dialog">'+headHtml+'<div class="dialog-content"></div>'+footerHtml+'</div>').hide();
	this.overLayer = $('<div id="' + this.overlayId + '" class="ui-overlayer"></div>');
	
    /**
     * 重置对话框的位置。
     *
     * 主要是在需要居中的时候，每次加载完内容，都要重新定位
     *
     * @return void
     */
    var resetPos = function(){
        /* 是否需要居中定位，必需在已经知道了dialog元素大小的情况下，才能正确居中，也就是要先设置dialog的内容。 */
        if(options.center){
            var left = ($(window).width() - that.dialog.outerWidth()) / 2;
            var top = ($(window).height() - that.dialog.outerHeight()) / 2;
			if(options.fixed){
				that.dialog.css({top:top,left:left});
			}else{
				that.dialog.css({top:top+$(document).scrollTop(),left:left+$(document).scrollLeft()});
			}
        }
    }
    /**
     * 初始化位置及一些事件函数。
     *
     * 其中的this表示Dialog对象而不是init函数。
     */
    var init = function()
    {
        /* 是否需要初始化背景遮罩层 */
        if(options.modal){
            $(document.body).append(that.overLayer);
            that.overLayer.css({'left':0, 'top':0,
                    'width':'100%',
                    'height':'100%',
                    'z-index':++Dialog.__zindex,
                    'position':'fixed'})
                .hide();
        }
		$(document.body).append(this.dialog);
        that.dialog.css({'z-index':++Dialog.__zindex,width:options.width+'px', 'position':options.fixed ? 'fixed' : 'absolute'});
		if(options.height) that.dialog.find(".dialog-content").height(options.height);
		
        /* 以下代码处理框体是否可以移动 */
        var mouse={x:0,y:0};
        function moveDialog(event)
        {
            var e = window.event || event;
            var top = parseInt(that.dialog.css('top')) + (e.clientY - mouse.y);
            var left = parseInt(that.dialog.css('left')) + (e.clientX - mouse.x);
            that.dialog.css({top:top,left:left});
            mouse.x = e.clientX;
            mouse.y = e.clientY;
        };
        that.dialog.find('.dialog-title').mousedown(function(event){
            if(!options.draggable){ return; }
            var e = window.event || event;
            mouse.x = e.clientX;
            mouse.y = e.clientY;
            $(document).bind('mousemove',moveDialog);
			$(this).css('cursor','move');
        });
        $(document).mouseup(function(event){
            $(document).unbind('mousemove', moveDialog);
			that.dialog.find('[node=bar]').css('cursor','default');
        });
        /* 绑定一些相关事件。 */
        that.dialog.find('.close').on('click', that.close);
		that.dialog.find('.oncancel').on('click', function(){
			that.close();
			if(options.onCancel) options.onCancel();
		});
		that.dialog.find('.onconfirm').on('click', function(){
			if(options.onConfirm) options.onConfirm();
		});
        that.dialog.on('mousedown', function(){
			$(this).css('z-index', ++Dialog.__zindex);
		});
        // 自动关闭 
        if(0 != options.time){that.timeId = setTimeout(this.close, options.time);}
    }
    /**
     * 设置对话框的内容。 
     *
     * @param string c 可以是HTML文本。
     * @return void
     */
    this.setContent = function(c){
		if (typeof c === 'object'){
			if(c.url) {
				$.ajax({
					url:c.url,
					dataType:"html",
					success: function(html){
						that.dialog.find('.dialog-content').html(html);
						resetPos();
						if(undefined != options.onReady){options.onReady(that);}
					}
				});
			}else if(c.frame) {
				that.dialog.find('.dialog-content').html('<iframe frameborder="0" style="width:100%; height:100%; display:block;" scrolling="no" src="'+c.frame+'"></iframe>');
			}else if(c.selector){
				that.dialog.find('.dialog-content').html($(c.selector).html());
			}
		}else {
			that.dialog.find('.dialog-content').html(c);
		}
	}
    /**
     * 显示对话框
     */
    this.show = function()
    {
        if(undefined != options.beforeShow){options.beforeShow();}
        /* 是否显示背景遮罩层 */
        if(options.modal) that.overLayer.show();
        that.dialog.show(0, function(){
            if(undefined != options.afterShow){options.afterShow(that);}
            that.isShow = true;
        });
        // 自动关闭 
        if(0 != options.time){that.timeId = setTimeout(this.close, options.time);}
        resetPos();
    }
    /**
     * 隐藏对话框。但并不取消窗口内容。
     */
    this.hide = function()
    {
        if(!that.isShow){return;}
        if(undefined != options.beforeHide){options.beforeHide();}
        that.dialog.hide(0,function(){
            if(undefined != options.afterHide){options.afterHide();}
        });
        if(options.modal){
			that.overLayer.hide();
		}
        that.isShow = false;
    }
    /**
     * 关闭对话框 
     *
     * @return void
     */
    this.close = function()
    {
		that.isShow = false;
        if(undefined != options.beforeClose){options.beforeClose();}
		that.dialog.remove();
		that.overLayer.remove();
        clearTimeout(that.timeId);
		if(undefined != options.afterClose){options.afterClose();}
    }
    init.call(this);
    this.setContent(content);
    Dialog.__count++;
    Dialog.__zindex++;
}
Dialog.__zindex = 1000;
Dialog.__count = 1;
function dialog(content,options)
{
	var dlg = new Dialog(content, options);
	dlg.show();
	return dlg;
}

// JavaScript Document
;(function($){
$.fn.Jscroll = function(settings){
	settings = $.extend({
		speed : 3000,
		animateSpeed:1000,
		direction : 'left',
		width:300,
		height:300,
		arrowLeft:'',
		arrowRight:''
	},settings);
	var that = this;
	var ul = $(this).find("ul");
	$(this).css({'overflow':'hidden','height':settings.height});
	if(settings.direction == 'left' || settings.direction == 'right') ul.css({'width':1000,'height':settings.height});
	that.t = setInterval(function(){that.AutoPlay();},settings.speed);
	that.scrollLeft = function(){
		ul.animate({marginLeft:-settings.width+"px"},settings.animateSpeed,function(){
			//把第一个li丢最后面去
			ul.css({marginLeft:0}).find("li:first").appendTo(ul);
		});
	}
	that.scrollRight = function(){
		ul.css({marginLeft:-settings.width+"px"}).find("li:last").prependTo(ul);
		ul.animate({marginLeft:0},settings.animateSpeed,function(){
			//把第一个li丢最后面去
		});
	}
	that.scrollUp = function(){
		ul.animate({marginTop:-settings.height+"px"},settings.animateSpeed,function(){
			//把第一个li丢最后面去
			ul.css({marginTop:0}).find("li:first").appendTo(ul);
		});
	}
	that.scrollDown = function(){
		ul.css({marginTop:-settings.height+"px"}).find("li:last").prependTo(ul);
		ul.animate({marginTop:0},settings.animateSpeed,function(){
			//把第一个li丢最后面去
		});
	}
	that.AutoPlay = function(){
		if(settings.direction == 'right'){
			that.scrollRight();
		}else if(settings.direction == 'up'){
			that.scrollUp();
		}else if(settings.direction == 'down'){
			that.scrollDown();
		}else{
			that.scrollLeft();
		}
	}
	$(this).hover(function(){
		clearTimeout(that.t);
	},
	function(){
		that.t = setInterval(function(){that.AutoPlay();},settings.speed);
	});
	$(this).find(settings.arrowLeft).bind('click',that.scrollRight);
	$(this).find(settings.arrowRight).bind('click',that.scrollLeft);
}
})(jQuery);

/*
 * jQuery JavaScript Library Marquee Plus 1.0
 * http://mzoe.com/
 *
 * Copyright (c) 2009 MZOE
 * Dual licensed under the MIT and GPL licenses.
 *
 * Date: 2009-05-13 18:54:21
 */
;(function($) {
	$.fn.marquee = function(o) {
		//获取滚动内容内各元素相关信息
		o = $.extend({
			speed:30, // 滚动速度
			step:1, // 滚动步长
			direction:'left', // 滚动方向
			pause: 0, // 停顿时长
			container:'ul',
			items : 'li'
		}, o || {});
		var dIndex = $.inArray(o.direction, ['right', 'down']);
		if (dIndex > -1) {
			o.direction = ['left', 'up'][dIndex];
			o.step = -o.step;
		}
		var mid;
		var div 		= $(this); // 容器对象
		var divWidth 	= div.innerWidth(); // 容器宽
		var divHeight 	= div.innerHeight(); // 容器高
		var ul 			= div.find(o.container);
		var li 			= ul.find(o.items);
		var liSize 		= li.size(); // 初始元素个数
		var liWidth 	= li.width(); // 元素宽
		var liHeight 	= li.height(); // 元素高
		var width 		= liWidth * liSize;
		var height 		= liHeight * liSize;
		div.height(liHeight);
		if ((o.direction == 'left' && width > divWidth) || 
			(o.direction == 'up' && height > divHeight)) {
			// 元素超出可显示范围才滚动
			if (o.direction == 'left') {
				ul.width(2 * liSize * liWidth);
				if (o.step < 0) div.scrollLeft(width);
			} else {
				ul.height(2 * liSize * liHeight);
				if (o.step < 0) div.scrollTop(height);
			}
			ul.append(li.clone()); // 复制元素
			mid = setInterval(_marquee, o.speed);
			div.hover(
				function(){clearInterval(mid);},
				function(){mid = setInterval(_marquee, o.speed);}
			);
		}
		function _marquee() {
			// 滚动
			if (o.direction == 'left') {
				var l = div.scrollLeft();
				if (o.step < 0) {
					div.scrollLeft((l <= 0 ? width : l) + o.step);
				} else {
					div.scrollLeft((l >= width ? 0 : l) + o.step);
				}
				if (l % liWidth == 0) _pause();
			} else {
				var t = div.scrollTop();
				if (o.step < 0) {
					div.scrollTop((t <= 0 ? height : t) + o.step);
				} else {
					div.scrollTop((t >= height ? 0 : t) + o.step);
				}
				if (t % liHeight == 0) _pause();
			}
		}
		function _pause() {
			// 停顿
			if (o.pause > 0) {
				var tempStep = o.step;
				o.step = 0;
				setTimeout(function() {
					o.step = tempStep;
				}, o.pause);
			}
		}
	};
})(jQuery);

;(function($){
	$.fn.extend({
		showPrompt:function(){
			var that = this;			
			var tips  = $('<div class="ui-tips-box"></div>');
			var arrow = $('<div class="ui-tips-arrow"></div>');
			this.show = function(){				
				$("body").append(tips);
				$("body").append(arrow);
				tips.empty().text($(this).attr('prompt'));
				var offset = $(this).offset();
				var left = offset.left + $(that).outerWidth()/2;
				arrow.css({top:offset.top-14, left:left-8});
				
				var tipsLeft = offset.left - (tips.outerWidth() - $(this).outerWidth())/2;
				tips.css({top:offset.top-$(tips).outerHeight()-8, left:tipsLeft});
			}
			this.hide = function(){
				tips.remove();
				arrow.remove();
			}
			
			$(this).hover(this.show,this.hide);
		},
		setImage : function(success,error){
			var that = this;
			var el = null;
			var div = $("<div/>").attr("id","ui-setimage-dialog").height(0);
			var form = $('<form id="ui-setimage-form" enctype="multipart/form-data" method="POST" action="/?m=common&c=photo&a=uploadimage"></form>');
            var file = $('<input type="file" name="filedata" title="点击上传图片">').css({'opacity':'0','position':'absolute','z-index':'500','cursor':'pointer'});
			
			form.append(file);
			div.append(form);
			$(file).change(function(e) {
				var loading;
                form.ajaxSubmit({
					dataType:'json',
					beforeSend:function(){
						loading = DSXUI.showloading('正在上传图片');
					},
					success:function(json){
						loading.close();
						if(json.errno == 0){
							$(el).attr('src',json.data.imageurl);
							if(success) success(el,json.data);
						}else {
							DSXUI.error('上传失败');
							if(error) error(json);
						}
					}
				});
            });
			$(this).each(function(index, element) {
                $(element).mouseover(function(){
					el = element;
					var offset = $(this).offset();
					file.css({'left':offset.left,'top':offset.top,'width':$(this).width(),'height':$(this).height()});
					$(document.body).append(div);
				});
            });
		},
		pickImage : function(settings){
			var opts = $.extend({
				multi:false
			},settings);
			
			var current;
			var that = this;
			$(this).click(function(e) {
                current = this;
				DSXUI.showImagePickView({
					multi:opts.multi,
					onPicked:function(data){
						if(opts.onPicked) opts.onPicked(current, data);
					}
				});
            });
		}
	});
})(jQuery);