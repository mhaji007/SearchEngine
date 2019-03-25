$(document).ready(function(){

	$(".result").on("click", function () {
		// console.log("I was clicked");

		//get the value of the href
		var url = $(this).attr("href");
		console.log(url);



		return false;  // don't do default behavior (here, don't go to the page clicked)
	});
});

function increaseLinkClicks(linkId, url) {



}