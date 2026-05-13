# Performance Checklist

Quick reference checklist for web application performance. Use alongside the `performance-optimization` skill.

## Laravel Modules notes (repo-specific)

- **Database first**: watch for N+1; use Eloquent eager loading (`with`) or explicit joins where appropriate.
- Prefer pagination for list screens; avoid unbounded queries.
- When performance work touches schema/indexes, update `docs/databases_docs/db_overview_etc_core_schema.md` and log the change.

## Table of Contents

- [Core Web Vitals Targets](#core-web-vitals-targets)
- [TTFB Diagnosis](#ttfb-diagnosis)
- [Frontend Checklist](#frontend-checklist)
- [Backend Checklist](#backend-checklist)
- [Measurement Commands](#measurement-commands)
- [Common Anti-Patterns](#common-anti-patterns)

## Core Web Vitals Targets

| Metric | Good | Needs Work | Poor |
|--------|------|------------|------|
| LCP (Largest Contentful Paint) | ≤ 2.5s | ≤ 4.0s | > 4.0s |
| INP (Interaction to Next Paint) | ≤ 200ms | ≤ 500ms | > 500ms |
| CLS (Cumulative Layout Shift) | ≤ 0.1 | ≤ 0.25 | > 0.25 |

## TTFB Diagnosis

When TTFB is slow (> 800ms), check each component in DevTools Network waterfall:

- [ ] **DNS resolution** slow → add `<link rel="dns-prefetch">` or `<link rel="preconnect">` for known origins
- [ ] **TCP/TLS handshake** slow → enable HTTP/2, consider edge deployment, verify keep-alive
- [ ] **Server processing** slow → profile backend, check slow queries, add caching

## Frontend Checklist

### Images
- [ ] Images use modern formats (WebP, AVIF)
- [ ] Images are responsively sized (`srcset` and `sizes`)
- [ ] Images and `<source>` elements have explicit `width` and `height` (prevents CLS in art direction)
- [ ] Below-the-fold images use `loading="lazy"` and `decoding="async"`
- [ ] Hero/LCP images use `fetchpriority="high"` and no lazy loading

### JavaScript
- [ ] Bundle size under 200KB gzipped (initial load)
- [ ] No blocking JavaScript in `<head>` (use `defer` where applicable)
- [ ] Keep page-specific scripts minimal (Blade page only loads required JS)
- [ ] Long tasks (> 50ms) broken up to keep the main thread available — main lever for INP
- [ ] `yieldToMain` pattern used inside long-running loops so input events can run between chunks
- [ ] Modern scheduling APIs used where available: `scheduler.yield()` (preferred), `scheduler.postTask()` with priorities, `isInputPending()` to yield only when needed
- [ ] `requestIdleCallback` for deferrable, non-urgent work (analytics flush, prefetch, warmup)
- [ ] Non-critical work deferred out of event handlers (e.g. analytics, logging) so the response to the interaction is not delayed
- [ ] Third-party scripts loaded with `async` / `defer`, audited for size, and fronted by a facade when heavy (chat widgets, embeds)

### CSS
- [ ] Critical CSS inlined or preloaded
- [ ] No render-blocking CSS for non-critical styles
- [ ] No CSS-in-JS runtime cost in production (use extraction)

### Fonts
- [ ] Limited to 2–3 font families, 2–3 weights each (every additional weight is another request)
- [ ] WOFF2 format only (smallest, universal support — skip WOFF/TTF/EOT)
- [ ] Self-hosted when possible (third-party font CDNs add DNS + TCP + TLS round-trips)
- [ ] LCP-critical fonts preloaded: `<link rel="preload" as="font" type="font/woff2" crossorigin>`
- [ ] `font-display: swap` (or `optional` for non-critical) to avoid FOIT blocking render
- [ ] Subsetted via `unicode-range` to ship only the glyphs each page needs
- [ ] Variable fonts considered when multiple weights/styles are required (one file replaces many)
- [ ] Fallback font metrics adjusted with `size-adjust`, `ascent-override`, `descent-override` to reduce CLS on font swap
- [ ] System font stack considered before any custom font

### Network
- [ ] Static assets cached with long `max-age` + content hashing
- [ ] API responses cached where appropriate (`Cache-Control`)
- [ ] HTTP/2 or HTTP/3 enabled
- [ ] Resources preconnected (`<link rel="preconnect">`) for known origins
- [ ] `fetchpriority` used on critical non-image resources (e.g., key `<link rel="preload">`, above-the-fold `<script>`) — not only on `<img>`
- [ ] No unnecessary redirects

### Rendering
- [ ] No layout thrashing (forced synchronous layouts)
- [ ] Animations use `transform` and `opacity` (GPU-accelerated)
- [ ] Blade list/table pages use server-side pagination and filters
- [ ] Avoid unnecessary full page payload (only required fields in view model)
- [ ] Off-screen sections use `content-visibility: auto` with `contain-intrinsic-size` to skip layout/paint of non-visible areas
- [ ] No `unload` event handlers and no `Cache-Control: no-store` on HTML responses — preserves back/forward cache (bfcache) eligibility

## Backend Checklist

### Database
- [ ] No N+1 query patterns (use eager loading / joins)
- [ ] Queries have appropriate indexes
- [ ] List endpoints paginated (never `SELECT * FROM table`)
- [ ] Avoid over-fetching columns (`select` only needed fields)
- [ ] Slow query logging enabled

### API
- [ ] Response times < 200ms (p95)
- [ ] No synchronous heavy computation in request handlers
- [ ] Bulk operations instead of loops of individual calls
- [ ] Response compression (gzip/brotli)
- [ ] Appropriate caching (in-memory, Redis, CDN)

### Infrastructure
- [ ] CDN for static assets
- [ ] Server located close to users (or edge deployment)
- [ ] Horizontal scaling configured (if needed)
- [ ] Health check endpoint for load balancer

## Measurement Commands

### Laravel/Blade workflow

```bash
# Route/profile checks
php artisan route:list

# DB performance checks
php artisan migrate:status

# Useful while developing query-heavy pages
# (enable query log / Telescope / Debugbar depending environment policy)
```

## Common Anti-Patterns

| Anti-Pattern | Impact | Fix |
|---|---|---|
| N+1 queries | Linear DB load growth | Use joins, includes, or batch loading |
| Unbounded queries | Memory exhaustion, timeouts | Always paginate, add LIMIT |
| Missing indexes | Slow reads as data grows | Add indexes for filtered/sorted columns |
| Layout thrashing | Jank, dropped frames | Batch DOM reads, then batch writes |
| Unoptimized images | Slow LCP, wasted bandwidth | Use WebP, responsive sizes, lazy load |
| Over-fetching Eloquent data | Slow responses, memory growth | Select required fields, eager load relations |
| Missing server-side pagination | Slow list pages | Use paginator and indexed filters |
| Blocking main thread | Poor INP, unresponsive UI | Defer non-critical JS and chunk expensive work |
| Memory leaks | Growing memory, eventual crash | Clean up listeners, intervals, refs |
