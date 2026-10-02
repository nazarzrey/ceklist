# WebKelas UI Style Guide

Source of truth for new screens and future UI changes. Follow this guide before adding one-off colors or component styles.

## Direction

WebKelas uses a calm blue palette: deep navy `#041b90cc` for navigation, a clear medium blue for actions, muted cyan for small highlights, and cool near-white surfaces. Keep the academic dashboard structure and make information easy to scan. Navigation labels stay light on navy; the active item gets a softly lit blue surface and a narrow cyan marker.

## Where styles live

- `assets/css/styles.css` is the active application/dashboard design system. It is loaded by `application/views/dashboard.php`.
- `assets/css/auth.css` is the login layout and loads the shared `styles.css` design tokens first.
- `assets/css/init.css` styles the setup login and database status screens. Keep its colors and controls aligned with the tokens below.
- `assets/css/style.css`, `assets/css/assets.css`, and the `jurnal_*.css` files are legacy or currently unused by the three active views. Do not build new UI on them unless a view is deliberately wired to them.
- Dashboard sections are rendered by `assets/js/ci-webkelas.js`; preserve its existing hooks and data attributes when changing markup.
- `styles.css` retains older component rules above a clearly marked **Final theme layer**. Put new shared overrides in that final layer so they take effect over the legacy dashboard styling.

## Classroom workflows

- Student accounts come from `unpam_mahasiswa` in class `TPLE004`. The WebKelas login uses NIM and starts with the final six digits of that NIM.
- `unpam_mahasiswa.tipe = 'super'` is the class leader. Do not assign leader privileges by checking a name, NIM example, or legacy `admin` type.
- Profile overrides are stored in `webkelas_profiles`. Never overwrite the source student roster table with WebKelas display names or passwords.
- The leader publishes a class broadcast and optional checklist items together. Empty course means a global class broadcast; `course_id` groups the message and checklist under a course.
- `class_task_checks` is keyed by both task and student NIM. A student's check must never change another student's personal remaining count.
- Announcement read receipts are per NIM. Keep broadcast detail URLs shareable as `#announcement/{id}` and make the login preserve a pending hash route.
- The leader types `@` directly into a message/checklist textarea. Place the semester-course suggestion menu at the current caret, and insert the selected full course name inline. Only a checklist row with a valid `@course` tag belongs to that course; an untagged row stays global.
- Weekly e-learning recap titles suggest the first missing numbered week already in announcement history. A recap may be global while each bullet checklist item is attached to its detected course and meeting.
- Lines beginning with `-`, `*`, `#`, or a bullet become checklist rows. Preserve ordinary paragraphs as announcement notes; associate a URL on a checklist line with that row and course.
- Profile quick-login links are bearer credentials: store only their SHA-256 hashes, make each link single-use with a 15-minute expiry, invalidate older links on regeneration, and let the owner revoke them. The UI must label the link as secret and must not persist its raw value in browser storage.

## Demo data and useful behavior

- First open of the WebKelas home page ensures the core course tables and checklist tables, then idempotently seeds class info from `contoh.txt` when those demo broadcast titles are not present.
- Demo content includes a global e-learning recap, course-specific Pemrograman II and RPL posts, and ten Teknik Kompilasi checklist items. The initial state is unchecked for every NIM.
- A student's remaining count is `unchecked checklist rows` for that NIM. Checking one of ten Teknik Kompilasi items changes that student's count to nine; it does not change a classmate's personal count.
- The aggregate “teman sudah cek” is a count only. Do not expose which students checked a task unless a future explicit requirement establishes that workflow.
- A broadcast detail link is `#announcement/{id}`. The login form preserves this route so a classmate can sign in and land on the shared detail.
- Deadline dates are shown on broadcast cards and details; `due_label` remains a readable reminder when the exact time is not known.
- The composer has a two-step flow: edit the three content parts, preview the ordered checklist and retained info, then publish. Do not publish directly from the edit step.
- Checklist preview rows reuse the `/ceklist` card styling and keep source order. Ordinary message lines remain visible as broadcast info even when checklist rows are also present.
- The share-copy action formats the title and parenthetical phrases with WhatsApp `**bold**` markup, then includes each checklist/reference plus the announcement detail and WebKelas URLs.

## Design tokens

| Token | Value | Use |
| --- | --- | --- |
| `--ink` | `#17233f` | Main text |
| `--ink-soft` | `#5e6d88` | Supporting text |
| `--muted` | `#8996ad` | Hints, timestamps |
| `--line` | `#e4e9f2` | Borders and dividers |
| `--surface` | `#ffffff` | Cards and controls |
| `--surface-soft` | `#f5f7fc` | Page and nested surfaces |
| `--sidebar` / `--brand-blue-dark` | `#041b90cc` | Main navigation and deep navy emphasis |
| `--accent` / `--brand-blue` | `#315bd6` | Primary actions and links |
| `--accent-2` / `--brand-cyan` | `#2b9eb3` | Small secondary highlight and information |
| `--brand-blue-dark` | `#041b90cc` | Hover/pressed primary action |
| `--danger` | `#dc5b53` | Destructive/error state |

Use tokens rather than repeating raw color values. Blue is the default action color; cyan is a supporting accent. Keep body copy at readable contrast and reserve muted color for secondary information.

## Typography and spacing

- Use **Poppins** for the application, headings, controls, and setup screens. The active views load it from Google Fonts.
- Use weight 400 for body copy, 500–600 for labels and controls, and 600–800 for headings and key numbers.
- Use the existing spacing rhythm: 8px increments for component gaps and padding; cards use 14–22px radii; pill controls use `999px` radius.
- Keep dashboard type compact, but do not reduce explanatory text below 11px. Main page headings should remain responsive with `clamp()`.
- On wide FHD desktop viewports (`min-width: 1600px`), use the readability overrides at the end of `styles.css`: metadata starts at 11px and card body text at 12px. Do not scale the whole interface; preserve clear hierarchy between headings, body copy, and labels.

## Buttons

Buttons are action controls: use a verb, keep a visible focus state, and include an icon only when it helps recognition. The shared variants are defined in `assets/css/styles.css` and mirrored for init screens in `assets/css/init.css`.

| Class | Intended use |
| --- | --- |
| `.btn .btn-primary` | Main action for a view or form; medium blue, pill shaped |
| `.btn .btn-outline-primary` | Secondary action that should remain visible |
| `.btn .btn-secondary` | Low emphasis alternative action |
| `.btn .btn-light` | Neutral actions in light surfaces or dialogs |
| `.btn .btn-outline-light` | Light outline action on a dark surface |
| `.btn .btn-accent` | Occasional information highlight; cyan. Avoid using beside another cyan control |
| `.btn .btn-ghost` | Tertiary action, usually inline or in a toolbar |
| `.btn .btn-danger` | Destructive action; pair with a clear action label |
| `.btn .btn-sm` / `.btn-lg` | Size modifier; keep one consistent size within a group |
| `.icon-btn` | Compact icon-only utility action; always provide `aria-label` |

Use one primary action per card or form area. Keep destructive actions visually separate from save/submit actions. Disabled buttons must use the native `disabled` attribute when possible.

## Components

- **Navigation:** navy sidebar with soft white inactive labels; active navigation uses a translucent lighter blue fill, white text, and a slim cyan marker. Keep cyan to small markers and icons so it does not compete with primary actions.
- **Cards:** white surface, subtle border, 15–20px radius, restrained shadow. Group related content; avoid nesting multiple heavy borders.
- **Forms:** labels above fields, Poppins controls, neutral border, blue focus ring. Keep placeholders examples rather than instructions.
- **Tables:** neutral header surface, uppercase compact labels, thin separators, horizontal scroll on small screens.
- **Status:** use text plus color; do not communicate status by color alone. Existing status chips and badges should retain their labels.
- **Alerts and toasts:** rounded, brief, and tied to a clear success, warning, or error state.
- **Responsive:** preserve the current breakpoints in `styles.css`; collapse multi-column grids and let toolbars wrap on narrow screens.
- **Motion:** use short transitions for hover/focus. Respect `prefers-reduced-motion`.

## Implementation checklist

1. Reuse a token and existing component class before adding a new color or selector.
2. Keep current IDs, `data-*` hooks, route hashes, and accessible labels intact when editing dashboard markup.
3. Add a reusable modifier to the shared stylesheet instead of styling a one-off element inline.
4. Check desktop and mobile behavior, keyboard focus, dark mode, and long Indonesian labels when verifying visual changes.
