<?php
include("config.php");
include("classes/DomDocumentParser.php");

// array to  contain all the links already visited
$alreadyCrawled = array();

// array to hold links still need to be crawled
$crawling = array();

// array to hold images aleady visited
$alreadyFoundImages = array();

function linkExists($url) {
	global $con;

	$query = $con->prepare("SELECT * FROM sites WHERE url = :url");
	$query->bindParam(":url", $url);
	
	$query->execute();

	return $query->rowCount() != 0;


}

function insertLink($url, $title, $description, $keywords) {
	global $con;

	$query = $con->prepare("INSERT INTO sites(url, title, description, keywords)  VALUES(:url, :title, :description, :keywords)");
	$query->bindParam(":url", $url);
	$query->bindParam(":title", $title);
	$query->bindParam(":description", $description);
	$query->bindParam(":keywords", $keywords);

	return $query->execute();


}

function insertImage($url, $src, $alt, $title) {
	global $con;

	$query = $con->prepare("INSERT INTO images (siteUrl, imageUrl, alt, title)  VALUES(:siteUrl, :imageUrl, :alt, :title)");
	$query->bindParam(":siteUrl", $url);
	$query->bindParam(":imageUrl", $src);
	$query->bindParam(":alt", $alt);
	$query->bindParam(":title", $title);

	$query->execute();


}

// function to convert relative links to absolute links (full url)
function createLink($src, $url) {

	/*
	
	Scheme: http or https
	Host: www.Domain.com

	URL cases to handle:

	//www.reecekenney.com
	//about/aboutUs.php
	./about/aboutUs.php
	../about/aboutUs.php ---> the ".." means go back a directory
	About/aboutUs.php

	*/	

	//parse_url is a built-in php function
	$scheme =  parse_url($url)["scheme"]; // http or https
	$host = parse_url($url)["host"]; // www.bbc.com

	// if first two characters are //
	if(substr($src, 0, 2) == "//") {
		$src = $scheme . ":" .$src;
	}
	//if first character is a /
	else if (substr($src,0, 1) == "/") {
		$src = $scheme . "://" . $host . $src;
	}
	// if first two characters are ./
	else if (substr($src,0, 2) == "./") {
		$src = $scheme . "//" . $host . dirname(parse_url($url)["path"]) . substr($src,1);
	}
	// if first three characters are ../
	else if (substr($src,0,3) == "../") {
		$src = $scheme . "://" . $host . "/" . $src;
	}
	// if first 5 characters are not https or first four characters are not http 
	else if (substr($src,0,5) != "https" && substr($src,0,4) != "http") {
		$src = $scheme . "://" . $host . "/" . $src;
	}

	return $src;

	/*
	echo "SRC: $src<br>";
	echo "URL: $url<br>";
	*/
}

// function to retrun the details of urls
function getDetails($url) {

	global $alreadyFoundImages;

	$parser = new DomDocumentParser($url);

	// returns an array of titles
	$titleArray = $parser->getTitleTags();

	// if size of the array is zero or the first item in the array is null, return
	if (sizeof($titleArray) == 0 || $titleArray->item(0) == NULL) {
		return;
	}
	
	// return the value of the first item of the array
	$title = $titleArray->item(0)->nodeValue;

	// replace new lines with empty strings (delete any new lines)
	$title = str_replace("\n","",$title);

	// if there are no titles we don't want them in our database. Google penalizes websites that do not have titles or proper keywords as well. If there are no titles we don't want to crawl them. There are alternatives. e.g., you could show the url instead of title.
	if($title == "") {
		return;
	}
	// Although we don't crawl websites with no title tage, we do crawl websites with no description or keywords tag. We initialize them with empty strings
	$description = "";
	$keywords = "";

	$metasArray = $parser -> getMetatags();

	foreach($metasArray as $meta) {

		if($meta->getAttribute("name") == "description") {
			$description = $meta->getAttribute("content");
		}

		if($meta->getAttribute("name") == "keywords") {
			$keywords = $meta->getAttribute("content");
		}

	}

	$description = str_replace("\n","",$description);
	$keywords = str_replace("\n","",$keywords);

	// for testing
	//echo "URL: $url, Title: $title, Description: $description, Keywords: $keywords<br>";
    
	if(linkExists($url)) {
		echo "$url already exists<br>";
	}

	else if(insertLink($url, $title, $description, $keywords)) {
		echo "SUCCESS: $url";

	}
	else {
		echo"ERROR: Failed to insert $url<br>";
	}


	//insertLink($url, $title, $description, $keywords);

	$imageArray =  $parser->getImages();
	foreach ($imageArray as $image) {

		$src = $image ->getAttribute("src");
		$alt = $image->getAttribute("alt");
		$title = $image->getAttribute("title");

		if(!$title && !$alt) {
			continue;
		}

		$src = createLink($src, $url);

		if(!in_array($src, $alreadyFoundImages)) {
			$alreadyFoundImages[] = $src;

			insertImage($url, $src, $alt, $title);
		}

		// insert images




	}

}

// function to recursively follow links on the webpage
function followLinks($url) {

	// global references to arrays
	global $alreadyCrawled;
	global $crawling;

	$parser = new DomDocumentParser($url);

	// retrieves all the links on the specified url
	$linkList = $parser->getlinks();

	// loops over each link and gets the href attribute 
	foreach($linkList as $link) {
		$href = $link->getAttribute("href");
		// ignore the hrefs that have # to use the behavior of the links (they are not real links)
		if(strpos($href,"#") !== false) {
			continue;
		}

		if(strpos(strtoupper($href),"FACEBOOK") or strpos(strtoupper($href),"TWITTER")  or strpos(strtoupper($href),"LINKEDIN" ) or strpos(strtoupper($href),"PLUS.GOOGLE") ) {
			continue;
		}

	/*
		if(strlen($href) == 1) {
				if(strpos($href,"/") == 0){
					continue;
				}
		}
	*/	
		// ignore href chatracters 0-11 that have javascript value. Javascript could be used in links to execute some commands. These are for our intents and purposes are not proper links. 
		else if(substr($href,0,11) == "javascript:") {
			continue;
		}

		// creates absolute links from relative links and stores them in $href
		$href = createLink($href, $url);
		// here is where the recursive visting of links is accomplished
		if (!in_array($href, $alreadyCrawled)) {
			$alreadyCrawled[] = $href; // put it at the next item same as array_push($alreadyCrawled, $href);
			$crawling[] = $href;

			// insert href into the database
			getDetails($href);
		}

		// to speed things up when testing. If there are any duplicates in the alreadyCrawled return
		//else return;


		//echo $href . "<br>";
	}

	// take the next item of the array
	array_shift($crawling);

	// go over every item of the crawling list
	foreach($crawling as $site) {
		// recursive call to the function
		followLinks($site);
	}
}

$startUrl = "http://www.apple.com";
followLinks($startUrl);
?>