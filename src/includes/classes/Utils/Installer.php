<?php
/**
 * Install utils.
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
 * Install utils.
 *
 * @since 161222.28602 Initial release.
 */
class Installer extends SCoreClasses\SCore\Base\Core
{
    /**
     * Version-specific upgrades.
     *
     * @since 161222.28602 Initial release.
     *
     * @param array $history Install history.
     */
    public function onVsUpgrades(array $history)
    {
        // Nothing here for now.
    }

    /**
     * Other install routines.
     *
     * @since 161222.28602 Initial release.
     *
     * @param array $history Install history.
     */
    public function onOtherInstallRoutines(array $history)
    {
        $this->addCaps(); // Install caps.
    }

    /**
     * Add capabilities.
     *
     * @since 161222.28602 Initial release.
     */
    protected function addCaps()
    {
        $caps = a::postTypeCaps();

        foreach (['administrator', 'editor', 'shop_manager'] as $_role) {
            if (!($_WP_Role = get_role($_role))) {
                continue; // Not possible.
            }
            foreach ($caps as $_cap) {
                $_WP_Role->add_cap($_cap);
            } // unset($_cap);
        } // unset($_role, $_WP_Role);

        if ($this->Wp->is_woocommerce_product_vendors_active) {
            $vendor_caps = a::postTypeVendorCaps();

            foreach (['wc_product_vendors_admin_vendor', 'wc_product_vendors_manager_vendor'] as $_role) {
                if (!($_WP_Role = get_role($_role))) {
                    continue; // Not possible.
                }
                foreach ($vendor_caps as $_cap) {
                    $_WP_Role->add_cap($_cap);
                } // unset($_cap);
            } // unset($_role, $_WP_Role);
        }
    }
}
