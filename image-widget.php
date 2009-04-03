<?php

/*
Plugin Name: Image Widget
Plugin URI: http://www.shaneandpeter.com/wordpress
Description: This widget accepts a title, a link and an image and displays them.  The admin panel is separated from the widget to offer independant control
Author: Shane and Peter, Inc. [Contributors: Kevin Miller, Nick Ohrn]
Version: 2.2
Author URI: http://www.shaneandpeter.com
*/

/*
Feature Ideas

* Settings in the widget editor that mirror the Editor view
* Size setting in the widget editor

*/

class sp_image_widget {
	
	var $options = array(
		'widget_options' => array(
			'classname' => 'widget_sp_image', 
			'description' => 'Showcase a single image with a Title/URL/Description'
		),
		'control_options' => array(
			'width' => null, 
			'height' => 200, 
			'id_base' => 'sp_image'
		),
		'default_widget_options' => array(
			'title' => '',
			'link' => '',
			'linktarget' => '',
			'description' => '',
			'image' => ''
		),
		'widget_name' => 'Image Widget'
	);
		
	var $admin_menu_header = 'Image Widgets';
	
	var $is_admin_page = false;

	var $is_widget_id = false;
	
	// Setup Widget
	function sp_image_widget() {
		
		$this->is_admin_page = (isset($_GET['page']) && $_GET['page'] == $this->options['control_options']['id_base']) ? true : false;
		$this->is_widget_id = (isset($_GET['widget_id'])) ? $_GET['widget_id'] : false;
		
		add_action('admin_head', array(&$this, 'admin_head'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}
	
	// Admin Header
	function admin_head() {
		
		// TODO: Submit this as a patch to Wordpress
		// 
		// FIX: Fixes the control div from hiccupping when it opens.
		?>
		
			<style type="text/css">
				
				.widget-control {
					padding: 0px !important;
				}
				
				.widget-control div.widget-control-actions {
					padding: 15px 15px 15px 15px !important;
				}
				
				.widget-control p {
					padding: 15px 15px 0 15px !important;
				}
			
			</style>
		
		<?php
	}
	
	// Admin Menu
	function admin_menu() {
		add_management_page($this->options['widget_name'], $this->options['widget_name'], 5, $this->options['control_options']['id_base'], array(&$this, 'control'));
	}
	
	// Get Widget Options
	function get_options() {
		return get_option('widget_' . $this->options['control_options']['id_base']);
	}
	
	// Set Widget Options
	function update_options($options) {
		return update_option('widget_' . $this->options['control_options']['id_base'], $options);
	}
	

	// Display Widget Output
	function widget($arguments, $widget_arguments = 1) {
		
		extract($arguments, EXTR_SKIP);
		
		if (is_numeric($widget_arguments)) {
			$widget_arguments = array( 'number' => $widget_arguments );
		}
		$widget_arguments = wp_parse_args($widget_arguments, array('number' => -1));
		extract($widget_arguments, EXTR_SKIP);

		$options = $this->get_options();
		if (!isset($options[$number])) {
			return;
		}

		$widget_options = $options[$number];


		$link = !empty($widget_options['link']);
		$linktarget = !empty($widget_options['linktarget']);
		
 		echo '<div id="'.$this->options['control_options']['id_base'].'-'.$number.'" class="widget '.$this->options['widget_options']['classname'].'">';
		
		
		if (!empty($widget_options['title'])) {
			echo $before_title;
			echo $widget_options['title'];
			echo $after_title;
		}
		
		if (!empty($widget_options['image'])) {	

			if ($link) {
				echo '<a class="' . $this->options['widget_options']['classname'] . '-image-link" href="' . $widget_options['link'] . '" target="' . $widget_options['linktarget'] . '">';
			}

			echo '<img class="' . $this->options['widget_options']['classname'] . '-image" src="' . $widget_options['image'] . '" alt="image widget" />';

			if ($link) {
				echo '</a>';
			}

		}
		
		if (!empty($widget_options['description'])) {
		
			echo '<p class="' . $this->options['widget_options']['classname'] . '-description" >';
			if ($link) {
				echo '<a class="' . $this->options['widget_options']['classname'] . '-image-link-p" href="' . $widget_options['link'] . '" target="' . $widget_options['linktarget'] . '">';
			}
			
			echo html_entity_decode($widget_options['description']);
			
			if ($link) { echo '</a>'; }

			echo "</p>";
		
		}
		
		echo $after_widget;
		
		echo "</div>\n";
			
	}


	// Widget Registration
	function register() {
		
		if (!$options = $this->get_options()) {
			$options = array();
		}
	
		$id = false;

		foreach (array_keys($options) as $option) {
			$widget_options = array_merge(array(), $this->options);
			
			$id = $this->options['control_options']['id_base'] . '-' . $option;
			
			wp_register_sidebar_widget($id, $this->options['widget_name'], array(&$this, 'widget'), $this->options['widget_options'], array('number' => $option));
			wp_register_widget_control($id, $this->options['widget_name'], array(&$this, 'control'), $this->options['control_options'], array('number' => $option));
		}

		if (!$id) {
			wp_register_sidebar_widget($this->options['control_options']['id_base'] . '-1', $this->options['widget_name'], array(&$this, 'widget'), $this->options['widget_options'], array('number' => -1));
			wp_register_widget_control($this->options['control_options']['id_base'] . '-1', $this->options['widget_name'], array(&$this, 'control'), $this->options['control_options'], array('number' => -1));
		}
	}


	// Widget Controller
	function control($widget_arguments = 1) {

		global $wp_registered_sidebars, $wp_registered_widgets;
		static $updated = false; 
		
		if (is_numeric($widget_arguments)) {
			$widget_arguments = array('number' => $widget_arguments);
		}
		$widget_arguments = wp_parse_args( $widget_arguments, array('number' => -1));
		extract($widget_arguments, EXTR_SKIP);

		$options = $this->get_options();
		if (!is_array($options)) {
			$options = array();
		}

		if ((!$updated && !empty($_POST['sidebar'])) || ($this->is_admin_page)) {

			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if (isset($sidebars_widgets[$sidebar])) {
				$this_sidebar =& $sidebars_widgets[$sidebar];
			} else {
				$this_sidebar = array();
			}
		}


		// Widget Control
		if (!$updated && !empty($_POST['sidebar'])) {

			foreach ($this_sidebar as $_widget_id) {

				if ('widget_' . $this->options['control_options']['id_base'] == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {

					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];

					if (!in_array($this->options['control_options']['id_base'] . '-$widget_number', $_POST['widget-id'])) {
						unset($options[$widget_number]);
					}
				}
			}

			// 
			// UNCOMMENT TO USE THE WIDGET CONTROL FOR UPDATING THE INFORMATION INSTEAD, WILL NEED THE FORM PROCESSING CODE
			//
			foreach ((array) $_POST['widget-' . $this->options['control_options']['id_base']] as $widget_number => $widget_image_instance) {
			
				$new_options = array_merge($this->options['default_widget_options'], array());
			
				// ---------- Update Options: CUSTOM ---------- //
				
					// WE DON'T USE THIS AS WE DON'T HAVE
					// FORM IN THE WIDGET CONTROL!!!
					
				// ---------- Update Options: CUSTOM ---------- //
			
				if (!isset($options[$widget_number])) {
					$options[$widget_number] = $new_options;
				}
			}
			
			$this->update_options($options);
			
			$updated = true; 

		}
	
		$number = ($number == -1 ? '%i%' : $number);

		// ---------- CUSTOMIZATIONS FOR ADMIN MENU AS WELL AS WIDGET CONTROL ---------- //
		if ($this->is_admin_page): 

			// Process Form Submission
			if ($_POST[$this->options['control_options']['id_base'] . '-submit']) {

				$new_options = array_merge($this->options['default_widget_options'], array());

				// ---------- Update Options: CUSTOM ---------- //

					// strip off the sidebar ID that we appended in the dropdown form for navigation
					$split_it = explode('&',$_POST['sp_image_admin_dropdown']);

					$_POST['sp_image_admin_dropdown'] = $split_it[0];

					// sanitize the title by removing all non ASCII characters - this include funky quotes, etc. from Word documents
					$new_options['title'] = $_POST[$this->options['control_options']['id_base'] . '-title'];
					$new_options['title'] = ereg_replace("[^A-Za-z0-9 _!-@#$%^&*()_+={}\":<>?/.,;'|\\~`]", "", $new_options['title']);
					$new_options['title'] = htmlentities(stripslashes($new_options['title']));

					$new_options['link'] = htmlentities(stripslashes($_POST[$this->options['control_options']['id_base'] . '-link']));

					$new_options['linktarget'] = htmlentities(stripslashes($_POST[$this->options['control_options']['id_base'] . '-linktarget']));

					$new_options['description'] = $_POST[$this->options['control_options']['id_base'] . '-description'];
					$new_options['description'] = ereg_replace("[^A-Za-z0-9 _!-@#$%^&*()_+={}\":<>?/.,;'|\\~`]", "", $new_options['description']);
					$new_options['description'] = htmlentities(stripslashes($new_options['description']));

					if ($_FILES[$this->options['control_options']['id_base'] . '-image']['size'] > 0) {

						$file = wp_handle_upload($_FILES[$this->options['control_options']['id_base'] . '-image'], array('test_form' => false, 'unique_filename_callback' => array($this,'sp_unique_filename') ));
					
						// Required Debug
						if (isset($file['error'])) {
							die($file['error']);
						}
					
						$_url = str_replace(basename($file['file']), '', $file['url']);
						$_path = str_replace(basename($file['file']), '', $file['file']);
						$_extension = explode('/', $_FILES[$this->options['control_options']['id_base'] . '-image']['type']);
						$_target = $this->options['control_options']['id_base'] . '-' . $_POST['sp_image_admin_dropdown'] . '-' . time() . '.' . $_extension[1];

						rename($file['file'], $_path . $_target);

						$url = $_url . $_target;
						$type = $file['type'];
						$file = $_path . $_target;
						$file_name = basename($file);
					
						// Construct the object array
						$_post_object = array(
							'post_title' => $file_name,
							'post_content' => $url,
							'post_mime_type' => $type,
							'guid' => $url
						);	
					
						$_post_id = wp_insert_attachment($_post_object, $file);
						list($width, $height, $type, $attributes) = getimagesize($file);
					
						wp_update_attachment_metadata($id, wp_generate_attachment_metadata($_post_id, $file));
					
						do_action('wp_create_file_in_uploads', $file, $_post_id); 
						
						$new_options['image'] = htmlentities(stripslashes($url));
					
					} else {
						$new_options['image'] = $options[$_POST['sp_image_admin_dropdown']]['image'];
					}
					
					$options[$_POST['sp_image_admin_dropdown']] = $new_options;
				
				// ---------- Update Options: CUSTOM ---------- //

				$this->update_options($options);
			}

			$dropdown = array();
			$first_widget_id = false;
			$first_sidebar = false;

			foreach ($sidebars_widgets as $_sidebar => $_widgets) {

				if (!isset($dropdown[$_sidebar])) {
					$dropdown[$_sidebar] = array();
				}

				foreach ($sidebars_widgets[$_sidebar] as $_widget) {

					$_t = explode('-', $_widget);
					$_widget_class = $_t[0];
					$_widget_id = $_t[1];
					
					if (isset($options[$_widget_id])) {

						$first_widget_id = $first_widget_id ? $first_widget_id : $_widget_id;
						$first_sidebar = $first_sidebar ? $first_sidebar : $_sidebar;

						array_push($dropdown[$_sidebar], 
							array(
								'id' => $_widget_id,
								'classname' => $_widget_class,
								'options' => $options[$_widget_id],
								'selected' => (($this->is_widget_id == $_widget_id) ? true : false)
							)
						);
						
					}	
					
				}

			}

			if ($this->is_widget_id && isset($options[$this->is_widget_id])) {
				$form_options = $options[$this->is_widget_id];
			} else {
				$form_options = $options[$first_widget_id];
				$dropdown[$first_sidebar][0]['selected'] = true;
			}

		?>
			
			<div class="wrap">
		
				<h2><?php echo $this->admin_menu_header; ?></h2>
	
				<?php if (!$first_widget_id): ?>
	
					<p>
						You must add a widget to a sidebar (Design &raquo; Widgets) before you can edit one.
					</p>
		
				<?php else: ?>
					
					<form name="form_<?php echo $this->options['control_options']['id_base']; ?>" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">

					<p>
						Select which Image Widget you would like to edit.
					</p>
					
					<p>

						<select id="sp_image_admin_dropdown" name="sp_image_admin_dropdown" style="width: 400px;" >
						
							<?php foreach ($dropdown as $_sidebar => $_info):
							
								$_widget_count = 1;

								foreach ($dropdown[$_sidebar] as $_widget):
							
 									
 									echo '<option value="' .  $_widget['id'] . '&sidebar=' . $_sidebar . '"';
 									if ($_widget['selected']) { echo ' SELECTED '; }
 									echo '>' . $wp_registered_sidebars[$_sidebar]['name'] . '&nbsp;&raquo;&nbsp;' . $this->ordinalize($_widget_count) . ' widget</option>';

									$_widget_count++;
								
								endforeach;

							endforeach; ?>
						
						</select>
					
						<script type='text/javascript'>
						/* <![CDATA[ */
						    var sp_image_admin_dropdown = document.getElementById('sp_image_admin_dropdown');
						    sp_image_admin_dropdown.onchange = function() {
								    widget_num = sp_image_admin_dropdown.options[sp_image_admin_dropdown.selectedIndex].value.split('&'); 
									if (widget_num[0] > 0) {
  										location.href = '<?php echo get_option('home'); ?>/wp-admin/tools.php?page=<?php echo $this->options['control_options']['id_base']; ?>&widget_id=' + sp_image_admin_dropdown.options[sp_image_admin_dropdown.selectedIndex].value;
									}
						    }
						/* ]]> */
						</script>
						
					</p>
			
					<table class="form-table">
						<tbody>
					
							<tr>
								<th>
									<label for="<?php echo $this->options['control_options']['id_base']; ?>[<?php echo $number; ?>][title]"><?php echo _e('Title:') ?></label>
								</th>
								<td>
									<input type="text" id="<?php echo $this->options['control_options']['id_base']; ?>[<?php echo $number; ?>][title]" name="<?php echo $this->options['control_options']['id_base']; ?>-title" value="<?php echo $form_options['title']; ?>" />
								</td>
							</tr>
					
							<tr>
								<th>
									<label for="<?php echo $this->options['control_options']['id_base']; ?>[<?php echo $number; ?>][link]"><?php echo _e('Link:'); ?></label>
								</th>
								<td>
									<input type="text" id="<?php echo $this->options['control_options']['id_base']; ?>[<?php echo $number; ?>][link]" name="<?php echo $this->options['control_options']['id_base']; ?>-link" value="<?php echo $form_options['link']; ?>" >
									<select id="<?php echo $this->options['control_options']['id_base']; ?>[<?php echo $number; ?>][linktarget]" name="<?php echo $this->options['control_options']['id_base']; ?>-linktarget">
										<option value="_self"<?php if ($form_options['linktarget']=="_self") { echo " selected"; } ?>>Same Window</option>
										<option value="_blank"<?php if ($form_options['linktarget']=="_blank") { echo " selected"; } ?>>New Window</option>
									</select>
								</td>
							</tr>
					
							<tr>
								<th>
									<label for="<?php echo $this->options['control_options']['id_base']; ?>[<?php echo $number; ?>][description]"><?php echo _e('Description:'); ?></label>
								</th>
								<td>
					 				<textarea type="text" id="<?php echo $this->options['control_options']['id_base']; ?>[<?php echo $number; ?>][description]" name="<?php echo $this->options['control_options']['id_base']; ?>-description"><?php echo  $form_options['description']; ?></textarea>
								</td>
							</tr>
							<tr>
								<th>
									<label for="<?php echo  $this->options['control_options']['id_base']; ?>[<?php echo  $number; ?>][image]"><?php echo  _e('Image:'); ?></label>
								</th>
								<td>
 						 			<input type="file" id="<?php echo  $this->options['control_options']['id_base']; ?>[<?php echo  $number; ?>][image]"  name="<?php echo  $this->options['control_options']['id_base']; ?>-image" />
								</td>
							</tr>
							
							<tr>
								<th>
									<label><?php echo  _e('Preview Image:'); ?></label>
								</th>
								<td>
									<?php if ($form_options['image']): ?>
										<img src="<?php echo  $form_options['image']; ?>" border="0" />
									<?php endif; ?>
								</td>
							</tr>
					
						</tbody>
					</table>

					<p class="submit">
						<input type="submit" value="Save" id="<?php echo  $this->options['control_options']['id_base']; ?>[<?php echo  $number; ?>][submit]"  name="<?php echo  $this->options['control_options']['id_base']; ?>-submit" value="1" />
					</p>
	
					<?php echo  wp_nonce_field($this->options['control_options']['id_base']); ?>
			
				</form>
	
				<?php endif; ?>
				
			</div>
		
		<?php else: ?>
				
			<p>
				<small>To edit the properties of this widget visit:
				<br />
				<?php if ($_GET['sidebar']) $_sidebar = $_GET['sidebar']; else $_sidebar = 'sidebar-1'; ?>
 				Manage &raquo; <a href="../wp-admin/tools.php?page=<?php echo  $this->options['control_options']['id_base']; ?>&widget_id=<?php echo  $number; ?>&sidebar=<?php echo $_sidebar; ?>"><?php echo  $this->options['widget_name']; ?></a></small>
				<input type="hidden" id="widget-sp_image-submit-<?php echo  $number; ?>" name="widget-sp_image[<?php echo  $number; ?>][submit]" value="1" />
			</p>
		
		<?php
		endif;
	
	}
	
	
	// Widget Positioning Suffixes
	function ordinalize($number) {
		
		if (in_array(($number % 100), range(11, 13))) {	
			return $number . 'th';
		} else {
			
			switch (($number % 10)) {
				case 1:
				
					return $number . 'st';
					break;
			
				case 2:
				
					return $number . 'nd';
					break;
		
				case 3:
				
					return $number . 'rd';
		
				default:
		
					return $number . 'th';
					break;
			}
		}
	}

}

// Instantiate class
$sp_image = new sp_image_widget();

// Load actions
add_action('widgets_init', array($sp_image, 'register'));

?>
