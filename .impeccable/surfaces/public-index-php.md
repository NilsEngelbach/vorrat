---
version: 1
slug: "public-index-php"
primary_target: "public/index.php"
related_targets: ["src/views"]
---

# Surface: Notvorrat app (all routes)

Scope: the whole authenticated app (Übersicht, Vorrat, Einlagern, Haltbarkeit, Planung, Einkaufsliste, Einstellungen) plus login/setup. Visitor mode: **Operate**.

Audience/job: one household, shared login; log a shopping trip in the pantry (phone), check the gap to the target, act on MHD warnings. Constraints: German UI, plain PHP, no build step, self-hosted fonts (no Google CDN, GDPR), status never by colour alone.

Memorable moment: the Übersicht shelf drawing, where dashed planned items fill in solid as you stock up.

## Direction contract

THESIS: The supply is the cellar shelf drawn as an assembly drawing: one board per food group, planned items as dashed outlines, stocked volume as hatched solid, numbered callouts bound to a parts list. It refuses the KPI-card dashboard with progress bars and traffic-light badges.

OWN-WORLD: White paper, one ink black, a pencil grey for construction lines, a pale fill tone, and signal orange reserved for MHD attention. Uniform 2px line weight, square ends, circled callout numbers, Stückliste tables in a grotesk with tabular figures. State as line form: solid = stocked, dashed = planned, doubled = bald fällig, struck = abgelaufen. Controls are drawn: outlined boxes, no rounded pills, no shadows.

STORY: The household sees at once how full the shelf is, which boards are empty, and what is due soon; they tap a callout to reach the item, log a purchase with MHD, and watch the outline fill.

FIRST VIEWPORT: Title block (Schriftfeld) top-right like a drawing sheet: household, date, sheet number. Left: one large "Tage versorgt" figure with food/water split. Centre: the shelf in front elevation; every item is drawn as its target number of packages (one symbol = one package; grouped only above 18), so larger demand occupies more board length; stocked packages hatched, missing ones dashed. Primary action "Einlagern" in the title block, one tap.

FORM: Furniture assembly drawing with parts list, candidate 7 of 7 (cellar-shelf steer), seed key 79840f40.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance

Raises kept from the round: state as line form (emission rail); one held-back accent (monochrome canon); map and list are one space, callout = parts-list row (doujin catalog); one fixed fill scale across views (star atlas); board width honest to target share (acetate manual).

Unresolved: none.
