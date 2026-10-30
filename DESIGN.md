---
name: Notvorrat
description: The household emergency supply drawn as a cellar shelf assembly drawing (Regalplan, Montagezeichnung).
colors:
  paper: "#fcfcfa"
  paper-2: "#f2f2ee"
  white: "#ffffff"
  ink: "#151515"
  ink-2: "#4f555c"
  pencil: "#a3a8ad"
  rule: "#d6d8db"
  fill: "#e9e9e3"
  signal: "#ff5b14"
  signal-ink: "#b23a06"
  signal-wash: "#fff1e9"
typography:
  display:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(64px, 8vw, 104px)"
    fontWeight: 700
    lineHeight: 0.86
    letterSpacing: "-0.04em"
  headline:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(28px, 3.4vw, 40px)"
    fontWeight: 700
    lineHeight: 1.15
    letterSpacing: "-0.015em"
  title:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "20px"
    fontWeight: 700
    lineHeight: 1.15
    letterSpacing: "-0.005em"
  body:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.5
    fontFeature: "\"kern\", \"liga\""
  label:
    fontFamily: "Barlow Semi Condensed, Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "14px"
    fontWeight: 600
    lineHeight: 1.25
  caption:
    fontFamily: "Barlow Semi Condensed, Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "13px"
    fontWeight: 500
    lineHeight: 1.3
  mono:
    fontFamily: "ui-monospace, Cascadia Mono, Consolas, monospace"
    fontSize: "13px"
    fontWeight: 400
    lineHeight: 1.5
rounded:
  none: "0px"
  callout: "50%"
spacing:
  s1: "4px"
  s2: "8px"
  s3: "12px"
  s4: "16px"
  s5: "24px"
  s6: "32px"
  s7: "48px"
  s8: "64px"
  gutter: "clamp(16px, 2.4vw, 32px)"
components:
  button:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    typography: "{typography.label}"
    rounded: "{rounded.none}"
    padding: "0 16px"
    height: "42px"
  button-hover:
    backgroundColor: "{colors.fill}"
  button-primary:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.paper}"
    typography: "{typography.label}"
    rounded: "{rounded.none}"
    padding: "0 16px"
    height: "42px"
  button-quiet:
    textColor: "{colors.ink}"
    typography: "{typography.label}"
    rounded: "{rounded.none}"
    padding: "0 16px"
  input:
    backgroundColor: "{colors.white}"
    textColor: "{colors.ink}"
    typography: "{typography.body}"
    rounded: "{rounded.none}"
    padding: "8px 10px"
    height: "44px"
  chip:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    padding: "0 10px"
    height: "32px"
  chip-on:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.paper}"
  callout:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.callout}"
    size: "26px"
  callout-active:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.paper}"
  callout-alert:
    backgroundColor: "{colors.signal}"
    textColor: "{colors.ink}"
  badge:
    backgroundColor: "{colors.signal}"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    height: "20px"
  titleblock-action:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.paper}"
    rounded: "{rounded.none}"
    padding: "10px 14px 12px"
---

# Design System: Notvorrat

## Overview

**Creative North Star: "Regalplan (Montagezeichnung)"**

The supply is drawn, not charted. Every screen is a sheet from a furniture assembly drawing: a double-ruled sheet frame, a title block (Schriftfeld), the cellar shelf in front elevation with one board per food group, numbered circular callouts bound to a parts list (Stückliste), and dimension lines that measure "Tage versorgt". Packages are line drawings; how full the shelf is reads from line form and hatching, not from bars, cards or traffic lights.

The world is monochrome ink on white paper. A pencil grey carries construction lines and missing items, a pale fill tone marks hover and selection, and a single signal orange is held back for best-before (MHD) attention. Density is that of a technical drawing: compact condensed labels, tabular figures, hairline rules between rows, 2px rules between zones. Calm, exact and quietly complete, never alarmist.

The world explicitly refuses the KPI-card dashboard: no progress bars, no traffic-light badges, no rounded pills, no drop shadows.

**Key Characteristics:**
- Ink on paper, one held-back signal accent.
- State is line form: hatched solid = stocked, dashed = planned/missing, doubled outline = bald fällig, struck through in signal = abgelaufen.
- Numbered circular callouts link the drawing to the parts list; hovering either lights both.
- Square corners and square line caps everywhere; the circle belongs to callouts alone.
- Condensed grotesk for labels and figures, tabular numerals in every quantity.

## Colors

A drafting palette: one black ink, three greys for construction, two papers, and one orange reserved for MHD attention.

### Primary
- **Drafting Ink** (ink): every structural line, text, filled primary controls, hatch strokes, selected chips and active callouts. The system's only "brand" colour.

### Secondary
- **Signal Orange** (signal): MHD attention only. Expired packages' strike line and hatch, the expired legend swatch, the alert callout fill, the Haltbarkeit count badge, the due/expired bar on the MHD timeline. Always paired with ink text when it is a fill.
- **Burnt Signal Ink** (signal-ink): the text-safe form of the signal. Due/expired state text, expired package outlines, error flash border and text.
- **Signal Wash** (signal-wash): background of error flashes only.

### Neutral
- **Drawing Paper** (paper): page and sheet background, default button and chip fill, callout interior.
- **Tracing Paper** (paper-2): group header rows in the parts list, code blocks, scrollbar track.
- **Field White** (white): text input and pick-tile surfaces, gridded figure stage behind single-item drawings.
- **Graphite** (ink-2): secondary text, field labels, meta lines, dashed outlines of missing packages.
- **Pencil** (pencil): construction lines (scale ticks), empty-slot outlines, idle pick-tile borders, checked-off shopping quantities.
- **Hairline** (rule): row dividers in tables and lists, the 16px drafting grid, warning-window hatch on timelines.
- **Fill Tone** (fill): hover on buttons, chips and table rows; selected pick tile; custom-target inputs.

### Named Rules
**The Held-Back Signal Rule.** Signal orange means "look at this MHD" and nothing else. It never decorates, never marks navigation or primary actions, and never appears without a text state ("fällig", "abgelaufen") beside it.

**The Never Colour Alone Rule.** Every state carried by colour is also carried by line form or text: doubled outline plus "bald fällig", strike line plus "abgelaufen", double underline for "bald fällig" in tables.

## Typography

**Display Font:** Barlow (with ui-sans-serif, system-ui)
**Body Font:** Barlow
**Label Font:** Barlow Semi Condensed (with Barlow)
**Mono Font:** ui-monospace, Cascadia Mono, Consolas (code blocks in settings only)

**Character:** A plain technical grotesk with its condensed sibling: the regular width speaks in headings and prose, the semi-condensed width labels the drawing like lettering on a plan. Both are self-hosted woff2 (no font CDN).

### Hierarchy
- **Display** (700, clamp 64-104px, 0.86): the single "Tage versorgt" figure on the Übersicht. One per screen at most.
- **Headline** (700, clamp 28-40px, 1.15, -0.015em): page title (h1), balanced wrap.
- **Title** (700, 20px, 1.15): section titles (h2) sitting on a 2px ink rule; fieldset legends on Einlagern.
- **Body** (400, 16px, 1.5): prose, table cells at 15px, ledes at 17px in graphite capped at 68ch.
- **Label** (600 condensed, 14-16px): field labels, navigation, buttons, board labels, item names under slots, column heads (13px).
- **Caption** (500 condensed, 12-13px): title-block field names, scale numbers, legend, colophon, meta lines.

### Named Rules
**The Tabular Figure Rule.** Every quantity, date, callout number and badge uses tabular lining numerals so columns and dimension values align like a parts list.

**The Lettering Rule.** Labels on the drawing (boards, slots, legends, title-block fields, controls) are set in the condensed face; continuous reading text is set in the regular face. Labels stay sentence case; no tracked uppercase.

## Layout

The app sits inside a drawing sheet: an 18px margin, a 2px ink border with a 1px outline 4px outside it. The edges carry no tick marks; they read as noise. Zones within the sheet are separated by full-bleed 2px ink rules rather than gaps; sub-cells (title block, key-value strips) by 1px ink rules; list rows by 1px hairlines.

Spacing runs on a 4px-based scale (s1-s8: 4, 8, 12, 16, 24, 32, 48, 64px) with a fluid horizontal gutter (16-32px). The Übersicht is a two-row sheet head (dimension plot left, title block right at 300-380px) over a shelf zone (rack left, notes column 300px right), then the parts list. Forms use auto-fit grids (minimum 160-190px columns).

Responsive: at 1120px the title block drops under the dimension plot as a four-column strip and the notes column moves beneath the shelf. At 760px the sheet frame is removed, the masthead becomes sticky, navigation moves to a fixed five-tab bar at the bottom (64px plus safe area: Übersicht, Vorrat, MHD, Plan, Einkauf), Einlagern becomes a floating icon-only ink button (plus sign, label kept for screen readers) 16px above the bar's right end (52px square, separated from content by a 3px paper ring, not a shadow; hidden on Einlagern and Planung, which have their own bottom actions), and the title block moves to the top as a compact three-cell strip. Tables hide secondary columns rather than scrolling where possible.

**Open decision (pending user confirmation):** the contract asked for board width proportional to each item's target share. The build realises this as package counts: one drawn symbol per target package, grouped once an item exceeds 18 symbols (one symbol then stands for several packages). Larger demand therefore occupies more board length, but not in exact proportion. Do not treat either reading as settled until the user confirms.

## Elevation & Depth

Flat. There are no drop shadows anywhere. Depth is drafted: zones are separated by rule weight (2px ink between zones, 1px ink within, 1px hairline between rows), the sheet frame is a double rule, and the shelf boards are drawn as cut sections with a hatched face. The only box-shadow use is an inset ink bar under the current navigation item (4px desktop, 3px top edge on the mobile tab), which is a rule line, not elevation.

### Named Rules
**The Drafted Depth Rule.** If something needs to stand apart, give it a heavier rule, a hatch or an ink fill. Never a shadow, blur or gradient lift.

## Shapes

Square everything: controls, inputs, tiles, flashes, badges and the sheet have 0 radius, and lines use square caps and mitred joins (icons and package drawings included). The one round form is the callout circle (26px, 24px small, 40px large), with a 1px leader line dropping to the item it numbers; an outlined second ring marks "bald fällig". Packages may have their own natural geometry (jar shoulders, bottle necks) because they are drawn objects, not UI chrome.

Line weights form a hierarchy rather than the contract's single 2px weight: 2px ink for frames, control borders and zone rules; 1.5-1.6px for package silhouettes and legend swatches; 1px for subdivisions, details and leaders; dashed for planned/missing and for the Soll reference line. The stocked hatch is a 45-degree ink line pattern at about 4-5px pitch, used identically for stocked packages, cut shelf boards and the figure-stage floor.

## Components

### Buttons
Drawn boxes: a 2px ink outline, no radius, condensed 600 lettering.
- **Shape:** square (0px), 42px high; small 32px, large 54px, icon-only 44px square.
- **Default:** paper fill, ink text and border; hover fills with the fill tone; active nudges down 1px; disabled at 45% opacity.
- **Primary:** solid ink with paper text; hover darkens slightly.
- **Quiet:** no border, underlined text; hover fills with the fill tone.
- **Focus:** a 2px dashed ink outline, 3px offset (all focusable elements).

### Chips
- **Style:** 1px ink outline, paper fill, condensed 500 14px, 32px high, square. Used as quick MHD presets (+3 Mon., +1 Jahr, ohne MHD).
- **State:** selected chip inverts to ink fill with paper text; hover uses the fill tone.

### Cards / Containers
There are no cards. Containers are ruled panels: a 2px ink top rule with the title sitting on it and content below (settings panels, household block, disclosures). The pick tile on Einlagern is the one boxed container: 1px pencil border on white, ink border on hover, 2px ink border with fill tone and a hatched package icon when selected.

### Inputs / Fields
- **Style:** 2px ink border, white fill, 0 radius, 44px high (54px large), 16px text. Label above in condensed 600 graphite; "optional" in lighter weight.
- **Focus:** 2px dashed ink outline, 2px offset.
- **Stepper:** minus and plus square buttons flank the number input, sharing borders.
- **Checkbox:** a 22px ink-bordered square that fills ink with a paper check.
- **Error:** flash in signal wash with burnt signal border and text plus a warning icon.

### Navigation
Masthead with the brand (drawn rack mark plus wordmark) in a ruled cell, condensed 600 16px links in graphite, ink on hover with a 2px underbar, ink with a 4px underbar when current. The Haltbarkeit link carries the signal count badge. The primary Einlagern button sits at the masthead end. Mobile: fixed bottom tab bar of five icon-and-label tabs, current tab marked by an ink bar on its top edge, Einlagern as the ink-filled tab.

### Callout (signature)
The numbered circle that binds drawing and parts list. Paper fill, 2px ink ring, condensed 700 13px number. Hovering a shelf slot or its parts-list row inverts the callout to ink; alert state fills it signal orange. Newly stocked items show an inverted callout with a dashed ring.

### Shelf Rack and Package Drawings (signature)
The shelf is drawn in front elevation between two perforated uprights (hole pattern every 12px). Each board is labelled with its food group and Ist/Soll sum, and each slot stands a callout and leader over a drawn group of packages on a hatched cut board, captioned with name, fill fraction and state. Packages come from a fixed set of line-drawn shapes (can, tin, jar, bottle, carton, box, bag, sack, crate, block, bar, roll, torch, radio, battery, candle, stove, kit), stacked up to three high. Fill state follows the line-form legend. On first load the hatch rises into the outlines once (1100ms, staggered 70ms per slot, expo-out ease), and the dimension lines draw out; both are skipped under reduced motion.

### Dimension Plot and Title Block (signature)
"Tage versorgt" is measured with dimension lines: arrowheads at both ends, extension lines at 0 and at the value, a dashed Soll line, and a pencil scale beneath. The title block beside it is a ruled grid of caption-labelled fields (Benennung, Haushalt, Ziel, Positionen komplett, Stand) ending in a full-width ink action cell for Einlagern whose arrow slides 4px on hover.

### MHD Timeline
Each batch is a horizontal bar from today to its best-before date on a shared scale, with an ink "heute" line and a hairline-hatched warning window. Soon: an open double-ruled bar. Due: a thicker signal bar. Expired: a dashed signal bar running backwards from today.

## Do's and Don'ts

### Do:
- **Do** draw state as line form first (hatch, dash, double outline, strike) and add signal orange only for MHD attention.
- **Do** separate zones with 2px ink rules and rows with 1px hairlines instead of cards, gaps or shadows.
- **Do** number anything drawn with a circular callout and give it a matching parts-list row; hovering one lights the other.
- **Do** use tabular figures for every quantity, date and number.
- **Do** mark focus with the 2px dashed ink outline.
- **Do** draw icons inline as SVG strokes at 2px with square caps and mitred joins.
- **Do** keep motion to one-time drawing gestures (hatch filling, lines drawing out) and respect reduced motion.

### Don't:
- **Don't** use progress bars, KPI cards or traffic-light badges to show stock.
- **Don't** round controls or containers; the circle is reserved for callouts.
- **Don't** add drop shadows, blurs or gradient lifts.
- **Don't** use signal orange for navigation, primary actions or decoration.
- **Don't** communicate any state by colour alone.
- **Don't** load fonts from a CDN; the faces are self-hosted.
