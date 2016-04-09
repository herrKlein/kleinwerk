

function gallerimgclick(e) {
	console.log('test');
	theEvent = e || event;
	theEvent.preventDefault();
	var theTarget = theEvent.target != null ? theEvent.target : theEvent.srcElement;
	// Get the XML of this page on click
	// $(theTarget).block('<img src="styles/img/ajax-loader.gif" />');
	$("div.menuitem > a > img.thumbselected").removeClass();
	$(theTarget).addClass("thumbselected");
	$("img#imgview").hide();
	$.get(theTarget.parentNode.href, { xml: "true" }, getgalleryimg);
	return false;
}

function nextprev(e) {
	theEvent = e || event;
	var theTarget = theEvent.target != null ? theEvent.target : theEvent.srcElement;
	
	if(theTarget.className == 'next' ) {
		if ($('img.thumbselected').parent().next().length > 0) {
			$('img.thumbselected').parent().next().children().slice(0,1).addClass("thumbselected");
			$("img#imgview").hide();
			$.get($('img.thumbselected').parent().next().attr("href"), { xml: "true" }, getgalleryimg);
			$("div.menuitem > a > img.thumbselected").slice(0,1).removeClass();
		}
	} else {
		if ($('img.thumbselected').parent().prev().length > 0) {
			$.get($('img.thumbselected').parent().prev().attr("href"), { xml: "true" }, getgalleryimg);
			$('img.thumbselected').parent().prev().children().slice(0,1).addClass("thumbselected");
			$("img#imgview").hide();
			$("div.menuitem > a > img.thumbselected").slice(1,2).removeClass();
		}
	}
	// Get the XML of this page on click
	// $(theTarget).block('<img src="styles/img/ajax-loader.gif" />');
	return false;
}

function getgalleryimg(xml) {
	// Get the src of the img and change the original img
    imgsrc = $('/page//functionality//galleryimage//img', xml).attr("src");
	$("img#imgview").load(function() { $("img#imgview").fadeIn() });   
	$("img#imgview").attr({ src: imgsrc});
	// $("img#imgview").fadeIn();

	// $(theTarget).unblock();
}

$(document).ready(function(){
	$("div.menuitem > a").click(gallerimgclick);
	$("div#chooser a").click(nextprev);
});