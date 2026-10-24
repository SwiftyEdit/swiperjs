---
title: Swiper Slider - Overview
description: Overview of the Swiper Slider plugin
btn: Overview
group: addons
priority: 10
---

# Swiper Slider

This plugin lets you create any number of [Swiper.js](https://swiperjs.com) slideshows and
place them anywhere in the frontend via a shortcode. A slide is never free-form content - it's
always a reference to an existing record: a snippet, page, blog post or product. Title, image,
teaser text and link are resolved automatically from that record every time the slideshow
renders.

## Creating a slideshow

1. Go to **Addons > Swiper Slider > Overview** and click **+ New slideshow**.
2. In the editor, set the name, key and Swiper options on the left (effect, loop, autoplay,
   speed, slides per view for mobile/tablet/desktop, spacing, arrows, pagination, fixed height,
   extra CSS class) and the default slide layout.
3. On the right, pick a source type (Snippet/Page/Blog post/Product), search for the record you
   want, and click it to add it as a slide. Reorder slides via drag & drop, then click
   **Save order**. The available slide layouts are described on the [Slides & layouts](slides.md) page.

## Shortcode

Every slideshow shows its own ready-to-use shortcode (in the overview list and in the editor),
which can be pasted into any page, snippet or blog post:

```
[plugin=swiperjs]key=hero-homepage[/plugin]
```

`key` is the slideshow's own freely chosen, unique key (set under the slideshow's settings) -
not its internal database id - so the shortcode stays readable and stable even after reordering
or renaming a slideshow.

It also works directly inside a theme template, as a Smarty block:

```
{plugin name="swiperjs"}key=hero-homepage{/plugin}
```

An inactive slideshow (the "Active" checkbox unchecked in the editor), or one with no valid
slides left, simply renders nothing - the shortcode can safely stay in the content.
