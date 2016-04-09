<?php

interface Functionality {
	// Constructor
	// every Page Functionality has got a pageid := CurrentPage
	function __construct($pageid);

	// every Functionality has got a show Method, for Showing the functionality
	function Header();

	// every Functionality has got a show Method, for Showing the functionality
	function Show();

	// Extra path addition
	function getFuncPath();

	// This function will tell the page to wether show its content or not.
	// In example for the gallery, you don't want the page content to be showed when displaying images
	// default mode the pagecontent is always shown
	function showPageContent();
}
?>