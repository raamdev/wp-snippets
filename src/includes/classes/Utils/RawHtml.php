<?php
/**
 * Raw HTML utils.
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
 * Raw HTML utils.
 *
 * @since 161222.28602 Initial release.
 */
class RawHtml extends SCoreClasses\SCore\Base\Core
{
    /**
     * On WP `get_post_metadata` filter.
     *
     * @since 161222.28602 Initial release.
     *
     * @param mixed      $value    Value.
     * @param string|int $post_id  Post ID.
     * @param string     $meta_key Post meta key.
     * @param bool       $single   Return single value?
     *
     * @return mixed `$value` (possibly filtered).
     */
    public function onGetPostMetadata($value, $post_id, $meta_key, $single)
    {
        // Force all `disable_` flags off; snippets must use `[raw][/raw]` tags.
        if ($meta_key === '_rawhtml_settings' && get_post_type($post_id) === 'snippet' && function_exists('rawhtml_get_settings_fields')) {
            $value = implode(',', array_fill(0, count(rawhtml_get_settings_fields()), '0'));
            $value = $single ? $value : [$value]; // Based on `$single` param.
        }
        return $value; // Default return value.
    }
}
