<?php
/* Article.inc
 *
 * Abstract class :  functionality.inc
 * For a description of the public methods of this object see functionality.inc;
 *
 * showArticleGroupSelector()
 * showArticles()
 *
 *
 * What would be Mighty cool to do
 *
 * extends Articles as domDocument ( http://trash.chregu.tv/phpconf2003/examples/src/extending.php )
 * And Use Articles as domDocument, and do methods like save and load
 * Why not now ? : Because we are already extending this class, and PHP only has one override method.
 */

require_once '../cms/core_content.php';
require_once 'func_abstract.php';

class Articles extends domDocument implements Functionality {

  public $nrArticlesonPage = 8;
  public $messages;

	public $r__articleid;
	public $r__artpage;

  public $currentarticle;

	function __construct($pageid) {
	  $this->currentPageid = $pageid;

	if (isset($_REQUEST['articleid']) && $_REQUEST['articleid'] != "" ) {
		$this->r__articleid = $_REQUEST['articleid']; 
	} else {
		$this->r__articleid = "";   	
	}
    
	if (isset($_REQUEST['artpage']) && $_REQUEST['artpage'] != "" ) {
		$this->r__artpage = $_REQUEST['artpage']; 
	} else {
		$this->r__artpage = "";   	
	}    

	  $this->loadArticles();

	  if ($this->r__articleid != '')
			if (count($this->messages) > 0)
				if (array_key_exists($this->r__articleid, $this->messages))
					$this->currentarticle = $this->messages[$this->r__articleid];
  }

  function Header(){}

  function Show() {
    $showfunctionality = '';
    if ( SESSION::get_session()->mode == "edit" || SESSION::get_session()->mode == "preview")
    	$showfunctionality .= '<postvalues>'.$this->getExtraPostValues().'</postvalues>';
    if ($this->currentarticle) {
      $showfunctionality .= '<article>'.$this->currentarticle->showFullArticle().'</article>';
      // $showfunctionality .= $this->currentarticle->Contact->show();
    } else
      $showfunctionality .= '<articles>'.$this->showArticles().'</articles>';

    return $showfunctionality;
  }

  function createArticle() {
    $newArticle = new Article($this,'');
    // put the article as first in the array.
		$this->messages = array($newArticle->getID() => $newArticle) + $this->messages;
		$this->r__articleid = $newArticle->getID();
		$this->currentarticle = $newArticle;
    reset($this->messages);
    return $newArticle;
  }

  function showPageContent() {
    return ($this->r__articleid == "");
  }

	function getFuncPath() {
	  $funcPath = '';
		if ($this->r__articleid != '')  {
			$title = $this->messages[$this->r__articleid]->getTitle();
			$funcPath .= ' &gt; '.$title;
		}
	  return $funcPath;
	}

/*
			<h2>{$this->currentPageid->website->language["month"]}</h2>
			{$this->showArticleGroupSelector()}
*/

	function getExtraQSValues() {
		if ($this->r__artpage != '')
			$getExtraQSValues = '&amp;artpage='.$this->r__artpage;
		if ($this->r__articleid != '')
		$getExtraQSValues .= '&amp;articleid='.$this->r__articleid;
		return $getExtraQSValues;
	}

  function loadArticles() {
    $webname = $this->currentPageid->website->webname;

		// This magically little whitespace thing will prevent whitespaces to be seen as domnodes..
 		$this->preserveWhiteSpace = false;
		// TODO : webname zou dan in het pad moeten van de lokatie van de XML file.
 		$this->load('../kleinwerk/data/articles.xml');

 	  if (SESSION::get_session()->mode == "edit" )
      $strPublished = "";
    else
      $strPublished = "[@published='1' and position()<3]";

		$xp = new domxpath($this);
		$articles = $xp->query("//articles/article".$strPublished);

		foreach ($articles as $article) {
		  $messages[$article->getAttribute("id")] = new Article($this,$article);
		  // putt all dbID to Object in a sessionmapping for recovery
			SESSION::get_session()->dbmapping[$article->getAttribute("id")] = $messages[$article->getAttribute("id")];
		}

    // TODO : WOAHOAHAHHHHHHHHHHHHH shitty php compiler..
    // Okay, when iterating all the article nodes and putting them in the array,
    // the first item always got lost.., first their was this assignment in the array :
    // $this->messages[$article->getAttribute(".......
    // now their is this assignment
    // $messages[$article->getAttribute(".......
    // The '$THIS->' factor screws it ALL UP !!!, so now we put it in a normal array, and then transfer it
    // to the public array of this object.

		$this->messages = $messages;

  }

	function saveArticles() {
		$this->formatOutput = true;
		$this->save('../kleinwerk/data/articles.xml');
	}

  	function showArticles() {
		$messages = '';
		foreach ($this->messages as $message)
	  		$messages .= $message->showArticle();

		/* This can also be done by Using a XSLT on the articles.xml data.
		 * It's just that not all things can be done in XSL
		 * Like formatting dates, or other spiffy things
		 * For now we just loop the messages tot generate XHTML code, and format this as we wish
		 */
		/*
		$xml = $this;

		$xsl = new DOMDocument;
		$xsl->load('../kleinwerk/data/articles.xsl');

		// Configure the transformer
		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl); // attach the xsl rules

		$artpage = $_REQUEST['artnumber'];

		if ($artpage=='')
		  $artpage = '0';

		$proc->setParameter('', 'pageNumber', $artpage);
		$proc->setParameter('', 'link', $this->currentPageid->buildLink(false,true));
		$proc->setParameter('', 'mode', SESSION::get_session()->mode);
		$proc->setParameter('', 'langid', SESSION::get_session()->langid);
		$messages = $proc->transformToXML($xml);
		*/
		return $messages;
	}
}

class Article extends Content {

	var $articles;
	var $website;
	var $articleEl;
	var $contact;

	function __construct($articles,$articleEl) {
	    $this->articles = $articles;
	    $this->website  = $articles->currentPageid->website;

	    if ($articleEl != '') {
			$this->articleEl = $articleEl;
		} else {
			$this->createcontentInDB();
		}

	    // Load the values of this page from the XML.
			$this->loadContent();
 	}

  function loadContent() {
		// $message = $this->articles->getElementById($articleDBID);
		// printf('asdasd :'. $message);

		$xp = new domxpath($this->articles);

    	$title = $xp->query("title_".SESSION::get_session()->langid,$this->articleEl);
    	$content = $xp->query("content_".SESSION::get_session()->langid,$this->articleEl);
		$published = $xp->query("published",$this->articleEl);
		$pubDate = $xp->query("pubDate",$this->articleEl);
		$link = $xp->query("link",$this->articleEl);

		$this->contentid = $this->articleEl->getAttribute("id");
		$this->contenttitle = $title->item(0)->nodeValue;
		$this->published = (boolean) $this->articleEl->getAttribute("published");
		$this->pubDate = $pubDate->item(0)->nodeValue;
		$this->link = $link->item(0)->nodeValue;

		// $this->contentcontent = $this->articles->saveXML($content->item(0));
		$articlecontent = $content->item(0);
		$this->contentcontent = $this->innerXML($articlecontent);
  }

  function showArticle() {
        $article = '';
		if ($this->getContent() != '' || SESSION::get_session()->mode == "edit" ) {
			if (!$this->published)
				$article  = '<div class="unpublished">';
			$article .= '<a href="'. $this->link .'" class="articlelink">';
      		// $article .= '<p class="date">'. date("D j M", strtotime($this->pubDate)) .'</p>';
			$article .= '<h2>'. $this->getTitle() .'</h2>';
			$article .= $this->getContent();
			$article .= '</a>';

			if (!$this->published)
				$article .= '</div>';
		}
		return $article;
  }

  function showFullArticle() {

    // We dont create the contact form for every article, since only with the full article
    // the contacts are show, so we only create the contacts when displaying fullarticle
  	// show the contact form
		// $this->Contact = new contact($this->articles->currentPageid);

    // TODO : show prev next buttons, als article full wordt getoond
		$article  = '<div class="item">';
      	$article .= '<p class="date">'. date("D j M", strtotime($this->pubDate)) .'</p>';
		$article .= $this->getContentTitle();
		$article .= $this->getContent();
		$article .= '</div>';
		return $article;
  }
}
?>