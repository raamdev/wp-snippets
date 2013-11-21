=== WP Snippets ===

Stable tag: 131121
Requires at least: 3.2
Tested up to: 3.7.1
Text Domain: wp-snippets

License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Contributors: WebSharks
Donate link: http://www.websharks-inc.com/r/wp-theme-plugin-donation/
Tags: snippet, snippets, include, includes, shortcode, shortcodes, php, post type, post types, utilities, posts, pages

Create Snippets! This plugin adds a new Post Type. Snippets can be included in Posts/Pages/Widgets via shortcodes.

== Description ==

This WordPress plugin is VERY simple; NO configuration options necessary.

This plugin adds a new Post Type. This plugin makes it SUPER easy to reuse fragments of content (i.e. Snippets). Snippets can be included in other Posts/Pages/Widgets via Shortcodes. This is a very lightweight plugin for WordPress!

After installing this plugin, create a new Snippet (find menu item on the left in your WordPress Dashboard). Give your Snippet a Slug (i.e. under the title of your Snippet). Once you have a Snippet and a Slug, you can include your Snippet anywhere you like — in other Posts/Pages/Widgets (and even inside PHP/WordPress template files).

Using a Snippet that you've created in the WordPress Editor. Follow this simple Shortcode syntax.

	[snippet slug="my-cool-snippet" /]

Using a Snippet that you've created inside a PHP template file.

	<?php echo do_shortcode('[snippet slug="my-cool-snippet" /]'); ?>

== Frequently Asked Questions ==

= How do I use a Snippet that I create? =

Using a Snippet that you've created in the WP Editor. Follow this simple Shortcode syntax.

	[snippet slug="my-cool-snippet" /]

Using a Snippet that you've created inside a PHP template file.

	<?php echo do_shortcode('[snippet slug="my-cool-snippet" /]'); ?>

= Who can manage Snippets in the Dashboard? =

By default, only WordPress® Administrators can manage (i.e. create/edit/delete/manage) Snippets. Editors and Authors can create/edit/delete their own Snippets, but permissions are limited for Editors/Authors. If you would like to give other WordPress Roles the Capabilities required, please use a plugin like [Enhanced Capability Manager](http://wordpress.org/extend/plugins/capability-manager-enhanced/).

Add the following Capabilities to the additional Roles that should be allowed to manage Snippets.

	$caps = array
			(
				'edit_snippets',
				'edit_others_snippets',
				'edit_published_snippets',
				'edit_private_snippets',
				'publish_snippets',
				'delete_snippets',
				'delete_private_snippets',
				'delete_published_snippets',
				'delete_others_snippets',
				'read_private_snippets'
			);

NOTE: There are also some WordPress filters integrated into the code for this plugin, which can make permissions easier to deal with in many cases. You can have a look at the source code and determine how to proceed on your own; if you choose this route.

= Is it possible to use other Shortcodes inside a Snippet? =

Yes. Absolutely. You can even nest one Snippet inside another one via Shortcodes.

= I also installed the [Raw HTML plugin](http://wordpress.org/extend/plugins/raw-html/). Can I use Raw HTML inside a Snippet? =

Yes. When creating a new Snippet, please wrap your Snippet content with `[raw][/raw]` tags; or with `<!--raw--><!--/raw-->` tags. Consult the [Raw HTML documentation](http://wordpress.org/extend/plugins/raw-html/) on this please. An important point to make is that Snippets are self-contained. Applying Raw HTML to a Post/Page that includes a Snippet via `[snippet slug="my-snippet" /]`, will NOT apply Raw HTML to the Snippet content itself. You must wrap the Snippet content with raw tags to achieve this. This actually provides a great deal of flexibility, because it allows you to have a Raw HTML Post or Page, but have Snippets that were designed in the WP Visual Editor (or vice versa — and even mixtures, if you include multiple Snippets).

= I also installed the [ezPHP plugin](http://wordpress.org/extend/plugins/ezphp/). Can I use PHP tags inside a Snippet? =

Yes. Absolutely. I recommend [ezPHP](http://wordpress.org/extend/plugins/ezphp/), but WP Snippets are also compatible with [Exec-PHP](http://wordpress.org/extend/plugins/exec-php/).

= I also installed the [s2Member® plugin](http://wordpress.org/extend/plugins/s2member/). Can I protect content in Snippets conditionally? =

Yes. Absolutely. I recommend this KB article: [s2Member® Simple Conditionals](http://www.s2member.com/kb/simple-shortcode-conditionals/). s2Member's Simple Shortcode Conditionals can be used inside a Snippet itself, or by wrapping your Snippet Shortcode when you put it into a Post or Page. Either way is fine.

== Installation ==

= WP Snippets is Very Easy to Install =

1. Upload the `/wp-snippets` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress®.
3. Use Snippets in your Posts/Pages/Widgets via Shortcodes.

		[snippet slug="my-cool-snippet" /]

== License ==

Copyright: © 2013 [WebSharks, Inc.](http://www.websharks-inc.com/bizdev/) (coded in the USA)

Released under the terms of the [GNU General Public License](http://www.gnu.org/licenses/gpl-2.0.html).

= Credits / Additional Acknowledgments =

* Software designed for WordPress®.
	- GPL License <http://codex.wordpress.org/GPL>
	- WordPress® <http://wordpress.org>
* Some JavaScript extensions require jQuery.
	- GPL-Compatible License <http://jquery.org/license>
	- jQuery <http://jquery.com/>
* CSS framework and some JavaScript functionality provided by Bootstrap.
	- GPL-Compatible License <http://getbootstrap.com/getting-started/#license-faqs>
	- Bootstrap <http://getbootstrap.com/>
* Icons provided by Font Awesome.
	- GPL-Compatible License <http://fortawesome.github.io/Font-Awesome/license/>
	- Font Awesome <http://fortawesome.github.io/Font-Awesome/>

== Changelog ==

= v131121 =

* Adding support for `[snippet_template slug="" /]`. This works the same as a normal `[snippet slug="" /]` shortcode; except with a Snippet Template shortcode, any additional attributes that you specify are used as replacement code values in the Snippet content. For instance, you might have a Snippet with content that includes this replacement code: `%%type%%`. Now create your Snippet shortcode like this to fill that value dynamically: `[snippet_template slug="my-slug" type="product" /]`; where `%%type%%` is now replaced by `product` dynamically.

= v130206 =

* Initial release.