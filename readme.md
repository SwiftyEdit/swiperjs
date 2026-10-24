# Swiper Slider

Create any number of [Swiper.js](https://swiperjs.com) slideshows and embed them in the frontend
via shortcode. Every slide points to an existing record - snippet, page, blog post or product -
and its title, image, teaser text and link are taken from that record automatically, so there is
no second, redundant place to maintain images or text in the plugin itself.

See the plugin's help (the **?** button next to the plugin name in Addons > Swiper Slider) for
the full walkthrough.

## Features

- **Any number of slideshows**, each with its own key (slug) for the shortcode.
- **Slide sources straight from the CMS**: snippets, pages, blog posts or products - found and
  added via live search in the editor, reordered via drag & drop.
- **One shortcode per slideshow**: `[plugin=swiperjs]key=<key>[/plugin]` - also usable as a
  Smarty block in a theme: `{plugin name="swiperjs"}key=<key>{/plugin}`.
- **Swiper options per slideshow**: effect (slide/fade/cube/coverflow/flip), loop, autoplay,
  speed, slides per view (mobile/tablet/desktop), spacing, navigation arrows, pagination dots,
  fixed height, extra CSS class, accent color.
- **Four slide layouts**: overlay, card, split, text only - a default per slideshow, overridable
  per slide.
- **Configurable JS/CSS loading**: under Addons > Swiper Slider > Settings you decide whether the
  active theme already loads Swiper.js itself or the plugin includes its own vendored Swiper
  bundle (self-hosted, no third-party CDN request from the frontend) - and, independently,
  whether the theme provides its own slide layout styles. Markup and the complete layout CSS are
  documented for theme designers in the help; the CSS also ships as
  `assets/swiperjs-layouts.css`.

## Requirements

SwiftyEdit build 26-0225 or newer.

## Bundled library

`assets/vendor/swiper-bundle.min.js` / `.min.css` - Swiper 14.2.0, MIT license, unmodified from
[cdn.jsdelivr.net/npm/swiper@14.2.0](https://cdn.jsdelivr.net/npm/swiper@14.2.0/). Only served
(via `/dispatch.php?p=swiperjs&asset=...`) if the Settings option saying the theme already
provides Swiper is **not** checked.

## License

GPL-3.0 for the plugin code - see [license.txt](license.txt). The vendored
`assets/vendor/swiper-bundle.min.{js,css}` is under Swiper's own MIT license, not GPL-3.0.
