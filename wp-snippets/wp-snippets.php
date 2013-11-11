<?php
/*
Version: 130206
Text Domain: wp-snippets
Plugin Name: WP Snippets

Author URI: http://www.s2member.com
Author: s2Member® / WebSharks, Inc.

Plugin URI: http://www.s2member.com/kb/wp-snippets-plugin
Description: Create Snippets! This plugin adds a new Post Type. Snippets can be included in other Posts/Pages/Widgets via Shortcodes. A very lightweight plugin!
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

add_action('init', 'wp_snippets::init');
register_activation_hook(__FILE__, 'wp_snippets::activate');
register_deactivation_hook(__FILE__, 'wp_snippets::deactivate');

class wp_snippets
{
	public static function init()
		{
			load_plugin_textdomain('wp-snippets');

			wp_snippets::register();

			add_filter('widget_text', 'do_shortcode');
			add_shortcode('snippet', 'wp_snippets::shortcode');

			if(defined('RAWHTML_PLUGIN_FILE') && function_exists('rawhtml_get_settings_fields'))
				add_filter('get_post_metadata', 'wp_snippets::raw_html_settings', 10, 4);
		}

	public static function register()
		{
			$args           = array
			(
				'show_ui'             => TRUE,
				'map_meta_cap'        => TRUE,
				'exclude_from_search' => TRUE,
				'show_in_nav_menus'   => FALSE,
				'capability_type'     => array('snippet', 'snippets'),
				'public'              => current_user_can('edit_snippets'),
				'rewrite'             => array('slug' => 'snippet', 'with_front' => FALSE),
				'supports'            => array('title', 'editor', 'author', 'revisions')
			);
			$args['labels'] = array
			(
				'name'               => __('Snippets', 'wp-snippets'),
				'singular_name'      => __('Snippet', 'wp-snippets'),
				'add_new'            => __('Add Snippet', 'wp-snippets'),
				'add_new_item'       => __('Add New Snippet', 'wp-snippets'),
				'edit_item'          => __('Edit Snippet', 'wp-snippets'),
				'new_item'           => __('New Snippet', 'wp-snippets'),
				'all_items'          => __('All Snippets', 'wp-snippets'),
				'view_item'          => __('View Snippet', 'wp-snippets'),
				'search_items'       => __('Search Snippets', 'wp-snippets'),
				'not_found'          => __('No Snippet found', 'wp-snippets'),
				'not_found_in_trash' => __('No Snippets found in Trash', 'wp-snippets')
			);
			register_post_type('snippet', $args);
		}

	public static function caps($action)
		{
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
			foreach(array('administrator') as $_role)
				if(is_object($_role = & get_role($_role)))
					foreach($caps as $_cap) switch($action)
					{
						case 'activate':
								$_role->add_cap($_cap);
								break;

						case 'deactivate':
								$_role->remove_cap($_cap);
								break;
					}
			unset($_role, $_cap); // Housekeeping.
		}

	public static function raw_html_settings($what_wp_says, $post_id, $meta_key, $single)
		{
			if(function_exists('rawhtml_get_settings_fields'))
				if($meta_key === '_rawhtml_settings' && get_post_type($post_id) === 'snippet')
					{
						$settings = implode(',', array_fill(0, count(rawhtml_get_settings_fields()), '0'));

						return ($single) ? $settings : array($settings);
					}
			return $what_wp_says; // Default return value.
		}

	public static function shortcode($attr = NULL, $content = NULL, $shortcode = NULL)
		{
			if(!$attr['slug']) // We do have a slug right?
				return ''; // Nothing to do in this case.

			if(!is_array($posts = get_posts(array('name' => (string)$attr['slug'], 'post_type' => 'snippet', 'numberposts' => 1))))
				return ''; // This slug was not found; possibly a typo in this case.

			if(empty($posts[0]) || empty($posts[0]->post_content))
				return ''; // No content; nothing to do.

			if(apply_filters('wp_snippet_exclude', FALSE, $posts[0]))
				return ''; // Excluding this one.

			$snippet = $posts[0]->post_content;

			foreach($attr as $_key => $_value) // Replacement codes.
				if($_key !== 'slug' && is_string($_key) && is_string($_value))
					$snippet = str_ireplace('%%'.$_key.'%%', $_value, $snippet);
			unset($_key, $_value); // Housekeeping.

			if(!($snippet = apply_filters('the_content', $snippet)))
				return ''; // Nothing to display.

			return apply_filters('wp_snippet', $snippet, $posts[0]);
		}

	public static function activate()
		{
			wp_snippets::register();
			wp_snippets::caps('activate');
			flush_rewrite_rules();
		}

	public static function deactivate()
		{
			wp_snippets::caps('deactivate');
			flush_rewrite_rules();
		}
}