// when add image is clicked currently active widget is set
function set_active_widget(imageId,widthId,heightId) {
	// establish which widget is being updated
	currentImageId = imageId;
	currentWidthId = widthId;
	currentHeightId = heightId;
}

// Hijack send to editor function
function send_to_editor(h) {
	alert(h);
	vars = eval(h);
	
	// store attachment id in hidden field
	jQuery( '#'+currentImageId ).val( vars[0] );

	// display attachment preview
	jQuery( '#display-'+currentImageId ).html( vars[1] );
	jQuery( '#display-'+currentImageId+' > img' ).css({
		'max-width':jQuery( '#'+currentWidthId ).val()+'px',
		'max-height':jQuery( '#'+currentHeightId ).val()+'px'
	});

	// close thickbox
	tb_remove();
}

// Hack for busted "Add Image" link courtesy of smurkas
jQuery(document).ready(function() {
	jQuery("body").click(function(event) {
		if (jQuery(event.target).is('a.thickbox-image-widget')) {
			tb_show("Add an Image", event.target.href, false);
		}
	});
});