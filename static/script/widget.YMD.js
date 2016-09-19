/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: widget.YMD.js 2034 2013-12-07 03:08:33Z langzhong $
 */
window.KT = window.KT || { verison : "1.0a" };
window.Widget  = window.Widget || {};
(function(K, $){
var YMD = Widget.YMD = Widget.YMD || {};
YMD.init = function(elm){
	var $YMD = $(elm);
	var $Y = $YMD.find("select[Y]"),$M = $YMD.find("select[M]"),$D = $YMD.find("select[D]");
	$Y.val($Y.attr("Y"));$M.val($M.attr("M"));$D.val($D.attr("D"));
	$YMD.find("select[Y],select[M]").change(function(){
		var Y = $Y.val(),M = $M.val(),D = $D.val();
		var MD = YMD.MaxDay(Y, M);
		var dd = [];
		for(var i=1; i<=MD; i++){
			if(i == D){
				dd.push("<option value='"+i+"' selected='selected'>"+i+"</option>");
			}else{
				dd.push("<option value='"+i+"'>"+i+"</option>");
			}
		}
		$D.empty().append(dd.join(""));
	});		
};
YMD.MaxDay = function(Y, M){return (new Date(Y, M, 0)).getDate();};
})(window.KT, window.jQuery);