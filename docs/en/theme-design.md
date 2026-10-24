---
title: Swiper Slider - For theme designers
description: Markup and complete CSS of the slide layouts
btn: For theme designers
group: addons
priority: 40
---

# For theme designers: markup and CSS

This page is for anyone who wants to define the slides' look entirely in their own theme - to
do so, check "Theme provides its own slide layout styles" under **Settings** (see
[Look & settings](appearance.md)).

## Markup

Every slide has the same structure in all layouts - only the layout class on the slide changes,
and "Text only" leaves out the image:

```html
<div class="swiperjs-wrap [extra CSS class]">
  <div class="swiper" id="sjs-…">
    <div class="swiper-wrapper">

      <div class="swiper-slide swiperjs-slide swiperjs-layout--card [swiperjs-slide--no-image]">
        <!-- <a href="…"> if the record has a link, otherwise <div> -->
        <a href="…" class="swiperjs-slide-inner swiperjs-slide-link">
          <img src="…" alt="…" loading="lazy" class="swiperjs-slide-image">
          <div class="swiperjs-slide-caption">
            <h3 class="swiperjs-slide-title">Title</h3>
            <p class="swiperjs-slide-text">Teaser, max. 160 characters</p>
          </div>
        </a>
      </div>

    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>
  </div>
</div>
```

- Layout classes: `swiperjs-layout--overlay`, `--card`, `--split`, `--text`
- `swiperjs-slide--no-image` is set on every slide without an image (always for "Text only").
- Title, text and image are left out of the markup if the record doesn't have them.

## CSS

The complete CSS of all layouts ships as its own commented file in the plugin:

- on disk: `plugins/swiperjs/assets/swiperjs-layouts.css`
- in the browser: under **Addons > Swiper Slider > Settings** via **Open CSS file**

This is exactly the file the plugin prints itself (without comments, as a `<style>` block), so it
is always current. Easiest is to copy it into the theme and adjust it there. It is split into
sections: base (all layouts), overlay, card, split, text only.

**Important when the theme provides the layout styles itself:** the theme has to set the base
height `.swiperjs-wrap .swiper { height: … }` itself (or every slideshow gets a fixed height).
Without a defined height the slider collapses to almost 0, and the arrows/pagination end up
outside the visible area.
