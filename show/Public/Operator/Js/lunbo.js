// JavaScript Document
 window.onload=function(){
	 
	 
	 
	$(".box li").each(function(index){
		var $img = $(this).find("img");
		var img_h = $img.height();
		var img_w = $img.width();
		var spanHtml = '<span style="position:absolute;top:0;left:0;width:'+img_w+'px;height:'+img_h+'px;" class="imageMask"></span>';
		$(spanHtml).appendTo(this);
	})
	$(".box").delegate(".imageMask", "hover", function(){
		$(this).toggleClass("imageOver");
	});
	
	/*
	$("#jnBrandList").find(".imageMask").live("hover", function(){
		$(this).toggleClass("imageOver");
	});
	*/

	
	
	var $timer;
$(function() {
	$(".next a").click(function() {
		nextscroll()
	});

	function nextscroll() {
		var vcon = $(".v_cont ");
		var offset = ($(".v_cont li").width()) * -1;
		vcon.stop().animate({
			left: offset
		}, "slow", function() {
			var firstItem = $(".v_cont ul li").first();
			vcon.find("ul").append(firstItem);
			$(this).css("left", "0px");
			circle()
		})
		clearInterval($timer);
	};

	function circle() {
		var currentItem = $(".v_cont ul li").first();
		var currentIndex = currentItem.attr("index");
		$(".circle li").removeClass("circle-cur");
		$(".circle li").eq(currentIndex).addClass("circle-cur")
	}
	$(".prev a").click(function() {
		var vcon = $(".v_cont ");
		var offset = ($(".v_cont li").width() * -1);
		var lastItem = $(".v_cont ul li").last();
		vcon.find("ul").prepend(lastItem);
		vcon.css("left", offset);
		vcon.animate({
			left: "0px"
		}, "slow", function() {
			circle()
		})
		clearInterval($timer);
	});
	var animateEnd = 1;
	$(".circle li").click(function() {
		if (animateEnd == 0) {
			return
		}
		$(this).addClass("circle-cur").siblings().removeClass("circle-cur");
		var nextindex = $(this).index();
		var currentindex = $(".v_cont li").first().attr("index");
		var curr = $(".v_cont li").first().clone();
		if (nextindex > currentindex) {
			for (var i = 0; i < nextindex - currentindex; i++) {
				var firstItem = $(".v_cont li").first();
				$(".v_cont ul").append(firstItem)
			}
			$(".v_cont ul").prepend(curr);
			var offset = ($(".v_cont li").width()) * -1;
			if (animateEnd == 1) {
				animateEnd = 0;
				$(".v_cont").stop().animate({
					left: offset
				}, "slow", function() {
					$(".v_cont ul li").first().remove();
					$(".v_cont").css("left", "0px");
					animateEnd = 1
				})
			}
		} else {
			var curt = $(".v_cont li").last().clone();
			for (var i = 0; i < currentindex - nextindex; i++) {
				var lastItem = $(".v_cont li").last();
				$(".v_cont ul").prepend(lastItem)
			}
			$(".v_cont ul").append(curt);
			var offset = ($(".v_cont li").width()) * -1;
			$(".v_cont").css("left", offset);
			if (animateEnd == 1) {
				animateEnd = 0;
				$(".v_cont").stop().animate({
					left: "0px"
				}, "slow", function() {
					$(".v_cont ul li").last().remove();
					animateEnd = 1
				})
			}
		}
	})
})

$(function() {
	$timer = setInterval(function() {
		$('.picpic').click();	
	},3000);
	
	$('div.prev , div.next').mouseover(function() {
		$(this).find('img').eq(0).show();	
		$(this).find('img').eq(1).hide();	
	}).mouseout(function() {
		$(this).find('img').eq(1).show();	
		$(this).find('img').eq(0).hide();	
	});
});



var $timer;
$(function() {
	$(".next1 a").click(function() {
		nextscroll()
	});

	function nextscroll() {
		var vcon = $(".v_cont11 ");
		var offset = ($(".v_cont11 li").width()) * -1;
		vcon.stop().animate({
			left: offset
		}, "slow", function() {
			var firstItem = $(".v_cont11 ul li").first();
			vcon.find("ul").append(firstItem);
			$(this).css("left", "0px");
			circle()
		})
		clearInterval($timer);
	};

	function circle() {
		var currentItem = $(".v_cont11 ul li").first();
		var currentIndex = currentItem.attr("index");
		
	}
	$(".prev1 a").click(function() {
		var vcon = $(".v_cont11 ");
		var offset = ($(".v_cont11 li").width() * -1);
		var lastItem = $(".v_cont11 ul li").last();
		vcon.find("ul").prepend(lastItem);
		vcon.css("left", offset);
		vcon.animate({
			left: "0px"
		}, "slow", function() {
			circle()
		})
		clearInterval($timer);
	});
	var animateEnd = 1;
	$(".circle li").click(function() {
		if (animateEnd == 0) {
			return
		}
		$(this).addClass("circle-cur").siblings().removeClass("circle-cur");
		var nextindex = $(this).index();
		var currentindex = $(".v_cont11 li").first().attr("index");
		var curr = $(".v_cont11 li").first().clone();
		if (nextindex > currentindex) {
			for (var i = 0; i < nextindex - currentindex; i++) {
				var firstItem = $(".v_cont11 li").first();
				$(".v_cont11 ul").append(firstItem)
			}
			$(".v_cont11 ul").prepend(curr);
			var offset = ($(".v_cont11 li").width()) * -1;
			if (animateEnd == 1) {
				animateEnd = 0;
				$(".v_cont11").stop().animate({
					left: offset
				}, "slow", function() {
					$(".v_cont11 ul li").first().remove();
					$(".v_cont11").css("left", "0px");
					animateEnd = 1
				})
			}
		} else {
			var curt = $(".v_cont11 li").last().clone();
			for (var i = 0; i < currentindex - nextindex; i++) {
				var lastItem = $(".v_cont11 li").last();
				$(".v_cont11 ul").prepend(lastItem)
			}
			$(".v_cont11 ul").append(curt);
			var offset = ($(".v_cont11 li").width()) * -1;
			$(".v_cont11").css("left", offset);
			if (animateEnd == 1) {
				animateEnd = 0;
				$(".v_cont11").stop().animate({
					left: "0px"
				}, "slow", function() {
					$(".v_cont11 ul li").last().remove();
					animateEnd = 1
				})
			}
		}
	})
})

$(function() {
	$timer = setInterval(function() {
		$('.picpic').click();	
	},3000);

});






	
	
	
	
	}