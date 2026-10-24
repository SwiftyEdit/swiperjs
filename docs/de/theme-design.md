---
title: Swiper Slider - Für Theme-Designer
description: Markup und komplettes CSS der Folien-Layouts
btn: Für Theme-Designer
group: addons
priority: 40
---

# Für Theme-Designer: Markup und CSS

Diese Seite richtet sich an alle, die das Aussehen der Folien komplett im eigenen Theme
bestimmen wollen - dazu unter **Einstellungen** "Theme bringt eigene Layout-Styles für die
Folien mit" anhaken (siehe [Optik & Einstellungen](appearance.md)).

## Markup

Jede Folie hat in allen Layouts dieselbe Struktur - nur die Layout-Klasse an der Folie ändert
sich, und "Nur Text" lässt das Bild weg:

```html
<div class="swiperjs-wrap [zusätzliche CSS-Klasse]">
  <div class="swiper" id="sjs-…">
    <div class="swiper-wrapper">

      <div class="swiper-slide swiperjs-slide swiperjs-layout--card [swiperjs-slide--no-image]">
        <!-- <a href="…"> wenn der Datensatz einen Link hat, sonst <div> -->
        <a href="…" class="swiperjs-slide-inner swiperjs-slide-link">
          <img src="…" alt="…" loading="lazy" class="swiperjs-slide-image">
          <div class="swiperjs-slide-caption">
            <h3 class="swiperjs-slide-title">Titel</h3>
            <p class="swiperjs-slide-text">Teaser, max. 160 Zeichen</p>
          </div>
        </a>
      </div>

    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>
  </div>
</div>
```

- Layout-Klassen: `swiperjs-layout--overlay`, `--card`, `--split`, `--text`
- `swiperjs-slide--no-image` steht an jeder Folie ohne Bild (bei "Nur Text" immer).
- Titel, Text und Bild fehlen im Markup, wenn der Datensatz sie nicht hat.

## CSS

Das komplette CSS aller Layouts liegt als eigene, kommentierte Datei im Plugin:

- im Dateisystem: `plugins/swiperjs/assets/swiperjs-layouts.css`
- im Browser: unter **Addons > Swiper Slider > Einstellungen** über **CSS-Datei öffnen**

Genau diese Datei gibt das Plugin auch selbst aus (ohne Kommentare, als `<style>`-Block) - sie
ist also immer aktuell. Am einfachsten die Datei ins Theme kopieren und dort anpassen. Sie ist in
Abschnitte gegliedert: Basis (alle Layouts), Overlay, Karte, Geteilt, Nur Text.

**Wichtig, wenn das Theme die Layout-Styles selbst mitbringt:** Die Grundhöhe
`.swiperjs-wrap .swiper { height: … }` muss das Theme selbst setzen (oder jede Slideshow bekommt
eine feste Höhe). Ohne definierte Höhe fällt der Slider fast auf 0 zusammen, und
Pfeile/Pagination liegen außerhalb des sichtbaren Bereichs.
