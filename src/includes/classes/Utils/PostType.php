<?php
/**
 * Post type utils.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WpSnippets\Classes\Utils;

use WebSharks\WpSharks\WpSnippets\Classes;
use WebSharks\WpSharks\WpSnippets\Interfaces;
use WebSharks\WpSharks\WpSnippets\Traits;
#
use WebSharks\WpSharks\WpSnippets\Classes\AppFacades as a;
use WebSharks\WpSharks\WpSnippets\Classes\SCoreFacades as s;
use WebSharks\WpSharks\WpSnippets\Classes\CoreFacades as c;
#
use WebSharks\WpSharks\Core\Classes as SCoreClasses;
use WebSharks\WpSharks\Core\Interfaces as SCoreInterfaces;
use WebSharks\WpSharks\Core\Traits as SCoreTraits;
#
use WebSharks\Core\WpSharksCore\Classes as CoreClasses;
use WebSharks\Core\WpSharksCore\Classes\Core\Base\Exception;
use WebSharks\Core\WpSharksCore\Interfaces as CoreInterfaces;
use WebSharks\Core\WpSharksCore\Traits as CoreTraits;
#
use function assert as debug;
use function get_defined_vars as vars;

/**
 * Post type utils.
 *
 * @since 161222.28602 Initial release.
 */
class PostType extends SCoreClasses\SCore\Base\Core
{
    /**
     * All caps.
     *
     * @since 161222.28602
     *
     * @type array All caps.
     */
    public $caps;

    /**
     * All vendor caps.
     *
     * @since 161222.28602
     *
     * @type array All vendor caps.
     */
    public $vendor_caps;

    /**
     * Class constructor.
     *
     * @since 161222.28602 Initial release.
     *
     * @param Classes\App $App Instance.
     */
    public function __construct(Classes\App $App)
    {
        parent::__construct($App);

        $this->caps = [
            'create_snippets',

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
        ];
        $this->vendor_caps = $this->caps;

        $this->caps        = s::applyFilters('caps', $this->caps);
        $this->vendor_caps = s::applyFilters('vendor_caps', $this->vendor_caps);
    }

    /**
     * On WP `init` hook.
     *
     * @since 161222.28602 Initial release.
     */
    public function onInit()
    {
        # Features.

        $supports = [
            'title',

            'editor',
            'revisions',
            'wpcom-markdown',

            'author',
            'custom-fields',
        ];
        if (!in_array('jetpack-markdown', s::getOption('content_filters'), true)) {
            $supports = array_diff($supports, ['wpcom-markdown']);
        }
        # Allow public access?

        $public_option = s::getOption('public');

        if ($public_option === 'always') {
            $public = true; // Always public.
        } elseif ($public_option === 'if_user_can_edit') {
            $public = current_user_can('edit_snippets');
        } else { // Fallback behavhior (default).
            $public = false; // Never public.
        }
        # Post type configuration.

        register_post_type(
            'snippet',
            s::applyFilters('post_type_args', [
                'public'   => $public,
                'supports' => $supports,

                'show_ui'             => true,
                'exclude_from_search' => true,
                'show_in_nav_menus'   => false,

                'rewrite' => [
                    'with_front' => false,
                    'slug'       => 'snippet',
                ],
                'menu_icon' => 'dashicons-editor-code',

                'description' => __('Snippets', 'wp-snippets'),

                'labels' => [ // See: <http://jas.xyz/244m2Sd>
                    'name'          => __('Snippets', 'wp-snippets'),
                    'singular_name' => __('Snippet', 'wp-snippets'),

                    'name_admin_bar' => __('Snippet', 'wp-snippets'),
                    'menu_name'      => __('Snippets', 'wp-snippets'),

                    'all_items'    => __('All Snippets', 'wp-snippets'),
                    'add_new'      => __('Add Snippet', 'wp-snippets'),
                    'add_new_item' => __('Add New Snippet', 'wp-snippets'),
                    'new_item'     => __('New Snippet', 'wp-snippets'),
                    'edit_item'    => __('Edit Snippet', 'wp-snippets'),
                    'view_item'    => __('View Snippet', 'wp-snippets'),

                    'search_items'       => __('Search Snippets', 'wp-snippets'),
                    'not_found'          => __('No Snippets Found', 'wp-snippets'),
                    'not_found_in_trash' => __('No Snippets Found in Trash', 'wp-snippets'),

                    'insert_into_item'      => __('Insert Into Snippet', 'wp-snippets'),
                    'uploaded_to_this_item' => __('Upload to this Snippet', 'wp-snippets'),

                    'featured_image'        => __('Set Featured Image', 'wp-snippets'),
                    'remove_featured_image' => __('Remove Featured Image', 'wp-snippets'),
                    'use_featured_image'    => __('Use as Featured Image', 'wp-snippets'),

                    'items_list'            => __('Snippets List', 'wp-snippets'),
                    'items_list_navigation' => __('Snippets List Navigation', 'wp-snippets'),

                    'archives'          => __('Snippet Archives', 'wp-snippets'),
                    'filter_items_list' => __('Filter Snippets List', 'wp-snippets'),
                    'parent_item_colon' => __('Parent Snippet:', 'wp-snippets'),
                ],

                'map_meta_cap'    => true,
                'capability_type' => [
                    'snippet',
                    'snippets',
                ],
            ])
        );
        # Post type category taxonomy.

        register_taxonomy(
            'snippet_category',
            'snippet',
            s::applyFilters('category_taxonomy_args', [
                'public'            => true,
                'show_in_nav_menus' => false,
                'show_admin_column' => true,
                'hierarchical'      => true,

                'rewrite' => [
                    'with_front' => false,
                    'slug'       => 'snippet-category',
                ],
                'description' => __('Snippet Categories', 'wp-snippets'),

                'labels' => [ // See: <http://jas.xyz/244m1Oc>
                    'name'          => __('Snippet Categories', 'wp-snippets'),
                    'singular_name' => __('Snippet Category', 'wp-snippets'),

                    'name_admin_bar' => __('Snippet Category', 'wp-snippets'),
                    'menu_name'      => __('Categories', 'wp-snippets'),

                    'all_items'           => __('All Categories', 'wp-snippets'),
                    'add_new_item'        => __('Add New Category', 'wp-snippets'),
                    'new_item_name'       => __('New Category Name', 'wp-snippets'),
                    'add_or_remove_items' => __('Add or Remove Categories', 'wp-snippets'),
                    'view_item'           => __('View Category', 'wp-snippets'),
                    'edit_item'           => __('Edit Category', 'wp-snippets'),
                    'update_item'         => __('Update Category', 'wp-snippets'),

                    'search_items' => __('Search Categories', 'wp-snippets'),
                    'not_found'    => __('No Categories Found', 'wp-snippets'),
                    'no_terms'     => __('No Categories', 'wp-snippets'),

                    'choose_from_most_used'      => __('Choose From the Most Used Categories', 'wp-snippets'),
                    'separate_items_with_commas' => __('Separate Categories w/ Commas', 'wp-snippets'),

                    'items_list'            => __('Categories List', 'wp-snippets'),
                    'items_list_navigation' => __('Categories List Navigation', 'wp-snippets'),

                    'archives'          => __('All Categories', 'wp-snippets'),
                    'popular_items'     => __('Popular Categories', 'wp-snippets'),
                    'parent_item'       => __('Parent Category', 'wp-snippets'),
                    'parent_item_colon' => __('Parent Category:', 'wp-snippets'),
                ],

                'capabilities' => [
                    'assign_terms' => 'edit_snippets',
                    'edit_terms'   => 'edit_snippets',
                    'manage_terms' => 'edit_others_snippets',
                    'delete_terms' => 'delete_others_snippets',
                ],
            ])
        );
        # Post type tag taxonomy.

        register_taxonomy(
            'snippet_tag',
            'snippet',
            s::applyFilters('tag_taxonomy_args', [
                'public'            => true,
                'show_in_nav_menus' => false,
                'show_admin_column' => true,
                'hierarchical'      => false,

                'rewrite' => [
                    'with_front' => false,
                    'slug'       => 'snippet-tag',
                ],
                'description' => __('Snippet Tags', 'wp-snippets'),

                'labels' => [ // See: <http://jas.xyz/244m1Oc>
                    'name'          => __('Snippet Tags', 'wp-snippets'),
                    'singular_name' => __('Snippet Tag', 'wp-snippets'),

                    'name_admin_bar' => __('Snippet Tag', 'wp-snippets'),
                    'menu_name'      => __('Tags', 'wp-snippets'),

                    'all_items'           => __('All Tags', 'wp-snippets'),
                    'add_new_item'        => __('Add New Tag', 'wp-snippets'),
                    'new_item_name'       => __('New Tag Name', 'wp-snippets'),
                    'add_or_remove_items' => __('Add or Remove Tags', 'wp-snippets'),
                    'view_item'           => __('View Tag', 'wp-snippets'),
                    'edit_item'           => __('Edit Tag', 'wp-snippets'),
                    'update_item'         => __('Update Tag', 'wp-snippets'),

                    'search_items' => __('Search Tags', 'wp-snippets'),
                    'not_found'    => __('No Tags Found', 'wp-snippets'),
                    'no_terms'     => __('No Tags', 'wp-snippets'),

                    'choose_from_most_used'      => __('Choose From the Most Used Tags', 'wp-snippets'),
                    'separate_items_with_commas' => __('Separate Tags w/ Commas', 'wp-snippets'),

                    'items_list'            => __('Tags List', 'wp-snippets'),
                    'items_list_navigation' => __('Tags List Navigation', 'wp-snippets'),

                    'archives'          => __('All Tags', 'wp-snippets'),
                    'popular_items'     => __('Popular Tags', 'wp-snippets'),
                    'parent_item'       => __('Parent Tag', 'wp-snippets'),
                    'parent_item_colon' => __('Parent Tag:', 'wp-snippets'),
                ],

                'capabilities' => [
                    'assign_terms' => 'edit_snippets',
                    'edit_terms'   => 'edit_snippets',
                    'manage_terms' => 'edit_others_snippets',
                    'delete_terms' => 'delete_others_snippets',
                ],
            ])
        );
    }
}
