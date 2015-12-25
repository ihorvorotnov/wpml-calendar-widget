# WPML Calendar Widget

This is a fork of standard WordPress Calendar widget, but with WPML support.

The problem with standard calendar widget is that it runs custom SQL queries and has no hooks to alter that queries from a translation plugin like WPML. That results in a calendar counting all posts, regardless of what current language is and are there **translated** posts.

## Extras

Standard WordPress calendar widget adds `id="today"` to current calendar day's table cell, which is a bit odd. Ids aren't for styling purposes, right? So I've added `class="is-today"`. Also, if you are browsing daily archives, the day you're currently viewing has an extra `is-current-day` class waiting to be styled.

## Compatibility

- PHP 5.3+ because `__construct()`
- Developed with WordPress 4.4, but should be fine with older versions
- Developed with WPML Multilingual CMS 3.3 plugin

However, it should work with any WPML version that has the same database tables (`_icl_translations` specifically) and same columns.

## Installation

As usual.

**Note:** _This plugins depends on WPML, so it checks if WPML (WPML Multilingual CMS) is installed and activated. If WPML isn't there, you'll get nothing._

## Support

Something is wrong? File an issue.

## Contributing

Want to add something? Fork it, write some code, make a pull request.

## License

GPL v2 or later, obviously.
