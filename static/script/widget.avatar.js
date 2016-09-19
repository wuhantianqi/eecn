/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: widget.avatar.js 2034 2013-12-07 03:08:33Z langzhong $
 */
/**
 * Flash 掉用接口
 * as~use: ExternalInterface.call("Widget.Ticket.Method"); 
 */
window.TP = window.TP || {version:"1.0a"};
window.Widget = window.Widget || {};
(function(T, $){
var Avatar = Widget.Avatar = {};
Avatar.Option = {
	face : "/face/face.jpg",
	flash: "/static/flash/avatar.swf",
	uid : "0",
	width : 680,
	height : 450,
	psize : "300|300|180|180|80|80|32|32",
	upload : "/member-face.html",	
	auth : null,
	complete : null,
	progress : null,
	language : null
};

Avatar.Language = {
	EX0001: "你尚未登录或登录已过期，请重新登录。",
	EX0002: "系统繁忙，请稍后再试。",
	EX0003: "参数错误",
	EX0004: "服务器度假中，请隔天再试 ",
	EX0005: "含有非法字符，请修改",
	EX0006: "很抱歉，此功能正在维护中，暂时无法提供。",
	EX0007: "请上传jpg、png、gif格式的图片。",
	EX0008: "上传失败，请重新上传。",
	EX0009: "请选择不超过2M的图片",
	EX0010: "保存失败，请重试。",
	EX0011: "请上传文件大小不超过5M的图片。",

	CX0001: "选择照片",
	CX0002: "正在加载现有的头像...",
	CX0003: "正在读取中，请稍候...",
	CX0004: "你的图片文件超出5M或宽高超出2880像素，请选择文件和尺寸较小的图片",
	CX0005: "取消",
	CX0006: "浏览...",
	CX0008: ["您上传的头像会自动生成三种尺寸，", "\n", "请注意中小尺寸的头像是否清晰"].join(""),
	CX0009: "大尺寸头像，180×180像素",
	CX0010: ["中尺寸头像", "\n", "80×80像素", "\n", "(自动生成)"].join(""),
	CX0011: ["小尺寸头像", "\n", "32×32像素", "\n", "(自动生成)"].join(""),
	CX0012: "仅支持JPG、GIF、PNG图片文件，且文件小于5M",
	CX0013: "向右旋转",
	CX0014: "向左旋转",
	
	CX1001: "请等待图片上传",
	CX1002: "请选择图片",
	CX1003: "文件格式不正确，请选择JPG、PNG或GIF图片格式",
	CX1004: "请等待图片上传",
	CX1005: "保存成功",

	CX2001: "你尚未登录或登录已过期，请重新登录。",
	CX2002: "系统繁忙，请稍后再试。",
	CX2003: "参数错误",
	CX2004: "服务器度假中，请隔天再试 ",
	CX2005: "含有非法字符，请修改",
	CX2006: "很抱歉，此功能正在维护中，暂时无法提供。",
	CX2007: "请上传jpg、png、gif格式的图片。",
	CX2008: "上传失败，请重新上传。",
	CX2009: "请选择不超过2M的图片",
	CX2010: "保存失败，请重试。",
	CX2011: "请上传文件大小不超过5M的图片。"
};

Avatar.requestLang = function(k){
	if(typeof(k) == 'undefined'){
		return Avatar.Language;
	}
	return Avatar.Language[k];
};
//取到当前登录用户Auth信息
Avatar.requestAuth = function(){
	
};

Avatar.handlerComplete = function(ret,handler){	
	if(ret.error == 101){
		Widget.MsgBox.error(ret.message.join(","));
		//Widget.Login(function(){window.location.reload();});
	}else if(ret.error){
		Widget.MsgBox.error(ret.message.join(","));
	}else{
		Widget.MsgBox.success("更新头像成功");
		window.location.reload(true);
	}
};
Avatar.handlerProgress = function(bytesLoaded,bytesTotal){
	Widget.MsgBox.load("正在上传"+bytesLoaded+"/"+bytesTotal)
	//console.log(bytesLoaded,bytesTotal);
};
Avatar.handlerError = function(code){
	Widget.MsgBox.error(Avatar.requestLang(code));
};
Avatar.Member = function(opt){
	opt = $.extend(Avatar.Option,opt||{});
};
//构建avatar swfhtml代码
Avatar.buildHtml = function(opt){
	return ['<object id="__AVATAR__" type="application/x-shockwave-flash" width="'+opt.width+'" height="'+opt.height+'" data="'+opt.flash+'">',
			'<param name="movie" value="'+opt.flash+'">',
			'<param name="quality" value="high">',
			'<param name="wmode" value="transparent">',
			'<param name="allowScriptAccess" value="always" />',
			'<param name="menu" value="false" />',
			'<param name="flashvars" value="'+Avatar._flashVars(opt)+'" />',
			'</object>'].join("");
};

Avatar._flashVars = function(opt){
	var params = '';
	params = "upload="+opt.upload+"&amp;itemId="+opt.uid;
	if(opt.face){
		params += "&amp;face="+opt.face;
	}
	if(opt.psize){
		params += "&amp;pSize="+opt.psize;
	}
	return params;
};

$.fn.Avatar = function(opt){
	opt = opt || {};
	opt = $.extend(Avatar.Option,opt);
	$(this).html(Widget.Avatar.buildHtml(opt));
};
})(window.KT, window.jQuery);