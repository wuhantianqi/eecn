/**
 定义基本常量
*/
/***/
(function(){
	//banner
	function banner(elm,xwh,isAuto, delayTime){
		var $banner = $(elm);
		var screenWidth = $banner.width();		
		var count = $banner.find('li').size();
		$banner.find("ul").width(screenWidth*count);
		$banner.find("ul").height(screenWidth*xwh);
		$banner.height(screenWidth*xwh);
		$banner.find('li').width(screenWidth).height(screenWidth*xwh);
		$banner.find('li img').width(screenWidth+10).height(screenWidth*xwh);		
		var flipsnap = Flipsnap(elm+" ul");
		flipsnap.element.addEventListener('fstouchend', function(ev) {
			$('.identify em').eq(ev.newPoint).addClass('cur').siblings().removeClass('cur');
		}, false);
		$('.identify em').eq(0).addClass('cur')
		if(isAuto){
			var point = 1;
			setInterval(function(){
				//console.log(point);
				flipsnap.moveToPoint(point);
				$('.identify em').eq(point).addClass('cur').siblings().removeClass('cur');
				if(point+1==$banner.find('li').size()){
					point=0;
				}else{
					point++;
				}				
			},delayTime)
		}
	}
	function squarePicSlide(isAuto,delayTime,width,height,prevBtn,nextBtn){
		var count = $('.banner li').size();
		$('.banner ul').width(width*count);
		$('.banner ul').height(height);
		$('.banner').height(height);
		$('.banner li').width(width).height(height);
		$('.banner li img').width(width).css('min-height',height);
		$('.banner li .title').css({'width':'98%','padding-left':'2%'})
		var flipsnap = Flipsnap('.banner ul');
		flipsnap.element.addEventListener('fstouchend', function(ev) {
			$('.identify em').eq(ev.newPoint).addClass('cur').siblings().removeClass('cur');
		}, false);
		$('.identify em').eq(0).addClass('cur');
		var point = 0;
		if(isAuto){
			
			setInterval(function(){
				//console.log(point);
				flipsnap.moveToPoint(point);
				},delayTime)
		}
		flipsnap.element.addEventListener('fstouchend', function(ev) {
			point = ev.newPoint;
			$('.identify em').eq(point).addClass('cur').siblings().removeClass('cur');
		}, false);
		$(prevBtn).click(function(){
			 if(flipsnap.hasPrev()){
				flipsnap.toPrev();
				point = point-1;
			 }else{
				flipsnap.moveToPoint(count-1);
				point = count-1;
				}
			$('.identify em').eq(point).addClass('cur').siblings().removeClass('cur');
			});
		$(nextBtn).click(function(){
			 if(flipsnap.hasNext()){
				flipsnap.toNext();
				point = point+1;
			 }else{
				flipsnap.moveToPoint(0);
				point = 0;
				}
			$('.identify em').eq(point).addClass('cur').siblings().removeClass('cur');
			
			});
	}
	$.extend({
		KT: {initBanner:banner, squarePicSlide:squarePicSlide}
	});
})();