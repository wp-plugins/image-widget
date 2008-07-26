<?php

/*
Plugin Name: Image Widget
Plugin URI: http://www.shaneandpeter.com/wordpress
Description: This widget accepts a title, a link and an image and displays them.  The admin panel is separated from the widget to offer independant control
Author: Shane & Peter, Inc.
Version: 1.0
Author URI: http://www.shaneandpeter.com
*/

class spImageWidget {

	var $id = "image-widget";
	var $name = "Image Widget";
	var $classname = "spImageWidget";
	var $optionsname = "spImageWidget_option";
	var $description = 'This widget accepts a title, a link and an image and displays them.  The admin panel is separated from the widget to offer independant control';
	var $thispage = 'sp-image-widget';

	// Display the widget
	function widget($args) {
		extract($args);
		$options = get_option($this->optionsname);
		echo $before_widget;
		if ($options[$this->id]['link']) {
			if ($options[$this->id]['title'])
				echo $before_title.'<a href="'.$options[$this->id]['link'].'">'.$options[$this->id]['title'].'</a>'.$after_title;
			if ($options[$this->id]['image'])
				echo '<a href="'.$options[$this->id]['link'].'"><img src="'.$options[$this->id]['image'].'" /></a>';
		} else {
			if ($options[$this->id]['title'])
				echo $before_title.$options[$this->id]['title'].$after_title;
			if ($options[$this->id]['image'])
				echo '<img src="'.$options[$this->id]['image'].'" />';
		}
		if ($options[$this->id]['description'])
			echo '<p>'.$options[$this->id]['description'].'</p>';
		echo $after_widget;
	}
	
	
	// Controller for modifying the Widget
	function control() {
		$options = get_option($this->optionsname);
		if ( !is_array($options) ) {
			$options = array();
			$options[$this->id] = array(
				'title' => &$this->name,
				'link' => '',
				'description' => '',
				'image' => ''
			);
		}
		
		$title = $this->id.'-title';
		$link = $this->id.'-link';	
		$description = $this->id.'-description';	
		$import = $this->id.'-import';	
		$submit = $this->id.'-submit';	

		if ( $_POST[$submit] ) {
			check_admin_referer($this->classname);

			$options[$this->id]['title'] = htmlentities(stripslashes($_POST[$title]));
			$options[$this->id]['description'] = htmlentities(stripslashes($_POST[$description]));
			$options[$this->id]['link'] = htmlentities(stripslashes($_POST[$link]));

			// Image Upload
			if ($_FILES[$import]['size'] > 0) {
				$file = wp_handle_upload($_FILES[$import], array('test_form' => false));
			
				if ( isset($file['error']) )
					die( $file['error'] );
			
				$url = $file['url'];
				$type = $file['type'];
				$file = $file['file'];
				$filename = basename($file);
			
				// Construct the object array
				$object = array(
					'post_title' => $filename,
					'post_content' => $url,
					'post_mime_type' => $type,
					'guid' => $url);	
			
				$id = wp_insert_attachment($object, $file);
				list($width, $height, $type, $attr) = getimagesize( $file );
				
				// Add the meta-data
				wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
				
				do_action('wp_create_file_in_uploads', $file, $id); // For replication
		
				$options[$this->id]['image'] = $url;
			}

			update_option($this->optionsname, $options);
		}

		
		echo '<div class="wrap">';
		echo '<h2>';
		echo $this->name;
		echo '</h2>';
		echo '<form name="form1" method="post" action="' . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . '" enctype="multipart/form-data">';
		echo '<p>';
		_e('Title:');
		echo ' <br /><input type="text" name="'.$title.'" value="' . htmlspecialchars($options[$this->id]['title'], ENT_QUOTES) . '" size="20">';
		echo '</p>';
		echo '<p>';
		_e('Link:');
		echo ' <br /><input type="text" name="'.$link.'" value="' . htmlspecialchars($options[$this->id]['link'], ENT_QUOTES) . '" size="20">';
		echo '</p>';
		echo '<p>';
		_e('Description:');
		echo ' <br /><textarea type="text" name="'.$description.'">' . htmlspecialchars($options[$this->id]['description'], ENT_QUOTES) . '</textarea>';
		echo '</p>';
		echo '<p>';
		_e('Image:');
		echo ' <input type="file" id="upload" name="'.$import.'" />';
		if ($options[$this->id]['image']) {
			echo "<br/><img src=\"".$options[$this->id]['image']."\" />";
		}
		echo '</p>';
		echo '<p class="submit"><input type="submit" name="'.$submit.'" value="Save" /></p>';
		wp_nonce_field($this->classname);
		echo '</form>';
		echo '</div>';
	}

	
	// Initialize the widget
	function init() {
		wp_register_sidebar_widget(
			$this->id, 
			$this->name, 
			array(&$this, 'widget'), 
			array('classname' => $this->classname, 'description' => $this->description), 
			array( 'number' => -1 )
		);	
	}

	// Admin menu placement
	function admin() {
		$options = get_option($this->optionsname);
		add_management_page($this->name, $this->name, 5, $this->thispage, array(&$this, 'control'));
	}

}

$o = new spImageWidget();
add_action("plugins_loaded", array($o,"init"));
add_action('admin_menu', array($o,"admin"));

?>