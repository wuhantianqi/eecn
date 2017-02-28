var site = {
	base: "",
	url: "http://www.bao315.com",
	locale: "zh_CN"
};
// 添加Cookie
function addCookie(name, value, options) {
	if (arguments.length > 1 && name != null) {
		if (options == null) {
			options = {};
		}
		if (value == null) {
			options.expires = -1;
		}
		if (typeof options.expires == "number") {
			var time = options.expires;
			var expires = options.expires = new Date();
			expires.setTime(expires.getTime() + time * 1000);
		}
		document.cookie = encodeURIComponent(String(name)) + "=" + encodeURIComponent(String(value)) + (options.expires ? "; expires=" + options.expires.toUTCString() : "") + (options.path ? "; path=" + options.path : "") + (options.domain ? "; domain=" + options.domain : ""), (options.secure ? "; secure" : "");
	}
}

// 获取Cookie
function getCookie(name) {
	if (name != null) {
		var value = new RegExp("(?:^|; )" + encodeURIComponent(String(name)) + "=([^;]*)").exec(document.cookie);
		return value ? decodeURIComponent(value[1]) : null;
	}
}

// 移除Cookie
function removeCookie(name, options) {
	addCookie(name, null, options);
}
//字符串缩略 
//str原字符串 width 宽度 ellipsis 省略符 return 缩略字符
function abbreviate(str,width,ellipsis){
 if (str!=null && width != null) {
		var strLength = 0;
		var reg=/^[\u4E00-\u9FA5]+$/;  
		for (var strWidth = 0; strLength < str.length; strLength++) {
         if(reg.test(str.charAt(strLength))){ 
           strWidth=strWidth+2;
         }else{
           strWidth=strWidth+1;
         } 
			if (strWidth >= width) {
				break;
			}
		}
		if (strLength < str.length) {
			if (ellipsis != null) {
				return str.substring(0, strLength + 1) + ellipsis;
			} else {
				return str.substring(0, strLength + 1);
			}
		} else {
			return str;
		}
	} else {
		if (ellipsis != null) {
			return str + ellipsis;
		} else {
			return str;
		}
	}
}
(function($) {
	//简单弹窗  
	$.myBox = {
	        /**
	         * 警告窗
	         * @params title 标题
	         * @params msg 正文
	         * */
	        alert: function (title, msg) {
	        	mask();
	        	generateHtml("alert", title, msg);
	        	okBtnEvent();
	        	cancelBtnEvent();
	        },
	        /**
	         * 确认框
	         * @params title 标题
	         * @params msg 正文
	         * @params callback 回调函数
	         * */
	        confirm: function (title, msg, callback) {
	        	mask()
	        	generateHtml("confirm", title, msg);
	        	okBtnEvent(callback);
	            cancelBtnEvent();
	        },
	        /**
	         * 显示框
	         * @params flag 标识 success,error
	         * @params title 标题
	         * @params msg 正文
	         * @params time 消失时间 单位毫秒
	         * @params callback 回调函数
	         * */
	        show: function(flag,title,msg,time,callback){
	        	mask();
	        	generateHtml2(flag ,title, msg,time);
	        	touchEvent(time,callback);
	        	countdown(time);
	        }
	    }
	
    //确定按钮事件
    var okBtnEvent = function (callback) {
        $("#_warn_btn_yes").click(function () {
        	$("#_warning_box").remove();
            if (typeof (callback) == 'function') {
                callback();
            }
            unmask();
        });
        $("#_ico_warnning_close").click(function () {
        	$("#_warning_box").remove();
            unmask();
        });
    }
    //取消按钮事件
    var cancelBtnEvent = function () {
        $("#_warn_btn_no,#_ico_warnning_close").click(function () {
            $("#_warning_box").remove();
            unmask();
        });
    }
    var touchEvent = function(time , callback){
		//设置延迟关闭
    	var timeOut=setTimeout(function(){
			$("#_warning_box_mask").click();
		}, time);
    	$("#_warning_box_mask,#_tooltip_box").click(function(){
    		 $("#_tooltip_box").remove();
    		 clearTimeout(timeOut);//关闭显示框时，强行关闭延迟设定
    		 clearTimeout(timeDown);//关闭显示框时，强行关闭倒计时显示延迟事件的延迟设定
    		 unmask();
    		 $('#_warning_box_mask,#_tooltip_box').unbind('click');
    		 
    		 if (typeof (callback) == 'function') {
                 callback();
             }
    	});
    }
    //用于存放显示框倒计时延迟事件
    var timeDown;
    //显示框时间倒计时显示
    var countdown=function(time){
    	if(time>=1000){
    		time=time-1000;
    		timeDown=setTimeout(function(){
    		$("#time").html('(本窗口'+time/1000+'秒后自动关闭)');
    		countdown(time);//设置一秒延迟后再次调用本体
    	}, 1000);
    	}else{
    		return;
    	}
    }
    /**
     * @params type 类型
     * @params title 标题
     * @params msg 正文
     * */
    var generateHtml = function(type, title, msg){
    	var html = "";
    	html += '<div id="_warning_box" class="warning_box">';
    	html += '<h2><span>'+ title +'</span><i class="ico_warnning_close" id="_ico_warnning_close" title="关闭"></i></h2>';
    	html += '<p class="warn_text">'+ msg +'</p>';
    	html += '<p class="warn_btn">';
		html += '<input name="btn_warn" class="warn_btn_yes" id="_warn_btn_yes" type="button" value="确定" />';
		if(type == 'confirm'){
			html += '<input name="btn_warn" class="warn_btn_no" id="_warn_btn_no" type="button" value="取消" />';
		}
	    html += '</p>';
	    html += '</div>';
	    $("body").append(html);
	    $('#_warning_box').show();
    }
    
    var generateHtml2 = function(flag ,title, msg,time){
    	var html = "";
        html += '<div class="operation" id="_tooltip_box">';
        html += '<h2><span>'+ title +'</span></h2>';
        html += '<p class="operation_text">';
        if(flag=='success'){
        	html += '<span class="ico_success">';
        }
        if(flag=='error'){
        	html += '<span class="ico_failure">';
        }
        html +=	'<i></i>'+ msg +'</span><br><br>';
        html += '<span id="time">'+'(本窗口'+time/1000+'秒后自动关闭)'+'</span>';
        html += '</p>';
        html += '</div>';
	    $("body").append(html);
	    $('#_tooltip_box').show();
    }
    //遮罩层
    var mask = function(){
	    var docHeight = $(document).height(); //获取窗口高度  
	    $('body').append('<div id="_warning_box_mask"></div> ');  
	    $('#_warning_box_mask')  
	      .height(docHeight)  
	      .css({  
	        'opacity': .6, //透明度  
	        'position': 'absolute',  
	        'top': 0, 
	        'left': 0,
	        'background-color': 'black',  
	        'width': '100%',  
	        'z-index': 100 //保证这个悬浮层位于其它内容之上  
	    });  
    }
    var unmask = function(){
    	$("#_warning_box_mask").remove();
    }
	// 检测登录
	$.checkLogin = function() {
		var result = false;
		$.ajax({
			url: site.base + "/login/check.html",
			type: "GET",
			dataType: "json",
			cache: false,
			async: false,
			success: function(data) {
				result = data;
			}
		});
		return result;
	}

	// 跳转登录
	$.redirectLogin = function (redirectUrl, message) {
		var href = site.base + "/login.html";
		if (redirectUrl != null) {
			href += "?redirectUrl=" + encodeURIComponent(redirectUrl);
		}
		if (message != null) {
			$.myBox.show("warn","提示",message,1000);
			setTimeout(function() {
				location.href = href;
			}, 1000);
		} else {
			location.href = href;
		}
	}
	$.addFavorite = function(favoriteId,markName,callback) {
		$.ajax({
			url:site.url + "/login/check_jsonp.html",
			type: "GET",
			dataType: "jsonp",
			cache: false,
			async: false,
			success: function(data) {
				if(data.result=="true"){
					if(favoriteId==null || favoriteId==undefined || markName==null || markName==undefined){
						return;
					}
					$.ajax({
						url: site.url +"/member/favorite/add.html",
						type: "GET",
						data:{"favorityId":favoriteId,"markName":markName},
						dataType: "jsonp",
						cache: false,
						async: false,
						success: function(data) {
							if(typeof(callback)=='function'){
								callback(data);
							}else{
								$.myBox.alert("提示",data.errmsg);
							}
						},
						error:function(XMLHttpRequest, textStatus, errorThrown){
						},
						complete:function(XMLHttpRequest, textStatus){
						}
					}); 
				}else{
					$.myBox.alert("提示","请先登录！");
				}
			},
			complete:function(XMLHttpRequest, textStatus){
			}
		});
	}
	$(document).ajaxComplete(function(event, request, settings) {
		var loginStatus = request.getResponseHeader("loginStatus");
		var tokenStatus = request.getResponseHeader("tokenStatus");
		
		if (loginStatus == "accessDenied") {
			$.redirectLogin(location.href, "请登录后再进行操作");
		} else if (tokenStatus == "accessDenied") {
			var token = getCookie("token");
			if (token != null) {
				$.extend(settings, {
					global: false,
					headers: {token: token}
				});
				$.ajax(settings);
			}
		}
	});
})(jQuery);	
//初始化(省、市)
function initProvinceCity(provinceElement,provinceKey,ctiyElement,cityKey,areaUrl){
	if($("#"+provinceElement).length>0 && $("#"+ctiyElement).length>0){
		//初始化省	
		initProvinceData(provinceElement,provinceKey,areaUrl);
		//初始化市
		initCityData(provinceKey,ctiyElement,cityKey,areaUrl);
		//初始化省选择事件
		initProvinceChangeEvent(provinceElement,ctiyElement,null,areaUrl);
	}
}
//初始化(省、市、区)
function initProvinceCityArea(provinceElement,provinceKey,ctiyElement,cityKey,areaElement,areaKey,areaUrl){
	if($("#"+provinceElement).length>0 && $("#"+ctiyElement).length>0 && $("#"+areaElement).length>0){
		//初始化省	
		initProvinceData(provinceElement,provinceKey,areaUrl)
		//初始化市
		initCityData(provinceKey,ctiyElement,cityKey,areaUrl);
		//初始化区
		if(cityKey!=null && cityKey!=undefined && cityKey>0){
			$.ajax({
				url: areaUrl,
				type: "GET",
				data: {parentId: cityKey},
				dataType: "json",
				cache: false,
				async: false,
				success: function(data) {
					if ($.isEmptyObject(data)) {
						return;
					}
					var optionHtml='<option value="">请选择...</option>';
					$.each(data, function(value, name) {
						if(value == areaKey) {
							optionHtml += '<option value="' + value + '" selected="selected">' + name + '</option>';
						} else {
							optionHtml += '<option value="' + value + '">' + name + '</option>';
						}
					});
					$("#"+areaElement).empty();
					$("#"+areaElement).html(optionHtml);
				}
			});
		}else{
			$("#"+areaElement).empty();
			$("#"+areaElement).html('<option value="">请选择...</option>');
		}
		//初始化省选择事件
		initProvinceChangeEvent(provinceElement,ctiyElement,areaElement,areaUrl);
		//初始化市选择事件
		$("#"+ctiyElement).change(function(){
			var ctiyId=$(this).val();
			if(ctiyId!=null && ctiyId!=undefined && ctiyId>0){
				$.ajax({
					url: areaUrl,
					type: "GET",
					data: {parentId: ctiyId},
					dataType: "json",
					cache: false,
					async: false,
					success: function(data) {
						if ($.isEmptyObject(data)) {
							return;
						}
						var optionHtml='<option value="">请选择...</option>';
						$.each(data, function(value, name) {
						    optionHtml += '<option value="' + value + '">' + name + '</option>';
						});
						$("#"+areaElement).empty();
						$("#"+areaElement).html(optionHtml);
					}
				});
			}else{
				$("#"+areaElement).empty();
				$("#"+areaElement).html('<option value="">请选择...</option>');
			}
		});
	}
}
//初始化省
function initProvinceData(provinceElement,provinceKey,areaUrl){
	$.ajax({
		url: areaUrl,
		type: "GET",
		data: null,
		dataType: "json",
		cache: false,
		async: false,
		success: function(data) {
			if ($.isEmptyObject(data)) {
				return;
			}
			var optionHtml='<option value="">请选择...</option>';
			$.each(data, function(value, name) {
				if(value == provinceKey) {
					optionHtml += '<option value="' + value + '" selected="selected">' + name + '</option>';
				} else {
					optionHtml += '<option value="' + value + '">' + name + '</option>';
				}
			});
			$("#"+provinceElement).empty();
			$("#"+provinceElement).html(optionHtml);
		}
	});
}
//初始化市
function initCityData(provinceKey,ctiyElement,cityKey,areaUrl){
	if(provinceKey!=null && provinceKey!=undefined && provinceKey>0){
		$.ajax({
			url: areaUrl,
			type: "GET",
			data: {parentId: provinceKey},
			dataType: "json",
			cache: false,
			async: false,
			success: function(data) {
				if ($.isEmptyObject(data)) {
					return;
				}
				var optionHtml='<option value="">请选择...</option>';
				$.each(data, function(value, name) {
					if(value == cityKey) {
						optionHtml += '<option value="' + value + '" selected="selected">' + name + '</option>';
					} else {
						optionHtml += '<option value="' + value + '">' + name + '</option>';
					}
				});
				$("#"+ctiyElement).empty();
				$("#"+ctiyElement).html(optionHtml);
			}
		});
	}else{
		$("#"+ctiyElement).empty();
		$("#"+ctiyElement).html('<option value="">请选择...</option>');
	}
}
function initProvinceChangeEvent(provinceElement,ctiyElement,areaElement,areaUrl){
	$("#"+provinceElement).change(function(){
		var provinceId=$(this).val();
		if(provinceId!=null && provinceId!=undefined && provinceId>0){
			$.ajax({
				url: areaUrl,
				type: "GET",
				data: {parentId: provinceId},
				dataType: "json",
				cache: false,
				async: false,
				success: function(data) {
					if ($.isEmptyObject(data)) {
						return;
					}
					var optionHtml='<option value="">请选择...</option>';
					$.each(data, function(value, name) {
					    optionHtml += '<option value="' + value + '">' + name + '</option>';
					});
					$("#"+ctiyElement).empty();
					$("#"+ctiyElement).html(optionHtml);
				}
			});
		}else{
			$("#"+ctiyElement).empty();
			$("#"+ctiyElement).html('<option value="">请选择...</option>');
		}
		if(areaElement!=null && areaElement!=undefined && $("#"+areaElement).length>0){
			$("#"+areaElement).empty();
			$("#"+areaElement).html('<option value="">请选择...</option>');
		}
	});
}



/*
*验证数字或者包含两位小数
*true:是数字数字或者包含两位小数；false:不是数字包含两位小数
*\d{1,2}：最少一位小数，最多两位小数
*/

function $isDecimal(num) {
    var telzz = /^-?\d+\.?\d{1,2}$/;
    if (telzz.test(num)) {
        return true;
    } else {
        return false;
    }
}

/*
*验证数字
*true:是数字；false:不是数字
*/
function $isNumber(num) {
    var telzz = /^\d+$/;
    if (telzz.test(num)) {
        return true;
    } else {
        return false;
    }
}
/*
 * 手机号码验证信息
 * true:符合要求；false:不符合要求
 */
function isMobil(s) {
    var numReg = /^((0\d{2,3})-)(\d{7,8})(-(\d{3,}))?|((\(\d{2,3}\))|(\d{3}\-))?(13|15|18|14|17)\d{9}$/g;
    if (!numReg.test(s)) {
        return false;
    }
    return true;
}
/*
 * 用户名规则验证信息
 * true:符合要求；false:不符合要求
 */
function isUsername(s) {
if(!isNaN(s)){
		 return false;
}
    var numReg =/^[\u4E00-\u9FA5\uf900-\ufa2d\w]{4,20}$/;
    if (!numReg.test(s)) {
        return false;
    }
    return true;
}
