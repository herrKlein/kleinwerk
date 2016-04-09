<?php

include("../cms/core_page.php");   

class Website {

  var $sitestyle;
  var $homepage;
  var $language = array();
  var $listOfPages = array();
  var $functionalities = array();
	public $pageDocument;

  // website settings
	var	$webname;
	var	$imgthumb_dir;
	var	$img_dir;
	var	$misc_dir;
	var	$db_prefix;
	var	$context_path;

	function __construct() {
//  $this->hostSettings = new SystemComponent();

		$this->sitestyle = "default";
		
		$this->webname = "kleinwerk";	
		$this->imgthumb_dir = "../".$this->webname."/data_img/upload/thumbs/";
		$this->img_dir = "../".$this->webname."/data_img/upload/pictures/";
		$this->misc_dir = "../".$this->webname."/data_img/upload/misc/";		
		$this->db_prefix = $this->webname;
		$this->context_path = "/".$this->webname; 		

		// XML Solution
		// get the homepage !
 		$this->preserveWhiteSpace = false; 
		// TODO : webname zou dan in het pad moeten van de lokatie van de XML file. 		 		
		$this->pageDocument = new domDocument;
 		$this->pageDocument->load('../kleinwerk/data/pages.xml');
		$xp = new domxpath($this->pageDocument);
		$pages = $xp->query("//pages/page");
		$homepageinfo = $pages->item(0)->getAttribute('id');

		$this->getAllFunctionalities();
		$this->homepage = new Page($this,$homepageinfo,'',0,array(),0);
		// create the entire page tree
   	$this->getAllPageObjects($this->homepage);
		$this->getAllLanguageLabels();
	}

	// The function wants a databaseId from the page, and returns the already created object !! great !!
	function getPageObjectFromDBID($dbid) {
       // echo('TEST'.$dbid);
        return $this->listOfPages[$dbid];
	}

	// this is the most spoken of function in the whole site
	// it creates A LOT of overhead and Queries.. 
	// BUT !!!!!!!!!
	// Code get MUCH MUCH cleaner and MUCH MORE readible !!
	// Oh yeah, the function is recursive... scary shit man !
  // delivers all pageObjects below the searchFromPage in an array
  
  public function getAllPageObjects($searchFrom) {
    unset($this->listOfPages);
    $this->listOfPages[$this->homepage->getID()] = $this->homepage;
	  // putt all dbID to Object in a sessionmapping for recovery    
    SESSION::get_session()->dbmapping[$this->homepage->getID()] = $this->homepage;
    $this->getAllPageObjectsRecursive($searchFrom);
    return $this->listOfPages;
  }
  
	private function getAllPageObjectsRecursive($searchFrom) {
		foreach ($searchFrom->subpages as $page) 
		{ 
			$this->listOfPages[$page->getID()] = $page;
		  // putt all dbID to Object in a sessionmapping for recovery			
			SESSION::get_session()->dbmapping[$page->getID()] = $page;			
			if (count($page->subpages) > 0) { 			
				$this->getAllPageObjectsRecursive($page);
			}
			$loop_item = '';    		
		}
	}

  function savePages() {
		$this->pageDocument->formatOutput = true;
 		$this->pageDocument->save('..\kleinwerk\data\pages.xml');		
  }  

	function getAllFunctionalities() {
        $functionalitiesXML = new DOMDocument();
	    $functionalitiesXML->load('../cms/functionalities.xml');
        
        if (!$functionalitiesXML) {
            echo("Failed to parse XML\n");
            return false;
        }        
        
		$functionalitiesXML->preserveWhiteSpace = false;
		$functionalities = $functionalitiesXML->getElementsByTagName("functionality");
		foreach ($functionalities as $functionality) {
	  	$this->functionalities[$functionality->getAttribute("id")] = $functionality->nodeValue; 	
		}		
	}

	function getAllLanguageLabels() {
    // Determine current language
		$keywordlang = "keyword_".SESSION::get_session()->langid;

	  // Get all language labels from XML file // TODO : relative path
        $languagelabelsXML = new DOMDocument();	  
	       $languagelabelsXML->load('../cms/language.xml');
           
        if (!$languagelabelsXML) {
            echo("Failed to parse XML\n");
            return false;
        }            
           
		$languagelabelsXML->preserveWhiteSpace = false;

    // WHAT ! ? a counter ? this can be done a lot easier !
    // Yep, I thought so, but how ? documentation is not much around for php5 dom programming.
    // Okay, lets keep it this way then till a better solution comes around.
		
		$keywords = $languagelabelsXML->getElementsByTagName("keyword");
		$keywordslang = $languagelabelsXML->getElementsByTagName($keywordlang);
		$counter = 0;
		foreach ($keywords as $keyword) {
		  $this->language[$keyword->nodeValue] = $keywordslang->item($counter)->nodeValue;
		  $counter = $counter + 1;
		}
	}

	function getHomepage() {
    return $this->homepage;
	}

	function showLanguageSwitch() {
	  $languageSwitch = '';

    $selected = '';
    if ( SESSION::get_session()->langid == "en" ) { 
    	$selected = ' class="langselected"';   
    	$languageSwitch .= '<a href="'.SESSION::get_session()->page->buildLink(false,false).'&amp;langid=nl" id="nl"'.$selected.'>&gt; nederlands</a>';
    }
    $selected = '';
    if ( SESSION::get_session()->langid == "nl" ) { 
    	$selected = ' class="langselected"'; 
    	$languageSwitch .= '<a href="'.SESSION::get_session()->page->buildLink(false,false).'&amp;langid=en" id="en"'.$selected.'>&gt; english</a>';
    }		
		return $languageSwitch;
  }	
	
	function showWebSiteMenu() {
	  // get all subpages from homepage
	  $allHomepageSubpages = $this->homepage->subpages;
	  // include the homepage in this menu
	  // $webSiteMenu = '<li>'.$this->homepage->buildLink(true,true).'</li>';
      $webSiteMenu = '';
	  if (count($allHomepageSubpages) > 0) {
			foreach ($allHomepageSubpages as $currentPage)
			{
				$webSiteMenu .= '<li>'.$currentPage->buildLink(true,false).'</li>';
			}
			return $webSiteMenu;
		}
	}
}
?>