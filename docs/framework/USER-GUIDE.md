# DMF — User guide (bureau team)

## Daily work

### Add a venue / hotel / restaurant / experience / supplier
1. wp-admin → **Destination** → pick the type → **Add**.
2. Title, description, excerpt (used on cards) and featured image.
3. Fill the field groups (Capacity, Location & contact, Media…). The GPS
   field powers the map and radius search.
4. Assign **Listing Categories** and **Areas**; publish.

### Add an event
Destination → Events → Add. Start/end dates drive the calendar ordering;
past events auto-hide from archives.

### Handle RFPs / inquiries
Destination → Inquiries — every submission is stored (private) and also
emailed to the address in Destination → Settings → Inquiries. Open an
inquiry to see contact, event profile and the listing it came from.

### Moderate reviews
Comments — reviews carry a 1–5 rating; approving/unapproving updates the
listing's average automatically.

## Site content (theme)

- **Homepage texts/images**: Appearance → Customize → Homepage content
  (hero title with the italic accent word, lead, hero image, final CTA,
  RFP URL, footer tagline).
- **Menus**: Appearance → Menus — Primary (add CSS class `mcb-mega` to a
  top-level item to get the full-width mega panel; 2nd level = column
  headings), Mobile, Footer columns, Legal bar.
- **Footer columns**: Appearance → Widgets.
- **Blocks**: in any page, add *DMF Listings Grid*, *DMF Search Bar*,
  *DMF Inquiry / RFP Form*, *DMF Upcoming Events* from the "Destination
  Framework" category.

## Administration

- **Create a listing type without code**: Destination → Listing Types.
- **Partner accounts**: give suppliers the *Destination Partner* role —
  they manage only their own listings, pending your review.
- **Settings**: Destination → Settings (map provider/key, default map
  center, inquiry recipient).
