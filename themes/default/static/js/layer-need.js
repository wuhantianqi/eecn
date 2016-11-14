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
//	    var mobile = /^(13[0-9]{9})|(18[0-9]{9})|(14[0-9]{9})|(17[0-9]{9})|(15[0-9]{9})$/; && mobile.test(value)   , "请正确填写您的手机号码"
	    return this.optional(element) || (length == 11);
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
	
})