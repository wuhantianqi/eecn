/**
 * Copy	Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */
window.KT = window.KT || { verison : "1.0a" };
window.Widget  = window.Widget || {};
(function(K, $){
Widget.Login = function(handler){
	handler = handler || function(){};
    window.__MINI_LOAD = false;
	Widget.Dialog.Load('/index.php?passport-minilogin.html',"快速登录", 650);
}
})(window.KT, window.jQuery);