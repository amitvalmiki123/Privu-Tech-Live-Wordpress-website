# Privu – Assessment Templates: download popup, CF7 form aur client-editable templates

**Page:** https://www.privu.tech/assessment-templates/
**Theme:** `siteorigin-corp-child`
**Form plugin:** Contact Form 7
**Templates editor:** ACF PRO options page (fallback: theme ka apna admin screen)

---

## 1. Sabse pehle: aapke 2 sawaalon ke seedhe jawab

### Q1. “CF7 ka shortcode kaha rakhenge? Page edit me?”

**Kahin nahi.** Page edit me **kuch nahi** rakhna.

Popup ka form theme khud popup ke andar print karta hai:

```php
// inc/privu-template-downloads.php → privu_tdl_print_modal()
echo do_shortcode( '[contact-form-7 id="' . $cf7_form_id . '" title="Privu Template Download"]' );
```

Matlab:
- Assessment Templates page ke content me form ka shortcode **na daalein** — warna page par ek extra form dikhne lagega.
- Popup me form tabhi dikhta hai jab visitor “Download Template” click karta hai.
- Form **automatically** ban jata hai: *Contact → Contact Forms → “Privu Template Download”*. Usi form ke Mail/Mail 2 tab se emails edit hoti hain.
- Agar kabhi manually kisi aur page par form chahiye ho, tab shortcode use karein:
  `[contact-form-7 id="12" title="Privu Template Download"]` (ID aapke CF7 me jo ho).

### Q2. “Naya template card + uski file kaise jodun? Client ke liye easy ho.”

3 simple steps — **code bilkul nahi**:

1. **Page me card banayein** — Assessment Templates page WP Bakery se edit karein, koi bhi card duplicate karein, heading badal dein (jaise “DPDP Readiness Assessment”), download link ka text “Download Template” hi rakhein.
2. **Admin → Template Downloads** kholein → **Add template** → Template name (wahi heading) + keywords + file upload karein.
3. **Save** → page cache purge → test.

Bas. Naya card turant popup me kaam karega, alag se code ya page-template edit karne ki zarurat nahi.

---

## 2. Kya-kya bana hai

| File | Kaam |
|---|---|
| `inc/privu-template-downloads.php` | Template catalogue, popup markup, download endpoint, shortcode |
| `inc/privu-cf7-template-form.php` | CF7 form auto-create, work-email + phone validation, attachment, mail tags |
| `inc/privu-template-acf.php` | **Client ke liye “Template Downloads” ACF page** (repeater) |
| `inc/privu-template-admin.php` | Same screen ka fallback (sirf jab ACF na ho) |
| `css/privu-template-downloads.css` | Popup + CF7 fields ki styling |
| `js/privu-template-downloads.js` | Link detect, template pehchaan, success screen |
| `assets/templates/` | 5 bundled `.xlsx` + `.htaccess` |
| `functions.php` | 6-line include (file ke end me) |

Page builder / theme design me koi change nahi.

---

## 3. Install

1. Child theme ka backup lein.
2. `wp-content/themes/siteorigin-corp-child/` me ye files upload/replace karein:

```
inc/privu-template-downloads.php
inc/privu-cf7-template-form.php
inc/privu-template-acf.php
inc/privu-template-admin.php
css/privu-template-downloads.css
js/privu-template-downloads.js
assets/templates/*        (5 xlsx + .htaccess + index.php)
functions.php             (ya file ke end me 6-line block add karein)
```

Ya `privu-child-theme-with-template-downloads.zip` → Appearance → Themes → Upload → “Replace current with uploaded”.

3. **WP admin me ek baar refresh** karein — CF7 form aur ACF “Template Downloads” page ban jayenge.
4. **WP-Optimize → Purge all cache** + Cloudflare purge.

---

## 4. Templates editor (client-friendly)

**Admin → Template Downloads** (ACF options page, ACF PRO already site par hai).

Har row me 4 cheezein:

| Field | Kya likhein |
|---|---|
| **Template name** | Page ke card ki heading, jaise “DPDP Readiness Assessment” |
| **Card keywords** | Heading ke 2–3 words comma se (`readiness, assessment`). Khali chhodein to naam se detect hoga |
| **Slug (optional)** | Sirf shortcode/email link ke liye. Purane slug na badlein (`dpia`, `consent-audit`, …) |
| **Downloadable file** | PDF / Word / Excel upload (media library se) |

Details:

- **Rows reorder** karein to cards ki priority change hoti hai.
- **Delete** karne par wo template popup se hat jata hai.
- **Saari rows delete** karein to theme ke bundled 6 templates wapas active ho jate hain (page kabhi dead nahi hoga).
- Pehli baar page khulte hi 6 default rows already bhare hue milenge. Jin rows me file upload nahi hai, unke liye theme ki **bundled file** hi use hoti hai — isliye purana flow turant kaam karta rahega; jab aap apni file upload karenge, wahi use hogi.
- File download visitor ko **email attachment** aur **Download now** button dono se milti hai.
- ACF available na ho to wahi screen theme ke apne admin page se chalti hai (`inc/privu-template-admin.php`), working same.

### Naya card add — exact steps

1. Page → WP Bakery → card duplicate → heading badlein → link text “Download Template” rakhein → Update.
2. Admin → Template Downloads → **Add template** → naam + keywords + file → Save.
3. Cache purge → page par us card ka “Download Template” click → popup me naya template name dikhna chahiye.

> Keywords/title match hone se popup ko pata chalta hai kaunsa card click hua. Agar aap chahein to page ke link par `data-template="slug"` bhi laga sakte hain (100% fixed mapping), par zarurat nahi.

---

## 5. Kisi bhi page par download button (optional)

```
[privu_template_download slug="dpia" text="Download Template"]
```

- `slug` — Template Downloads page wala slug. Khali/unknown ho to pehla template use hota hai.
- `text` — button ka text.
- `class` — extra CSS class.

Shortcode jis page par ho, wahan popup + CSS/JS automatically load ho jate hain.

---

## 6. CF7 form

**Contact → Contact Forms → “Privu Template Download”**

| Field | CF7 tag |
|---|---|
| Full name | `[text* your-name]` |
| Work email | `[email* your-email]` |
| Phone | `[tel* your-phone]` |
| Selected template | `[hidden privu-template]` |
| Page URL | `[hidden privu-page-url]` |

- **Mail tab (admin):** `eric@privu.tech, contact@privu.tech`, Reply-To = lead
- **Mail 2 tab (visitor):** auto-reply; selected template ki file **automatically attach** hoti hai
- Validation CF7 me hi hai: Gmail/Yahoo/Outlook/etc. block, phone 10-digit (6–9)
- Field names **rename na karein**, warna validation/attachment/mail tags nahi chalenge
- CF7 deactivate ho jaye to popup apna built-in form + email handler use karta hai (feature band nahi hota)

Custom mail tags: `[privu-template-name]`, `[privu-download-link]`

---

## 7. Test checklist

| # | Step | Expected |
|---|---|---|
| 1 | Page kholein | Design pehle jaisa |
| 2 | “Download Template” click | Popup + us card ka sahi template name |
| 3 | `test@gmail.com` | Work email error |
| 4 | Phone `12345` | 10-digit error |
| 5 | Valid details → submit | Success + Download now |
| 6 | Download now | File download |
| 7 | Visitor inbox | Attachment wali email |
| 8 | eric@ + contact@ | Lead details email |
| 9 | Admin → Template Downloads | 6 rows dikhein |
| 10 | Naya row + file + page card | Naya template popup me kaam kare |
| 11 | Mobile | Bottom-sheet popup, scrollable |

---

## 8. Emails na aayein to

1. WP Mail SMTP → **Email Test**
2. Contact → Contact Forms → **Mail / Mail 2** tab
3. Flamingo → Inbound Messages
4. `wp-content/debug.log` me `[Privu template download]` lines
5. Cache purge karke dobara test

---

## 9. Technical notes

- Popup CSS/JS sirf us page par load hote hain (ya jahan shortcode ho).
- Page cache safe: nonce runtime par `admin-ajax.php` se aata hai (native fallback ke liye).
- Native fallback: form submit `admin-ajax.php` par, server-side validation, HMAC-signed download links, IP rate-limit + honeypot.
- Download `?privu_tdl=slug&key=…` se hota hai; raw files `.htaccess` se blocked hain (Apache/LiteSpeed). Nginx par ye rule ignore hota hai.
- Template priority: **ACF rows → theme admin rows → bundled defaults**.

### Filters

```php
// Manually banaya hua CF7 form use karein
add_filter( 'privu_tdl_contact_form_id', fn() => 123 );

// Extra blocked email domain
add_filter( 'privu_tdl_blocked_email_domains', function ( $domains ) {
    $domains[] = 'example.com';
    return $domains;
} );

// Popup kisi aur page par bhi
add_filter( 'privu_tdl_is_target_page', fn( $t ) => $t || is_page( 'resources' ) );
```

---

## 10. Rollback

1. `functions.php` se aakhir ka 6-line include block hata dein
2. Cache clear karein

Page builder/design untouched hai, rollback safe hai.

---

## 11. Preview (bina WordPress)

- `preview/index.html` — self-contained demo (CF7-style form, fake submit)
- `preview/template-download-demo.html` — same demo, external CSS/JS ke saath

Live page par iska koi asar nahi.
