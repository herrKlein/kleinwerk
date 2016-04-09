$(function(){
	
	var $container = $('#scrollimages');
	
	$container.imagesLoaded(function(){
		$container.masonry({
			itemSelector: '.item',
			columnWidth: 235,
			gutter: 10
		});
	});
	
	$container.infinitescroll({
		      navSelector  : '#page-nav',    // selector for the paged navigation 
		      nextSelector : '#page-nav a',  // selector for the NEXT link (to page 2)
		      itemSelector : '.item',        // selector for all items you'll retrieve
		      debug: true,
		      pathParse: function() {
		      	return ['/kleinwerk/index.php?page=scrollimages&scrollpage=', '']
		      },
				loading: {
				          finishedMsg: 'No more pages to load.',
				          img: 'http://i.imgur.com/6RMhx.gif'
				        }
				      
		  },
		      // trigger Masonry as a callback
		      function( newElements ) {
		        // hide new items while they are loading
		        var $newElems = $( newElements ).css({ opacity: 0 });
		        // ensure that images load before adding to masonry layout
		        $newElems.imagesLoaded(function(){
		          // show elems now they're ready
		          $newElems.animate({ opacity: 1 });
		          $container.masonry( 'appended', $newElems, true ); 
		      });
		    }
		    );
	
});