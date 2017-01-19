// JavaScript Document

	
	
	
         
         $( function(){
			 
			 $(".ph ul li").click( function(){
				 
				  $(this).addClass("cs").siblings().removeClass("cs");
				  $(this).removeClass("bd").siblings().addClass("bd");
				  var a=$(".ph ul li").index(this);
				   $(".pt").eq(a).css("display","block").siblings().css("display","none");
				 
				 })
			 
			 })
         
       
	
	
	
