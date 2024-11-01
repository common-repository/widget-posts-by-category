=== Posts By Category Widget ===

Contributors: volfro
Tags: widget, posts, category, themeable
Requires at least: 3.7
Tested up to: 3.8
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple, themeable widget which displays posts in the category, order, and quantity of your choosing.

== Description ==

This widget displays posts in the category, order, and quantity of your choosing. It supports custom templates!

After installation, drag-and-drop the new "Category Widget" to a widget area, choose the categories you wish to display, and tell it the order and quantity in which you wish to display them.

== Installation ==

1. Upload `posts-by-cat-widget` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the "Category Widget" in any of your sidebars/widget areas

== Changelog ==
= 1.0.4 =
* Update to 3.8

= 1.0.2 =
* Plugin wasn't installing properly in the WP dashboard...maybe updating its headers will fix the issue.

= 1.0.1 = 
* Minor updates to readme

= 1.0 =
* Release

== Templates ==
The default template is just plain-ol' HTML5, without any CSS; it's meant to fit right into any theme without modification.

However, if you're a theme developer and you wish to use custom markup or WP functions inside your template, here's how to customize it:

1. In the root of your theme's directory, create a new file called `catswidget.php` (or copy `template.php` from `posts-by-cat-widget/views` to your theme's root, and rename it `catswidget.php`, if you'd rather use the default as a starting point).
1. Craft your loop. Just make sure you use the `$posts` variable, like this:`
        if ( $posts -> have_posts() ) : 
            while ( $posts -> have_posts() ) :
                $posts -> the_post(); ?>
                // Your markup here
            endwhile;
        endif;`
1. The widget is just a simple custom loop, so you'll have access to whatever data WP_Query has access to inside the loop.

== TODO ==
* Perhaps we could use it to query custom post types/taxonomies, not just Posts
* Add hooks/filters
* Add ability to extend admin