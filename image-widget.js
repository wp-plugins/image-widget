function set_active_widget(imageId,widthId,heightId) {
	// establish which widget is being updated
	currentImageId = imageId;
	currentWidthId = widthId;
	currentHeightId = heightId;
}
function send_to_editor(h) {
	vars = eval(h);
	
	// store attachment id in hidden field
	jQuery( '#'+currentImageId ).val( vars[0] );

	// display attachment preview
	jQuery( '#display-'+currentImageId ).html( vars[1] );
	jQuery( '#display-'+currentImageId+' > img' ).css({
		'max-width':jQuery( '#'+currentWidthId ).val()+'px',
		'max-height':jQuery( '#'+currentHeightId ).val()+'px'
	});

	/*jQuery.ajax({url:'/wp-admin/admin-ajax.php',
		type:'POST',
		success:function(response) {
			responses = eval(response);
			jQuery( '#display-'+currentImageId ).html(responses[0]);
			//jQuery( '#display-'+currentImageId ).html(response);
		},
		data:{
			id : vars[0],
			width : jQuery( '#'+currentWidthId ).val(),
			height : jQuery( '#'+currentHeightId ).val(),
			action : 'image_widget'
		}
	});*/

	// close thickbox
	tb_remove();
}