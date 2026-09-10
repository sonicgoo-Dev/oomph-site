# Swapping ACF Pro for Secure Custom Fields

Do this on **staging** first (`staging2.oomphtravel.com`). Production only
after staging has been proven and `main` carries `oomph-travel-core` 1.2.0.

Part 1 is written for Eric and needs nothing but a browser. Part 2 is the
command-line equivalent for whoever has SSH.

---

## Why we are doing this

The site currently uses **Advanced Custom Fields PRO**, a paid plugin, to draw
the labelled boxes on the edit screens. **Secure Custom Fields** is the free
version maintained by WordPress.org and it now does everything we need.

Every one of the fifteen kinds of box the site uses exists in Secure Custom
Fields 6.9.5, including the two that were the reason for paying: the repeating
rows used for itinerary days and FAQs, and the image gallery on a tour.
Checked against the plugin's source code, not its marketing page.

Nothing you have typed into the site is lost. Both plugins save their content
in exactly the same place in the database, and both read the same settings
files we keep in the repository. Switching back is one click.

---

## The one thing to be careful about

The two plugins do the same job and **cannot both be switched on at once**. If
both are on at the same time the site will break. So the order matters:

> Install the new one. Switch the old one **off**. Then switch the new one
> **on**.

For the few seconds in between, pages on the site fall back to their default
wording. On staging nobody is looking, which is exactly why we do it there.

---

## Part 1 — On staging, in your browser

1. Go to **https://staging2.oomphtravel.com/wp-admin/** and log in with your
   usual WordPress username and password. Staging is a copy of the live site,
   so the login is the same.
2. **Check the web address bar starts with `staging2`.** If it says only
   `oomphtravel.com`, stop. That is the live site and this is the wrong place.
3. In the black menu down the left, click **Plugins**.
4. At the top of the page, click the **Add New Plugin** button.
5. In the **Search plugins…** box at the top right, type `Secure Custom Fields`
   and wait a moment for the results.
6. Find the box titled **Secure Custom Fields**. The author underneath should
   read **WordPress.org**. Click its **Install Now** button.
7. Wait until that button changes to say **Activate**. **Do not click it.**
   This is the step where the order matters.
8. In the left menu, click **Plugins** again to go back to the full list.
9. Find **Advanced Custom Fields PRO** in the list. Under its name, click
   **Deactivate**. The page will reload.
10. Now find **Secure Custom Fields** in the same list. Under its name, click
    **Activate**.

That is the swap done.

## Part 1b — Check it worked

1. In the left menu you should now see **Destinations**, **Operators**,
   **Tours** and **Inquiries**.
2. Click **Destinations**, then **Add New**. Below the title box you should see
   a panel of labelled boxes: Headline, Intro, Page variant, Regions, Sample
   itinerary, Stays, Best months, FAQ, Related operators, CruiseOomph region.
   Do not save anything. This is only a look.
3. Click **Tours**, then **Add New**. Scroll to **Gallery** and to
   **Day-by-day itinerary** and click **Add day** once. Both of these are the
   features we were paying for. If they work, the swap is good. Do not save.
4. Open **https://staging2.oomphtravel.com/** in a new tab. The homepage should
   look normal, with its real wording rather than blank spaces.

If all four look right, tell Claude and it will write up the result.

---

## If something looks wrong

Go to **Plugins**, click **Deactivate** under **Secure Custom Fields**, then
click **Activate** under **Advanced Custom Fields PRO**. That puts everything
back exactly as it was. Then tell Claude what you saw on screen.

**Do not delete Advanced Custom Fields PRO** and do not cancel its licence.
Leave it switched off but installed until the live site has been swapped too
and has run cleanly for a week.

**Do not use Secure Custom Fields' own screens for adding post types or
taxonomies.** Ours are already defined in the plugin code. Creating duplicates
in the admin would clash with them.

---

## Part 2 — The command-line equivalent

For whoever has SSH and WP-CLI on the box.

```bash
wp option get home
```

```bash
wp db export ~/pre-scf-$(date +%F-%H%M).sql
```

```bash
wp plugin install secure-custom-fields
```

```bash
wp plugin deactivate advanced-custom-fields-pro && wp plugin activate secure-custom-fields
```

```bash
wp eval 'foreach ( array( "group_oomph_destination", "group_oomph_operator", "group_oomph_tour", "group_oomph_inquiry", "group_oomph_page_hero", "group_oomph_service_page" ) as $k ) { $g = acf_get_field_group( $k ); printf( "%-30s %s\n", $k, $g ? "loaded" : "MISSING" ); }'
```

```bash
wp cache flush && wp sg purge
```

Rollback:

```bash
wp plugin deactivate secure-custom-fields && wp plugin activate advanced-custom-fields-pro
```

### Field types checked against SCF 6.9.5 source

| Type | Uses | Present |
|---|---|---|
| `repeater` | 8, across Destination, Tour, Service Page | yes |
| `gallery` | 1, on Tour | yes |
| `relationship`, `post_object`, `user` | 5 | yes |
| `text`, `textarea`, `wysiwyg`, `select`, `checkbox`, `number`, `url`, `image`, `true_false`, `message` | rest | yes |

The only ACF function this codebase calls is `get_field`, 17 times, all behind
a `function_exists` guard in `kadence-oomph-child/inc/helpers.php`. No options
pages, no ACF Blocks, no `acf_form`, which are the three things that would
have required staying on Pro.
