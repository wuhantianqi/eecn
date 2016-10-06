// JavaScript Document
$(document).ready(function(){
	serveCheck();
	houseRadio();
	changePage();
	
	
	function houseRadio(){
		
	var $houseLi=$(".zh-m-house>ul>li")
	
	
	for(var i=0;i<$houseLi.length;i++){
		
		$houseLi.eq(i).click(function(){
			for(var j=0;j<$houseLi.length;j++){
				$houseLi.eq(j).find(".replace-radio").find("img").attr("src","/themes/default/mobile/static/images/radio-default.png");
			}
			$(this).find(".replace-radio").find("img").attr("src","/themes/default/mobile/static/images/radio-checked.png");
		})
		
	}
	}
	
	function serveCheck(){
	
	var $serveLi=$(".zh-m-serve>ul>li");
	
	$serveLi.each(function(){
		$(this).click(function(){
			var $img=$(this).find("img");
			if( $img.attr("src")=="/themes/default/mobile/static/images/checkbox-default.png"){
				$img.attr("src","/themes/default/mobile/static/images/checkbox-checked.png")
			}
			else{
				$img.attr("src","/themes/default/mobile/static/images/checkbox-default.png")
			}
		})
	})
	}
	
	function changePage(){
		var $hide=$(".zh-hide");
		var $btnChange=$(".zh-next-page");
		var ikey=true;  //true表示第一次来
		
		
		if(sessionStorage.match){
			$hide.eq(0).css("display","none")
			$hide.eq(1).css("display","none");
			$hide.eq(2).css("display","none")
			$hide.eq(3).css("display","block");
		}
		
		$btnChange.eq(0).click(function(){
			$hide.css("display","none");
			$hide.eq(1).css("display","block")
		})
		$btnChange.eq(1).click(function(){
			$hide.css("display","none");
			$hide.eq(2).css("display","block")
		})
		$btnChange.eq(2).click(function(){
			$hide.css("display","none");
			$hide.eq(3).css("display","block");
			
			sessionStorage.match=true;
			
		})
		
	
	}
		
})
