# Privu – Assessment Templates: download popup, CF7 form aur client-editable templates

**Page:** https://www.privu.tech/assessment-templates/
**Theme:** `siteorigin-corp-child`
**Form plugin:** Contact Form 7
**Templates editor:** ACF PRO options page (fallback: theme ka apna admin screen)

---

## 1. Confusion clear: Bakery me kya, ACF me kya? (sabse important)

**Ek line me:** Bakery = page ka **design/cards**. ACF = **kaunsi file kis card se judi hai**. Form = sabke liye **ek hi**, auto bana hua.

| Kaam | Kahan hota hai | Abhi karna hai? |
|---|---|---|
| 6 cards ka design, heading, text, “Download Template” link | **WP Bakery page editor** | ❌ **kuch nahi** — already bane hue hain, unhe chhedna nahi hai |
| Kis card par kaunsi file jaaye (name, keywords, file upload) | **Admin → Template Downloads (ACF)** | ⚠️ optional — 6 rows already bhari hain, bundled files se chalu hai |
| Lead form ke fields, emails, attachment | **Contact → Contact Forms → “Privu Template Download”** | ❌ auto bana hua hai |
| Naya card add karna | Bakery (card copy) **+** ACF (ek row) | sirf jab naya template add karna ho |

### Q1. “Kya ACF me wo 6 templates cards dobara banane honge?”

**Nahi.** ACF me cards nahi bante — wahan sirf **mapping rows** hoti hain.

- Aapke 6 cards **Bakery me hi rahenge**, jaisе hain waise.
- ACF page pehli baar kholne par **6 rows already bhare hue milenge** (title + keywords + slug).
- Har row me sirf ek cheez kaam ki hai: **Downloadable file**.
  - File upload nahi ki → theme ki **bundled file** use hoti hai (aapka current flow waisa hi chalta rahega).
  - File upload ki → wahi file visitor ko email attachment aur “Download now” se milegi.
- Yani aaj kuch bhi kiye bina feature chalu rahega. ACF sirf tab kholna hai jab file badalni ho ya naya template jodna ho.

### Q2. “Har template ke liye alag form shortcode hoga?”

**Nahi — sirf ek form hai** (`Privu Template Download`), aur **page me koi shortcode nahi lagta**.

Flow aisa hai:

```
6 cards (Bakery)  →  koi bhi "Download Template" click
        →  ek hi popup + ek hi CF7 form khulta hai
        →  form ke hidden field me chala jata hai: privu-template = dpia / consent-audit / …
        →  CF7 admin mail me [privu-template-name] = "Data Protection Impact Assessment (DPIA) Template"
        →  visitor ko Mail 2 me usi template ki file attach ho kar jati hai
```

- Ek form = ek jagah edit (fields, recipients, email body, design).
- 6 alag forms banate to 6 jagah maintain karna padta — isliye nahi banaye.
- Page edit me `[contact-form-7 …]` daalne ki **zarurat nahi**; theme popup ke andar khud print karta hai.
- Shortcode sirf tab use hota hai jab kisi **aur page** par alag se download button chahiye:
  `[privu_template_download slug="dpia" text="Download Template"]`

### Q3. “Form pehle se create kar diya hai kya?”

Code me form **auto-create** hota hai, par wo aapki site ki database me tab banta hai jab theme files upload hone ke baad aap **admin me ek baar page load** karte hain. Maine yahan se aapki live site ki DB me kuch nahi likha (access hi nahi hai).

Check karne ka tarika (install ke baad):

1. WP admin me koi bhi page refresh karein (Dashboard).
2. **Contact → Contact Forms** kholein.
3. Wahan **“Privu Template Download”** dikhna chahiye.
4. Na dikhe to: **Admin → Template Downloads** page kholein — uske instructions me form ID + “form edit karein” link dikh jayega.

### Q4. “Bakery me kya change karna hai?”

**Zero.** Purane 6 cards ke liye kuch nahi.

Sirf naya card add karte waqt:

1. Bakery me koi existing card **duplicate** karein.
2. Heading + description + image + bullets badal dein.
3. “Download Template” link ka **text waisa hi** rakhein (link ka URL khali `#` bhi chalega).
4. Update.
5. ACF → **Add template** → wahi heading + keywords + file → Save → cache purge.

> Link ka URL kuch bhi ho — popup JS click pakad leta hai. Chahein to us link me `data-template="slug"` bhi daal sakte hain (fixed mapping), par zarurat nahi.

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

## 6b. OTP verification — possible hai, par paisa lagta hai

Abhi **OTP nahi hai** — sirf format validation hai (10 digit + 6/7/8/9 + work email). OTP ke 3 raste hain:

### Option A — SMS OTP (India) 💰 paid

| Cheez | Cost |
|---|---|
| DLT Principal Entity registration (TRAI mandatory) | **₹5,900 one-time** (₹5,000 + GST) |
| Sender ID / Header registration | ~₹5,900 one-time (operator portal) |
| Per OTP SMS | **₹0.15 – ₹0.25 + GST** (MSG91 ~₹0.15, Gupshup ~₹0.17, Twilio ₹0.45) |
| Setup time | 1–3 hafte (DLT entity + template approval) |

- DLT ke bina SMS Jio/Airtel/Vi block kar dete hain — koi shortcut nahi.
- Providers: MSG91, Gupshup, VerifyNow, 2Factor, Twilio.
- Implementation: CF7 ke saath MSG91 ka OTP plugin ya custom AJAX OTP step (~1 din ka kaam).

### Option B — Email OTP (free) ✅ recommended agar OTP chahiye

- OTP work email par jaata hai (aapka WP Mail SMTP hi use hota hai) — **koi extra cost nahi**.
- Fayda: email ownership verify ho jaata hai, fake leads kam.
- Nuksan: visitor ko email kholna padta hai — drop-off badh sakta hai.
- Implementation: 6-digit code + 10 min expiry + 3 attempts, ek extra step popup me.

### Option C — Koi OTP nahi (abhi yahi hai) 🟢

- Sirf format + domain validation.
- Bina cost ek chhota improvement: email domain ka **MX record check** (domain actually email leta hai ya nahi) — isse fake domains pakde jaate hain, cost zero.

> Recommendation: pehle **Option C + MX check** se chala kar dekhein. Agar fake numbers/leads aa rahe hon tab **Option B (email OTP)** — free hai. SMS OTP sirf tab jab phone number genuinely verify karna business ke liye zaroori ho, kyunki usme ~₹12,000 one-time + per-SMS kharcha aata hai.

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

- `preview/index.html` — popup demo (Header Login CTA + Global Footer Login CTA + Template Download popup)
- `preview/template-download-demo.html` — same demo, external CSS/JS ke saath
- `preview/admin-template-downloads.html` — **Admin → Templates & Checklists screen ka mock**

Live page par iska koi asar nahi.

---

## 12. Work Task #2 — Product Login CTA (Header + Global Footer → Lead Form → `https://cms.privu.tech/`)

### WP Admin se 100% Editable Settings (**Admin → Product Login**)
Theme upload hote hi left WP Admin sidebar me **`Product Login`** (ACF Options Page) automatically ban jayega, jisme saare fields **pehle se pre-filled** milenge — koi PHP file chhedne ki zarurat nahi:

| ACF Field | Pre-filled Value | Kaam |
|---|---|---|
| **Button text** | `Login` | Header (`Get Started` ke paas) + Global Footer Quick Links + Bottom Bar + Mobile Menu me button text update karta hai |
| **Destination / Redirect URL** | `https://cms.privu.tech/` | Lead form submit hone ke baad (ya direct mode me click par) kis URL par bhejna hai |
| **Require Lead Form before redirect** | `Yes` (`ON`) | `Yes` = pehle CF7 popup form khulega phir redirect hoga; `No` = button seedha Destination URL kholega |
| **Popup CF7 form shortcode** | `[contact-form-7 id="..." title="Privu Product Login"]` | Jo bhi CF7 form shortcode yahan paste karenge wahi popup me render hoga + Work Email & 10-digit `[6–9]` validation automatically jud jayega |
| **Popup heading / subtitle** | `Log in to Privu Platform` | Popup ka title aur subtitle |

> **Bonus:** **Admin → Templates & Checklists** me bhi **`Popup CF7 form shortcode`** field pre-filled add kar diya gaya hai, taaki Task #1 aur Task #2 **dono** ke form shortcodes 100% ACF se editable rahein.

