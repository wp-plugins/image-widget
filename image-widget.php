<?php
/*
Plugin Name: Image Widget
Plugin URI: http://wordpress.org/extend/plugins/image-widget/
Description: This widget accepts a title, an image, a link and a description and displays them.
Author: Shane and Peter, Inc.
Version: 3.0.6
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
			// add our filter to send modified output back to image widget
			add_filter( 'image_send_to_editor', array( $this,'image_send_to_editor'), 1, 7 );
			add_action( 'admin_head-widgets.php', array( $this, 'admin_head' ) );
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
		// Normally, media uploader return an HTML string (in this case, typically a complete image tag surrounded by a caption).
		// Don't change that; instead, send custom javascript variables back to opener.
		// Check that this is for the widget. Shouldn't hurt anything if it runs, but let's do it needlessly.
		if (strpos($_REQUEST['_wp_http_referer'],$this->id_base)) {
			?>
			<script type="text/javascript">
				// send image variables back to opener
				var win = window.dialogArguments || opener || parent || top;
				win.IW_html = '<?php echo addslashes($html) ?>';
				win.IW_img_id = '<?php echo $id ?>';
				win.IW_alt = '<?php echo addslashes($alt) ?>';
				win.IW_title = '<?php echo addslashes($title) ?>';
				win.IW_align = '<?php echo $align ?>';
				win.IW_url = '<?php echo $url ?>';
				win.IW_size = '<?php echo $size ?>';
				//alert("sending variables: id: "+win.IW_img_id+"\n"+"alt: "+win.IW_alt+"\n"+"title: "+win.IW_title+"\n"+"align: "+win.IW_align+"\n"+"url: "+win.IW_url+"\n"+"size: "+win.IW_size);
			</script>
			<?php
		}
		return $html;
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
				echo "<img src=\"{$instance['imageurl']}\" alt=\"{$instance['title']}\" style=\"";
				if (!empty($instance['width']) && is_numeric($instance['width'])) {
					echo "max-width: {$instance['width']}px;";
				}
	 			if (!empty($instance['height']) && is_numeric($instance['height'])) {
					echo "max-height: {$instance['height']}px;";
				}
				echo "\"";
				if (!empty($instance['align']) && $instance['align'] != 'none') {
					echo " class=\"align{$instance['align']}\"";
				}
				echo " />";
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
		$instance['imageurl'] = $this->get_image_url($new_instance['image'],$new_instance['width'],$new_instance['height']);  // image resizing not working right now
		$instance['linktarget'] = $new_instance['linktarget'];
		$instance['width'] = $new_instance['width'];
		$instance['height'] = $new_instance['height'];
		$instance['align'] = $new_instance['align'];

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
			'imageurl' => '',
			'align' => ''
		) );
		?>

		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sp_image_widget'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Image:', 'sp_image_widget'); ?></label>
		<?php
			$media_upload_iframe_src = "media-upload.php?type=image&widget_id=".$this->id; //NOTE #1: the widget id is added here to allow uploader to only return array if this is used with image widget so that all other uploads are not harmed.
			$image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src");
			$image_title = __(($instance['image'] ? 'Change Image' : 'Add Image'), 'sp_image_widget');
		?><br />
		<a href="<?php echo $image_upload_iframe_src; ?>&TB_iframe=true" id="add_image-<?php echo $this->get_field_id('image'); ?>" class="thickbox-image-widget" title='<?php echo $image_title; ?>' onClick="set_active_widget('<?php echo $this->id; ?>');return false;" style="text-decoration:none"><img src='images/media-button-image.gif' alt='<?php echo $image_title; ?>' align="absmiddle" /> <?php echo $image_title; ?></a>
		<div id="display-<?php echo $this->get_field_id('image'); ?>"><?php 
		if ($instance['imageurl']) {
			echo "<img src=\"{$instance['imageurl']}\" alt=\"{$instance['title']}\" style=\"";
				if ($instance['width'] && is_numeric($instance['width'])) {
					echo "max-width: {$instance['width']}px;";
				}
 				if ($instance['height'] && is_numeric($instance['height'])) {
					echo "max-height: {$instance['height']}px;";
				}
				echo "\"";
				if (!empty($instance['align']) && $instance['align'] != 'none') {
					echo " class=\"align{$instance['align']}\"";
				}
				echo " />";
		}
		?></div>
		<br clear="all" />
		<input id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" type="hidden" value="<?php echo $instance['image']; ?>" />
		</p>

		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Caption:', 'sp_image_widget'); ?></label>
		<textarea rows="8" class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>"><?php echo format_to_edit($instance['description']); ?></textarea></p>

		<p><label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Link:', 'sp_image_widget'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['link'])); ?>" /><br />
		<select name="<?php echo $this->get_field_name('linktarget'); ?>" id="<?php echo $this->get_field_id('linktarget'); ?>">
			<option value="_self"<?php selected( $instance['linktarget'], '_self' ); ?>><?php _e('Stay in Window', 'sp_image_widget'); ?></option>
			<option value="_blank"<?php selected( $instance['linktarget'], '_blank' ); ?>><?php _e('Open New Window', 'sp_image_widget'); ?></option>
		</select></p>

		<p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:', 'sp_image_widget'); ?></label>
		<input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['width'])); ?>" onchange="changeImgWidth('<?php echo $this->id; ?>')" /></p>

		<p><label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height:', 'sp_image_widget'); ?></label>
		<input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['height'])); ?>" onchange="changeImgHeight('<?php echo $this->id; ?>')" /></p>
	
		<p><label for="<?php echo $this->get_field_id('align'); ?>"><?php _e('Align:', 'sp_image_widget'); ?></label>
		<select name="<?php echo $this->get_field_name('align'); ?>" id="<?php echo $this->get_field_id('align'); ?>" onchange="changeImgAlign('<?php echo $this->id; ?>')">
			<option value="none"<?php selected( $instance['align'], 'none' ); ?>><?php _e('none', 'sp_image_widget'); ?></option>
			<option value="left"<?php selected( $instance['align'], 'left' ); ?>><?php _e('left', 'sp_image_widget'); ?></option>
			<option value="center"<?php selected( $instance['align'], 'center' ); ?>><?php _e('center', 'sp_image_widget'); ?></option>
			<option value="right"<?php selected( $instance['align'], 'right' ); ?>><?php _e('right', 'sp_image_widget'); ?></option>
		</select></p>

<?php
	}
	
	/**
	 * Admin header css
	 *
	 * @return void
	 * @author Shane & Peter, Inc. (Peter Chester)
	 */
	function admin_head() {
		?>
		<style type="text/css">
			.aligncenter {
				display: block;
				margin-left: auto;
				margin-right: auto;
			}
		</style>
		<?php
	}
}
?>
