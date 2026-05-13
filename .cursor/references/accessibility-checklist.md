# Accessibility Checklist

Quick reference for WCAG 2.1 AA compliance.

## Laravel Blade template notes (repo-specific)

Codebase này dùng **Laravel Blade components** + (một số chỗ) **Alpine.js** cho UI (ví dụ modal). Khi viết/đổi UI:

- Ưu tiên dùng component có sẵn: `resources/views/components/forms/*`, `resources/views/components/ui/*`, `resources/views/components/feedback/*`.
- Nơi dễ sai A11y nhất: form validation, icon-only buttons, modal focus, và menu mobile drawer.
- Nếu đổi schema/UI mapping, chỉ cần đảm bảo UI A11y; DB docs không liên quan trực tiếp.

## Table of Contents

- [Essential Checks](#essential-checks)
- [Common HTML Patterns](#common-html-patterns)
- [Testing Tools](#testing-tools)
- [Quick Reference: ARIA Live Regions](#quick-reference-aria-live-regions)
- [Common Anti-Patterns](#common-anti-patterns)

## Essential Checks

### Keyboard Navigation
- [ ] All interactive elements focusable via Tab key
- [ ] Focus order follows visual/logical order
- [ ] Focus is visible (outline/ring on focused elements)
- [ ] Custom widgets have keyboard support (Enter to activate, Escape to close)
- [ ] No keyboard traps (user can always Tab away from a component)
- [ ] Skip-to-content link at top of page - visible (at least) on keyboard focus
- [ ] Modals trap focus while open, return focus on close

### Screen Readers
- [ ] All images have `alt` text (or `alt=""` for decorative images)
- [ ] All form inputs have associated labels (`<label>` or `aria-label`)
- [ ] Buttons and links have descriptive text (not "Click here")
- [ ] Icon-only buttons have `aria-label`
- [ ] Page has one `<h1>` and headings don't skip levels
- [ ] Dynamic content changes announced (`aria-live` regions)
- [ ] Tables have `<th>` headers with scope

### Visual
- [ ] Text contrast ≥ 4.5:1 (normal text) or ≥ 3:1 (large text, 18px+)
- [ ] UI components contrast ≥ 3:1 against background
- [ ] Color is not the only way to convey information
- [ ] Text resizable to 200% without breaking layout
- [ ] No content that flashes more than 3 times per second

### Forms
- [ ] Every input has a visible label
- [ ] Required fields indicated (not by color alone)
- [ ] Error messages specific and associated with the field
- [ ] Error state visible by more than color (icon, text, border)
- [ ] Form submission errors summarized and focusable
- [ ] Known fields use autocomplete (for example `type="email" autocomplete="email"`)

### Content
- [ ] Language declared (`<html lang="en">`)
- [ ] Page has a descriptive `<title>`
- [ ] Links distinguish from surrounding text (not by color alone)
- [ ] Touch targets ≥ 44x44px on mobile
- [ ] Meaningful empty states (not blank screens)

## Common HTML Patterns

### Buttons vs. Links

```html
<!-- Use <button> for actions -->
<button type="button">保存</button>

<!-- Use <a> for navigation -->
<a href="/fuel/fuel_member.php">燃料組合員マスタへ</a>

<!-- NEVER use div/span as buttons -->
<div role="button">保存</div>  <!-- BAD -->
```

#### Blade component (recommended)

```html
<x-ui.button type="submit">ログイン</x-ui.button>

<x-ui.button type="button" variant="ghost" aria_label="メニューを開く">
  <x-ui.icon name="menu" />
</x-ui.button>
```

### Form Labels

```html
<!-- Explicit label association -->
<label for="login_id">ユーザー名</label>
<input id="login_id" name="login_id" type="text" autocomplete="username" required />

<!-- Implicit wrapping -->
<label>
  Email address
  <input type="email" required />
</label>

<!-- Hidden label (visible label preferred) -->
<input type="search" aria-label="検索" />
```

#### Blade form component (recommended)

Các component form trong repo đã hỗ trợ `aria-invalid` và `aria-describedby` khi có error/hint (xem `components/forms/input.blade.php`).

```html
<x-forms.label for="login_id">ユーザー名</x-forms.label>
<x-forms.input name="login_id" type="text" autocomplete="username" :required="true" />
```

### ARIA Roles

```html
<!-- Navigation -->
<nav aria-label="Main navigation">...</nav>
<nav aria-label="Footer links">...</nav>

<!-- Status messages -->
<div role="status" aria-live="polite">Task saved</div>

<!-- Alert messages -->
<div role="alert">Error: Title is required</div>

<!-- Modal dialogs -->
<div role="dialog" aria-modal="true" aria-labelledby="modal-title-example">...</div>

<!-- Loading states -->
<div aria-busy="true" aria-label="Loading tasks">
  <span>Loading...</span>
</div>
```

#### Modal pattern in this repo (Alpine)

`x-feedback.modal` đang set `role="dialog"`, `aria-modal="true"`, `aria-labelledby="modal-title-..."` sẵn.

Checklist khi dùng modal:
- [ ] Có nút đóng với `aria-label="Close"`
- [ ] Click backdrop đóng (nếu nghiệp vụ cho phép)
- [ ] (Khuyến nghị) trap focus + đóng bằng Escape (nếu chưa có, cần bổ sung khi yêu cầu UI phức tạp)

### Accessible Lists

```html
<ul role="list" aria-label="一覧">
  <li>
    <input type="checkbox" id="row-1" aria-label="選択: 1行目" />
    <label for="row-1">1行目</label>
  </li>
</ul>
```

### Page landmarks

```html
<a class="sr-only focus:not-sr-only" href="#main">Skip to content</a>
<header>...</header>
<nav aria-label="Breadcrumb">...</nav>
<main id="main" role="main">...</main>
```

## Testing Tools

```bash
# Automated audit
npx axe-core          # Programmatic accessibility testing
npx pa11y             # CLI accessibility checker

# In browser
# Chrome DevTools → Lighthouse → Accessibility
# Chrome DevTools → Elements → Accessibility tree

# Screen reader testing
# macOS: VoiceOver (Cmd + F5)
# Windows: NVDA (free) or JAWS
# Linux: Orca
```

## Quick Reference: ARIA Live Regions

| Value | Behavior | Use For |
|-------|----------|---------|
| `aria-live="polite"` | Announced at next pause | Status updates, saved confirmations |
| `aria-live="assertive"` | Announced immediately | Errors, time-sensitive alerts |
| `role="status"` | Same as `polite` | Status messages |
| `role="alert"` | Same as `assertive` | Error messages |

## Common Anti-Patterns

| Anti-Pattern | Problem | Fix |
|---|---|---|
| `div` as button | Not focusable, no keyboard support | Use `<button>` |
| Missing `alt` text | Images invisible to screen readers | Add descriptive `alt` |
| Color-only states | Invisible to color-blind users | Add icons, text, or patterns |
| Autoplaying media | Disorienting, can't be stopped | Add controls, don't autoplay |
| Custom dropdown with no ARIA | Unusable by keyboard/screen reader | Use native `<select>` or proper ARIA listbox |
| Removing focus outlines | Users can't see where they are | Style outlines, don't remove them |
| Empty links/buttons | "Link" announced with no description | Add text or `aria-label` |
| `tabindex > 0` | Breaks natural tab order | Use `tabindex="0"` or `-1` only |
