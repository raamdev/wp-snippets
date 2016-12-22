<?php
/**
 * Post type.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WpSnippets\Traits\Facades;

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
 * Post type.
 *
 * @since 161222.28602 Initial release.
 */
trait PostType
{
    /**
     * @since 161222.28602 Initial release.
     * @see Classes\Utils\PostType::$caps
     */
    public static function postTypeCaps()
    {
        return $GLOBALS[static::class]->Utils->PostType->caps;
    }

    /**
     * @since 161222.28602 Initial release.
     * @see Classes\Utils\PostType::$vendor_caps
     */
    public static function postTypeVendorCaps()
    {
        return $GLOBALS[static::class]->Utils->PostType->vendor_caps;
    }
}
