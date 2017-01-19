// JavaScript Document

	
	$(function(){
	
	
	$("#date_tab li").click(function(){
		$(this).addClass("click").siblings().removeClass("click");
		var index =$(this).index();
		$(".host").eq(index).css("display","block").siblings(".host").css("display","none");
		
		
		})
	
	
	
	})
	
