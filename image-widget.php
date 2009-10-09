<?php
/*
Plugin Name: Image Widget
Plugin URI: http://wordpress.org/extend/plugins/image-widget/
Description: This widget accepts a title, an image, a link and a description and displays them.
Author: Shane and Peter, Inc.
Version: 3.0.4
Author URI: http://www.shaneandpeter.com
*/

// Load the widget on widgets_init
function load_sp_image_widget() {
	register_widget('SP_Image_Widget');
}
add_action('widgets_init', 'load_sp_image_widget');

/**
 * SP Image Widget class
 *
 * @author Shane & Peter, Inc. (Peter Chester)
 **/
class SP_Image_Widget extends WP_Widget {

	/**
	 * SP Image Widget constructor
	 *
	 * @return void
	 * @author Shane & Peter, Inc. (Peter Chester)
	 */
	function SP_Image_Widget() {
		$widget_ops = array( 'classname' => 'widget_sp_image', 'description' => __( 'Showcase a single image with a Title, URL, and a Description', 'sp_image_widget' ) );
		$control_ops = array( 'id_base' => 'widget_sp_image' );
		$this->WP_Widget('widget_sp_image', __('Image Widget', 'sp_image_widget'), $widget_ops, $control_ops);

		if (WP_ADMIN) {
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( $control_ops['id_base'], WP_PLUGIN_URL.'/image-widget/image-widget.js' );
			add_filter( 'image_send_to_editor', array( $this,'image_send_to_editor'), 10, 7 );
		}
	}
	
	/**
	 * Retrieve resized image URL
	 *
	 * @param int $id Post ID or Attachment ID
	 * @param int $width desired width of image (optional)
	 * @param int $height desired height of image (optional)
	 * @return string URL
	 * @author Shane & Peter, Inc. (Peter Chester)
	 */
	function get_image_url( $id, $width=false, $height=false ) {
		
		/**/
		// Get attachment and resize but return attachment path (needs to return url)
		$attachment = wp_get_attachment_metadata( $id );
		$attachment_url = wp_get_attachment_url( $id );
		if (isset($attachment_url)) {
			if ($width && $height) {
				$uploads = wp_upload_dir();
				$imgpath = $uploads['basedir'].'/'.$attachment['file'];
				if ($image = image_resize( $imgpath, $width, $height )) {
					$image = path_join( dirname($attachment_url), basename($image) );
				} else {
					$image = $attachment_url;
				}
			} else {
				$image = $attachment_url;
			}
			if (isset($image)) {
				return $image;
			}
		}
	}
	
	/**
	 * Filter image_end_to_editor results
	 *
	 * @param string $html 
	 * @param int $id 
	 * @param string $alt 
	 * @param string $title 
	 * @param string $align 
	 * @param string $url 
	 * @param array $size 
	 * @return string javascript array of attachment url and id or just the url
	 * @author Shane & Peter, Inc. (Peter Chester)
	 */
	function image_send_to_editor( $html, $id, $alt, $title, $align, $url, $size ) {
		if (strpos($_REQUEST['_wp_http_referer'],$this->id_base)) { // check that this is for the widget. SEE NOTE #1
			$img = addslashes('<img src="' . wp_get_attachment_url( $id ) . '" />');
			return "new Array ( '$id', '$img' )";
		} else {
			return $html;
		}
	}
	
	/**
	 * Widget frontend output
	 *
	 * @param array $args 
	 * @param array $instance 
	 * @return void
	 * @author Shane & Peter, Inc. (Peter Chester)
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }
		if (!empty($instance['image'])) {
			if ($instance['link']) {
				echo '<a class="'.$instance['classname'].'-image-link" href="'.$instance['link'].'" target="'.$instance['linktarget'].'">';
			}
			
			if ($instance['imageurl']) {
				echo "<img src=\"{$instance['imageurl']}\" alt=\"{$instance['title']}\" />";
			}

			if ($instance['link']) { echo '</a>'; }
		}
		if (!empty($instance['description'])) {
			$text = apply_filters( 'widget_text', $instance['description'] );
			echo '<p class="'.$this->widget_ops['classname'].'-description" >';
			if ($instance['link']) {
				echo '<a class="'.$this->widget_ops['classname'].'-image-link-p" href="'.$instance['link'].'" target="'.$instance['linktarget'].'">';
			}
			echo wpautop($text);			
			if ($instance['link']) { echo '</a>'; }
			echo "</p>";
		}
		echo $after_widget;
	}

	/**
	 * Update widget options
	 *
	 * @param object $new_instance Widget Instance
	 * @param object $old_instance Widget Instance 
	 * @return object
	 * @author Shane & Peter, Inc. (Peter Chester)
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( isset($new_instance['description']) ) {
			if ( current_user_can('unfiltered_html') ) {
				$instance['description'] = $new_instance['description'];
			} else {
				$instance['description'] = wp_filter_post_kses($new_instance['description']);
			}
		}
		$instance['link'] = $new_instance['link'];
		$instance['image'] = $new_instance['image'];
		$instance['imageurl'] = $this->get_image_url($new_instance['image'],$new_instance['width'],$new_instance['height']);
		$instance['linktarget'] = $new_instance['linktarget'];
		$instance['width'] = $new_instance['width'];
		$instance['height'] = $new_instance['height'];

		return $instance;
	}

	/**
	 * Form UI
	 *
	 * @param object $instance Widget Instance
	 * @return void
	 * @author Shane & Peter, Inc. (Peter Chester)
	 */
	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array( 
			'title' => '', 
			'description' => '', 
			'link' => '', 
			'linktarget' => '', 
			'width' => '', 
			'height' => '', 
			'image' => '',
			'imageurl' => ''
		) );
		?>

		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sp_image_widget'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Image:', 'sp_image_widget'); ?></label>
		<?php
			$media_upload_iframe_src = "media-upload.php?type=image&widget_id=".$this->id; //NOTE #1: the widget id is added here to allow uploader to only return array if this is used with image widget so that all other uploads are not harmed.
			$image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src");
			$image_title = __('Add an Image', 'sp_image_widget');
		?><br />
		<a href="<?php echo $image_upload_iframe_src; ?>&TB_iframe=true" id="add_image-<?php echo $this->get_field_id('image'); ?>" class="thickbox-image-widget" title='<?php echo $image_title; ?>' onClick="set_active_widget('<?php echo $this->get_field_id('image'); ?>','<?php echo $this->get_field_id('width'); ?>','<?php echo $this->get_field_id('height'); ?>');return false;"><img src='images/media-button-image.gif' alt='<?php echo $image_title; ?>' /> <?php echo $image_title; ?></a>
		<div id="display-<?php echo $this->get_field_id('image'); ?>"><?php 
		if ($instance['imageurl']) { echo "<img src=\"{$instance['imageurl']}\" alt=\"{$instance['title']}\" />"; } 
		?></div>
		<input id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" type="hidden" value="<?php echo $instance['image']; ?>" />
		</p>		

		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', 'sp_image_widget'); ?></label>
		<textarea rows="8" class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>"><?php echo format_to_edit($instance['description']); ?></textarea></p>

		<p><label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Link:', 'sp_image_widget'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['link'])); ?>" /><br />
		<select name="<?php echo $this->get_field_name('linktarget'); ?>" id="<?php echo $this->get_field_id('linktarget'); ?>">
			<option value="_self"<?php selected( $instance['linktarget'], '_self' ); ?>><?php _e('Stay in Window', 'sp_image_widget'); ?></option>
			<option value="_blank"<?php selected( $instance['linktarget'], '_blank' ); ?>><?php _e('Open New Window', 'sp_image_widget'); ?></option>
		</select></p>

		<p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:', 'sp_image_widget'); ?></label>
		<input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['width'])); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height:', 'sp_image_widget'); ?></label>
		<input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['height'])); ?>" /></p>
	
<?php
	}
}
?>
