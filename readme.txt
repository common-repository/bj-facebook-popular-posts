=== BJ Facebook Popular Posts ===
Contributors: bjornjohansen
Tags: facebook, widget, popular posts, popular
Author URI: http://twitter.com/bjornjohansen
Requires at least: 3.1
Tested up to: 3.5.1
Stable tag: 0.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Finds the number of Facebook shares for each of your posts. Includes widget for displaying most popular posts.

== Description ==

Finds the number each of your posts have been shared on Facebook and saves it as a hidden meta field.

The numbers are updated each hour through a cron job.

The update is non-blocking so visitors won't notice any performance degradation.

A widget for displaying most shared posts is included, and the output is easy to override.

I have only tested this on one site, but it works flawlessly for me. Please let me know if you have any issues. Fastest way to get a response is by Twitter: http://twitter.com/bjornjohansen

== Installation ==
1. Download and unzip plugin
2. Upload the 'bj-facebook-popular-posts' folder to the '/wp-content/plugins/' directory,
3. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= I don't like your markup. How do I provide my own? =
Copy bj-fbpp-widget.tmpl.php to your theme folder and customize as you like. It's a standard WP_Query Loop.

= How do I display the number of shares? =
Look inside bj-fbpp-widget.tmpl.php (there's sample code in a comment).

== Screenshots ==

1. The Posts overview gets a new, sortable column that shows number of shares on Facebook
2. Widget settings
3. Sample usage of widget. The HTML is really easy to override.

== Changelog ==

= Version 0.2.2 =
* Added missing screenshot

= Version 0.2.1 =
* The included widget now lets you select what post types to show

= Version 0.2.0 =
* Changed from the graph API to FQL

= Version 0.1.8 =
* The background updates now uses HTTP 1.1 so it works with shared hosting as well
* Tested up to: 3.4.2

= Version 0.1.7 =
* Tested up to: 3.4.1

= Version 0.1.6 =
* Screenshot descriptions in readme.txt had fallen out. Back in now.

= Version 0.1.5 =
* Added screenshots
* Removes output buffering from the update response to avoid timeouts
* Improved the fetch queue
* Added filter: bj_fb_num_posts - Number of posts to fetch per batch (hour). Defaults to -1 (all posts)
* Added filter: bj_fb_post_type - Post type (content type) to find shares for. Defaults to 'all'

= Version 0.1 =
* Seems to work just fine
