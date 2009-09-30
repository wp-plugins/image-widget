=== Image Widget ===
Contributors: Shane & Peter, Inc.
Donate link: http://www.shaneandpeter.com
Tags: widget, image, ad, banner, simple, upload, sidebar
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 3.0.2

Simple image widget.  Use native Wordpress upload thickbox to add image widgets to your site.

== Description ==
Simple image widget.  Use native Wordpress upload thickbox to add image widgets to your site.

Todo:
* 'Add an image' breaks after save is clicked

== Installation ==

**Install**

1. Unzip the `image-widget.zip` file. 
1. Upload the the `image-widget` folder (not just the files in it!) to your `wp-contents/plugins` folder. If you're using FTP, use 'binary' mode.

**Activate**

1. In your WordPress administration, go to the Plugins page
1. Activate the Image Widget plugin and a subpage for the plugin will appear
   in your Manage menu.
1. Go to the Appearance > Widget page and place the widget in your sidebar in the Design

If you find any bugs or have any ideas, please mail us.

== Changelog ==
New in version 3.0.2

* Added PHPDoc comments
* Temporarily fixed install bug where no image is saved if resize is not working. (thank you Paul Kaiser from Champaign, Il for your helpful QA support)

New in version 3.0.1

* Added 'sp_image_widget' domain for language support.

New in version 3.0

* Completely remodeled the plugin to use the native wordpress uploader and be compatible with Wordpress 2.8 plugin architecture.
* Removed externalized widget admin.

New in version 2.2.2

* Update <li> to be $before_widget and $after_widget (Thanks again to Lois Turley)

New in version 2.2.1

* Update <div> to be <li> (Thanks to Lois Turley)

New in version 2.2

* Fixed missing DIV close tag (Thank you Jesper Goos)
* Updated all short tags to proper php tags (Thank you Jonathan Volks from Mannix Marketing)

New in version 2.1

* Link Target

New in version 2.0

* Multi widget support
* WP 2.7.1 Compatibility
* Class encapsulation

== Screenshots ==

1. Image administration screen