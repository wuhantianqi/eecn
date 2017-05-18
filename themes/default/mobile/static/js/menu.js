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
//  var offset = $('.sub_choose_menu').offset();
//  $(window).scroll(function () {
//  //检查对象的顶部是否在游览器可见的范围内
//      var scrollTop = $(window).scrollTop();
//          if (offset.top < scrollTop){
//              $('.sub_choose_menu').hide();
//          }
//          else{
//              $('.sub_choose_menu').removeClass('fixed');
//          } 
//      });

	// 针对输入面积进行判断
	$(".myend p").css('font-size','16px');
	var nubuer = $(".com_form_intx input");   //面积
	var fphone = $(".comphone input");    //电话
	nubuer.blur(function () {
		if($(this).val() < 20){
			layer.open({
			    content: '建筑面积不能小于5'
			    ,btn: '我知道了'
			  });
			nubbul = false;
		}
	})

    //根据面积显示户型
    $('.com_form_intx input').on('keyup', function(){
        selectDoorModle($(this).val(), this);
    })
	$(".com_form_intx input").focus(function () {
		$(".jg").css("display","none");
		$(".com_form_intx").css("border-color","#e1e1e1");
		if($(".myend p").className = 'ondp'){
			$(".myend p").removeClass('ondp');
			$(".myend h2 em").html('0');
			$("#rengongf span").html('0');
			$("#cailiaof span").html('0');
			$("#shejif span").html('0').css('text-decoration','none');
			$("#zhijianf span").html('0').css('text-decoration','none');

		}
	})

    //根据面积显示户型
    function selectDoorModle(square, squareEle){
        var square = Number(square);
        if (square + '' == 'NaN' || $(squareEle).val() == '') {
            return
        };
        if (square < 60) {
			$('#shi').val(1);
            $('#ting').val(1);
            $('#chu').val(1);
            $('#wei').val(1);
            $('#yangtai').val(1);
        } else if (square >= 60 && square < 90) {
            $('#shi').val(2);
            $('#ting').val(1);
            $('#chu').val(1);
            $('#wei').val(1);
            $('#yangtai').val(1);
        } else if ( square >= 90 && square < 150) {
            $('#shi').val(3);
            $('#ting').val(2);
            $('#chu').val(1);
            $('#wei').val(2);
            $('#yangtai').val(1);
        }
        else if (square >= 150) {
            $('#shi').val(4);
            $('#ting').val(2);
            $('#chu').val(1);
            $('#wei').val(2);
            $('#yangtai').val(2);
        }
    }
    /*计算最终结果*/
	$("input[type='button']").click(function () {
		if( isNaN(nubuer.val()) || nubuer.val() == null || nubuer.val() == '' ){
			layer.open({
			    content: '您的输入正确的面积'
			    ,btn: '我知道了'
			  });
			return false;
		}
		var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;    //手机正则表达式
		$(".comphone input").focus(function(){
			$(".jg").css("display","none");
		})
		if( fphone.val() == '' || fphone.val() == null ){
			layer.open({
			    content: '手机号不能为空'
			    ,btn: '我知道了'
			  });
			return false;
	
		}else if(!myreg.test(fphone.val())){
			layer.open({
			    content: '请添写正确的11位手机号码'
			    ,btn: '我知道了'
			  });
			return false;

		}	
		var squre = $('.com_form_intx input').val()*500;
		if(squre){
			$(".myend h2 em").html(squre).css('color','#ff5a00');
			$("#rengongf span").html(squre*0.45).css('color','#ff5a00');
			$("#cailiaof span").html(Math.floor(squre*0.55)).css('color','#ff5a00');
			$("#shejif span").html(Math.round(squre*0.226666)).css('text-decoration','line-through');
			$("#zhijianf span").html(Math.round(squre*0.063333)).css('text-decoration','line-through');
			$(".myend p").addClass('ondp');
		}
		
		$.post("/index.php?tenders-save.html", $(this).parents('form').serialize(), function(data, textStatus, xhr) {
			if(data.error == 0 ){
				layer.open({
				    content: data.message[0]
				    ,btn: '我知道了'
				  });
			}else{
				layer.open({
				    content: data.message[0]
				    ,btn: '我知道了'
				  });
			}
		},'json');
		
	})

});

