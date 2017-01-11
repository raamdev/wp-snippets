<?php
/**
 * Application.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WpSnippets\Classes;

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
 * Application.
 *
 * @since 161222.28602 Initial release.
 */
class App extends SCoreClasses\App
{
    /**
     * Version.
     *
     * @since 161222.28602
     *
     * @type string Version.
     */
    const VERSION = '170111.29996'; //v//

    /**
     * Constructor.
     *
     * @since 161222.28602 Initial release.
     *
     * @param array $instance Instance args.
     */
    public function __construct(array $instance = [])
    {
        $instance_base = [
            '©di' => [
                '©default_rule' => [
                    'new_instances' => [
                    ],
                ],
            ],

            '§specs' => [
                '§in_wp'           => false,
                '§is_network_wide' => false,

                '§type'            => 'plugin',
                '§file'            => dirname(__FILE__, 4).'/plugin.php',
            ],
            '©brand' => [
                '©acronym'     => 'WP SNPTS',
                '©name'        => 'WP Snippets',

                '©slug'        => 'wp-snippets',
                '©var'         => 'wp_snippets',

                '©short_slug'  => 'wp-snpts',
                '©short_var'   => 'wp_snpts',

                '©text_domain' => 'wp-snippets',
            ],

            '§pro_option_keys' => [
                // Nothing for now.
            ],
            '§default_options' => [
                'public' => 'if_user_can_edit',

                'content_filters' => [
                    'jetpack-markdown',
                    'jetpack-latex',
                    'wptexturize',
                    'wpautop',
                    'shortcode_unautop',
                    'wp_make_content_images_responsive',
                    'capital_P_dangit',
                    'do_shortcode',
                    'convert_smilies',
                ],
            ],
        ];
        parent::__construct($instance_base, $instance);
    }

    /**
     * Early hook setup handler.
     *
     * @since 161222.28602 Initial release.
     */
    protected function onSetupEarlyHooks()
    {
        parent::onSetupEarlyHooks();

        s::addAction('vs_upgrades', [$this->Utils->Installer, 'onVsUpgrades']);
        s::addAction('other_install_routines', [$this->Utils->Installer, 'onOtherInstallRoutines']);
        s::addAction('other_uninstall_routines', [$this->Utils->Uninstaller, 'onOtherUninstallRoutines']);
    }

    /**
     * Other hook setup handler.
     *
     * @since 161222.28602 Initial release.
     */
    protected function onSetupOtherHooks()
    {
        parent::onSetupOtherHooks();

        // Post Type setup.

        add_action('init', [$this->Utils->PostType, 'onInit'], 6);

        // Shortcode-related hooks.

        add_filter('widget_text', 'do_shortcode');
        add_shortcode('snippet', [$this->Utils->Shortcode, 'onShortcode']);
        add_shortcode('snippet_template', [$this->Utils->Shortcode, 'onShortcode']);

        // Begin content filter hooks.

        $content_filters = s::getOption('content_filters');

        // Markdown is supported in the Post Type config. See {@link Utils->PostType{}}.
        // Therefore, these filters only apply to `[snippet_template]content[/snippet_template]`.

        if (in_array('jetpack-markdown', $content_filters, true) && s::jetpackCanMarkdown()) {
            s::addFilter('shortcode_content', s::class.'::jetpackMarkdown', -10000);
        }
        if (in_array('jetpack-latex', $content_filters, true) && s::jetpackCanLatex()) {
            s::addFilter('shortcode_content', 'latex_markup', 9);
        }
        // These filters apply to the snippet `\WP_Post->post_content` overall.
        // Therefore, they also apply to `[snippet_template]content[/snippet_template]`.

        if (in_array('wptexturize', $content_filters, true)) {
            s::addFilter('content', 'wptexturize', 10);
        }
        if (in_array('wpautop', $content_filters, true)) {
            s::addFilter('content', 'wpautop', 10);
        }
        if (in_array('shortcode_unautop', $content_filters, true)) {
            s::addFilter('content', 'shortcode_unautop', 10);
        }
        if (in_array('wp_make_content_images_responsive', $content_filters, true)) {
            s::addFilter('content', 'wp_make_content_images_responsive', 10);
        }
        if (in_array('capital_P_dangit', $content_filters, true)) {
            s::addFilter('content', 'capital_P_dangit', 11);
        }
        if (in_array('do_shortcode', $content_filters, true)) {
            s::addFilter('content', 'do_shortcode', 11);
        }
        if (in_array('convert_smilies', $content_filters, true)) {
            s::addFilter('content', 'convert_smilies', 20);
        }
        // Additional compatibility hooks.

        if (defined('RAWHTML_PLUGIN_FILE')) { // Raw HTML plugin compatibility.
            add_filter('get_post_metadata', [$this->Utils->RawHtml, 'onGetPostMetadata'], 10, 4);
        }
        if ($this->Wp->is_woocommerce_product_vendors_active) {
            add_action('pre_get_posts', [$this->Utils->Vendors, 'onPreGetPosts']);
            add_filter('wcpv_default_admin_vendor_role_caps', [$this->Utils->Vendors, 'onDefaultCaps']);
            add_filter('wcpv_default_manager_vendor_role_caps', [$this->Utils->Vendors, 'onDefaultCaps']);
        }
        // WordPress admin-related hooks; e.g., menu pages, etc.

        if ($this->Wp->is_admin) { // Plugin menu page.
            add_action('admin_menu', [$this->Utils->MenuPage, 'onAdminMenu']);
        }
    }
}
