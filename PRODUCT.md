# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Plain PHP 8.2 + PDO + MySQL 8, no Composer, no build step (user's choice). Front controller in `public/`; app code outside the webroot. Runs under Apache (`public/.htaccess`), nginx (`try_files` to `index.php`) or `php -S` for development. Production target: any ordinary PHP host with MySQL and cron.

## Users

One private household planning its emergency food and water supply ("Notvorrat"). A few household members share one login. They open the app occasionally: after a shopping trip to log what was bought, when a warning says something is close to its MHD, and when they sit down to check how far they are from the target.

## Product Purpose

Plan, build and maintain a household emergency supply. The app shows what the household should hold (target), what it holds now (stock, per batch with MHD), and the gap. It warns before items reach their best-before date so they get eaten and replaced instead of thrown away. Success means the household reaches its target and keeps it rotating without waste.

## Positioning

A private, self-hosted planner built on the BBK (Bundesamt für Bevölkerungsschutz und Katastrophenhilfe) guideline, scaled to the actual household. It tracks individual batches with their own MHD and tracks calorie and nutrient coverage as well as item counts, so the answer to "how many days would we last?" comes from real data.

## Operating Context

- German-language UI. German terminology: Notvorrat, Vorrat, MHD (Mindesthaltbarkeitsdatum), Einlagern, Verbrauchen, Bedarf, Soll/Ist.
- Typical sessions: logging a shopping trip right after it (often on a phone in the kitchen or pantry), checking the dashboard, acting on an MHD warning.
- Rotation ritual: eat the oldest batch, buy a replacement, log it.

## Capabilities and Constraints

- Shared household password, set on first launch; session-based login.
- Household is configurable: adults, children, pets, target days (BBK default 10 days).
- Recommended list derived from the BBK checklist, scaled per person and day; users can adjust targets and add their own items.
- Planning: target quantities per item, shopping list = target minus current stock.
- Stocking up records quantity and MHD per batch; consuming removes from batches, oldest MHD first.
- Nutrition per item (kcal, protein, fat, carbs per 100 g/ml) feeding calorie coverage ("days covered").
- MHD warnings: in-app (dashboard, nav badge, expiring list) plus an email digest sent by a cron script.
- Water is tracked in liters (BBK: 2 L per person per day).

## Evidence on Hand

- BBK guideline quantities (public guidance) used as the seed recommendation. Nutrition seed values are typical reference values, not product-specific measurements; the UI must not present them as exact.
- No real household stock data exists yet; demo data, if shown, must be labeled.

## Product Principles

1. The gap is the headline: always show target vs. current stock.
2. Never waste: MHD warnings arrive early enough to eat the item first, and the oldest batch is always consumed first.
3. Logging a shopping trip must be fast enough to do standing in the pantry.
4. Scale to this household, not to a generic one.
5. Calm preparedness, not fear: no alarmism, no doomsday tone.

## Accessibility & Inclusion

Must work well on phones (pantry use) and desktop. Status must never depend on color alone (MHD state also carries text/icon).
