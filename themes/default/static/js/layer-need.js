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
	// 定时关闭弹窗
	function clockes(){
		setTimeout(function(){
			$('.t_top').css("display","none");
		},3000);  
	}
	
	$("#baojia-form").validate({
		submitHandler: function(){      
		    $("#baojia-form").ajaxSubmit({
		    	type:"post",
		    	dataType:"json",          
		    	url:"/index.php?tenders-save.html",
	            success: function(data) { 
	                if(data.error==0){
	                	$('.t_top').css({
						    "display": "block",
						    "position": "absolute",
						    "width": "20%",
						    "text-align": "center",
						    "top": "33%",
						    "left": "42%",
						    "background": "#ff4401",
						    "color": "#FFF",
						    "padding": "10px",
						    "border-radius": "5px",
						    "line-height": "24px",
	                	}).text("报价成功！优优美家装修网客服将于24小时内联系您，请您保持手机畅通");
	                	clockes()
	                }else{
	                	$('.t_top').css({
						    "display": "block",
						    "position": "absolute",
						    "width": "20%",
						    "text-align": "center",
						    "top": "33%",
						    "left": "42%",
						    "background": "#ff4401",
						    "color": "#FFF",
						    "padding": "10px",
						    "border-radius": "5px",
						    "line-height": "24px",
	                	}).text(data.message[0]);
	                	clockes()
	                }
	            },
		    	error:function(){
		    		alert("失败");
		    	}
		    });
		},
		rules:{
			"data[house_mj]":{
				required:true,
				minlength:2,
				maxlength:4,
				
			},
			xiaoqu:{
				
			},
			"data[mobile]":{
				required:true,
				isMobile:true
			}
		},
		messages:{
			"data[house_mj]":{
				required:"请输入房屋面积",
				minlength:"房子太小了吧,亲",
				maxlength:"房子太大了吧，亲"
			},
			
			"data[mobile]":{
				required:"请输入手机号码",
				isMobile:"请正确填写您的手机号码"
			}
		},
		onkeyup:false,
	
	});


	
	
	$("#sheji-form").validate({
			submitHandler: function(){      
			    $("#sheji-form").ajaxSubmit({
			    	type:"post",
			    	dataType:"json",          
			    	url:"/index.php?tenders-save.html",
		            success: function(data) { 
		            	var cdate = $('.t_top').css({
							    "display": "block",
							    "position": "absolute",
							    "width": "20%",
							    "text-align": "center",
							    "top": "33%",
							    "left": "42%",
							    "background": "#ff4401",
							    "color": "#FFF",
							    "padding": "10px",
							    "border-radius": "5px",
							    "line-height": "24px",
		                	});
		                if(data.error==0){
		                	cdate.text("申请成功！优优美家装修网客服将于24小时内联系您，请您保持手机畅通");
		                	clockes()
		                }else{
		                	cdate.text(data.message[0]);
		                	clockes()
		                };
		            },
			    	error:function(){
			    		alert("失败");
			    	}
			    });
			},
		rules:{
			"data[contact]":{
				required:true,
			},
			"data[mobile]":{
				required:true,
				isMobile:true
			},
			xiaoqu:{
				
			}
		},
		messages:{
			"data[contact]":"请输入用户名",
			"data[mobile]":{
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
        
        	$('#new_base_info').css("background-position","0 -168px");
        }
    	else{
    		return false;
    	}
   });
	
})