<?php
require_once 'func_abstract.php';

class Gallery extends domDocument implements Functionality {

	public $r__category;										// Requested variable category
	public $r__imageid;											// Requested variable imageid
	public $galleryImages = array();							// array with all the current images of the chosen category
	public $galleryCategories = array();						// array with all the categories
	public $currentimage;										// Object which represents the current viewing image

	function __construct($pageid) {
		$this->currentPageid = $pageid;

if (isset($_REQUEST['category']) && $_REQUEST['category'] != "" ) {
		$this->r__category = $_REQUEST['category']; 
	} else {
		$this->r__category = "";   	
	}
    
if (isset($_REQUEST['imageid']) && $_REQUEST['imageid'] != "" ) {
		$this->r__imageid = $_REQUEST['imageid']; 
	} else {
		$this->r__imageid = "";   	
	}    

		// This magically little whitespace thing will prevent whitespaces to be seen as domnodes..
 		$this->preserveWhiteSpace = false;
		// TODO : webname zou dan in het pad moeten van de lokatie van de XML file.
 		$this->load('../kleinwerk/data/images.xml');

	  	$this->loadCategories();

  		// if no category is requested, load the images of the first category
	  	if ($this->r__category == '' && $this->currentPageid != '') {
	  		// This really shouldn't be here, a list of parameters with each
	  		// functionality would be far more appropriate
	  		// instead of making the gallery dependent on the page domdocument
			$xp = new domxpath($this->currentPageid->website->pageDocument);
			$pageEl = $xp->query("//*[@id = '".$this->currentPageid->pageDBID."']");
			$page = $pageEl->item(0);
			$functionality = $xp->query("functionality/category",$page);
			$this->r__category = $functionality->item(0)->nodeValue;
	  	}


  		$this->loadGalleryImages($this->r__category);

		// if no image is requested, set the currentimage on the first image in this category
	  	if ($this->r__imageid != '')
			$this->currentimage = $this->galleryImages[$this->r__imageid];
		else
			// is this crap or what ? asking for the first element in an array...
			$this->currentimage = $this->galleryImages[$this->first($this->galleryImages)];
	}

	// get first element out of an array ( WHY IS THIS NOT A DEFAULT FUNCTION OF PHP )
	private function first(&$array) {
		if (!is_array($array)) return null;
		if (!count($array)) return null;
		reset($array);
		return key($array);
	}

    function Header(){}

	function show() {

    	if ( SESSION::get_session()->mode == "edit" || SESSION::get_session()->mode == "preview")
			$showfunctionality = $this->getExtraPostValues();

		if ($this->currentimage != null) {
			// $showfunctionality = $this->showNextPrev();
    		// $showfunctionality .= '<gallerynextprev>'.$this->showNextPrev().'</gallerynextprev>';
    		$showfunctionality = '<galleryimages>'.$this->showGalleryImages().'</galleryimages>';
			$showfunctionality .= '<galleryimage>'.$this->currentimage->showGalleryImage().'</galleryimage>';
			// This was for the contact form
			// $showfunctionality .= $this->currentimage->Contact->show();
		}
		return $showfunctionality;
	}

	function getFuncPath() {
	  	$funcPath = '';
    	$funcPath .= ' &gt; '. $this->galleryCategories[$this->r__category];

		if ($this->currentimage != -1 ) {
			if (($title = $this->currentimage->getTitle()) == '')
				$title = '<em>'.$this->currentPageid->website->language["notitle"].'</em>';
			$funcPath .= ' &gt; '.$title;
		}
		return '<span>'.$funcPath.'</span>';
	}

	function showNextPrev() {
		$my_keys = array_keys($this->galleryImages);
		// $nextimage = $this->galleryImages[$my_keys[array_search($my_keys, $this->galleryImages)+1]];
		$nextimage = $my_keys[array_search($this->currentimage->getID(), $my_keys)+1];
		$previmage = $my_keys[array_search($this->currentimage->getID(), $my_keys)-1];
	  	$nextprev = '<li><a class="prev" href="'. $this->currentPageid->buildLink(false,false) .'&amp;category='. $this->r__category .'&amp;imageid='.$previmage.'">previous';
  		$nextprev .= '</a></li>';
  		$nextprev .= '<li><a class="next" href="'. $this->currentPageid->buildLink(false,false) .'&amp;category='. $this->r__category .'&amp;imageid='.$nextimage.'">next';
  		$nextprev .= '</a></li>';
		return $nextprev;
	}

	function getFuncMetaButtons($disabled) {
  		$alldisabled = '';
	  	if ($disabled)
			$alldisabled = 'disabled="disabled" class="disabled"';
		if ($this->r__category != '')
			$funcMetaButtons = '<a href="../cms/func_gallery.php?directmode=on" class="greybox">add image</a>';
			// $funcMetaButtons = '<input id="button_addimage" '.$alldisabled.' type="submit" title="add image" name="contentaction" value="add image" />';

		return $funcMetaButtons;
	}

	function showPageContent() {
  		return false;
		// return ($this->r__imageid == "");
	}

	function getExtraPostValues() {
		$extraPostValues  = '<input type="hidden" name="category" value="'.$this->r__category.'" />';
		$extraPostValues .= '<input type="hidden" name="imageid" value="'.$this->r__imageid.'" />';
    	return $extraPostValues;
	}

  	function getExtraQSValues() {
  		if($this->r__category != '')
    		$getExtraQSValues  = '&amp;category='.$this->r__category;
  		if($this->r__imageid != '')
    		$getExtraQSValues .= '&amp;imageid='.$this->r__imageid;
    	return $getExtraQSValues;
	}

	function createImage() {
    	$newImage = new galleryImage($this,'');
    	// put the galleryimage as first in the array.
		$this->galleryImages = array($newImage->getID() => $newImage) + $this->galleryImages;
    	reset($this->galleryImages);
		$this->r__imageid = $newImage->getID();
		$this->currentimage = $newImage;
    	return $newImage;
	}

	function showSidebar() {
		$sidebar = '';
    	return $sidebar;
	}

	function loadGalleryImages($categoryid) {
    	$webname = $this->currentPageid->website->webname;

		if (SESSION::get_session()->mode == "edit" )
			$strPublished = "[category = '".$this->r__category."']";
    	else
      		$strPublished = "[category = '".$this->r__category."'][@published='1']";

		$xp = new domxpath($this);
		$images = $xp->query("//images/oneimage".$strPublished);

		foreach ($images as $image) {
			$this->galleryImages[$image->getAttribute("id")] = new galleryImage($this,$image);
			// putt all dbID to Object in a sessionmapping for recovery
			SESSION::get_session()->dbmapping[$image->getAttribute("id")] = $this->galleryImages[$image->getAttribute("id")];
		}
	}

	function loadCategories() {
    	$webname = $this->currentPageid->website->webname;
		$xp = new domxpath($this);
		$categories = $xp->query("//images/oneimage[@published='1']/category");

		foreach ($categories as $category) {
			// TODO : by inserting the nodeValue as Key in the Array, the keys are overwritten when there is
			// the same nodeValue, this is like a distinct in SQL. could be done nicer though...
			$this->galleryCategories[$category->nodeValue] = $category->nodeValue;
		}
	}

	function showGalleryImages() {
    	if ($this->currentPageid->website->language["portfolio"] != '') $galleryImages  = '<h2>'. $this->currentPageid->website->language["portfolio"] .'</h2>';
		$galleryImages .= '<div class="menuitem">';
		if (count($this->galleryImages) > 0) {
	    	foreach ($this->galleryImages as $galleryImage) {
	    		$selected = false;
	    		if ($this->currentimage != null && $galleryImage->getID() == $this->currentimage->getID())
	    			$selected = true;
		  		$galleryImages .= $galleryImage->showSelectGalleryImage($selected);
			}
		}
		$galleryImages .= '</div>';
		return $galleryImages;
	}
}

class galleryImage extends Content {

	var $thumbnail;
	var $image;
	var $gallery;
	var $website;
	var $galleryEl;

	function __construct($gallery,$galleryEl) {
    	$this->gallery = $gallery;
    	$this->website  = $gallery->currentPageid->website;

		if ($galleryEl != '') {
	    	$this->galleryEl = $galleryEl;
		} else {
	    	$this->createcontentInDB();
		}
		// Load the values of this page from DB
		$this->loadContent();
	}

	function loadContent() {
		$xp = new domxpath($this->gallery);

		$title = $xp->query("title_".SESSION::get_session()->langid,$this->galleryEl);
		$content = $xp->query("content_".SESSION::get_session()->langid,$this->galleryEl);
		$published = $xp->query("published",$this->galleryEl);
		$creationdate = $xp->query("creationdate",$this->galleryEl);
		$image = $xp->query("image",$this->galleryEl);
		$thumbnail = $xp->query("thumbnail",$this->galleryEl);
		$this->contentid = $this->galleryEl->getAttribute("id");
		$this->contenttitle = $title->item(0)->nodeValue;
		$this->contentcontent = $content->item(0)->nodeValue;
		$this->published = (boolean) $this->galleryEl->getAttribute("published");
		$this->creationdate = $creationdate->item(0)->nodeValue;
		$this->image = $image->item(0)->nodeValue;
		$this->thumbnail = $thumbnail->item(0)->nodeValue;
	}

	function showGalleryImage() {

    // We dont create the contact form for every article, since only with the full article
    // the contacts are show, so we only create the contacts when displaying fullarticle
  	// show the contact form
	// $this->Contact = new contact($this->gallery->currentPageid);
        $galleryImage = '';
  		// $galleryImage   = $this->getContentTitle();
		if (SESSION::get_session()->mode == "edit")
			$galleryImage .= '<a href="../cms/func_gallery.php?directmode=on&amp;category='. $this->gallery->r__category .'&amp;imageid='. $this->getID() .'">';
		$galleryImage .= '<img id="imgview" src="'. $this->gallery->currentPageid->website->img_dir . $this->image .'" alt="'. $this->getTitle() .'" />';
		if (SESSION::get_session()->mode == "edit")
			$galleryImage .= '</a>';
		$galleryImage  .= $this->getContent();
		return $galleryImage;
  }

	function showSelectGalleryImage($selected) {
		$galleryImage  = '<a href="'. $this->gallery->currentPageid->buildLink(false,false) .'&amp;imageid='. $this->getID() .'&amp;category='. $this->gallery->r__category .'" rel="history">';
		$galleryImage .= '<img id="'.$this->getID().'" ';
		if ($selected) $galleryImage .= 'class="thumbselected"';
		$galleryImage .= ' src="'. $this->website->imgthumb_dir . $this->thumbnail .'" width="40" height="40" />';
   		// $galleryImage .= ' src="'. $this->website->imgthumb_dir . $this->thumbnail .'" width="40" height="40" alt="'. $this->content_nl .'" />';
		// $galleryImage .= ' src="../cms/image.php?image=/kleinwerk/data/upload/pictures/'. $this->image .'&amp;width=160&amp;height=160&amp;cropratio=1:1" width="160" height="160" alt="'. $this->content_nl .'" />';
  		$galleryImage .= '</a>';

		return $galleryImage;
  }

}