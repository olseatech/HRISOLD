# HRIS Government Frontend Redesign Prompt
> Paste this entire prompt into your AI of choice. It is self-contained and context-efficient.

---

## SYSTEM

You are a senior frontend engineer and UI/UX designer. You are redesigning the frontend of a **PHP MVC server-rendered HRIS** (Human Resource Information System) for a Philippine barangay government office.

**Hard constraints — never break these:**
- PHP backend logic, routes, form actions, CSRF tokens, session checks: **untouched**
- All existing PHP class names and HTML structure: **preserved**
- Only CSS files and PHP view markup (classes, SVGs, minor structural wrappers) may change

---

## STACK

```
PHP MVC · No framework · Server-rendered · No React/Vue
CSS: multi-file custom architecture loaded in this order:
  variables.css → base.css → layout.css → components.css
  → dashboard.css → [module].css → responsive.css
Tailwind CDN (utility classes only, no purge)
Fonts: Inter (body) · Plus Jakarta Sans (display headings)
Icons: inline SVG only — no icon library
```

---

## DESIGN DIRECTION

**Theme:** Government-official · White-first · Structured · Compact · Trustworthy

### Color System

| Role | Token | Hex | Notes |
|------|-------|-----|-------|
| Sidebar / shell | `--sidebar-bg` | `#1a2d4f` | Government navy — do not change |
| Sidebar brand | `--sidebar-brand-bg` | `#10203a` | Darker navy top block |
| Primary action | `--color-primary` | `#2563eb` | Blue-600 — buttons, links, active nav |
| **NEW: Option accent** | `--color-accent` | `#0e7490` | Cyan-700 — use for KPI cards, section dividers, status chips, chart lines. Replaces teal-600 everywhere. More distinctive, readable on white. |
| Page background | `--color-bg-page` | `#f8fafc` | Slate-50 — never gradient |
| Card surface | `--color-bg-card` | `#ffffff` | White |
| Success | `--green-600` | `#16a34a` | Attendance present, approved |
| Warning | `--amber-600` | `#d97706` | Leave pending, late |
| Danger | `--red-600` | `#dc2626` | Absent, rejected |

**Banned:** purple (`#6B4EFF`, `#5a3fe0`, `#9333ea`), coral as primary, any `radial-gradient` blob outside the login brand panel, any unsanctioned `linear-gradient` background fill.

---

## LAYOUT SHELL

```
┌──────────────────────────────────────────────────────┐
│ [3px blue top stripe]                                │
│ [Shield SVG] Barangay HRIS  ← sidebar-brand #10203a │
│              Management Portal                       │
├──────────────────────────────────────────────────────│
│ NAVIGATION ─────────────────                         │
│ [icon] Dashboard     ← active: 3px blue left bar     │
│ [icon] Billing                                       │
│ [icon] Employees                                     │
│ [icon] Attendance                                    │
│ [icon] Leave                                         │
│ [icon] Payroll                                       │
│ [icon] Settings                                      │
├──────────────────────────────────────────────────────│
│ [icon] Sign out      ← red tint on hover             │
└──────────────────────────────────────────────────────┘

Topbar (white, sticky, 60px):
  [hamburger] | [kicker + page title] ────── [user pill: name | role | date]
  2px accent line at bottom (color = current module's accent)

Content: padding 24px, background transparent (shows page bg)
```

---

## KEY COMPONENT SPECS

### KPI Cards (dashboard)
- White card, `border-radius: 16px`, `box-shadow: shadow-sm`
- **Top accent bar** 3px via `::before` — color by metric type
- **Icon badge** — 34×34px rounded square, tinted background, SVG icon
- **Large number** — Plus Jakarta Sans, `clamp(2rem, 3.5vw, 2.75rem)`, bold
- **Divider line** `<hr>` before footer note
- **Hover:** lift `translateY(-1px)`, `shadow-md` — respect `prefers-reduced-motion`

### Login (two-panel)
```
Left panel (dark navy #10203a):
  - Subtle grid texture overlay (repeating-linear-gradient lines, ~2% opacity)
  - Blue radial glow at top (rgba(37,99,235,0.14))
  - 3px blue→cyan bottom accent stripe
  - Government seal (circular, HR monogram, glow ring)
  - "Republic of the Philippines" eyebrow
  - 2×2 metrics grid in framed container
  - "System Online" green pulse chip

Right panel (white):
  - "Secure Access" kicker (blue, uppercase, short line before)
  - Clean form fields, 1px border, focus: 2px blue outline
  - Full-width solid blue submit button, box-shadow
  - Footer: "Encrypted · CSRF Protected · RBAC" metadata row
  - Legal note with border-top divider
```

### Module Page Heroes (left-border pattern)
```css
/* Each module gets its own accent color on the left border */
.emp-hero   { border-left: 4px solid #2563eb; }   /* blue   */
.att-hero   { border-left: 4px solid #0e7490; }   /* cyan   */
.leave-hero { border-left: 4px solid #d97706; }   /* amber  */
.pay-hero   { border-left: 4px solid #16a34a; }   /* green  */
.set-hero   { border-left: 4px solid #64748b; }   /* slate  */
```

### Tables
- Header: `background: #f8fafc`, uppercase labels, `letter-spacing: 0.06em`
- Even rows: `background: #eff6ff` (blue-50)
- Hover row: `background: #dbeafe` (blue-100)
- Sticky header, `border-collapse: collapse`

### Buttons
```css
.btn-primary   { background: #1d4ed8; color: #fff; border-radius: 8px; }
.btn-secondary { border: 1px solid #e2e8f0; color: #0f172a; background: #fff; }
.btn-danger    { background: #dc2626; color: #fff; }
/* No gradients on any button. Solid colors only. */
```

### Badges / Status Chips
```
Active/Present/Approved → green-100 bg, green-800 text
Pending/Late            → amber-100 bg, amber-800 text
Absent/Rejected         → red-100 bg, red-800 text
Probation/Info          → blue-50 bg, blue-800 text
Neutral/Inactive        → slate-100 bg, slate-600 text
```

---

## FILES TO MODIFY

| File | What to do |
|------|-----------|
| `public/assets/css/variables.css` | Add `--color-accent: #0e7490` and `--cyan-*` scale tokens |
| `public/assets/css/base.css` | Login: grid texture, seal glow, metrics frame, full-width button |
| `public/assets/css/layout.css` | Shield brand block, sidebar-label rule, topbar accent `::after` |
| `public/assets/css/components.css` | Buttons solid-only, alert left-border pattern, badge system |
| `public/assets/css/dashboard.css` | KPI icon-wrap, divider, pulse chip, quick-link arrows |
| `public/assets/css/marketing.css` | Replace all purple with blue/cyan; hero gradient: `#1a2d4f → #1d4ed8` |
| `public/assets/css/billing.css` | `.bill-btn-primary` → solid `var(--blue-700)`, no purple shadows |
| `public/assets/css/[module].css` | Update left-border accent to new cyan token where appropriate |
| `public/assets/css/responsive.css` | 900px: sidebar off-canvas, `transform: translateX(-100%)` / `.is-open` |
| `app/views/partials/sidebar.php` | Shield SVG brand block, inline SVG nav icons |
| `app/views/dashboard/index.php` | KPI icon-wraps, ql-icon + ql-arrow in quick links |
| `app/views/auth/login.php` | Seal with glow ring, metrics frame container |

---

## IMPLEMENTATION APPROACH

1. **Read each file before editing** — use your file read tool
2. **Use bash heredoc writes** for full rewrites (avoids truncation bugs)
3. **Use Python `str.replace()`** for targeted patches in large files (e.g. marketing.css)
4. **Token-first:** every color reference must use a CSS variable — no raw hex in components
5. **QA after each major file:** Python scan for purple hex and rgba(107,78,255,...) remaining
6. **Final QA:** verify all new CSS classes used in PHP views are defined in CSS files

---

## QUALITY CHECKLIST

Before finishing, verify with a Python script:
- [ ] Zero purple hex (`#6B4EFF`, `#5a3fe0`, `#9333ea`, `rgba(107,78,255`) in all CSS + PHP
- [ ] `--color-accent: #0e7490` added to `variables.css`
- [ ] `.bill-btn-primary` uses `var(--blue-700)` not a gradient
- [ ] `.kpi-icon-wrap` defined in `dashboard.css`
- [ ] Login left panel has grid texture pattern (repeating-linear-gradient)
- [ ] Shield SVG present in `sidebar.php`
- [ ] No gradient background fills outside login brand panel
- [ ] All module heroes have 4px left border with correct module color
- [ ] `:focus-visible` outline defined globally
- [ ] `prefers-reduced-motion` applied to all animations

---

## TONE OF OUTPUT

- Government official — not SaaS startup
- Compact, dense, information-rich — not airy or decorative
- Confident spacing with clear visual hierarchy
- Every element earns its place — no decorative blobs or hero illustrations
- Feels like GOV.UK or Philippines eGov portals — not Figma community templates
