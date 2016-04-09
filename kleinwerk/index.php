<?php
  ini_set("arg_separator.output","&amp;");

  include("../cms/core_website.php");
  include("../cms/core_session.php");

  $session  = SESSION::get_session();
  $website  = new website();
  $session->prepareSite($website);

	function getPageXML() {
		global $session;
		global $website;
		$pageXML =  '<page>';
		$pageXML .= '<contenttitle>'.$session->page->getContentTitle().'</contenttitle>';
	  	$pageXML .= '<path>'.$session->page->showPath().'</path>';
		$pageXML .= '<subpages>'.$session->page->showSubPages(1).'</subpages>';
		$contentArray = $session->page->getContent();
		for ( $contentindex = 0; $contentindex <= count($contentArray); $contentindex += 1) {
            if (count($contentArray) > $contentindex) {
			 $pageXML .= '<content>'.$contentArray[$contentindex].'</content>';
            }
		}
		$pageXML .= $session->page->showFunctionality();
		$pageXML .= $session->page->showFunctionalityHeader();
		$pageXML .= '<websitemenu>'.$website->showWebSiteMenu().'</websitemenu>';
		$pageXML .= '<languageswitch>'.$website->showLanguageSwitch().'</languageswitch>';
		$pageXML .= '</page>';
		return $pageXML;
	}

	// replace all links with internal links
	// keeping xml / mobile / language intact

function replaceInternalLinks($website, $xmldoc, $notescapeamps) {
	$elements = $xmldoc->getElementsByTagName('pagelink');
	$i = $elements->length - 1;
	while ($i > -1) {
		$element = $elements->item($i);

		// create new anchor node
		$child = $xmldoc->createElement('a');
    	$value = $child->appendChild($element->firstChild);
		$linkedpage = $website->getPageObjectFromDBID($element->getAttribute("ref"));
		$child->setAttribute('href',$linkedpage->buildLink(false,$notescapeamps));

		$element->parentNode->replaceChild($child, $element);
		$i--;
	}
	return $xmldoc;
}

function sizeImagesToMobile($website, $xmldoc) {
	$elements = $xmldoc->getElementsByTagName('img');
	$i = $elements->length - 1;
	while ($i > -1) {
		$element = $elements->item($i);

		// create new img node
		$child = $xmldoc->createElement('img');

  		// Just a little something to aid local development
  		$referer = $_SERVER["HTTP_HOST"];
		if(stristr($referer , "kleinwerk.dev")) {
			$child->setAttribute('src','/cms/imgsize.php?w=320&img=/users/pklein/Sites/kleinwerk/kleinwerk/'.$element->getAttribute('src'));
		} else {
			$child->setAttribute('src','/cms/imgsize.php?w=320&img=/nfs/vsp/dds.nl/r/rosss/public_html/kleinwerk/'.$element->getAttribute('src'));
		}

		$element->parentNode->replaceChild($child, $element);
		$i--;
	}
	return $xmldoc;
}

function xhtmlToHTML5($XHTMLpage) {

	// https://gist.github.com/bzerangue/3518650
	// determining if output is html document
	$html = $XHTMLpage;
	// splitting up html document at doctype and doc
	$html_array = explode("\n",$html,15);
	$html_doc = array_pop($html_array);
	$html_doctype = implode("\n",$html_array);
	// convert XHTML syntax to HTML5
	// <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	// <!DOCTYPE html>
	$html_doctype = preg_replace("/<!DOCTYPE [^>]+>/", "<!DOCTYPE html>", $html_doctype);
	// <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	// <html lang="en">
	$html_doctype = preg_replace('/ xmlns=\"http:\/\/www.w3.org\/1999\/xhtml\"| xml:lang="[^\"]*\"/', '', $html_doctype);
	// <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	// to this --> <meta charset="utf-8" />
	$html_doctype = preg_replace('/<meta http-equiv=\"Content-Type\" content=\"text\/html; charset=(.*[a-z0-9-])\" \/>/i', '<meta charset="\1" />', $html_doctype);
    // get rid of the first line :<*xml version="1.0"*>
    $html_doctype = preg_replace('!^[^>]+>(\r\n|\n)!','',$html_doctype);

	$html = $html_doctype . "\n" . $html_doc;

	return $html;
}

$mobile_browser = '0';

if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
    $mobile_browser++;
}

if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
    $mobile_browser++;
}

$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
$mobile_agents = array(
    'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
    'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
    'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
    'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
    'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
    'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
    'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
    'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
    'wapr','webc','winw','winw','xda','xda-');

if(in_array($mobile_ua,$mobile_agents)) {
    $mobile_browser++;
}

if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'OperaMini')>0) {
    $mobile_browser++;
}

if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
    $mobile_browser=0;
}

if (isset($_REQUEST['xml']) && $_REQUEST['xml'] == "true" ) {
	header('Content-type: text/xml');
  printf('<?xml version="1.0" ?>
  ');
  printf('<?xml-stylesheet type="text/xsl" href="../kleinwerk/design/xsl/'.$session->page->xsl.'.xsl"?>
  ');
	print(getPageXML());
} else {
	$xml = new DOMDocument;
	$xml->loadXML(getPageXML());

	$xml = replaceInternalLinks($website, $xml, false);

	$xsl = new DOMDocument;

	// check if the browser is a mobile phone
	if($mobile_browser>0 || $session->mobile !='') {
		$xml = sizeImagesToMobile($website, $xml);
		$xsl->load('../kleinwerk/design/xsl/mobile.xsl');
	} else {
		$xsl->load('../kleinwerk/design/xsl/'.$session->page->xsl.'.xsl');
	}

	// Configure the transformer
	$proc = new XSLTProcessor;
	$proc->importStyleSheet($xsl); // attach the xsl rules

	$getPage = $proc->transformToDoc($xml);
	$getPage = replaceInternalLinks($website, $getPage, true);
	$getPage->formatOutput = true;

	$XHTMLpage = $getPage->saveXML();

	$HTML5page = xhtmlToHTML5($XHTMLpage);

	print($HTML5page);
}
?>
