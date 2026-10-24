---
title: Swiper Slider - Optik & Einstellungen
description: Farben, eigene CSS-Klassen und das JS/CSS-Ladeverhalten
btn: Optik & Einstellungen
group: addons
priority: 30
---

# Optik & Einstellungen

## Optik ans eigene Theme anpassen

Hintergrund- und Textfarbe einer Folie passen sich automatisch an die umgebende Seite an
(Bootstrap-Variablen `--bs-body-bg`/`--bs-body-color`, sonst einfach transparent/geerbt) - ganz
ohne eigenes CSS. Nur das Overlay-Layout nutzt bewusst weißen Text auf dunklem Verlauf, damit der
Text auf jedem Foto lesbar bleibt.

Für die Farbe von Pfeilen und Pagination-Punkten gibt es im Slideshow-Editor das Feld
**Akzentfarbe**:

- **Bootstrap-Theme**: einen Farbnamen eintragen, z.B. `primary`, `danger` oder `dark` - wird zu
  `var(--bs-primary)` usw. aufgelöst und folgt damit automatisch der eigenen Theme-Farbe, auch
  wenn diese individuell angepasst wurde.
- **Tailwind oder ein anderes Theme ohne Bootstrap-Variablen**: stattdessen direkt einen echten
  Farbwert eintragen, z.B. `#4f46e5` oder `rgb(79,70,229)` - wird 1:1 übernommen, ganz ohne
  Bootstrap-Abhängigkeit.
- Leer lassen für Swipers eigene Standardfarbe (Blau).

Zusätzlich lässt sich pro Slideshow im Feld **Zusätzliche CSS-Klasse** ein eigener Klassenname
vergeben, z.B. für die CSS-Variablen `--swiperjs-slide-bg`/`--swiperjs-slide-color`.

Das CSS des Plugins wird als `<style>`-Block direkt vor der ersten Slideshow ausgegeben, also
**nach** dem Theme-CSS im `<head>`. Eine Theme-Regel mit gleicher Spezifität verliert deshalb -
einzelne Anpassungen brauchen einen stärkeren Selektor, z.B. über die zusätzliche CSS-Klasse:
`.meine-klasse .swiperjs-slide-title { … }`. Wer viel ändern will, schaltet das Plugin-CSS besser
ganz ab (siehe **JS/CSS-Ladeverhalten** weiter unten).

## JS/CSS-Ladeverhalten

Unter **Addons > Swiper Slider > Einstellungen** gibt es zwei voneinander unabhängige Schalter.
Bei beiden gilt: angehakt = das Theme bringt es mit, das Plugin lässt es weg.

**Theme bringt Swiper.js/CSS bereits selbst mit** - betrifft nur die Swiper-Bibliothek selbst:

- **Deaktiviert** (Standard): Das Plugin lädt sein eigenes, mitgeliefertes Swiper-Bundle
  automatisch, sobald mindestens eine Slideshow auf der Seite ausgegeben wird - selbst gehostet,
  kein Request an einen Dritt-CDN im Frontend.
- **Aktiviert**: Das Plugin geht davon aus, dass Swiper.js bereits vom Theme geladen wird (z.B.
  im Header/Footer-Template), und bindet sein eigenes Bundle nicht zusätzlich ein.

**Theme bringt eigene Layout-Styles für die Folien mit** - betrifft das CSS der vier Layouts:

- **Deaktiviert** (Standard): Das Plugin gibt sein Folien-CSS einmal pro Seite aus.
- **Aktiviert**: Kein Plugin-CSS mehr. Das Theme ist dann für das komplette Aussehen der Folien
  verantwortlich - am einfachsten das CSS von der Seite [Für Theme-Designer](theme-design.md) ins Theme kopieren und dort anpassen.
