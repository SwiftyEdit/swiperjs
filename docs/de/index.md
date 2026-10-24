---
title: Swiper Slider - Übersicht
description: Übersicht über das Swiper Slider Plugin
btn: Übersicht
group: addons
priority: 10
---

# Swiper Slider

Mit diesem Plugin lassen sich beliebig viele [Swiper.js](https://swiperjs.com)-Slideshows
anlegen und per Shortcode an beliebiger Stelle im Frontend platzieren. Eine Folie ist dabei nie
frei eingegebener Inhalt, sondern immer ein Verweis auf einen bestehenden Datensatz - Snippet,
Seite, Blog-Eintrag oder Produkt. Titel, Bild, Teaser-Text und Link werden bei jeder Anzeige
automatisch aus diesem Datensatz ermittelt.

## Slideshow anlegen

1. Unter **Addons > Swiper Slider > Übersicht** auf **+ Neue Slideshow** klicken.
2. Im Editor links Name, Schlüssel (`key`) und die Swiper-Optionen (Effekt, Loop, Autoplay,
   Geschwindigkeit, Folien pro Ansicht für Mobil/Tablet/Desktop, Abstand, Pfeile, Pagination,
   feste Höhe, zusätzliche CSS-Klasse) sowie das Standard-Layout der Folien einstellen.
3. Rechts über das Suchfeld einen Quelltyp (Snippet/Seite/Blog-Eintrag/Produkt) wählen, den
   gewünschten Datensatz suchen und per Klick als Folie hinzufügen. Die Reihenfolge der
   Folien lässt sich per Drag & Drop ändern - danach **Reihenfolge speichern** nicht
   vergessen. Welche Layouts es für die Folien gibt, steht auf der Seite [Folien & Layouts](slides.md).

## Shortcode

Jede Slideshow bekommt automatisch einen fertigen Shortcode angezeigt (Übersicht-Liste und
Editor), der einfach in eine Seite, ein Snippet oder einen Blog-Eintrag kopiert werden kann:

```
[plugin=swiperjs]key=hero-startseite[/plugin]
```

`key` ist dabei der unter **Einstellungen** der Slideshow frei wählbare, eindeutige
Schlüssel (nicht die interne Datenbank-ID) - so bleibt der Shortcode auch nach dem Umsortieren
oder Umbenennen einer Slideshow stabil lesbar.

Der Shortcode funktioniert auch direkt in einem Theme-Template, als Smarty-Block:

```
{plugin name="swiperjs"}key=hero-startseite{/plugin}
```

Eine Slideshow, die nicht aktiv ist (Haken "Aktiv" im Editor entfernt) oder keine gültigen
Folien mehr hat, rendert einfach nichts - der Shortcode bleibt gefahrlos im Inhalt stehen.
