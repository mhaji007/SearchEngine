<?php
class DomDocumentParser {
	
	// variable to save the HTML contents so all functions on this page have access to HTML contents
	private $doc;

	// this function receives a url and makes a request to go to mentioned url. It passes the contents of the website to DOM document object. The doc variable contains the HTML contents of the website.
	public function __construct($url) {

		// array to specify the options when we request the webpage. Options are method to retireve the data and a header and a user-agent. User-Agent is how a website knows who visited the website. 
		$options = array(
			'http' =>array('method'=>"GET", 'header'=>"User-Agent: doodleBot/0.1\n")
			);

		// A stream context used for making requets
		$context = stream_context_create($options);

		// built-in php class which allows performing actions on webpages (DOM documents)
		$this->doc = new DomDocument(); 

		// false value is whether we want to use the include path for php. Include path is a configuration url. We don't need it here.
		//@ supresses the warnings related to php 7 supporting HTML 5 and not being able to support new HTML elements
		@$this->doc->loadHTML(file_get_contents($url, false, $context)); 

	}

	// function to get an array of the links on the website
	public function getlinks() {
		return $this->doc->getElementsByTagName("a");
	}
	// function to get the titles of the websites
	public function getTitleTags() {
		return $this->doc->getElementsByTagName("title");
	}
	// function to get meta tags included in html of the websites
	public function getMetaTags() {
		return $this->doc->getElementsByTagName("meta");
	}
	// function to get the images on the websites
	public function getImages() {
		return $this->doc->getElementsByTagName("img");
	}
}
?>