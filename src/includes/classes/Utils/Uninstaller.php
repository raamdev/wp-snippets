<?php
/**
 * Uninstall utils.
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
 * Uninstall utils.
 *
 * @since 161222.28602 Initial release.
 */
class Uninstaller extends SCoreClasses\SCore\Base\Core
{
    /**
     * Other uninstall routines.
     *
     * @since 161222.28602 Initial release.
     *
     * @param int $site_counter Site counter.
     */
    public function onOtherUninstallRoutines(int $site_counter)
    {
        $this->deletePosts($site_counter);
        $this->deleteTaxonomies($site_counter);
        $this->deleteCaps($site_counter);
    }

    /**
     * Delete posts.
     *
     * @since 161222.28602 Initial release.
     *
     * @param int $site_counter Site counter.
     */
    protected function deletePosts(int $site_counter)
    {
        $WpDb = s::wpDb();

        $sql = /* All post IDs. */ '
            SELECT `ID`
                FROM `'.esc_sql($WpDb->posts).'`
            WHERE `post_type` = \'snippet\'
        ';
        if (!($results = $WpDb->get_results($sql))) {
            return; // Nothing to delete.
        }
        foreach ($results as $_result) {
            wp_delete_post($_result->ID, true);
        } // unset($_result); // Housekeeping.
    }

    /**
     * Delete taxonomies.
     *
     * @since 161222.28602 Initial release.
     *
     * @param int $site_counter Site counter.
     */
    protected function deleteTaxonomies(int $site_counter)
    {
        $term_ids = get_terms([
            'taxonomy'   => 'snippet_category',
            'hide_empty' => false,
            'fields'     => 'ids',
        ]);
        $term_ids = is_array($term_ids) ? $term_ids : [];

        foreach ($term_ids as $_term_id) {
            wp_delete_term($_term_id, 'snippet_category');
        } // unset($_term_id); // Houskeeping.

        $term_ids = get_terms([
            'taxonomy'   => 'snippet_tag',
            'hide_empty' => false,
            'fields'     => 'ids',
        ]);
        $term_ids = is_array($term_ids) ? $term_ids : [];

        foreach ($term_ids as $_term_id) {
            wp_delete_term($_term_id, 'snippet_tag');
        } // unset($_term_id); // Houskeeping.
    }

    /**
     * Delete caps.
     *
     * @since 161222.28602 Initial release.
     *
     * @param int $site_counter Site counter.
     */
    protected function deleteCaps(int $site_counter)
    {
        $caps = a::postTypeCaps(); // All caps.

        foreach (array_keys(wp_roles()->roles) as $_role) {
            if (!($_WP_Role = get_role($_role))) {
                continue; // Not possible.
            }
            foreach ($caps as $_cap) {
                $_WP_Role->remove_cap($_cap);
            } // unset($_cap);
        } // unset($_role, $_WP_Role);
    }
}
