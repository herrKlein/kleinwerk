<?php
require_once '../cms/core_content.php';

class Page extends Content {

  public $parent;  						// link to parent object
  public $pageorder;					// order in which the subpages of this page appear
  public $functionalities = array();
  public $functionalitiesnames = array();		// The functionality of this page
  public $website; 						// the website that this page belongs to
  public $depth; 						  // depth of the page
  public $xsl; 						  // The xsl stylesheet in which this page is rendered
  public $parents = array();	// all parents of this page in an array;
  public $subpages = array(); // array with subpage objects
  public $external; // if string is filled with external address, this link is external
  public $pagereference; // if a pagereference is given not this page but the reference is shown

  /********************************
   *
   *	Constructor
   *
	 * 	$website		website object
	 *	$pageDBID		Database id of the page in SQL table
	 *	$parent			parent object
	 *	$depth			depth
	 *	$parents		array of parent objects
	 *	$pageorder	order in which this page appears
	 *
   ********************************/

	function __construct($website, $pageDBID, $parent, $depth, $parents, $pageorder) {
	$this->website = $website;
	$this->depth = $depth;
	$this->parents = $parents;
	$this->pageorder = $pageorder;
	$this->pageDBID = $pageDBID;

			if ($parent != '')
	  $this->parent = $parent;

	if ($pageDBID == '') {
	    $pageDBID = $this->createContentInDB();
	  }

		// Load the values of this page from DB, and load the cascading subpages
	 	$this->loadContent($pageDBID);
	    $this->subpages = $this->loadSubPages();

	    // I heard about a feature that when calling objects which can not be find
		// PHP5 will look for a filename and include the matching filename with object name
		// .. hmmm .. thats cool
		// Yeah, if I can get it to work that is !
	    $this->assignPageFunctionality();
	  }

  function loadContent($pageDBID) {
    // Content::loadContent($pageDBID);

    	$langid = SESSION::get_session()->langid;
		$webname = $this->website->webname;

		$xp = new domxpath($this->website->pageDocument);
		$pageEl = $xp->query("//*[@id = '".$pageDBID."']");
		$page = $pageEl->item(0);

		$external = $xp->query("external",$page);
		$this->external = $external->item(0) && $external->item(0)->nodeValue;

		$pagereference = $xp->query("pagereference",$page);
		$this->pagereference = $pagereference->item(0) && $pagereference->item(0)->nodeValue;

    	$title = $xp->query("title_".SESSION::get_session()->langid,$page);

    	$content = $xp->query("content/xhtml[@language = '".SESSION::get_session()->langid."']",$page);
   		$functionality = $xp->query("functionality/id",$page);

		$published = $xp->query("published",$page);
		$creationdate = $xp->query("creationdate",$page);
		$xsl = $xp->query("xsl",$page);

		$this->contentid = $page->getAttribute("id");
		$this->contenttitle = $title->item(0)->nodeValue;
		// Fatal error: Only variables can be passed by reference in /nfs/vsp/dds.nl/r/rosss/public_html/cms/core_page.inc on line 136
		// So I put this in a variable
		for ( $contentindex = 0; $contentindex < $content->length; $contentindex += 1) {
			$firstitem = $content->item($contentindex);
			$this->contentcontent[$contentindex] = $this->innerXML($firstitem);
		}
		$this->published = (boolean) $page->getAttribute("published");
		$this->creationdate = $creationdate->item(0) && $creationdate->item(0)->nodeValue;
		$this->xsl = $xsl->item(0)->nodeValue;
		for ( $functionalityindex = 0; $functionalityindex < $functionality->length; $functionalityindex += 1) {
			$pagefuncid = $functionality->item($functionalityindex)->nodeValue;
            if ($pagefuncid != 0) {
			     $this->functionalitiesnames[$pagefuncid] = $this->website->functionalities[$pagefuncid];
		    }
        }
  }

  // get all pages below this page in an 'Array' of objects
	function loadSubPages() {
	$subpages = array();
 	  if (SESSION::get_session()->mode == "edit" || SESSION::get_session()->mode == "preview" )
      $strPublished = "";
    else
      $strPublished = "[@published='1']";

		$xp = new domxpath($this->website->pageDocument);
		$pageEl = $xp->query("//*[@id = '".$this->getID()."']");
    	$pages  = $xp->query("page".$strPublished,$pageEl->item(0));

		$order = 0;
		foreach ($pages as $pageinfo) {
			$subpages[$pageinfo->getAttribute('id')] = new Page($this->website, $pageinfo->getAttribute('id'), $this, $this->depth + 1, $this->parents + array($this->depth => $this),$order);
			$order = $order + 1;
		}
    return $subpages;
	}

  /********************************
   *
   *	Edit Functionality
   *
   ********************************/

  function assignPageFunctionality() {
	  $funcindex = 0;
      foreach ($this->functionalitiesnames as $functionalityname) {

     		if ($functionalityname != "") {
			// include the file containing the functionality of this page

			require_once("../cms/func_".$functionalityname.".php");
			// create an object of the functionality of this page, classname = filename - '_';
			$this->functionalities[$funcindex] = eval(sprintf(" return new ".$functionalityname."(\$this);"));
		}
		$funcindex += 1;
  	  }
	}

	function showFunctionality() {
		$functionalityXML = '';
		foreach ($this->functionalities as $functionality) {
			$functionalityXML .= '<functionality>'.$functionality->show().'</functionality>';
		}
	  	return $functionalityXML;
	}

	function showFunctionalityHeader() {
		$functionalityXML = '';
		foreach ($this->functionalities as $functionality) {
			$functionalityXML .= '<functionalityheader>'.$functionality->header().'</functionalityheader>';
		}
	  	return $functionalityXML;
	}
  /********************************
   *
   *	Show Functionality
   *
   ********************************/

	function getContent() {
		$content = Content::getContent();
    	return $content;
    }

	function getContentTitle() {
		$title = Content::getContentTitle();
    	return $title;
	}

	function buildLink($complete,$notescapeamps) {
		// sometimes its not wishfull to escape the amps here
		// cause the XML will escape them another time making
		// from an &amp; to &amp;amp;, see index.html
		$ampesc = '&amp;';
		if ($notescapeamps == 1) {
			$ampesc = '&';
    	}
		$qs ='';
		if (SESSION::get_session()->mode != "" ) {
			$qs = $ampesc."mode=".SESSION::get_session()->mode;
	  	}
	    if (SESSION::get_session()->langid != "" ) {
	      $qs .= $ampesc."langid=".SESSION::get_session()->langid;
	    }
	    if (SESSION::get_session()->xml != "" ) {
	      $qs .= $ampesc."xml=".SESSION::get_session()->xml;
	    }
	    if (SESSION::get_session()->mobile != "" ) {
	      $qs .= $ampesc."mobile=".SESSION::get_session()->mobile;
	    }
  		if ($this->external != "") {
			$link = $this->external;
			$link = '<a href="'.$link.'" '.$selected.'>'.$this->getTitle().'</a>';
		  	if ( !$this->published )
				$link = '<em>'.$link.'</em>';
  		} else {
			$link = 'index.php?page='.$this->getID().$qs;
			if ($complete) {
				$selected = '';
				if ( $this->getID() == SESSION::get_session()->page->getID() )
				$selected = 'class="selected"';
		  		$link = '<a href="'.$link.'" '.$selected.'>'.$this->getTitle().'</a>';
		  		if ( !$this->published )
				$link = '<em>'.$link.'</em>';
			}
  		}
		return $link;
	}

	function showPath() {
  		$path = '';
	  	foreach ($this->parents as $parentpage) {
			$path .= '<span> &gt; '.$parentpage->buildLink(true,false).'</span>';
	    }
//	    if ($this->pagefuncname != "")
//			$pathfunc = $this->functionality->getFuncPath();
//	    if ($pathfunc != "")
//    		$path .= '<span> &gt; '.$this->buildLink(true,true).'</span>'.$pathfunc;
//    	else
    	$path .= '<span> &gt; '.$this->getTitle().'</span>';
    	return $path;
	}

	function showSubPages($level) {
        $subPages ='';
		$parents = $this->parents + array($this->depth => $this);
		if (array_key_exists($level, $parents)) {
// 		TODO : a while loop will be a lot better cause then we don't
//		have to check if the array is empty, I can't get it to work though.
//		while (list($key, $value) = each($this->parents)) {
			if (count($parents[$level]->subpages) > 0) {
				// $subPages = '<h2>'.$this->website->language["related"].'</h2>';
				foreach ($parents[$level]->subpages as $page) {
					$subPages .= '<li>'.$page->buildLink(true,false).'</li>';
				}
			}
	  }
	  return $subPages;
	}
}
?>