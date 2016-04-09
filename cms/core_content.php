<?php
class content {

  public $contenttitle;
  public $contentcontent = array(); // The content consists of several elements
  public $published;
  public $contentid;
  public $contentdate;  
  public $website; 
  
  /********************************
   *
   *	Constructor
   *
   ********************************/

	function __construct($website, $contentDBID) {
    $this->website = $website;
  
    if ($contentDBID == '') {
	    $contentDBID = $this->createcontentInDB();
	  }
	  
    // Load the values of this page from DB, and load the cascading subpages	  
		$this->loadContent($contentDBID);
  }

  /*******************************************************************
   * This should really be in the standard php functions   
   *******************************************************************/
	function innerXML(&$node){
	   if(!$node) return false;
	   $document = $node->ownerDocument;
	   $nodeAsString = $document->saveXML($node);
	   $match = '';
	   preg_match('!\<.*?\>(.*)\</.*?\>!s',$nodeAsString,$match);
	   return $match[1];
	} 
  
  /********************************
   *
   *	Show Functionality
   *
   ********************************/

	function getTitle() {  
	  return html_entity_decode($this->contenttitle);
	}

	// The getContentTitle version is different form getTitle cause its just used in the contentframe
	// ( ie NOT for paths, menus , links )
	// and the title is editable when in editmode !!
	function getContentTitle() {  
  	$titlebox = htmlentities($this->contenttitle);	  
	  /*
	  if (SESSION::get_session()->mode == "edit" && SESSION::get_session()->content->getID() == $this->getID()) {
   	  	$titlebox  = '<input type="text" name="contenttitle" class="h1" value="'. $titlebox .'" />';      
	  }
	  */	
	  if ($titlebox!='') {
	  	$titlebox = '<h1>'.$titlebox.'</h1>';
	  	
	  }  
		return $titlebox;
	}

	function getContent() {
	  /*
	  if (SESSION::get_session()->mode == "edit" && SESSION::get_session()->content->getID() == $this->getID()) {
      	$contentContent = '<textarea id="iTextarea" name="contentcontent" class="widgEditor nothing">'. $this->contentcontent .'</textarea>';
	  }
	  */	  
	  return $this->contentcontent;
	}

	function getID() {  
	  return $this->contentid;
	}
}
?>