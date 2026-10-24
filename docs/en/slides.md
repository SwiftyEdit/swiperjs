---
title: Swiper Slider - Slides & layouts
description: Where a slide's content comes from and which layouts there are
btn: Slides & layouts
group: addons
priority: 20
---

# Slides & layouts

## Where each slide's title/image/text/link come from

| Source type | Title              | Teaser text            | Image              | Link                       |
|-------------|--------------------|--------------------------|---------------------|------------------------------|
| Snippet     | Snippet title (or name) | Snippet teaser (or content) | Snippet image | Snippet permalink, if set     |
| Page        | Page title          | Meta description         | Page thumbnail      | Page permalink                |
| Blog post   | Post title          | Post teaser              | Post image           | Post canonical URL            |
| Product     | Product name        | Product teaser           | Product image         | Product canonical URL         |

If a record is missing one of these fields (e.g. no image set), the slide still renders -
just without that element. If the linked record is later deleted, the affected slide
automatically disappears from the frontend (in the editor it instead shows a red "record was
deleted" notice).

## Layouts

Every slideshow has a **Default slide layout** (editor, left column). In the slide list on the
right, each slide can use a different layout - the choice is saved immediately. "Same as
slideshow" uses the default layout.

| Layout    | Looks like |
|-----------|------------|
| Overlay   | The image fills the whole slide, title/text sit at the bottom on a dark gradient. All slides are the same height, however long the text. (Default, same as the behaviour up to 1.1.0) |
| Card      | Image on top, title/text below - works well with several slides side by side. |
| Split     | Image left, text right, vertically centered. On narrow screens (below 576px) stacked like "Card". |
| Text only | No image, title/text centered - e.g. for quotes or announcements. |

If a slide has no image, its title/text simply show as normal flowing text in every layout.
