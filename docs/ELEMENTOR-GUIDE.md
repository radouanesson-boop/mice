# Elementor integration guide

## Strategy

1. **Theme-builder locations.** The child registers `header` and `footer`
   locations. With Elementor Pro, the client can build either in Templates →
   Theme Builder and it automatically replaces the PHP version (the code in
   `inc/hooks/header.php` / `footer.php` checks the location first).
2. **MCB widget category.** Four purpose-built widgets under
   **Marrakech CB** render the same PHP template parts as the rest of the
   theme, so Elementor pages match non-Elementor pages exactly:
   - **MCB Hero** — kicker, title, copy, two CTAs, background image + overlay
     slider, optional Routiz search bar.
   - **MCB Section Heading** — the shared kicker/title/intro opener.
   - **MCB Stats** — repeater of animated counters.
   - **MCB Listings Grid** — live query on Routiz listings: card style
     (auto/venue/hotel/experience/event), taxonomy + terms, count, order,
     columns.
3. **Native widgets restyled.** `vendors/_elementor.scss` aligns Elementor's
   boxed width with the MCB container and re-skins headings, buttons and
   testimonials, so the client can freely mix native widgets.
4. **Dynamic Tags.** Every text/image/URL control is dynamic-enabled for
   ACF/site/post bindings (Pro).
5. **Editor parity.** MCB styles load inside the editor canvas
   (`elementor/editor/before_enqueue_styles`), so what the client sees while
   editing is what ships.

## Recommended homepage composition

| # | Widget(s) | Notes |
|---|---|---|
| 1 | MCB Hero | Enable search bar; upload the hero photograph (≥1920px) |
| 2 | MCB Stats | 4 items |
| 3 | MCB Section Heading + MCB Listings Grid | card style *venue*, 6 items, 3 columns |
| 4 | MCB Section Heading + MCB Listings Grid | card style *hotel*, 3 items |
| 5 | MCB Section Heading + MCB Listings Grid | card style *experience*, 4 items, 4 columns |
| 6 | Native Testimonial Carousel | picks up `.mcb-testimonial` cosmetics |
| 7 | MCB Section Heading + MCB Listings Grid | card style *event*, 3 items |
| 8 | Native Posts widget | news cards |
| 9 | Native Image Carousel | partner logos (grayscale hover handled by CSS) |
| 10 | Section with `.mcb-cta` CSS class | or reuse the PHP CTA via shortcode wrapper |

Section backgrounds: use the token values (`#f7f2ea` sand) so alternating
bands match the PHP homepage.

## Rules for editors

- Prefer MCB widgets over rebuilding equivalents from scratch — they stay in
  sync with the design system automatically.
- Don't hardcode hex colors in widget styles; pick the global colors (set
  Elementor's Global Colors to the token palette in Site Settings once).
- Images: choose the `MCB Card (4:3)` / `MCB Hero` sizes in image controls
  for correct cropping and responsive srcset.
