$(document).ready(function(){
	//头部筛选列表
	$(".choose_menu li").click(function(){
		$(this).siblings().find('.sub_choose_menu').hide();
		$(this).find(".sub_choose_menu").slideToggle(300);
	});
	//搜索框下拉列表
	$('.search_box').click(function(){
		$(this).find('.search_choose').slideToggle(300);							  
    });
	
	 
    //检查对象，#boxs是要随滚动条固定的ID
    var offset = $('.sub_choose_menu').offset();
    $(window).scroll(function () {
    //检查对象的顶部是否在游览器可见的范围内
        var scrollTop = $(window).scrollTop();
            if (offset.top < scrollTop){
                $('.sub_choose_menu').hide();
            }
            else{
                $('.sub_choose_menu').removeClass('fixed');
            } 
        });
});

