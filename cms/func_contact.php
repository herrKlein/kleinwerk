<?php
require_once 'func_abstract.php';

class Contact extends domDocument implements Functionality {

  var	$newmessage;
  var $nrGuestbookonPage = 10;

	function __construct($pageid) {

		date_default_timezone_set('Europe/Berlin');
	  $this->currentPageid = $pageid;

    $webname = $this->currentPageid->website->webname;

		// This magically little whitespace thing will prevent whitespaces to be seen as domnodes..
 		$this->preserveWhiteSpace = false;
		// TODO : webname zou dan in het pad moeten van de lokatie van de XML file.
 		$this->load('../kleinwerk/data/guestbook.xml');

  }

  function Header(){}

  function Show() {
    $showfunctionality = '';

    if ( SESSION::get_session()->mode == "edit" || SESSION::get_session()->mode == "preview")
    $showfunctionality .= '<postvalues>'.$this->getExtraPostValues().'</postvalues>';
  	$showfunctionality .= '<newmessage>'.$this->checkNewMessage().'</newmessage>';
    // $showfunctionality .= '<contactMessages>'.$this->showContactMessages().'</contactMessages>';

    return $showfunctionality;
  }

	function getFuncPath() {
	  $funcPath = '';
	  return $funcPath;
	}

	function getFuncMetaButtons($disabled) {
		$funcMetaButtons = '';
		return $funcMetaButtons;
	}

  function showPageContent() {
		return true;
	}

  function checkNewMessage() {
    $contactfrom = '';


		$this->newmessage = new contactMessage($this,'');

		if (isset($_POST['submitguestbook']) && $_POST['submitguestbook'] != "" || isset($_POST['submitemail']) && $_POST['submitemail'] ) {

		  if (isset($_REQUEST['message']) && $_REQUEST['message'] != "") {
			// !!!! Watch out, something probably isn't allright.. SPAMBOTS..
			// This field is not seen by end users, but is seen by BOTS..
			// If it contains text, we almost certainly now a spambot has been busy.
			// Send myself a "spambot has been busy mail"
			$msg = "First Name:\t".$_REQUEST['name']."\n";
			$msg .= "Message:\t".$_REQUEST['message']."\n\n";
			// I tried, but no spaces allowed in these headers, so strip them
			$mailheaders = "From:My_Web_Site\r\n";
			$mailheaders .= "Reply-To:".str_replace(" ", "", $this->email )."\r\n";
			return mail("rosss@dds.nl", "SPAMBOTS !!!!", $msg, $mailheaders);
		  }

		  $this->newmessage->getValuesFromForm();
			if ($_REQUEST['name'] != "" and $_REQUEST['message_dmy'] != "" and $_REQUEST['message'] == "") {

				if($_POST['submitemail']) {
					$success = $this->newmessage->sendMessageAsMail();
				 	// since guestbook is gone, and mail is not reliable all the time
					// we keep a copy of the send message in our guestbook.xml
				  	$this->newmessage->insertMessageInDB();
				}
/*
				if ($_POST['submitguestbook']) {
				  $this->newmessage->insertMessageInDB();
				}
*/
				if ($success == "1") {
					$contactfrom .= '<p>bedankt voor uw reactie</p>';
					$contactfrom .= $this->newmessage->showContactMessage();
					// everything went well, so empty the contactmessage by calling the constructor.
					$this->newmessage->__construct($this,'');
					$contactfrom .= $this->newmessage->editContactForm($this->currentPageid,"","");
				}


			} else {
				// there is something missing, notify the user, and keep the filled fields.
				$missing_name = "";
				$missing_message = "";
				if ($_REQUEST['name'] == "")    { $missing_name    = "<em>missing</em>"; }
				if ($_REQUEST['message_dmy'] == "") { $missing_message = "<em>missing</em>"; }
				$contactfrom .= $this->newmessage->editContactForm($this->currentPageid,$missing_name,$missing_message);
			}
		} else {
			$missing_name = "";
			$missing_message = "";
		  	$contactfrom .= $this->newmessage->editContactForm($this->currentPageid,$missing_name,$missing_message);
		}
		return $contactfrom;
	}

  function getExtraPostValues() {
		$extraPostValues = '<input type="hidden" name="gbpage" value="'.$_REQUEST['gbpage'].'" />';
    return $extraPostValues;
  }

  function getExtraQSValues() {
    $getExtraQSValues = '&amp;gbpage='.$_REQUEST['gbpage'];
    return $getExtraQSValues;
  }

  function saveContact() {
		$this->formatOutput = true;
 		$this->save('../kleinwerk/data/guestbook.xml');
  }

  function showMessageGroupSelector() {
/*
    $webname = $this->currentPageid->website->webname;
		$result = DB::get_db()->query("select count(*) from " . $webname . "_replies");
		$nrofPages = ceil(mysql_result($result ,0) / $this->nrGuestbookonPage);

		$contactSelector;
		$contactSelector = '<ul>';
		for ($i = 0; $i < $nrofPages; $i++) {
			$contactSelector .= '<li><a href="'. $this->currentPageid->buildLink(false) .'&amp;gbpage='. $i .'" ';
			if ($_REQUEST['gbpage'] == $i) { $class='class="pageselected"'; } else { $class=''; }
			$contactSelector .= $class . '>';
			$contactSelector .= ($i + 1).'</a></li>';
		}
		$contactSelector .= '</ul>';
		return $contactSelector;
*/
  }

  function showContactMessages() {

    $showContactMessages = '';

		$xp = new domxpath($this);
		$gbmessages = $xp->query("//guestbook/guestbookmessage");

		foreach ($gbmessages as $gbmessage) {
		  $message = new contactMessage($this,$gbmessage);
		  $showContactMessages .= $message->showContactMessage();
		}
	return $showContactMessages;
  }
}


class contactMessage {

  var	$name;
  var	$email;
  var	$homepage;
  var	$message;
  var $curdate;
  var $contactmessageEl;

	function __construct($contact,$contactmessageEl) {

		$this->name = "";
		$this->email = "";
		$this->homepage = "";
		$this->message = "";
		$this->curdate = date("Y-m-d G:i:s");

    $this->contact = $contact;
    $this->website  = $contact->currentPageid->website;

    if ($contactmessageEl != '') {
	    $this->contactmessageEl = $contactmessageEl;
	    // Load the values of this page from the XML.
			$this->loadContent();
	  } else {
	    $this->contactmessageEl='';
	  }
  }

  function loadContent() {

		$xp = new domxpath($this->contact);

    $message = $xp->query("message",$this->contactmessageEl);
    $homepage = $xp->query("homepage",$this->contactmessageEl);
		$creationdate = $xp->query("creationdate",$this->contactmessageEl);
		$name = $xp->query("name",$this->contactmessageEl);
		$email = $xp->query("email",$this->contactmessageEl);
		$reply = $xp->query("reply",$this->contactmessageEl);

		$this->message = $message->item(0)->nodeValue;
		$this->homepage = $homepage->item(0)->nodeValue;
		$this->creationdate = $creationdate->item(0)->nodeValue;
		$this->name = $name->item(0)->nodeValue;
		$this->email = $email->item(0)->nodeValue;
		$this->guestbookreply = $reply->item(0)->nodeValue;
  }

  function getValuesFromForm() {
		$this->name = $_REQUEST['name'];
		$this->email = $_REQUEST['email'];
		$this->homepage = $_REQUEST['homepage'];
		$this->message = $_REQUEST['message_dmy'];
  }

  function sendMessageAsMail() {
		$msg = "First Name:\t".$this->name."\n";
		$msg .= "Sender's E-mail Address:\t".$this->email."\n";
		$msg .= "Sender's Homepage:\t".$this->homepage."\n";
		$msg .= "Message:\t".$this->message."\n\n";
		// I tried, but no spaces allowed in these headers, so strip them
		$mailheaders = "From:My_Web_Site\r\n";
		$mailheaders .= "Reply-To:".str_replace(" ", "", $this->email )."\r\n";
		return mail("rosss@dds.nl", "Feedback Form", $msg, $mailheaders);
	}

	function safeEscapeString($string)
	{
	 if (get_magic_quotes_gpc()) {
		 return $string;
	 } else {
		 return mysql_escape_string($string);
	 }
	}

  function insertMessageInDB() {

		// TODO : get lastChilds ID !!!!
		// THIS REALLY SHOULD WORK !!!.. WEIRDD..
		// dan maar een xpadje..
		// $this->articles->documentElement->lastChild->getAttribute("id")

		$xp = new domxpath($this->contact);

    $firstGbMessage = $xp->query("/guestbook/guestbookmessage[1]");
		$lastid = $firstGbMessage->item(0)->getAttribute("id");
		$splittedid = explode('_', $lastid);
		$splittedid[1]  = $splittedid[1] + 1;
		$newid = implode("_", $splittedid);

    // maak een nieuw record
		$newGbMessage = $this->contact->documentElement->insertBefore(new domElement('guestbookmessage'),$firstGbMessage->item(0));
		$newGbMessage->setAttribute('id', $newid );
		$newGbMessage->appendChild(new domElement('message', $this->message));
		$newGbMessage->appendChild(new domElement('homepage', $this->homepage));
		$newGbMessage->appendChild(new domElement('creationdate', date("Y-m-d G:i:s")));
		$newGbMessage->appendChild(new domElement('name', $this->name));
		$newGbMessage->appendChild(new domElement('email', $this->email));
		$newGbMessage->appendChild(new domElement('reply', ""));
		// $this->articleEl = $newArticle;

		$this->contact->saveContact();

		return $newid;
  }

  function editContactForm($currentPageid, $missing_name, $missing_message) {

  	$editContactForm = '';

  	$alldisabled = '';

    if (SESSION::get_session()->mode == "edit") {
    	$alldisabled = 'disabled="disabled" class="disabled"';
    } else {
    	$editContactForm .= '<form method="post" id="contactform" action="index.php?page='.$currentPageid->getID().'">';
    }
				$editContactForm .= '<input type="hidden" name="langid" value="'.SESSION::get_session()->langid.'" />';
				$editContactForm .= '<fieldset>';
				$editContactForm .= '<label for="name" accesskey="n"><u>n</u>ame * : '.$missing_name.'</label>';
				$editContactForm .= '<input '.$alldisabled.' id="name" name="name" tabindex="1" type="text" value="'.$this->name.'" /><br />';
				$editContactForm .= '<label for="email" accesskey="e"><u>e</u>mail : </label>';
				$editContactForm .= '<input '.$alldisabled.' id="email" name="email" tabindex="2" type="text" value="'.$this->email.'" /><br />';
				$editContactForm .= '<label for="homepage" accesskey="w"><u>w</u>eb : </label>';
				$editContactForm .= '<input '.$alldisabled.' id="homepage" name="homepage" tabindex="3" type="text" value="'. $this->homepage .'" /><br />';
				$editContactForm .= '<label for="message_dmy" accesskey="m"><u>m</u>essage * : '.$missing_message.'</label>';
				$editContactForm .= '<textarea '.$alldisabled.' id="message_dmy" name="message_dmy" cols="10" rows="5" tabindex="4">'.$this->message.' </textarea><br />';
				$editContactForm .= '<textarea id="message" name="message" cols="30" rows="2"></textarea>';
				$editContactForm .= '<input '.$alldisabled.' id="emailsubmit" type="submit" name="submitemail" value="email" tabindex="5"/>';
				// $editContactForm .= '<input '.$alldisabled.' id="guestbooksubmit" type="submit" name="submitguestbook" value="gastenboek" />';
				$editContactForm .= '</fieldset>';
    if (SESSION::get_session()->mode != "edit") {
			$editContactForm .= '</form>';
 		}
 		return $editContactForm;
  }

  function showContactMessage() {

   $showContactMessage = '';

		$showContactMessage .= '<div class="message">';

		if ($this->email != "")
			$showContactMessage .= '<a href="mailto:'.str_replace("@","_AT_",$this->email) .'">'.$this->name.'</a>';
		else
			$showContactMessage .= $this->name;

		if ($this->homepage != "")
			$showContactMessage .= ' from <a href="http://'.str_replace("http://", "", $this->homepage).'">'.$this->homepage.'</a>';

		if ($this->date != "") $showContactMessage .= '<p>'.$this->date.'<p>';
		$showContactMessage .= '<p>'.$this->message.'</p>';

		if ($this->guestbookreply != "")
			$showContactMessage .= '<p>'.$this->guestbookreply.'</p>';

		$showContactMessage .= '</div>';

  	return $showContactMessage;
  }
}

?>