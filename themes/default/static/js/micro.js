$(function() {
	/*翻新项目切换*/
	$(".kitchen-click").hover(function(){
		$(".case-toilet").show();
		$(".case-kitchen").hide();
	});
	$(".toilet-click").hover(function(){
		$(".case-kitchen").show();
		$(".case-toilet").hide();
	});
	/*弹出表单*/
	// $(".item-btn").click(function(){
	// 	var type = $(this).siblings().html();
 // 		$(".pop_mask").show();
 // 		$(".pop_mask input[name='wzx_item']").val(type);
	// })
	// $(".pop-window").click(function(){
	// 	$(".pop_mask").hide();
	// })
	// $(".pop_close").click(function(){
	// 	$(".pop_mask").hide();
	// })
})
