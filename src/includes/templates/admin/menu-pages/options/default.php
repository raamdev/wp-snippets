<?php
/**
 * Template.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
declare(strict_types=1);
namespace WebSharks\WpSharks\WpSnippets;

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

$Form = $this->s::menuPageForm('§save-options');
?>
<?= $Form->openTag(); ?>

    <?= $Form->openTable(
        __('General Options', 'wp-snippets'),
        sprintf(__('You can browse our <a href="%1$s" target="_blank">knowledge base</a> to learn more about these options.', 'wp-snippets'), esc_url(s::brandUrl('/kb')))
    ); ?>

        <?= $Form->selectRow([
            'label' => __('Publicly Queryable?', 'wp-snippets'),
            'tip'   => __('This determines whether Snippets themselves can be viewed in a stand-alone fashion (i.e., accessible via their own Permalink).<hr />As opposed to being hidden from the public and only used as a design tool behind-the-scenes, within WordPress.', 'wp-snippets'),

            'name'     => 'public',
            'value'    => s::getOption('public'),
            'options'  => [
                'always'           => __('Yes, Snippets are always public (via their own permalink).', 'wp-snippets'),
                'if_user_can_edit' => __('Yes, but only while being edited (makes it easier to test/preview Snippets).', 'wp-snippets'),
                'never'            => __('No, never allow a Snippet to be viewed stand-alone.', 'wp-snippets'),
            ],
        ]); ?>

        <?= $Form->selectRow([
            'label' => __('Content Filters', 'wp-snippets'),
            'tip'   => __('This controls which built-in WordPress content filters are applied to content in a Snippet. All are suggested.<hr />Note: <code>jetpack-markdown</code> is only possible if you have Jetpack installed with Markdown enabled. The same is true for <code>jetpack-latex</code>.', 'wp-snippets'),

            'name'     => 'content_filters',
            'multiple' => true, // i.e., An array.
            'value'    => s::getOption('content_filters'),
            'options'  => [
                'jetpack-markdown'                  => 'jetpack-markdown',
                'jetpack-latex'                     => 'jetpack-latex',
                'wptexturize'                       => 'wptexturize',
                'wpautop'                           => 'wpautop',
                'shortcode_unautop'                 => 'shortcode_unautop',
                'wp_make_content_images_responsive' => 'wp_make_content_images_responsive',
                'capital_P_dangit'                  => 'capital_P_dangit',
                'do_shortcode'                      => 'do_shortcode',
                'convert_smilies'                   => 'convert_smilies',
            ],
        ]); ?>

    <?= $Form->closeTable(); ?>

    <?= $Form->submitButton(); ?>
<?= $Form->closeTag(); ?>
