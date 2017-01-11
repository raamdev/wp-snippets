<?php
/**
 * Shortcode utils.
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
 * Shortcode utils.
 *
 * @since 161222.28602 Initial release.
 */
class Shortcode extends SCoreClasses\SCore\Base\Core
{
    /**
     * Parsing?
     *
     * @since 161222.28602
     *
     * @type bool
     */
    protected $is_parsing;

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

        $this->is_parsing = false; // Initialize.
    }

    /**
     * Shortcode.
     *
     * @since 161222.28602 Initial release.
     *
     * @param array|string $atts      Shortcode attributes.
     * @param string|null  $content   Shortcode content.
     * @param string       $shortcode Shortcode name.
     */
    public function onShortcode($atts = [], $content = '', $shortcode = ''): string
    {
        /*
         * Parameters.
         */
        $atts      = is_array($atts) ? $atts : [];
        $content   = (string) $content;
        $shortcode = (string) $shortcode;

        /*
         * Attributes.
         */
        $default_atts = [
            'slug'        => '', // A single slug.
            'in_category' => '', // Comma-delimited slugs.
            'with_tag'    => '', // Comma-delimited slugs.
            'orderby'     => '', // e.g., `rand`, `date`, `modified`, etc.
            // See `orderby`: <https://codex.wordpress.org/Class_Reference/WP_Query>
        ];
        $raw_atts         = $atts; // Copy.
        $atts             = c::unescHtml($atts);
        $atts             = array_merge($default_atts, $atts);
        $default_att_keys = array_keys($default_atts);

        if ($shortcode) { // We don't use `shortcode_atts()` on purpose.
            $atts = apply_filters('shortcode_atts_'.$shortcode, $atts, $default_atts, $raw_atts, $shortcode);
        } // However, this will still apply the filter like `shortcode_atts()` would do.

        $atts = array_map('strval', $atts); // Force string values on all please.

        // Handle `random:` prefix (back compat).
        if (mb_stripos($atts['slug'], 'random:') === 0) {
            $atts['slug']    = mb_substr($atts['slug'], 7);
            $atts['orderby'] = 'rand'; // Force `orderby`.
        } // ↑ Should eventually be removed in a future release.

        // Convert these keys into arrays, for the WP_Query below.
        $atts['slug']        = preg_split('/[\s,]+/u', $atts['slug'], -1, PREG_SPLIT_NO_EMPTY);
        $atts['in_category'] = preg_split('/[\s,]+/u', $atts['in_category'], -1, PREG_SPLIT_NO_EMPTY);
        $atts['with_tag']    = preg_split('/[\s,]+/u', $atts['with_tag'], -1, PREG_SPLIT_NO_EMPTY);

        /*
         * Query posts (based on atts).
         */
        $query_args = [
            'paged'               => 1,
            'posts_per_page'      => 1,
            'ignore_sticky_posts' => true,
            'suppress_filters'    => true,
            'post_type'           => 'snippet',
            'post_status'         => 'publish',
        ];
        if ($atts['slug']) {
            $query_args['post_name__in'] = $atts['slug'];
        }
        if ($atts['in_category']) {
            if (!empty($query_args['tax_query'])) {
                $query_args['tax_query']['relation'] = 'AND';
            }
            $query_args['tax_query'][] = [
                'taxonomy' => 'snippet_category',
                'terms'    => $atts['in_category'],
                'field'    => 'slug', 'operator' => 'IN',
            ];
        }
        if ($atts['with_tag']) {
            if (!empty($query_args['tax_query'])) {
                $query_args['tax_query']['relation'] = 'AND';
            }
            $query_args['tax_query'][] = [
                'taxonomy' => 'snippet_tag',
                'terms'    => $atts['with_tag'],
                'field'    => 'slug', 'operator' => 'IN',
            ];
        }
        if ($atts['orderby']) {
            $query_args['orderby'] = $atts['orderby'];
        }
        $WP_Query = new \WP_Query($query_args); // New query.

        if (!($WP_Posts = $WP_Query->get_posts()) || empty($WP_Posts[0]->post_content)) {
            return ''; // No snippet or no content; nothing to do.
        }
        $Snippet         = $WP_Posts[0]; // WP_Post object reference.
        $snippet_content = $WP_Posts[0]->post_content->post_content; // A copy.
        // This must be a copy of the snippet content; i.e., it is filtered down below.

        /*
         * Parse `%%replacement_codes%%` in templates.
         */
        if ($shortcode === 'snippet_template') {
            foreach ($atts as $_key => $_value) {
                if (is_string($_value) && !in_array($_key, $default_att_keys, true)) {
                    $snippet_content = str_ireplace('%%'.$_key.'%%', $_value, $snippet_content);
                } // Bypass all default attribute keys; i.e., fill custom keys only.
            } // unset($_key, $_value); // Housekeeping.
            $content                 = s::applyFilters('shortcode_content', $content);
            $snippet_content         = preg_replace('/%%content%%/', $content, $snippet_content);
            $snippet_content         = preg_replace('/%%.+?%%/', '', $snippet_content);
        }
        /*
         * Handle top-level flags/globals.
         */
        $is_a_top_level_snippet = false; // Initialize.

        if (!$this->is_parsing) {
            $this->is_parsing        = true;
            $is_a_top_level_snippet  = true;
            $GLOBALS['snippet_post'] = $GLOBALS['post'] ?? null;
        }
        /*
         * Point post data to the parent post.
         */
        setup_postdata($GLOBALS['post'] = $Snippet);

        /*
         * Apply content/snippet filters.
         * This is where nested snippets may occur.
         */
        $snippet_content = s::applyFilters('content', $snippet_content);

        /*
         * Restore top-level flags/globals.
         */
        if ($this->is_parsing && $is_a_top_level_snippet) {
            $GLOBALS['post'] = $GLOBALS['snippet_post'];

            if ($GLOBALS['post'] instanceof \WP_Post) {
                setup_postdata($GLOBALS['post']);
            }
            $GLOBALS['snippet_post'] = null;
            $this->is_parsing        = false;
        }
        /*
         * Return content.
         */
        return $snippet_content;
    }
}
