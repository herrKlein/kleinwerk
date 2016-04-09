<?php
require_once 'func_abstract.php';

class Scrollimages implements Functionality {

	const PageImages = 10;

	function __construct($pageid) {
	}

	function Header(){
		$showfunctionality = '';
		return $showfunctionality;
    }

	function Show() {
		$showfunctionality = '';

		$scrollpage = 0;
		if (isset($_GET['scrollpage']) && $_GET['scrollpage'] != '') {
			$scrollpage = intval($_GET['scrollpage']);
		}

// http://stackoverflow.com/questions/14869104/writing-array-to-file-in-php-and-getting-the-data

		if ($scrollpage == 0) {
			$images = glob('../images/*');
			shuffle($images);
			// write to the file the choosen ordering
			$string_data = serialize($images);
			file_put_contents("../kleinwerk/scrollimages.txt", $string_data);
		} else {
			// read from the file to keep the ordering
			$string_data = file_get_contents("../kleinwerk/scrollimages.txt");
			$images = unserialize($string_data);
			// slice of the images we alread have
		}

		$images = array_slice($images,$scrollpage * Scrollimages::PageImages,Scrollimages::PageImages);

		$showfunctionality .= '<div id="scrollimages" class="transitions-enabled infinite-scroll clearfix">';

		foreach($images as $k => $v) : 
			list($width, $height) = getimagesize($images[$k]);
			$aspectratio = (intval($width) / intval($height));
			$width=235;
			if ( $aspectratio > 1.4 ) {
				$width = 480;
			}
			$showfunctionality .= '
				<div class="item"><a href="'. $images[$k] .'">
				<img src="../cms/imgsize.php?w='. $width .'&amp;img='. $images[$k] .'" />
				</a></div>';
		endforeach;
		$scrollpage = $scrollpage + 1;
		$showfunctionality .= '</div>';
	    return $showfunctionality;
  	}

	// Extra path addition
	function getFuncPath() {}

	// This function will tell the page to wether show its content or not.
	// In example for the gallery, you don't want the page content to be showed when displaying images
	// default mode the pagecontent is always shown
	function showPageContent() {}
}

?>