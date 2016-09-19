/**
 * Copy	Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: widget.bmap.js 9926 2015-04-28 06:09:32Z youyi $
 */
window.KT = window.KT || { "verison" : "1.0a" };
window.Widget  = window.Widget || {};
(function(K, $){
	var BMap = Widget.BMap = Widget.BMap || {};
	BMap.Marker = function(point, handler){
		var option = {width:600,height:500,modal:true,dialogClass:'ui-hack-widget-dialog',position:{my: "center top",at: "center top+80px",of: window},
				buttons: {
					"确定选择": function() {
						 var point =  $("#widget-dialog-iframe-content").contents().find("#Baidu_Map_Marker").val().split(",");
						 if(point.lenght<2){
							alert("未选择坐标");return ;
						 }
						 handler({"lng":$.trim(point[0]),"lat":$.trim(point[1])});
						 $(this).dialog("destroy");
					}
				}
		};
		var link = "/static/baidu/marker.html";
		if(typeof(point) == 'object'){
			if(link.indexOf("?")>-1){
				link += "&lng="+point.lng+"&lat="+point.lat;
			}else{
				link += "?lng="+point.lng+"&lat="+point.lat;
			}
		}
		var opt = $.extend({},option);
		opt.title = "地图坐标拾取器";
		Widget.MsgBox.success("数据处理中...");
		Widget.MsgBox.load("数据努力加载中...");
		$('<div style="padding:0px;margin:0px;overflow:hidden;"><iframe id="widget-dialog-iframe-content" style="width:100%;height:100%;border:0px;padding:0px;margin:0px;" border=0/></div>').dialog($.extend({create:function(event,ui){
			$("#widget-dialog-iframe-content").attr("src", link);Widget.MsgBox.hide();},close:function(event,ui){
			$(this).dialog("destroy");
		}},opt));	
	};
})(window.KT, window.jQuery);