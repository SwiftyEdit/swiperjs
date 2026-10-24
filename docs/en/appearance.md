---
title: Swiper Slider - Look & settings
description: Colors, extra CSS classes and the JS/CSS loading behaviour
btn: Look & settings
group: addons
priority: 30
---

# Look & settings

## Matching your own theme's look

A slide's background and text color automatically follow the surrounding page (Bootstrap's
`--bs-body-bg`/`--bs-body-color` variables, or plain transparent/inherited otherwise) - no CSS
required. Only the overlay layout deliberately uses white text on a dark gradient, so the text
stays readable on any photo.

For the arrow and pagination dot color, the slideshow editor has an **Accent color** field:

- **Bootstrap theme**: enter a color name, e.g. `primary`, `danger` or `dark` - resolved to
  `var(--bs-primary)` etc., so it automatically follows your theme's own color, even if you've
  customized it.
- **Tailwind or any other theme without Bootstrap variables**: enter a real color value
  instead, e.g. `#4f46e5` or `rgb(79,70,229)` - used as-is, no Bootstrap dependency at all.
- Leave it empty for Swiper's own default color (blue).

Each slideshow also has its own **Extra CSS class** field, e.g. for the
`--swiperjs-slide-bg`/`--swiperjs-slide-color` custom properties.

The plugin's CSS is printed as a `<style>` block right before the first slideshow, i.e. **after**
the theme CSS in the `<head>`. A theme rule with the same specificity therefore loses - small
tweaks need a stronger selector, e.g. via the extra CSS class:
`.my-class .swiperjs-slide-title { … }`. If you want to change a lot, better turn the plugin CSS
off entirely (see **JS/CSS loading behaviour** further down).

## JS/CSS loading behaviour

Under **Addons > Swiper Slider > Settings** there are two independent switches.
For both: checked = the theme brings it, the plugin leaves it out.

**Theme already provides Swiper.js/CSS** - only affects the Swiper library itself:

- **Disabled** (default): the plugin automatically loads its own bundled Swiper build as soon as
  at least one slideshow is rendered on the page - self-hosted, no third-party CDN request from
  the frontend.
- **Enabled**: the plugin assumes Swiper.js is already loaded by the theme (e.g. in its
  header/footer template) and doesn't load its own bundle again.

**Theme provides its own slide layout styles** - affects the CSS of the four layouts:

- **Disabled** (default): the plugin prints its slide CSS once per page.
- **Enabled**: no plugin CSS at all. The theme is then responsible for the slides' entire look -
  easiest is to copy the CSS from the [For theme designers](theme-design.md) page into the theme and adjust it there.
