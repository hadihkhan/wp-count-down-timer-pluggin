# Date Time Count Down Timer

A WordPress plugin that counts down to a recurring weekly service and swaps the
timer for a **Live** link once the service is underway.

Built for [Global Kingdom Ministries](http://gkm.churchonline.org/) as part of a
church management setup: the front end shows visitors exactly how long until the
next prayer service starts, then links them straight to the stream while it runs.

> **Status: work in progress (v1.0, beta).** The admin settings screen saves to the
> database, but the front-end timer still runs on hardcoded values. See
> [Known issues](#known-issues) before deploying this anywhere public.

---

## Contents

- [What it does](#what-it-does)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Settings reference](#settings-reference)
- [Database schema](#database-schema)
- [How it works](#how-it-works)
- [Project structure](#project-structure)
- [Known issues](#known-issues)
- [Roadmap](#roadmap)
- [Credits](#credits)
- [License](#license)

---

## What it does

| | |
|---|---|
| **Countdown** | Renders a live-ticking `%D days %H:%M:%S` countdown to the next service. |
| **Live switch** | During the service window the countdown is replaced by a link to the stream. |
| **Shortcode** | Drop `[sp_countdown_timer]` into any post, page, or widget. |
| **Admin screen** | A **Prayer Counter** menu item for setting the day, start/end time, and daylight-savings flag. |
| **Self-installing** | Creates its own database table when the plugin is activated. |

## Requirements

- WordPress 3.0+ (uses `add_shortcode`, `add_menu_page`, `dbDelta`)
- PHP 5.2+ (uses `DateTime`)
- jQuery on the front end — WordPress bundles this already
- A user role with the `edit_posts` capability to reach the settings screen

## Installation

Clone or copy the plugin into your WordPress plugins directory:

```bash
cd wp-content/plugins
git clone https://github.com/hadihkhan/wp-count-down-timer-pluggin.git
```

Then activate it:

1. Go to **Plugins** in the WordPress admin.
2. Find **Date Time Count Down Timer** and click **Activate**.
3. On activation the plugin creates the `wp_countdown_timer` table automatically.

## Usage

### Front end

Place the shortcode wherever the timer should appear:

```
[sp_countdown_timer]
```

In a theme template, call it directly:

```php
<?php echo do_shortcode( '[sp_countdown_timer]' ); ?>
```

The shortcode renders one of two things:

- **Outside the service window** — a `<div id="getting-started">` that jQuery
  Countdown ticks down to the next service.
- **Inside the service window** — an anchor linking to the live stream.

### Admin

Go to **Prayer Counter** in the left-hand admin menu (clock icon). Set the
service day, start and end time, and whether daylight savings applies, then save.

## Settings reference

| Field | Input | Stored as | Notes |
|---|---|---|---|
| Prayer Day | Radio, Monday–Sunday | `prayer_day` | The weekday the service recurs on. |
| Prayer Start Time | `<input type="time">` | `prayer_start_time` | Normalised to `HH:MM:SS`. |
| Prayer End Time | `<input type="time">` | `prayer_end_time` | Marks the end of the Live window. |
| Daylight Savings | Radio, Enable / Disable | `daysaving_is_enabled` | Flag only; not yet applied to the countdown maths. |

The plugin assumes **exactly one row** of settings. More than one row and the
admin screen reports a configuration error instead of a form.

## Database schema

Table: `{$wpdb->prefix}countdown_timer`

| Column | Type | Notes |
|---|---|---|
| `id` | `INT AUTO_INCREMENT` | Unique key. |
| `prayer_day` | `VARCHAR(100)` | Weekday name, e.g. `Sunday`. |
| `prayer_start_time` | `DATETIME` | Service start. |
| `prayer_end_time` | `DATETIME` | Service end — required. |
| `current_date_time` | `TIMESTAMP` | Auto-set, auto-updated on change. |
| `daysaving_is_enabled` | `INT(2)` | Defaults to `0`. |

The table is **not** dropped on deactivation or uninstall — settings survive a
reinstall, and removing it is a manual job.

## How it works

```
Plugin activation
  └─ register_activation_hook → create_database_for_pluggin()   creates the settings table

Admin request
  └─ admin_menu → wp_dateTime_counter_admin_page_display()      registers the menu page
       └─ wp_dateTime_counter_admin_page()                      renders the wrapper
            └─ form_display()                                   reads settings, renders/saves the form

Front-end request
  └─ [sp_countdown_timer] → wp_dateTime_counter()
       ├─ inside the window  → echo the Live link
       └─ outside the window → compute next service, print the countdown markup + inline JS
```

### Hooks registered

| Hook | Callback | Purpose |
|---|---|---|
| `register_activation_hook` | `create_database_for_pluggin` | Create the settings table. |
| `admin_menu` | `wp_dateTime_counter_admin_page_display` | Add the Prayer Counter menu page. |
| `add_shortcode` (`sp_countdown_timer`) | `wp_dateTime_counter` | Render the timer. |
| `admin_engueue_scripts` *(sic)* | `load_cutom_plugin_scripts` | Enqueue jQuery Countdown — see [Known issues](#known-issues). |

## Project structure

```
.
├── index.php                          # The entire plugin: hooks, admin UI, DB access, shortcode
├── assets/
│   └── js/
│       └── jquery.countdown.min.js    # The Final Countdown v2.1.0 (vendored)
└── README.md
```

## Known issues

These are real defects in the current code, not hypotheticals. Worth fixing
before this runs on a live site.

**Blocking**

- **The countdown ignores your settings.** `wp_dateTime_counter()` calls
  `select_data_from_database()` but discards the return value, then uses hardcoded
  values instead: `EST`, an `11:30`–`1:30` window, `Sunday`, and a magic
  `+41400`-second offset. Changing the admin settings has no visible effect.
- **Saving settings fails.** `form_display()` calls
  `$wpdb->insert('$table_name', ...)` with single quotes, so the literal string
  `$table_name` is passed as the table name rather than the variable's value.
- **Table creation is unreliable.** Neither `$wpdb` nor `$table_name` is declared
  `global` inside `create_database_for_pluggin()`, and `$table_name` is assigned at
  file scope where `$wpdb` may not exist yet.
- **The countdown script never loads on the front end.** It is hooked to
  `admin_engueue_scripts` — a typo for `admin_enqueue_scripts` — so the action never
  fires. Even corrected, the admin hook is the wrong one: the timer renders on the
  front end, so this belongs on `wp_enqueue_scripts`.
- **The Live link can never appear.** The window is compared as
  `>= 11:30:00 && <= 1:30:00`, which no single time satisfies, and `date('h:i:s')`
  supplies a 12-hour clock to a 24-hour comparison.

**Security**

- The settings form has **no nonce, no capability check, and no sanitisation** of
  `$_POST` input before it reaches the database. Any logged-in user who can reach
  the page — or anyone who can forge a request to it — can write to the table.
- Table names are interpolated into SQL strings rather than prepared.

**Correctness and polish**

- The form only ever **inserts**, never updates, so each save adds a row. Two rows
  in, the admin screen shows `Database Configuration Error!` instead of a form.
- After a successful save the form is not re-rendered — the page looks blank.
- `select_data_from_database()` handles row counts 0, 1, and 2 only; three or more
  rows fall through and return `null` silently.
- With an empty table, `$database_data['prayer_day']` and siblings produce
  null-index warnings.
- `wp_enqueue_script()` is called with malformed arguments — `"", "". true`
  concatenates to a version string of `"1"` and omits `$in_footer`.
- Several function names (`form_display`, `get_time_formated`) carry no unique
  prefix and risk collisions with themes or other plugins.
- The main file is `index.php`; WordPress convention is a descriptively named file
  such as `date-time-countdown-timer.php`.
- The stream URL and the `Plugin URI` / `Author URI` headers are hardcoded, the
  latter still pointing at `http://localhost/`.

## Roadmap

- [ ] Drive the front-end countdown from the stored settings
- [ ] Fix the insert and make the form update an existing row
- [ ] Add nonce verification, capability checks, and input sanitisation
- [ ] Enqueue the countdown script on `wp_enqueue_scripts`
- [ ] Honour the daylight-savings flag and make the timezone configurable
- [ ] Make the stream URL a setting
- [ ] Namespace all functions behind a single prefix or class
- [ ] Add an uninstall routine that cleans up the table
- [ ] Support multiple service windows per week

## Credits

- **Plugin** — [Hadi Khan](http://www.hadikhan.com)
- **Countdown engine** — [The Final Countdown](http://hilios.github.io/jQuery.countdown/)
  v2.1.0 by Edson Hilios, MIT licensed, vendored at
  `assets/js/jquery.countdown.min.js`

## License

No licence has been declared for this plugin. The bundled jQuery Countdown
library is MIT licensed and retains its own copyright notice. Consider adding a
`LICENSE` file — [GPLv2 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
is the conventional choice for WordPress plugins.
