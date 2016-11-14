// JavaScript Document
$(function(){
	(function(){				//表单切换
		var $tab=$(".dialog>.tab>span");
		var $diaLi=$(".dialog>ul>li");
		
		$tab.click(function(){
			var $index=$(this).index();
			$(this).siblings().removeClass('active');
			$(this).addClass('active');
			
			$diaLi.addClass('none');
			$diaLi.eq($index).removeClass('none');
		})
	})();
	
	
	jQuery.validator.addMethod("isMobile", function(value, element) {  //验证手机
	    var length = value.length;
	    var mobile = /^1{1}[34578]{1}\d{9}$/;  // && mobile.test(value)   , "请正确填写您的手机号码"
	    return this.optional(element) || (length == 11) && mobile.test(value);
	});
	
	
	$("#baojia-form").validate({
		rules:{
			square:{
				required:true,
				minlength:2,
				maxlength:4,
				
			},
			xiaoqu:{
				required:true,
			},
			phone:{
				required:true,
				isMobile:true
			}
			
		},
		messages:{
			square:{
				required:"请输入用户名",
				minlength:"房子太小了吧,亲",
				maxlength:"房子太大了吧，亲"
			},
			xiaoqu:"请输入小区名称",
			phone:{
				required:"请输入手机号码",
				isMobile:"请正确填写您的手机号码"
			}
		},
		onkeyup:false
	});


	
	
	$("#sheji-form").validate({
		rules:{
			name:{
				required:true,
			},
			phone:{
				required:true,
				isMobile:true
			},
			xiaoqu:{
				required:true,
			}
		},
		messages:{
			name:"请输入用户名",
			phone:{
				required:"请输入手机号码",
				isMobile:"请正确填写您的手机号码"
			},
			xiaoqu:"请输入小区名称",
		},
		onkeyup:false
	});
	
	$('#new_base_info').click(function(){
    	if ($("#baojia-form").valid()) {
    		
	      	var mjia = $('#square').val()*500;
	      	
	      	var mjiaNum=(mjia*0.8/10000).toFixed(1);
	      	
	        $('#bprice').html(mjiaNum);
	        $('.bj_res_t span').html("万元");
	
	        $('#materialPay em').html(mjia*0.48);
	        $('#artificialPay em').html(mjia*0.32);
	        
	        $('#designPay em').html(0);
	        if($('#designPay del')[0]){
	        	$('#designPay del').eq(0).html(mjia*0.19+'元')
	        }
	        else{
	        	$('#designPay').append('<del>'+mjia*0.19+'元'+'</del>')
	        }
	          
	        $('#qualityPay em').html(0);
	        if($('#qualityPay del')[0]){
	        	$('#qualityPay del').eq(0).html(mjia*0.19+'元')
	        }
	        else{
	        	$('#qualityPay').append('<del>'+mjia*0.19+'元'+'</del>')
	        }
        
        $('#new_base_info').css("background-position","0 -168px")
        }
    	else{
    		return false;
    	}
   });
	
})