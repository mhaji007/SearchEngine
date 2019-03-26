var timer;

$(document).ready(function() {


	$(".result").on("click", function() {
		
		var id = $(this).attr("data-linkId");
		var url = $(this).attr("href");

		if(!id) {
			alert("data-linkId attribute not found");
		}

		increaseLinkClicks(id, url);

		//return false;
	});

	var grid = $(".imageResults");

	grid.on("layoutComplete", function () {
		$(".gridItem img").css("visibility", "visible");
	});

	grid.masonry({
		itemSelector: ".gridItem",
		columnWidth: 200,
		gutter: 5,
		isInitLayout: false
	});

	$("[data-fancybox]").fancybox();
});

function loadImage(src, className) {
	//console.log(src);
	var image = $("<img>");

	image.on("load", function() {
		$("." + className + " a").append(image);

		clearTimeout(timer);

		timer = setTimeout(function() {
			$(".imageResults").masonry();
		}, 500);

		

	});

	image.on("error", function() {

		$("." + className).remove();

		$.post("ajax/setBroken.php",{src: src});

	});

	image.attr("src", src);
}


function increaseLinkClicks(linkId, url) {

	$.post("ajax/updateLinkCount.php", {linkId: linkId})
	.done(function(result) {
		if(result != "") {
			alert(result);
			return;
		}

		window.location.href = url;

	});

}





































// $(document).ready(function(){

// 	$(".result").on("click", function () {
// 		// console.log("I was clicked");

// 		//get the value of the href
// 		var id = $(this).attr("data-linkId");
// 		var url = $(this).attr("href");
// 		//console.log(id);

// 		if(!id) {
// 			alert("data-linkId attribute not found");
// 		}

// 		increaseLinkClicks(id, url);



// 		return false;  // don't do default behavior (here, don't go to the page clicked)
// 	});
// });

// function increaseLinkClicks(linkId, url) {

// 	$.post("ajax/updateLinkCount.php", {linkId: linkId});

// }