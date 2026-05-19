# Banhawy Design System

**Status:** Phase 1 — Foundation. Tokens are stable; component contracts are aspirational where marked **(planned)**.
**Audience:** Anyone touching Blade views or `resources/css/app.css`.
**Last review:** 2026-05-19.

This document is the **single source of truth** for visual decisions. If a screen looks "off," it's because it diverges from here — fix the screen, not this doc. Edits to tokens are scoped changes that must be discussed before landing.

---

## 1. Brand & voice

- **Product:** Banhawy — a local city app for Banha (الفئة المستهدفة: سكان بنها والقليوبية).
- **Voice in copy:** Arabic, conversational, short, helpful. Egyptian dialect over MSA when it sounds natural ("شوف الطريق" not "اعرض المسار").
- **Visual mood:** Clean, premium, *useful before pretty*. Inspired by Careem/Talabat/Airbnb — soft surfaces, generous spacing, brand as accent not statement.
- **Hard rules:**
  - **No emoji as icons.** Always inline SVG via [`<x-icon>`](resources/views/components/icon.blade.php). Emojis render inconsistently across OSes.
  - **No gradients as primary surfaces.** Solid color + subtle tint only.
  - **No hero blocks that exist only to fill space.** Every component justifies its pixels.
  - **Brand-as-accent:** the blue is for CTAs, selected state, and key signals — not big colored panels.

---

## 2. Token foundation

All tokens live in [`resources/css/app.css`](resources/css/app.css) under `@theme { ... }`. Tailwind generates utility classes from them (e.g. `--color-coral-500` → `bg-coral-500`, `text-coral-500`, `border-coral-500`).

> **Legacy-naming note:** Classes are still called `coral-*` and `honey-*` from a previous brand iteration. The **values** have been migrated to blue/yellow. **Do not** rename them in views — that's churn for zero user benefit. Treat the names as opaque tokens.

### 2.1 Color — semantic mapping

| Token group | Class prefix | Hex range | Semantic role |
|---|---|---|---|
| **Primary** (brand blue) | `coral-50` → `coral-800` | `#EEF2FF` → `#0F2787` | CTAs, links, selected state, focus rings, key icons |
| **Accent** (sale yellow) | `honey-300` → `honey-500` | `#FFE082` → `#F5BA12` | Promoted/sale strips, featured badges — sparing use |
| **Surface** (background) | `cream-50` → `cream-200` | `#FAFAFC` → `#E9EBF1` | Page bg, card bg, soft fills behind chips/inputs |
| **Ink** (text + UI) | `ink-300` → `ink-950` | `#B5B5BC` → `#0B0B0C` | All text and neutral UI — see hierarchy below |
| **Success** | `mint-100`, `mint-500`, `mint-700` | greens | Verified, online, success toasts |
| **Danger** | `blush-100`, `blush-500` | reds | Destructive actions, errors |

**Text hierarchy (use these, not arbitrary grays):**
- `text-ink-950` — primary headings & body
- `text-ink-700` — secondary body
- `text-ink-500` — meta / labels
- `text-ink-400` — placeholder / disabled
- `text-ink-300` — decorative only (icons, borders)

**Contrast minimums to respect:**
- `ink-500` on `cream-100` ≈ 4.6:1 (passes AA for body text 16px+)
- `ink-400` on `cream-100` ≈ 3.2:1 — **only for ≥14px bold** or non-essential text
- `ink-300` is never used for text — only borders/dividers

### 2.2 Typography

- **Font:** Cairo (primary), IBM Plex Sans Arabic (fallback), system sans (last resort).
- **Loaded via:** `--font-sans` token + `<link rel=preconnect href="fonts.gstatic.com">` in the layout.
- **Mobile root size:** 16px (iOS auto-zoom guard on inputs is set in app.css — don't change).

**Scale — use Tailwind classes; no arbitrary `text-[Xpx]` in new code:**

| Class | Size | Weight default | Used for |
|---|---|---|---|
| `text-2xl` (24px) | h1 | `font-black` | Hero titles only |
| `text-xl` (20px) | h2 | `font-extrabold` | Page titles |
| `text-base` (16px) | h3 / body strong | `font-extrabold` or `font-bold` | Card titles, primary buttons |
| `text-sm` (14px) | body | `font-bold` or `font-medium` | Default body |
| `text-xs` (12px) | meta | `font-bold` | Captions, chips, prices |
| `text-[11px]` | micro | `font-bold` to `font-extrabold` | Badges, small labels — **allowed exception** |
| `text-[10px]` | nano | `font-extrabold uppercase tracking-wider` | Section labels — sparingly |

**Weights allowed:** `font-medium` (500), `font-bold` (700), `font-extrabold` (800), `font-black` (900). No 600. No 400 for Arabic body — looks anemic with Cairo.

### 2.3 Spacing

Use Tailwind's default 4px scale. No arbitrary values for layout spacing.

| Token | px | Use |
|---|---|---|
| `1` | 4 | Hairline gaps in chip clusters |
| `1.5` | 6 | Tight icon-to-text gaps |
| `2` | 8 | Default gap between siblings |
| `3` | 12 | Card inner gaps |
| `4` | 16 | Card outer padding (mobile) |
| `5` | 20 | Section breaks inside a card |
| `6` | 24 | Section breaks between cards |
| `8` | 32 | Major page sections |

**Page gutters:** `px-4` (16px) on mobile. The main wrapper `<main class="mx-auto max-w-3xl px-4 py-4">` is in [`layouts/app.blade.php`](resources/views/layouts/app.blade.php). Don't override.

### 2.4 Border radius

| Token | px | Use |
|---|---|---|
| `rounded-md` | 6 | Internal small elements (avatar fallback, icon tiles ≤ 24px) |
| `rounded-lg` | 8 | Small chips, icon tiles |
| `rounded-xl` | 12 | Inputs, small cards, action chips |
| `rounded-2xl` | 16 | Standard cards (`card-light` ≈ 22px — close enough) |
| `rounded-3xl` | 24 | Sheets, modals, hero cards |
| `rounded-full` | 999px | Pills, avatars, FABs, chips |

**The `card-light` class uses `border-radius: 22px`** (non-Tailwind value, locked in app.css). New cards in views can use `rounded-2xl` (16px) for tighter UI or extend `card-light` for the canonical look.

### 2.5 Shadow / elevation

Only three elevations exist. Anything else is an anti-pattern.

| Token | Use |
|---|---|
| `shadow-card` (`var(--shadow-card)`) | Resting cards, the default. Used by `.card-light`. |
| `shadow-soft` (`var(--shadow-soft)`) | Hover/raised cards, dropdowns, popovers. |
| `shadow-glow` (`var(--shadow-glow)`) | **Only** the brand `card-orange` (blue brand card on welcome page). Do not reuse. |

For drop-shadows on small SVGs (badges), use `filter: drop-shadow(0 1px 2px rgba(11,11,12,.15))` directly. Already encapsulated in `.v-badge` and `.p-badge`.

### 2.6 Motion

| Duration | Use |
|---|---|
| `.15s` | Color/background hover swaps |
| `.2s` | Transform / scale on cards |
| `.25s–.35s` | Image hover zoom, larger movements |
| `cubic-bezier(.2,.9,.3,1)` | Default easing for "spring-y" feel |

**Don't introduce new transition durations.** Reuse one of the four.

---

## 3. Icons

- **Source:** [`<x-icon name="..." />`](resources/views/components/icon.blade.php) — currently ~85 named icons.
- **Style:** Stroke icons (Feather/Lucide-derived). `stroke-width="2"`, `stroke-linecap="round"`, `stroke-linejoin="round"`, `fill="none"`. Filled variant available via `filled` prop.
- **Sizes:** `w-3 h-3` (12px micro), `w-3.5 h-3.5` (14px), `w-4 h-4` (16px default in chips/buttons), `w-5 h-5` (20px section markers), `w-6 h-6` (24px tab bar).
- **Adding a new icon:** Edit `icon.blade.php`, add a new `@case('name')` with the path data. Don't import another icon library.
- **Hard rule (memory):** No emojis as icons. **Ever.** Even in placeholders, even in copy.

---

## 4. Component contracts

### 4.1 Cards — `card-light` is the default

```html
<div class="card-light p-4 mb-3">…</div>
```

- White background, 22px radius, 1px subtle border, `shadow-card`.
- Variants in app.css: `tier-silver`, `tier-gold` (verified-post strips), `post-sponsored`, `post-announcement`. **Do not invent new variants without RFC.**
- **Anti-pattern:** building a card with `bg-white rounded-2xl border border-ink-950/8 shadow-sm` inline — that's almost-but-not-quite `card-light`. Use the class.

### 4.2 Chips — `.chip` + state

```html
<a class="chip">سعر</a>                  {{-- resting (blue tint, blue-700 text) --}}
<a class="chip chip-active">مفعّل</a>    {{-- selected (solid blue, white text) --}}
```

- All filter pills, category jumpers, and tag groups must use `.chip`.
- For inline data tags (features, amenities), the current inline pattern is acceptable but should migrate to a `<x-chip>` component in Phase 2.

### 4.3 Buttons — **(planned `<x-button>`)**

Current state: every button is inline Tailwind. This is the biggest source of visual inconsistency. The Phase 2 spec:

| Variant | Background | Text | Border | Use |
|---|---|---|---|---|
| `primary` | `bg-coral-500 hover:bg-coral-600` | white | none | Main CTA, one per screen |
| `secondary` | `bg-cream-100 hover:bg-cream-200` | `ink-950` | none | Secondary action |
| `outline` | white | `coral-600` | `border border-coral-500/30` | Tertiary, in dense rows |
| `ghost` | transparent | `ink-700` | none | Toolbar buttons |
| `danger` | `bg-blush-500 hover:bg-blush-600` | white | none | Destructive action |
| `whatsapp` | `bg-[#25D366]` | white | none | WhatsApp-specific actions only |

| Size | Padding | Text | Height |
|---|---|---|---|
| `sm` | `px-3 py-1.5` | `text-xs font-extrabold` | 32px |
| `md` | `px-4 py-2.5` | `text-sm font-extrabold` | 40px |
| `lg` | `px-5 py-3.5` | `text-sm font-black` | 52px |

**Common shape:** `rounded-xl` (12px) for buttons, `rounded-full` for pill CTAs and FABs.

**States required:** default, hover, active (pressed), disabled (`opacity-60 cursor-not-allowed`), loading (text replaced with spinner + same width).

### 4.4 Inputs — **(planned `<x-input>` / `<x-textarea>`)**

Canonical input:
```
bg-cream-50 rounded-xl px-4 py-3
text-ink-950 placeholder-ink-400
outline-0 border border-ink-950/8
focus:border-coral-500 focus:bg-white focus:ring-4 focus:ring-coral-500/10
transition text-sm
```

(This is what the menu add-form uses — extract to a component in Phase 2.)

**States:** default, focus (blue ring), invalid (red border + helper text in `text-blush-500`), disabled (`bg-cream-200 text-ink-400`).

### 4.5 Badges

- `.v-badge` (verified — green/silver/gold tier)
- `.p-badge` (promoted/sponsored — blue)
- `.biz-card__verified` (corner-pinned badge on business cards)

Do not invent new badge classes; if you need a new signal, propose adding it here.

---

## 5. Layout & navigation

- **Page shell:** [`layouts/app.blade.php`](resources/views/layouts/app.blade.php) — `<html dir="rtl" lang="ar-EG">`.
- **Main wrapper:** `<main class="mx-auto max-w-3xl px-4 py-4">`.
- **Bottom nav:** present on logged-in screens; 5 tabs max — if you're adding a 6th, you need to consolidate.
- **Sticky header:** category filter rows use `sticky top-14 z-20` (top of bottom-of-fixed-topbar). Z-indexes used: `10` (cards w/ overlays), `20` (sticky headers), `30` (modals/popovers), `50` (toasts/tooltips). **Stay in these tiers.**
- **Safe areas:** the layout adds `padding-bottom: calc(7rem + env(safe-area-inset-bottom))` for iOS notch + bottom nav. New full-screen overlays must respect `env(safe-area-inset-*)`.

---

## 6. RTL rules

- The app is RTL-default. `dir="rtl"` is on `<html>`.
- **Always use logical properties:** `start` / `end` instead of `left` / `right`. Tailwind has `ms-*`, `me-*`, `ps-*`, `pe-*`, `start-*`, `end-*`.
- **Icons that imply direction** (`arrow-left`, `arrow-right`, `chevron`): pick the **visual** direction you want, don't rely on RTL to flip them. We've already flipped `arrow-right` in the back button — keep that pattern.
- **`dir="ltr"`** on inline elements only when content is intrinsically LTR (phone numbers, dates in Latin numerals, prices when shown with a Latin "ج.م"). Done via `dir="ltr"` attribute.

---

## 7. State patterns

Every screen must handle four states. **Failure to ship all four is a regression.**

### 7.1 Loading (`<x-skeleton>` — planned)
- Show skeleton blocks matching the final layout's shape — never spinners on initial load.
- For tab/section reloads, OK to use a small inline spinner.

### 7.2 Empty
- **Tone:** helpful, not apologetic.
- **Pattern:** centered icon (`w-12 h-12` in a soft-tinted disc) + one-line headline + 1–2 line hint + optional CTA.
- **Don't say:** "لا توجد نتائج", "فارغ", "Empty state"
- **Do say:** "لسه مفيش هنا — جرّب…", "ابدأ بـ…", with a clear next action

### 7.3 Error
- **Tone:** solution-focused, not blame-focused.
- **Pattern:** same shape as empty state but with `text-blush-500` icon disc + retry button.
- **Don't say:** "حصل خطأ", "Network error"
- **Do say:** "النت ضعيف شوية — حاول تاني", "السيرفر بيرد ببطء، استنى ثانية"

### 7.4 Disabled / forbidden
- Buttons: `opacity-60 cursor-not-allowed pointer-events-none`.
- Whole sections: light overlay + a short hint of why ("لازم تسجّل دخول الأول").

---

## 8. Accessibility minimums

- **Tap targets:** 40×40px minimum (use `h-10 w-10` or padding to hit). 32px allowed for dense icon-only buttons but flag for review.
- **Focus visibility:** every interactive needs a visible focus ring. Tailwind's default `focus:ring-4` with brand color at 10% works (`focus:ring-coral-500/10`).
- **`aria-label`** required on icon-only buttons. No exceptions.
- **`alt`** required on `<img>`. If decorative, `alt=""` explicitly — not omitted.
- **Modals/popovers:** focus-trap and ESC-to-close — currently not all do this; flag in Phase 5.

---

## 9. Anti-patterns (grounded — these are in the codebase today)

Each item is something I'd reject in review.

1. **Inline button styling instead of a component.** The menu manage page alone has 7 distinct button looks. → Phase 2 `<x-button>`.
2. **Re-defining card chrome.** `bg-white rounded-2xl border border-ink-950/8 shadow-sm` should be `card-light`.
3. **Arbitrary text sizes** like `text-[13px]`, `text-[15px]`. Stick to the scale.
4. **Arbitrary colors** like `text-[#1E40AF]` (hardcoded in `.biz-card__subtitle`) — use the `coral-700` token.
5. **Stacking shadows for "depth."** One shadow is enough. If you find yourself adding a second, you want elevation change, not more shadow.
6. **`bg-gradient-*` for normal surfaces.** Banned by visual style memory. Allowed only on the welcome page hero and on `.wallet-card` (existing exceptions).
7. **`left-*` / `right-*` for positioning** in new code. Use `start-*` / `end-*`.
8. **Multiple primary CTAs on one screen.** One primary, the rest secondary/ghost.
9. **Skeleton-less initial loads** that flash empty layouts.
10. **Inventing chip variants** (`.chip-mint`, `.chip-honey-active`). Use `chip` + `chip-active` only.
11. **Nested Blade comments in component docstrings.** `{{-- outer {{-- inner --}} more --}}` — Blade doesn't support nesting; the first `--}}` closes the outer block and anything after (including `<x-tag>` examples) compiles as live markup. Components have docstring usage examples that look like `<x-button>…</x-button>` — keep each example on its own single `{{-- … --}}` line with **no nested delimiters**. We hit this bug live: `<x-button>` recursed infinitely on /search until we discovered the docstring was leaking 7 component renders per parent render.

---

## 10. Known inconsistencies (to fix in later phases)

These are documented so we don't accidentally codify them.

- **`<meta name="theme-color" content="#FFF7F1">`** in [`layouts/app.blade.php`](resources/views/layouts/app.blade.php:9) is peach — should be `#EEF2FF` (coral-50) or `#FFFFFF` to match the blue brand.
- **`.biz-card__subtitle`** uses raw hex `#1E40AF` — replace with `var(--color-coral-700)`.
- **`.promo-card`** declares `min-height: 160px` then `min-height: 180px` 40 lines later — duplicate; the 180 wins. Cleanup pass below.
- **`card-orange`** name is misleading (it's now blue). Used in `welcome.blade.php` only. Rename to `card-primary` in Phase 2 (with a search-and-replace).
- **`tier-gold`** and `tier-silver` use linear-gradients on backgrounds — exception to "no gradients" rule, narrowly allowed for premium-tier strips.

---

## 11. Component reference (Phase 2 — shipped)

Seven anonymous Blade components live in [`resources/views/components/`](resources/views/components/). Each file has a usage block at the top — read it before using.

### `<x-button>` · [components/button.blade.php](resources/views/components/button.blade.php)

```blade
<x-button>حفظ</x-button>                                      {{-- primary, md --}}
<x-button variant="secondary">إلغاء</x-button>
<x-button variant="outline" icon="filter" iconEnd>فلتر</x-button>
<x-button variant="danger" icon="trash" size="sm">حذف</x-button>
<x-button variant="ghost">المزيد</x-button>
<x-button variant="whatsapp" icon="whatsapp" href="https://wa.me/...">اتصل</x-button>
<x-button :loading="$saving" type="submit" block size="lg">احفظ التعديلات</x-button>
```

Props: `variant` (primary/secondary/outline/ghost/danger/whatsapp), `size` (sm/md/lg), `icon`, `iconEnd`, `href`, `loading`, `block`, `type`. All HTML attrs pass through.

### `<x-input>` · [components/input.blade.php](resources/views/components/input.blade.php)

```blade
<x-input name="name" label="الاسم" required placeholder="مثلاً: مطعم النيل"/>
<x-input name="phone" type="tel" dir="ltr" label="رقم التليفون"
         :value="old('phone')" :error="$errors->first('phone')"
         helper="هنبعت كود تأكيد على واتساب"/>
<x-input name="price" type="number" min="0" suffix="ج.م" label="السعر"/>
<x-input name="search" icon="search" placeholder="ابحث…"/>
```

Props: `name` (required), `label`, `type`, `value`, `placeholder`, `error`, `helper`, `icon`, `suffix`, `required`, `disabled`. All other input attrs (min, max, pattern, autocomplete, dir) pass through.

### `<x-textarea>` · [components/textarea.blade.php](resources/views/components/textarea.blade.php)

```blade
<x-textarea name="description" label="الوصف" rows="3" maxlength="500" counter
            placeholder="كل التفاصيل…"
            :error="$errors->first('description')"/>
```

Props: `name`, `label`, `value`, `rows`, `maxlength`, `counter`, `error`, `helper`, `required`. The `counter` prop activates a live "X / max" display (auto-hydrated by JS in the component).

### `<x-card>` · [components/card.blade.php](resources/views/components/card.blade.php)

```blade
<x-card>… content …</x-card>
<x-card padding="lg">…</x-card>
<x-card padding="none"> {{-- caller controls padding (e.g. media inside) --}} </x-card>
<x-card tier="gold">…</x-card>                       {{-- premium verified strip --}}
<x-card variant="sponsored">…</x-card>               {{-- yellow-accent sponsored --}}
<x-card as="a" href="…">… clickable card …</x-card>
```

Props: `padding` (none/sm/md/lg), `tier` (silver/gold), `variant` (sponsored/announcement/dark), `as`, `href`.

### `<x-chip>` · [components/chip.blade.php](resources/views/components/chip.blade.php)

```blade
<x-chip>كل المطاعم</x-chip>
<x-chip active>قهاوي</x-chip>
<x-chip icon="filter">فلتر</x-chip>
<x-chip href="{{ route('directory.category', 'food') }}" :count="$count">مطاعم</x-chip>
```

Props: `active`, `icon`, `href`, `count`.

### `<x-empty-state>` · [components/empty-state.blade.php](resources/views/components/empty-state.blade.php)

```blade
<x-empty-state icon="search" title="لسه مفيش نتايج"
               hint="جرّب كلمة تانية أو شيل الفلتر"/>

<x-empty-state tone="danger" icon="bolt" title="السيرفر بيرد ببطء"
               hint="حاول تاني بعد ثانية">
    <x-slot:cta>
        <x-button variant="outline" onclick="location.reload()">إعادة المحاولة</x-button>
    </x-slot:cta>
</x-empty-state>
```

Props: `icon`, `title`, `hint`, `tone` (default/danger). Optional `cta` slot.

### `<x-icon-tile>` · [components/icon-tile.blade.php](resources/views/components/icon-tile.blade.php)

The most-repeated UI primitive in the app (~77 inline instances at the start of Phase 4). Pairs an SVG icon with a colored square/circle backdrop — used for section markers, list-item leading icons, tab headers, action hints. **Always use this; never re-roll `w-X h-X rounded-… bg-…-100 text-…-600 grid place-items-center` inline.**

```blade
<x-icon-tile icon="map-pin"/>                                {{-- default coral, md (40px), rounded square --}}
<x-icon-tile icon="bell" tone="honey" size="sm"/>            {{-- 24px honey-tinted tile --}}
<x-icon-tile icon="check" tone="mint" shape="circle"/>       {{-- round mint tile --}}
<x-icon-tile icon="bolt" intensity="strong" size="lg"/>      {{-- solid brand 48px --}}
<x-icon-tile icon="search" href="/search"/>                  {{-- clickable (renders as <a>) --}}
```

Props: `icon` (required), `tone` (coral/mint/honey/blush/cream), `size` (sm 24 / md 40 / lg 48 / xl 56), `shape` (square/circle), `intensity` (soft/strong), `href`.

**Note:** the size scale is intentional and the codebase had drift (w-7, w-8, w-9 instances). Migrating to `<x-icon-tile>` standardizes everything to `sm/md/lg/xl` — small visual shifts (e.g. w-9 → w-10) are accepted as the design intent.

### `<x-skeleton>` · [components/skeleton.blade.php](resources/views/components/skeleton.blade.php)

```blade
<x-skeleton class="h-4 w-32"/>
<x-skeleton variant="circle" class="w-12 h-12"/>
<x-skeleton variant="text" lines="3"/>
<x-skeleton class="aspect-[16/10] w-full"/>
```

Props: `variant` (block/circle/text), `lines`.

---

## 12. Migration guide

Replacing existing inline markup with components is **always safe** and **always preferred**. Migrate as you touch a screen for unrelated work; don't open a "migrate everything" PR.

### Find & replace patterns

| Found in code | Replace with |
|---|---|
| `bg-coral-500 hover:bg-coral-600 text-white font-extrabold rounded-xl px-4 py-2.5 …` | `<x-button>` |
| `bg-cream-100 hover:bg-cream-200 text-ink-950 …` | `<x-button variant="secondary">` |
| `bg-blush-500 … text-white …` (destructive button) | `<x-button variant="danger">` |
| `bg-white rounded-2xl border border-ink-950/8 shadow-sm` | `<x-card>` |
| `bg-coral-100 text-coral-700 rounded-full px-3 py-1 text-xs font-bold` (pill) | `<x-chip>` |
| Hand-rolled empty placeholders ("لا توجد نتائج", etc.) | `<x-empty-state>` |
| `<input type="text" class="…" />` with hand-rolled label/error | `<x-input>` |
| `<textarea class="…" />` with hand-rolled counter | `<x-textarea>` |

### Migrated already (proof)

- [`menu/manage.blade.php`](resources/views/menu/manage.blade.php) — add-category submit button, empty state.
- [`menu/partials/add-item-form.blade.php`](resources/views/menu/partials/add-item-form.blade.php) — main "احفظ" submit button.
- [`menu/partials/business-features-form.blade.php`](resources/views/menu/partials/business-features-form.blade.php) — header "حفظ" button.
- [`search/index.blade.php`](resources/views/search/index.blade.php) — full migration: search submit button, two empty states, listing list cards.
- [`directory/show.blade.php`](resources/views/directory/show.blade.php) — owner "إدارة النشاط" pill button.
- [`feed.blade.php`](resources/views/feed.blade.php) — 5 "see all" chevron tiles → `<x-icon-tile>`; 3 standalone chevron arrows → `<x-icon name="chevron-left">`.
- [`notifications/index.blade.php`](resources/views/notifications/index.blade.php) — bell icon tile in list rows.
- [`profile.blade.php`](resources/views/profile.blade.php) — top-section primary buttons, comment/bell tile chips, wallet hint big tile, settings rows (camera/lock/logout tiles), my-orders cart tile.
- [`directory/manage.blade.php`](resources/views/directory/manage.blade.php) — owner dashboard: edit/menu/photos/stats/globe tiles (5 inline rows).

### Components added since first list

- **[`<x-icon-tile>`](resources/views/components/icon-tile.blade.php)** — soft-tinted icon container. Documented in §11 above.
- **`chevron-left`, `chevron-right`, `bookmark`, `lock`, `edit`, `menu`, `chart`, `globe`** added to [`<x-icon>`](resources/views/components/icon.blade.php).
- **`pill` prop on `<x-button>`** — rounded-full for header/CTAs.

### What NOT to migrate (yet)

- **Icon-only round buttons / tiles** (`w-9 h-9 rounded-full bg-coral-50 text-coral-600` …). Repeated 6+ times in [`feed.blade.php`](resources/views/feed.blade.php) alone. **Action:** add `<x-icon-tile>` in Phase 4 — high-impact win, will eliminate ~25+ inline tile patterns across the app.
- Buttons with **bespoke behavior** that the component doesn't yet model (e.g. toggle buttons that swap icons inline, dynamic-class `is_active` toggles, map route-trigger buttons). Leave as-is; add a variant when there's a third instance.
- `<summary>` elements styled as buttons (used by `<details>` collapsibles in admin sections). They have to stay as `<summary>` HTML.
- Cards with **internal scroll/snap layouts** specific to one feature (e.g. `.feat-card`, `.biz-card`, marketplace mini-cards in horizontal scrolls). They have their own dedicated classes in app.css.
- **Positioned popups / dropdowns** (`absolute … bg-white rounded-2xl shadow-xl`). Different elevation contract than `<x-card>`.

---

## 13. Roadmap (updated)

- ~~**Phase 1:** Tokens + DESIGN-SYSTEM.md~~ ✓ shipped
- ~~**Phase 2:** Core components (button, input, textarea, card, chip, empty-state, skeleton)~~ ✓ shipped
- ~~**Phase 3:** First-pass migration of Search/Feed/Business-profile~~ ✓ shipped (limited).
- ~~**Phase 4 — part 1:** `<x-icon-tile>` + feed/notifications/profile icon-tile migration~~ ✓ shipped.
- ~~**Phase 4 — part 2:** profile + directory/manage migrations~~ ✓ shipped (51 inline tile patterns remain, all in low-traffic files; migrate opportunistically).
- ~~**Phase 5:** Empty-state sweep (5 hero migrations) + a11y focus rings on components~~ ✓ shipped (partial — 25 hand-rolled empties remain; migrate as touched).
- ~~**Phase 6:** Dead-code detection report + dark mode plan + production checklist~~ ✓ shipped (see §14, §15, §16).

---

## 14. Dead-code audit (snapshot — 2026-05-19)

Run this audit before any cleanup PR.

### Unused Blade partials

- [`resources/views/partials/feed-page.blade.php`](resources/views/partials/feed-page.blade.php) (25 lines) — declares itself as "unified feed renderer"; never `@included` anywhere. **Do not delete blindly** — looks like work-in-progress for a unified renderer. Verify with author or git history (`git log -- resources/views/partials/feed-page.blade.php`) before removing.

### Dead CSS classes

**0/210** class definitions in `app.css` are unreferenced — surprisingly clean for a codebase this size. (Detection is fuzzy: a "reference" inside a comment counts; sample-verify before deleting any class.)

### npm packages

All 13 production deps + 7 dev deps are in use:
- `@capacitor/*` — mobile (PWA → native) shell
- `@hotwired/turbo` — partial page navigations
- `sharp` — image post-processing
- `@tailwindcss/vite`, `tailwindcss`, `vite`, `laravel-vite-plugin` — build chain
- `concurrently` — dev script orchestration
- `@capacitor/assets`, `@capacitor/cli` — Capacitor build tooling

### Composer (PHP) packages

All production + dev deps are standard Laravel 13 + tooling. `laravel/pao` is the only non-obvious one — confirm with the team if uncertain.

### Detection method (re-runnable)

```bash
# Unused Blade partials
find resources/views -type d -name "partials" -exec find {} -name "*.blade.php" \; | while read p; do
    rel=$(echo "$p" | sed 's|resources/views/||; s|\.blade\.php$||; s|/|.|g')
    used=$(grep -rl "@include.*['\"]$rel['\"]" resources/views/ 2>/dev/null | wc -l)
    [ "$used" -eq 0 ] && echo "UNUSED: $rel"
done

# Dead CSS classes
grep -oE "^\.[a-z][a-z0-9_-]+\b" resources/css/app.css | sort -u | while read cls; do
    name="${cls#.}"
    hits=$(grep -rE "\b$name\b" resources/views/ resources/js/ public/ 2>/dev/null | wc -l)
    [ "$hits" -eq 0 ] && echo "DEAD: $name"
done
```

---

## 15. Dark mode — strategy (not yet implemented)

**Status:** intentionally not shipped. The current app has [`<meta name="color-scheme" content="light">`](resources/views/layouts/app.blade.php#L10) so the OS won't auto-invert.

### Why deferred

Dark mode in a Tailwind app **is not a 5-minute task.** It needs:
- A dark variant for every brand surface token (cream-*, ink-*, white)
- A per-screen audit (many cards hard-code `bg-white`; many text colors hard-code `text-ink-950`)
- Image/photo backgrounds need handling (cover photos work in dark; hero blobs do not)
- The 30+ inline gradient/image overlays in app.css need dark variants
- Maps need a dark tile layer
- Brand color (`coral-500` = `#2D5BFF`) needs reverification for contrast against dark surfaces

### When we do it (Phase 7 — separate roadmap)

1. Add dark companions to the surface tokens in `app.css`:
   ```css
   @media (prefers-color-scheme: dark) {
     :root {
       --color-cream-50:  #0F0F11;
       --color-cream-100: #131316;
       --color-cream-200: #1A1A1E;
       --color-ink-950:   #F2F2F4;
       --color-ink-900:   #E1E1E5;
       --color-ink-700:   #C2C2CA;
       --color-ink-500:   #8A8A93;
       /* coral-* stays the same — brand survives dark */
     }
   }
   ```
2. Switch [layouts/app.blade.php](resources/views/layouts/app.blade.php#L10) to `color-scheme: light dark`.
3. Update `theme-color` meta with a `media="(prefers-color-scheme: dark)"` variant.
4. Audit every screen — fix any `bg-white`, `text-black`, hex-coded `#FFF`/`#000`, gradient overlays.
5. Test in iOS/Android dark mode.

**Don't ship partial dark mode** — half-converted screens look worse than no dark mode.

---

## 16. Production readiness checklist

Tick before any release-candidate push. The first time you tick a box, leave a date+link.

### Build & assets
- [ ] `npm run build` succeeds with no warnings — `php artisan view:cache` succeeds
- [ ] Compiled CSS bundle stays under 200 KB (currently ~160 KB / 26 KB gzipped)
- [ ] Compiled JS bundle stays under 200 KB (currently ~133 KB / 37 KB gzipped)
- [ ] No `console.log` / `dd()` / `dump()` left in shipped code
- [ ] `npm audit --omit=dev` shows no high/critical CVEs

### Performance budgets (mobile, 3G)
- [ ] Largest Contentful Paint < 2.5s on `/feed`
- [ ] Cumulative Layout Shift < 0.1 (verify image dimensions are set; `aspect-[16/10]` on cover photos)
- [ ] First Input Delay < 100ms
- [ ] Lighthouse mobile score ≥ 90 on `/feed`, `/biz/{slug}`, `/m/{business}`

### SEO
- [ ] Every page has a unique `<title>` (currently driven by `@extends('layouts.app', ['title' => …])`)
- [ ] Every page has a meta description
- [ ] `/sitemap.xml` and `/robots.txt` resolve and update on new content
- [ ] JSON-LD structured data on business profiles + menu pages

### Accessibility
- [ ] All icon-only buttons have `aria-label` (`<x-icon-tile aria-label="…"/>` when not in a labelled link)
- [ ] Keyboard navigation reaches every interactive element
- [ ] Focus rings visible on `<x-button>`, `<x-icon-tile>`, `<x-card as="a">`, `.chip` (✓ shipped Phase 5)
- [ ] Contrast: `text-ink-500` on `cream-100` passes WCAG AA at 14px+
- [ ] Forms have visible error states (✓ `<x-input :error>` supports this)
- [ ] No `:hover` reveals critical info (mobile has no hover)

### Errors & states
- [ ] Every list page has an empty state (`<x-empty-state>`) — current sweep: 5 done, ~25 hand-rolled remain
- [ ] Every async fetch has a loading state (skeleton, not spinner) — Phase 7 work
- [ ] Every fetch failure shows an actionable error (`<x-empty-state tone="danger">`)
- [ ] 404 and 500 pages match the design system
- [ ] `/offline` page exists and renders without external assets (already present)

### Security
- [ ] CSRF token on every form (Laravel default; verify no `@csrf` was dropped)
- [ ] No secrets in `.env.example` or committed config
- [ ] CSP headers configured (review for inline `<script>` in views)
- [ ] Rate limits on `/m/{business}/order`, `/login`, `/forgot/send` (already throttled)
- [ ] User-generated content goes through `e()` or `{{ }}` — never `{!! !!}` without trust

### Mobile / PWA
- [ ] `manifest.webmanifest` lists current screen icons
- [ ] iOS `apple-touch-icon` set
- [ ] `viewport-fit=cover` honoured with `env(safe-area-inset-*)` (already done in [`layouts/app.blade.php`](resources/views/layouts/app.blade.php))
- [ ] PWA install prompt path tested
- [ ] Capacitor builds succeed on iOS + Android

### Observability
- [ ] Laravel logs to a real destination (file or remote)
- [ ] Error tracker (Sentry/Bugsnag) configured for production env
- [ ] Critical user actions (signup, business claim, menu order) emit a track event

### Pre-launch one-liners
```bash
php artisan view:clear && php artisan view:cache && npm run build       # rebuild fresh
php artisan route:list                                                  # sanity-check routes
php artisan migrate:status                                              # confirm migrations
php artisan optimize                                                    # cache config/routes/views
```

---

**To propose a change to this doc:** edit it in a PR; if you're changing tokens, link to the screens that demonstrate why. Token changes are global and noisy — be sure.
