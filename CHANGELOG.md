## v170111.28751

- Bug fix. Shortcode error due to incorrect 'by reference' value. See [Issue #1](https://github.com/websharks/wp-snippets-pro/issues/1).

## v161222.28602

- Initial release.
- Backward compatible with the original WP Snippets plugin.
- WP Snippets now have a Tag taxonomy in addition to the Category taxonomy.
- The `slug=""` attribute now accepts an optional comma-delimited list of slugs, or just a single slug like before.
- There is a new attribute that accepts a comma-delimited list of category slugs: `in_category=""`
- There is a new attribute that accepts a comma-delimited list of tag slugs: `with_tag=""`
- There is a new attribute that accepts an orderby clause: `orderby="rand"` or `title`, `date`, `modified`, etc.
- Now compatible with Jetpack Markdown and Jetpack Latex.
- Now compatible with the WooCommerce Vendors extension.
- Still compatible with the Raw HTML plugin.
- Works as a drop-in replacement for the original WP Snippets plugin and will continue to work with content from the past.
- Snippet content filters are now configurable.
- Snippets can now be made public or private; i.e., with their own public permalink, or not.
