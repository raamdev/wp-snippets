<?php
/*
Version: 131121
Text Domain: wp-snippets
Plugin Name: WP Snippets

Author: WebSharks, Inc. (Jason Caldwell)
Author URI: http://www.websharks-inc.com/

Plugin URI: http://www.websharks-inc.com/product/wp-snippets/
Description: Create Snippets! This plugin adds a new Post Type. Snippets can be included in Posts/Pages/Widgets via shortcodes.
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));
/* ------------------------------------------------------------------------- */

/**
 * WP Snippets class.
 */
class wp_snippets // Like PHP includes; for WordPress.
{
	/**
	 * Flag for initializer.
	 *
	 * @var boolean Initialized?
	 */
	public static $initialized = FALSE;

	/**
	 * Roles to receive all caps.
	 *
	 * @var array Array of WP roles.
	 */
	public static $roles_all_caps = array();

	/**
	 * Roles to receive edit caps.
	 *
	 * @var array Array of WP roles.
	 */
	public static $roles_edit_caps = array();

	/**
	 * Flag used by shortcode parser.
	 *
	 * @var boolean Currently parsing?
	 */
	public static $is_parsing = FALSE;

	/**
	 * Plugin activation handler.
	 */
	public static function activate()
	{
		self::initialize();
		self::caps('activate');
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation handler.
	 */
	public static function deactivate()
	{
		self::initialize();
		self::caps('deactivate');
		flush_rewrite_rules();
	}

	/**
	 * Plugin initializer.
	 */
	public static function initialize()
	{
		if(self::$initialized)
			return; // Already did this.

		load_plugin_textdomain('wp-snippets');

		$GLOBALS['snippet_post'] = NULL; // Initialize.

		if(!defined('WP_SNIPPET_ROLES_ALL_CAPS'))
			define('WP_SNIPPET_ROLES_ALL_CAPS', 'administrator');

		if(!defined('WP_SNIPPET_ROLES_EDIT_CAPS'))
			define('WP_SNIPPET_ROLES_EDIT_CAPS', 'administrator,editor,author');

		if(WP_SNIPPET_ROLES_ALL_CAPS) // Certain roles should receive all snippet-related caps?
			self::$roles_all_caps = preg_split('/[\s;,]+/', WP_SNIPPET_ROLES_ALL_CAPS, NULL, PREG_SPLIT_NO_EMPTY);
		self::$roles_all_caps = apply_filters('wp_snippet_roles_all_caps', self::$roles_all_caps);

		if(WP_SNIPPET_ROLES_EDIT_CAPS) // Certain roles should receive edit-related caps for snippets?
			self::$roles_edit_caps = preg_split('/[\s;,]+/', WP_SNIPPET_ROLES_EDIT_CAPS, NULL, PREG_SPLIT_NO_EMPTY);
		self::$roles_edit_caps = apply_filters('wp_snippet_roles_edit_caps', self::$roles_edit_caps);

		self::register(); // Register snippet post type.

		add_filter('widget_text', 'do_shortcode');
		add_shortcode('snippet', 'wp_snippets::shortcode');
		add_shortcode('snippet_template', 'wp_snippets::shortcode');

		if(defined('RAWHTML_PLUGIN_FILE') && function_exists('rawhtml_get_settings_fields'))
			add_filter('get_post_metadata', 'wp_snippets::raw_html_settings', 10, 4);

		self::$initialized = TRUE; // Initialized now.
	}

	/**
	 * Registers the snippet post type.
	 */
	public static function register()
	{
		$post_type_args           = array
		(
			'public'       => current_user_can('edit_snippets'), // For previews.
			'show_ui'      => TRUE, 'exclude_from_search' => TRUE, 'show_in_nav_menus' => FALSE,
			'map_meta_cap' => TRUE, 'capability_type' => array('snippet', 'snippets'),
			'rewrite'      => array('slug' => 'snippet', 'with_front' => FALSE),
			'supports'     => array('title', 'editor', 'author', 'revisions'),
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
			'not_found_in_trash' => __('No Snippets found in Trash', 'wp-snippets'),
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
		                                               'delete_terms' => 'delete_others_snippets'),
		);
		register_taxonomy('snippet_category', array('snippet'), $taxonomy_args);
	}

	/**
	 * Activates or deactivates snippet-related caps.
	 *
	 * @param string $action One of `activate` or `deactivate`.
	 */
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

		                  'read_private_snippets',
		);
		if($action === 'deactivate') // All on deactivate.
			$_roles = array_keys($GLOBALS['wp_roles']->roles);
		else $_roles = self::$roles_all_caps;

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
		                   'delete_published_snippets',
		);
		if($action === 'deactivate') // All on deactivate.
			$_roles = array_keys($GLOBALS['wp_roles']->roles);
		else $_roles = self::$roles_edit_caps;

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

	/**
	 * Filters Raw HTML plugin settings for a specific post.
	 *
	 * @param mixed   $what_wp_says Current post meta value.
	 * @param integer $post_id Post ID.
	 * @param string  $meta_key Post meta key.
	 * @param boolean $single Return a single value?
	 *
	 * @return mixed Post meta value after having been filtered by this routine.
	 */
	public static function raw_html_settings($what_wp_says, $post_id, $meta_key, $single)
	{
		if(function_exists('rawhtml_get_settings_fields'))
			if($meta_key === '_rawhtml_settings' && get_post_type($post_id) === 'snippet')
			{
				$settings = // Force all `disable_` flags off; Snippets must use `[raw][/raw]` tags.
					implode(',', array_fill(0, count(rawhtml_get_settings_fields()), '0'));

				return $single ? $settings : array($settings);
			}
		return $what_wp_says; // Default return value.
	}

	/**
	 * Shortcode (i.e. snippet) parser.
	 *
	 * @param array|null  $attr An array of shortcode attributes.
	 * @param string|null $content Shortcode content; if applicable.
	 * @param string|null $shortcode The name of the shortcode.
	 *
	 * @return string Shortcode (i.e. snippet) content.
	 */
	public static function shortcode($attr = NULL, $content = NULL, $shortcode = NULL)
	{
		# Validation and DB query.

		if(empty($attr['slug'])) return ''; // Nothing to do.

		if(!is_array($posts = get_posts(array('name' => (string)$attr['slug'], 'post_type' => 'snippet', 'numberposts' => 1))))
			return ''; // The slug was not found; possibly a typo in this case.

		if(empty($posts[0]) || empty($posts[0]->post_content))
			return ''; // No content; nothing to do.

		# Snippet and its content.

		$snippet         = $posts[0]; // Object reference.
		$snippet_content = $snippet->post_content;

		# Parse replacement codes in snippet templates.

		if($shortcode === 'snippet_template') // A snippet template?
		{
			foreach($attr as $_key => $_value) // Fill replacement codes.
				$snippet_content = str_ireplace('%%'.$_key.'%%', (string)$_value, $snippet_content);
			unset($_key, $_value); // Housekeeping.

			$snippet_content = preg_replace('/%%content%%/', (string)$content, $snippet_content);
			$snippet_content = preg_replace('/%%.+?%%/', '', $snippet_content);
		}
		# Setup post data; and detect top-level snippets here.

		$is_top_level_snippet = FALSE; // Initialize.
		$wp_current_filter    = $wp_current_filter_temp = NULL;

		if(!self::$is_parsing) // Not currently parsing?
		{
			self::$is_parsing        = TRUE;
			$is_top_level_snippet    = TRUE;
			$GLOBALS['snippet_post'] = $GLOBALS['post'];

			$wp_current_filter      = &$GLOBALS['wp_filter'][current_filter()];
			$wp_current_filter_temp = $wp_current_filter; // Backup filter state.
		}
		setup_postdata($GLOBALS['post'] = $snippet);

		# Apply content/snippet filters.

		$snippet_content = apply_filters('the_content', $snippet_content);
		$snippet_content = apply_filters('the_snippet_content', $snippet_content);

		# Restore post data; if applicable.

		if($is_top_level_snippet) // Only when done w/ top-level snippet.
		{
			$wp_current_filter = $wp_current_filter_temp; // Restore filter state.

			$GLOBALS['post'] = $GLOBALS['snippet_post']; // Restore.
			if(!empty($GLOBALS['post']->ID)) setup_postdata($GLOBALS['post']);

			$GLOBALS['snippet_post'] = NULL; // Nullify.
			self::$is_parsing        = FALSE; // Reset.
		}
		# Return snippet content now.

		return $snippet_content;
	}
}

/*
 * Primary plugin hooks.
 */
add_action('init', 'wp_snippets::initialize');
register_activation_hook(__FILE__, 'wp_snippets::activate');
register_deactivation_hook(__FILE__, 'wp_snippets::deactivate');