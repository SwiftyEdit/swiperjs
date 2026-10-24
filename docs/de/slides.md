---
title: Swiper Slider - Folien & Layouts
description: Woher die Inhalte einer Folie kommen und welche Layouts es gibt
btn: Folien & Layouts
group: addons
priority: 20
---

# Folien & Layouts

## Woher Titel, Bild, Text und Link je Folie kommen

| Quelltyp     | Titel               | Teaser-Text              | Bild                  | Link                          |
|--------------|---------------------|---------------------------|-----------------------|--------------------------------|
| Snippet      | Snippet-Titel (oder -Name) | Snippet-Teaser (oder -Inhalt) | Snippet-Bild    | Snippet-Permalink, falls gesetzt |
| Seite        | Seitentitel          | Meta-Beschreibung         | Vorschaubild der Seite | Permalink der Seite            |
| Blog-Eintrag | Beitragstitel        | Teaser des Beitrags       | Beitragsbild          | Canonical-URL des Beitrags     |
| Produkt      | Produktname          | Produkt-Teaser            | Produktbild           | Canonical-URL des Produkts     |

Fehlt bei einem Datensatz eines dieser Felder (z.B. kein Bild hinterlegt), wird die Folie
trotzdem angezeigt - nur eben ohne dieses Element. Wird der verknüpfte Datensatz später
gelöscht, verschwindet die betroffene Folie automatisch aus der Anzeige (im Editor erscheint
sie stattdessen mit einem roten Hinweis "Datensatz wurde gelöscht").

## Layouts

Jede Slideshow hat ein **Standard-Layout der Folien** (Editor links). In der Folienliste rechts
lässt sich pro Folie ein abweichendes Layout wählen - die Auswahl wird sofort gespeichert.
"Wie Slideshow" übernimmt das Standard-Layout.

| Layout   | Aussehen |
|----------|----------|
| Overlay  | Das Bild füllt die komplette Folie, Titel/Text liegen unten auf einem dunklen Verlauf. Alle Folien sind gleich hoch, egal wie lang der Text ist. (Standard, entspricht dem Verhalten bis 1.1.0) |
| Karte    | Bild oben, Titel/Text darunter - passt gut zu mehreren Folien nebeneinander. |
| Geteilt  | Bild links, Text rechts, vertikal zentriert. Auf schmalen Bildschirmen (unter 576px) untereinander wie bei "Karte". |
| Nur Text | Ohne Bild, Titel/Text zentriert - z.B. für Zitate oder Ankündigungen. |

Hat eine Folie kein Bild, steht Titel/Text in jedem Layout einfach ganz normal im Fließtext.
