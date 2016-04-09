<?php

class SESSION {
  static private $session;
  public $langid;							// the language we are viewing the site in.
  public $xmlview;             				// you want the xml instead of the HTML, okay..   
  public $mobile;							// Is the page viewed on a Iphone, .. ehh mobile phone.
  public $content;							// the object that currently is been edited ( page, image, article )
  public $page;								// the current pageObject 
  public $mode;								// current mode you are in.
  private $cei;  							// currently editable ID ( yeah, I know, I've shouldn't made it a abbreviation )
  public $dbmapping = array();				// maps all content db ids on objects

  private function __construct() {
	if (isset($_REQUEST['langid']) && $_REQUEST['langid'] != "" ) {
		$this->langid = $_REQUEST['langid']; 
	} else {
		$this->langid = "nl";   	
	}
    
    if (isset($_REQUEST['xml']) && $_REQUEST['xml'] != "" ) {
      $this->xml = $_REQUEST['xml']; 
    } else {
      $this->xml = "";     
    }
  
    if (isset($_REQUEST['mobile']) && $_REQUEST['mobile'] != "" ) {
      $this->mobile = $_REQUEST['mobile']; 
    } else {
      $this->mobile = "";     
    }
    
    if (isset($_REQUEST['page']) && $_REQUEST['page'] != "" ) {
	    $this->cei = $_REQUEST['page']; 
    } else {
	$this->cei = ""; 
    }    

	}

  function prepareSite($website) {
    if (isset($_REQUEST['page']) && $_REQUEST['page'] != '' ) { 
  	  $pageobject = $website->getPageObjectFromDBID($_REQUEST['page']);
  	} else {
        $pageobject = $website->homepage;  	  
  	}
    // From now on you now what the requested pageobject is , but wait.. !
    // It still is possible that this is a reference to another page.
    // F.E. you have a level1 page, which contains subs. You don't want the 
    // level 1 page to be shown, but instead show the first of the subpages 
    /*
	if ($pageobject->pagereference != '') { 
  	  	$pageobject = $website->getPageObjectFromDBID($pageobject->pagereference);
	}
    */
	
	// Now that we have the page on which we are editing on
	$this->page = $pageobject;
  }
  
  // singleton function
	static function get_session()
	{
		if ( ! isset(SESSION::$session) )
			SESSION::$session = new SESSION();
		return SESSION::$session;
	}
}
?>