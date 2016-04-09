<?php
require_once 'func_abstract.php';

class Colors implements Functionality {

	function __construct($pageid) {
	}

    function Header(){
		$showfunctionality = '';
				$fileindex = 0;

				$s_path = 'colorsds/paintings/';

				$a_types = array ('b64');
				$a_images = array ();
				$h_dir = opendir($s_path);

				$showfunctionality ='
				<script type="text/javascript">
				var entries = [
				';

				while ($s_file = readdir($h_dir)) {
					$s_fileid = substr($s_file,0,strpos($s_file,"."));
					$s_type = strtolower(substr(strrchr($s_file, '.'), 1));

					if (in_array($s_type, $a_types)) {
						array_push($a_images, $s_fileid);
					}

				}

				rsort($a_images);

				foreach ($a_images as $painting)
				{
					$showfunctionality .='{
						image: "colorsds/paintings/_'.$painting .'.jpg",
						drw: "colorsds/paintings/'. $painting .'.b64",
						width: 256,
						height: 192,
						image_width: 256,
						image_height: 192,
						orientation: ColorsDraw.Orientation.Normal
					},';
				}


				// strip last comma
				$showfunctionality = substr($showfunctionality,0,strlen($showfunctionality) -1);
				$showfunctionality .=']</script>';;
				closedir($h_dir);
		return $showfunctionality;
    }

	function Show() {
		$showfunctionality = '';
		$fileindex = 0;

		$s_path = 'colorsds/paintings/';

		$a_types = array ('b64');
		$a_images = array ();
		$h_dir = opendir($s_path);

		while ($s_file = readdir($h_dir)) {
			$s_fileid = substr($s_file,0,strpos($s_file,"."));
			$s_type = strtolower(substr(strrchr($s_file, '.'), 1));

			if (in_array($s_type, $a_types)) {
				array_push($a_images, $s_fileid);
			}
		}

		rsort($a_images);

		foreach ($a_images as $painting)
		{
			$showfunctionality .='
		<div class="display-here" onmouseout="mouseOutGallery('. $fileindex .')">
		<div id="gallery'. $fileindex .'">
			<img id="image-'. $fileindex .'" src="colorsds/paintings/_'. $painting .'.jpg" width="256" height="192" title="" border="0" alt="" onmouseover="mouseOverGallery('. $fileindex .')" />
			</div>
		</div>
			';

			$fileindex++;
		}


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