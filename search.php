<!DOCTYPE html>

<?php
include("config.php");
include("classes/SiteResultsProvider.php");
	
	if(isset($_GET["term"])) {

	$term =  $_GET["term"];
	}

	else

	{
		exit("You must enter a search term");
	}

	$type = isset($_GET["type"]) ? $_GET["type"] : "sites";
	$page = isset($_GET["page"]) ? $_GET["page"] : "1";

?> 


<html>
<head>
	<title> Welcome to Doodle</title>

	<link rel="stylesheet" type="text/css" href="assets/css/style.css">

</head>
<body>

	<div class="wrapper">

		<div class="header">

			<div class="headerContent">

				<div class="logoContainer">
					
					<a href="index.php">

						<img src="assets/images/inquire.png ">
						

					</a>
				</div>

				<div class ="searchContainer">

					<form action="search.php" method="GET">
						<div class="searchBarContainer">
							
							<input class = "searchBox" type="text" name="term" value="<?php echo $term;?>">

							<button class="searchButton">
								<img src="assets/images/icons/search.png">
							</button>
						</div>

					</form>
					
				</div>
				
				
			</div>

			<div class ="tabsContainer">
				<ul class ="tabList">
					
					<li class="<?php echo $type == 'sites' ? 'active' : ''?>">
						<a href='<?php echo "search.php?term=$term&type=sites";?>'>

							Sites
							
						</a>
					</li>

					<li class="<?php echo $type == 'images' ? 'active' : ''?>">
						<a href='<?php echo "search.php?term=$term&type=images";?>'>

							Images
							
						</a>
					</li>

				</ul>
				

			</div>
			
		</div>






		<div class="mainResultsSection">

			<?php
			$resultsProvider = new SiteResultsProvider($con);
			$pageLimit = 20;

			$numResults = $resultsProvider->getNumResults($term);

			echo "<p class='resultsCount'>$numResults results found</p>";

			echo $resultsProvider->getResultsHtml($page, $pageLimit, $term);
			?>
		</div>


			
	</div>

</body>
</html>