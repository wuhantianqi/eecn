// JavaScript Document
window.onload=function(){
	(function() {
    function setFontSize() {
        var fontSize=0;
        if (document.documentElement.clientWidth <= 750) {
            fontSize = document.documentElement.clientWidth / 15;
        } else {
            fontSize = 50;
        }
        document.documentElement.style.fontSize=fontSize+"px";
    };
    window.addEventListener("resize", function() {
        setFontSize();
    });
    setFontSize();
})();
}






























