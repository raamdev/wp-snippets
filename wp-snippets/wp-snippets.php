<?php
/*
Version: 131121
Text Domain: wp-snippets
Plugin Name: WP Snippets

Author URI: http://www.websharks-inc.com/
Author: WebSharks, Inc. (Jason Caldwell)

Plugin URI: http://www.websharks-inc.com/product/wp-snippets/
Description: Create Snippets! This plugin adds a new Post Type. Snippets can be included in Posts/Pages/Widgets via shortcodes.
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

if(!defined('WP_SNIPPET_ROLES_ALL_CAPS')) define('WP_SNIPPET_ROLES_ALL_CAPS', 'administrator');
if(!defined('WP_SNIPPET_ROLES_EDIT_CAPS')) define('WP_SNIPPET_ROLES_EDIT_CAPS', 'administrator,editor,author');

class wp_snippets // WP Snippets; like PHP includes for WordPress.
{
	public static $roles_all_caps = array(); // WP Roles; as array.
	public static $roles_edit_caps = array(); // WP Roles; as array.

	public static function init() // Initialize WP Snippets.
		{
			load_plugin_textdomain('wp-snippets');

			if(WP_SNIPPET_ROLES_ALL_CAPS) // Specific Roles?
				wp_snippets::$roles_all_caps = // Convert these to an array.
					preg_split('/[\s;,]+/', WP_SNIPPET_ROLES_ALL_CAPS, NULL, PREG_SPLIT_NO_EMPTY);
			wp_snippets::$roles_all_caps = apply_filters('wp_snippet_roles_all_caps', wp_snippets::$roles_all_caps);

			if(WP_SNIPPET_ROLES_EDIT_CAPS) // Specific Roles?
				wp_snippets::$roles_edit_caps = // Convert these to an array.
					preg_split('/[\s;,]+/', WP_SNIPPET_ROLES_EDIT_CAPS, NULL, PREG_SPLIT_NO_EMPTY);
			wp_snippets::$roles_edit_caps = apply_filters('wp_snippet_roles_edit_caps', wp_snippets::$roles_edit_caps);

			wp_snippets::register();

			add_filter('widget_text', 'do_shortcode');
			add_shortcode('snippet', 'wp_snippets::shortcode');
			add_shortcode('snippet_template', 'wp_snippets::shortcode');

			if(defined('RAWHTML_PLUGIN_FILE') && function_exists('rawhtml_get_settings_fields'))
				add_filter('get_post_metadata', 'wp_snippets::raw_html_settings', 10, 4);

			$GLOBALS['snippet_post'] = NULL; // Initialize this reference.
		}

	public static function register()
		{
			$post_type_args           = array
			(
				'public'       => current_user_can('edit_snippets'), // For previews.
				'show_ui'      => TRUE, 'exclude_from_search' => TRUE, 'show_in_nav_menus' => FALSE,
				'map_meta_cap' => TRUE, 'capability_type' => array('snippet', 'snippets'),
				'rewrite'      => array('slug' => 'snippet', 'with_front' => FALSE),
				'supports'     => array('title', 'editor', 'author', 'revisions')
			);
			$post_type_args['labels'] = array
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
				'not_found'          => __('No Snippets found', 'wp-snippets'),
				'not_found_in_trash' => __('No Snippets found in Trash', 'wp-snippets')
			);
			register_post_type('snippet', $post_type_args);

			$taxonomy_args = array // Categories.
			(
				'public'       => TRUE, 'show_admin_column' => TRUE,
				'hierarchical' => TRUE, // This will use category labels.
				'rewrite'      => array('slug' => 'snippet-category', 'with_front' => FALSE),
				'capabilities' => array('assign_terms' => 'edit_snippets',
				                        'edit_terms'   => 'edit_snippets',
				                        'manage_terms' => 'edit_others_snippets',
				                        'delete_terms' => 'delete_others_snippets')
			);
			register_taxonomy('snippet_category', array('snippet'), $taxonomy_args);
		}

	public static function caps($action)
		{
			$all_caps = array // The ability to manage (all caps).
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
			if($action === 'deactivate') // All on deactivate.
				$_roles = array_keys($GLOBALS['wp_roles']->roles);
			else $_roles = wp_snippets::$roles_all_caps;

			foreach($_roles as $_role) if(is_object($_role = get_role($_role)))
				foreach($all_caps as $_cap) switch($action)
				{
					case 'activate':
							$_role->add_cap($_cap);
							break;

					case 'deactivate':
							$_role->remove_cap($_cap);
							break;
				}
			unset($_roles, $_role, $_cap); // Housekeeping.

			$edit_caps = array // The ability to edit/publish/delete.
			(
				'edit_snippets',
				'edit_published_snippets',

				'publish_snippets',

				'delete_snippets',
				'delete_published_snippets'
			);
			if($action === 'deactivate') // All on deactivate.
				$_roles = array_keys($GLOBALS['wp_roles']->roles);
			else $_roles = wp_snippets::$roles_edit_caps;

			foreach($_roles as $_role) if(is_object($_role = get_role($_role)))
				foreach((($action === 'deactivate') ? $all_caps : $edit_caps) as $_cap) switch($action)
				{
					case 'activate':
							$_role->add_cap($_cap);
							break;

					case 'deactivate':
							$_role->remove_cap($_cap);
							break;
				}
			unset($_roles, $_role, $_cap); // Housekeeping.
		}

	public static function raw_html_settings($what_wp_says, $post_id, $meta_key, $single)
		{
			if(function_exists('rawhtml_get_settings_fields'))
				if($meta_key === '_rawhtml_settings' && get_post_type($post_id) === 'snippet')
					{
						$settings = // Force all `disable_` flags off; Snippets must use `[raw][/raw]` tags.
							implode(',', array_fill(0, count(rawhtml_get_settings_fields()), '0'));

						return ($single) ? $settings : array($settings);
					}
			return $what_wp_says; // Default return value.
		}

	public static function shortcode($attr = NULL, $content = NULL, $shortcode = NULL)
		{
			if(empty($attr['slug'])) return ''; // Nothing to do in this case.

			if(!is_array($posts = get_posts(array('name' => (string)$attr['slug'], 'post_type' => 'snippet', 'numberposts' => 1))))
				return ''; // The slug was not found; possibly a typo in this case.

			if(empty($posts[0]) || empty($posts[0]->post_content))
				return ''; // No content; nothing to do.

			$snippet         = $posts[0]; // Object reference.
			$snippet_content = $snippet->post_content;

			if($shortcode === 'snippet_template') // Template?
				{
					foreach($attr as $_key => $_value)
						$snippet_content = str_ireplace('%%'.$_key.'%%', (string)$_value, $snippet_content);
					$snippet_content = preg_replace('/%%content%%/', (string)$content, $snippet_content);
					$snippet_content = preg_replace('/%%.+?%%/', '', $snippet_content);
					unset($_key, $_value); // Housekeeping.
				}
			$GLOBALS['snippet_post'] = // Possible parent post reference.
				$GLOBALS['post']; // This could be empty; Snippets can be in widgets too.
			setup_postdata($GLOBALS['post'] = $snippet); // For filters.

			$snippet_content = apply_filters('the_content', $snippet_content);
			$snippet_content = apply_filters('the_snippet_content', $snippet_content);

			$GLOBALS['post'] = $GLOBALS['snippet_post']; // Restore.
			if(!empty($GLOBALS['post']->ID)) setup_postdata($GLOBALS['post']);
			$GLOBALS['snippet_post'] = NULL; // Nullify now.

			return $snippet_content;
		}

	public static function activate()
		{
			wp_snippets::init();
			wp_snippets::caps('activate');
			flush_rewrite_rules();
		}

	public static function deactivate()
		{
			wp_snippets::caps('deactivate');
			flush_rewrite_rules();
		}
}

add_action('init', 'wp_snippets::init');
register_activation_hook(__FILE__, 'wp_snippets::activate');
register_deactivation_hook(__FILE__, 'wp_snippets::deactivate');