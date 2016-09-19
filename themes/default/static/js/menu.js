$(document).ready(function(){
			//-----------------首页JS效果---------------------
			//绿色头部搜索框
			$(".search_cont").mouseover(function(){
						$(this).find(".search_cont_list").show();
					}).mouseleave(function(){
						$(this).find(".search_cont_list").hide();
					});
						
			//绿色导航
			$(".main_nav ul li").mouseover(function(){
						$(this).find(".main_nav_son").show();
					}).mouseleave(function(){
						$(this).find(".main_nav_son").hide();
					});
			
			//首页banner左边切换
            $(".index_banner_lt p.index_banner_tit a").mouseover(function(){
				var index=$(this).index();
					 $(".index_banner_lt p.index_banner_tit a").each(function(a){	
						if(a == index){
							$(this).addClass('current');
							$('.index_banner_lt').find('.index_menu_list').eq(a).show();
						}
						else{
							 $(this).removeClass('current');
							 $('.index_banner_lt').find('.index_menu_list').eq(a).hide();
						 }
					})				   
			   });				   
			$(".index_banner_lt p.index_banner_tit a").eq(0).mouseover();	
			
			//首页热门案例切换
			$('ul.index_case_list li').mouseover(function(){
				$('ul.index_case_list li').siblings().find('p').hide();
				$(this).find('p').show();			 
			});
			 $(".index_case h2.index_tit span.tit_list a").mouseover(function(){
				var index=$(this).index();
					 $(".index_case h2.index_tit span.tit_list a").each(function(a){	
						if(a == index){
							$(this).addClass('current');
							$('.index_case').find('ul.index_case_list').eq(a).show();
						}
						else{
							 $(this).removeClass('current');
							 $('.index_case').find('.index_case_list').eq(a).hide();
						 }
					 })				   
		  		 });				   
			$(".index_case h2.index_tit span.tit_list a").eq(0).mouseover();	
			
			
			
			//首页最新订单文字无缝滚动效果
			$(function(){
				$("div.index_nwod_box").myScroll({
					speed:40, //数值越大，速度越慢
					rowHeight:40 //li的高度
				});
			});
			
			//首页在建工地切换
			$(".index_site li").mouseover(function(){
				$('.index_site li').find('.index_site_hover').hide();
				$(this).find('.index_site_hover').show();
				$(".index_site li").css('background','#f8f8f8');
				$(this).css('background','#fff');
				$('.index_site li').find('.index_site_list').show();
				$(this).find('.index_site_list').hide();
			});
			$(".index_site li").eq(0).mouseover();
			
			//头部登录后效果
			$(".top_nav_login li").mouseover(function(){
				$(this).find('.top_nav_login_son').show();
			}).mouseout(function(){
				$(this).find('.top_nav_login_son').hide();
			});
			
			//首页装修公司排行
			$(".index_paih p.tit a").mouseover(function(){
				var index=$(this).index();
					 $(".index_paih p.tit a").each(function(a){	
						if(a == index){
							$(this).addClass('current');
							$('.index_paih .index_paihang').find('ul').eq(a).show();
						}
						else{
							 $(this).removeClass('current');
							 $('.index_paih .index_paihang').find('ul').eq(a).hide();
						 }
					 })				   
		  		 });				   
			$(".index_paih p.tit a").eq(0).mouseover();	
			
			//-----------------我要装修页面JS效果---------------------
			
			//我要装修页面 最新订单文字无缝滚动效果
			$(function(){
				$("div.tenders_order_box").myScroll({
					speed:40, //数值越大，速度越慢
					rowHeight:45 //li的高度
				});
			});		
			//-----------------列表页面JS效果---------------------
			
			//页码效果
			$('.page a').click(function(){
				$('.page a').siblings().removeClass('current');
				$(this).addClass('current');
				});
			 $(".page a").eq(0).click();	
			 
			//列表页头部排序
			/*
			$(".sort_list a").click(function(){
				 $(".sort_list a").siblings().find('span').removeClass('sort_on_ico');
				 $(this).find('span').addClass('sort_on_ico');
		  	 });				   
			$(".sort_list a").eq(0).click();	
			*/
			//设计师列表页头像上图标
			$(".main_designer_ul li").mouseover(function(){
				$(this).find("span.love_span").show();
			 }).mouseleave(function(){
				$(this).find("span.love_span").hide();
			});
			
			//活动列表页头部切换
			$(".main_activity_choose a").click(function(){
				$(".main_activity_choose a").siblings().removeClass('current');
				 $(this).addClass('current');
			});
			
			 //装修案例列表模式透明层
			 $(".case_aterfall_li").mouseover(function(){
				 $(this).find(".opacity_img span").show();
			 }).mouseleave(function(){
				$(this).find(".opacity_img span").hide();
			 });
			
			 //商城头部切换
			  $(".mall_top ul li").mouseover(function(){
				 $(this).find(".mall_top_show").addClass('current');
				 $(this).find(".mall_top_show p.tit").css('color','#fe7902');
				 $(this).find(".mall_top_hidden").show();
			 }).mouseleave(function(){
				 $(this).find(".mall_top_show").removeClass('current');
				 $(this).find(".mall_top_show p.tit").css('color','#333');
				 $(this).find(".mall_top_hidden").hide();
			 });	
			 //商城头部图片左右滑动 
			  $(".mall_banner_bt_lt").hover(function(){
						$(this).find(".prev,.next").fadeTo("show",0.8);
					  },function(){
						$(this).find(".prev,.next").hide();
					  })

                    var de_num = $(".mall_banner_bt_lt_ul ul li").length;
                    var th_num = 0;
                    $(".next").click(function(){
                         if(th_num < de_num - 4){
                             th_num++;
                             $(".mall_banner_bt_lt_ul ul").stop().animate({'left':'-'+(th_num*195)+'px'});
                         }    
                    });
                    $(".prev").click(function(){
                         if(th_num <= de_num - 4 && th_num >0){
                             th_num--;
                             $(".mall_banner_bt_lt_ul ul").stop().animate({'left':'-'+(th_num*194)+'px'});
                         }   
                    });
			  
			  //商铺商品详情页
			  $("p.sub_shop_fl a").click(function(){
				 $('p.sub_shop_fl a').removeClass('current');
				 $(this).addClass('current');
			 });	
			  
			  //商铺商品数量加减
			  $("[quantity]").click(function(){
				var quantity = parseInt($("#cart_product_num").val(), 10);
				if($(this).attr("quantity") == '+'){
					quantity += 1;
				}else{
					quantity -= 1;
				}
				if(quantity < 1){
					quantity = 1;
				}
				$("#cart_product_num").val(quantity);
			});
			
			//活动专题页面 最新报名文字无缝滚动效果
			$(function(){
				$("div.aczt_table").myScroll({
					speed:40, //数值越大，速度越慢
					rowHeight:32 //li的高度
				});
			});
			
			//活动专题页面 定位导航
			$(function () {
			var ie6 = document.all;
			var dv = $('#fixedMenu_keleyi_com'), st;
			dv.attr('otop', dv.offset().top); //存储原来的距离顶部的距离
			$(window).scroll(function () {
			st = Math.max(document.body.scrollTop || document.documentElement.scrollTop);
			if (st > parseInt(dv.attr('otop'))) {
			if (ie6) {//IE6不支持fixed属性，所以只能靠设置position为absolute和top实现此效果
			dv.css({ position: 'absolute', top: st });
			}
			else if (dv.css('position') != 'fixed') dv.css({ 'position': 'fixed', top: 0 });
			} else if (dv.css('position') != 'static') dv.css({ 'position': 'static' });
			});
			});
			
			//装修宝页面效果
		    $(".xuanfu_nav li").click(function(){
				 $('.xuanfu_nav li').find('a').removeClass('current');
				 $(this).find('a').addClass('current');
			 });
			$(window).scroll(function() {
				var myt = (document.body.scrollTop || document.documentElement.scrollTop);			
				if (myt > 300) {
					$('.bao_xuanfu').show();
				} else {
					$('.bao_xuanfu').hide();
				}
			});
   			
			//购物车支付页面效果
		 	 $(".shop_apply_bt_cont label").click(function(){
				 $('.shop_apply_bt_cont label').find('img').removeClass('current');
				 $(this).find('img').addClass('current');
		 	});
			$(".shop_apply_bt_cont label").eq(0).click();
 });
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 