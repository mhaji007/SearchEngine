<?php
include("config.php");
include("classes/DomDocumentParser.php");

$alreadyCrawled = array();
$crawling = array();
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

	$scheme =  parse_url($url)["scheme"] ; // http or https
	$host = parse_url($url)["host"]; // www.bbc.com

	if(substr($src, 0, 2) == "//") {
		$src = $scheme . ":" .$src;
	}

	else if (substr($src,0, 1) == "/") {
		$src = $scheme . "://" . $host . $src;
	}

	else if (substr($src,0, 2) == "./") {
		$src = $scheme . "//" . $host . dirname(parse_url($url)["path"]) . substr($src,1);
	}

	else if (substr($src,0,3) == "../") {
		$src = $scheme . "://" / $host . "/" . $src;
	}

	else if (substr($src,0,5) != "https" && substr($src,0,4) != "http") {
		$src = $scheme . "://" . $host . "/" . $src;
	}

	return $src;

	/*
	echo "SRC: $src<br>";
	echo "URL: $url<br>";
	*/
}

function getDetails($url) {

	global $alreadyFoundImages;

	$parser = new DomDocumentParser($url);

	$titleArray = $parser->getTitleTags();

	if (sizeof($titleArray) == 0 || $titleArray->item(0) == NULL) {
		return;
	}
	
	$title = $titleArray->item(0)->nodeValue;
	
	$title = str_replace("\n","",$title);

	if($title == "") {
		return;
	}

	$description = "";
	$keywords = "";

	$metasArray = $parser -> getMetatags();

	foreach($metasArray as $meta) {

		if($meta->getAttribute("name") == "description") {
			$description = $meta->getAttribute("content");
		}

		if($meta->getAttribute("name") == "keywords") {
			$keyworsds = $meta->getAttribute("content");
		}

	}

	$description = str_replace("\n","",$description);
	$keywords = str_replace("\n","",$keywords);

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

function followLinks($url) {

	global $alreadyCrawled;
	global $crawling;

	$parser = new DomDocumentParser($url);

	$linkList = $parser->getlinks();

	foreach($linkList as $link) {
		$href = $link->getAttribute("href");

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

		else if(substr($href,0,11) == "javascript:") {
			continue;
		}

		$href = createLink($href, $url);

		if (!in_array($href, $alreadyCrawled)) {
			$alreadyCrawled[] = $href;
			$crawling[] = $href;

			// insert href
			getDetails($href);
		}

		//else return;


		//echo $href . "<br>";
	}

	array_shift($crawling);

	foreach($crawling as $site) {
		followLinks($site);
	}
}

$startUrl = "https://alibaba.com";
followLinks($startUrl);
?>