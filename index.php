<!DOCTYPE html> 
<html>
<head>
	<title> Welcome to Inquire</title>

  	<meta charset="UTF-8">
  	<meta name="description" content="Search the web for sites and images">
  	<meta name="keywords" content="Search engine, Inquire, HTML, CSS, XML, JavaScript, websites">
  	<meta name="author" content="Mehdi Hajikhani">
  	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" type="text/css" href="assets/css/style.css">

</head>
<body>

	<div class="wrapper indexPage">

		<div class="mainSection">

			<div class = "logoContainer">
				
				<img src="assets/images/inquire.png ">

			</div>


			<div class="searchContainer">
				
				<form action = "search.php" method="GET">

				<input class="searchBox" type="text" name="term">
				<input class="searchButton" type="submit" value="Search">

				</form>
				
			</div>
			
		</div>
	</div>

</body>
</html>