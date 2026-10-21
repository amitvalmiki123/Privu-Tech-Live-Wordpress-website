# Privu – Assessment Templates: gated downloads + CF7 lead form

**Page:** https://www.privu.tech/assessment-templates/
**Theme:** `siteorigin-corp-child` (Privu child theme)
**Form plugin:** Contact Form 7 (same as the site's other forms)
**Date:** 21 Oct 2026

---

## 1. Kya banaya (ek nazar me)

1. Page par koi bhi **“Download Template”** link click → lead form ka popup khulta hai.
2. Popup ka form ek **real Contact Form 7 form** hai (`Privu Template Download`), isliye:
   - entries/emails CF7 handle karta hai
   - mail settings CF7 admin se edit hoti hain
   - Flamingo active ho to submissions wahan save hoti hain
3. Fields: **Full name, Work email, Phone** (CF7 tags: `your-name`, `your-email`, `your-phone`).
4. **Work email only** — Gmail, Yahoo, Outlook, Hotmail, iCloud, Rediffmail, ProtonMail, Zoho aur disposable domains reject (CF7 validation + theme filter, server-side).
5. **Phone:** 10 digits, pehla digit **6/7/8/9** (`+91`, `91`, `0` normalize).
6. Lead details **eric@privu.tech** + **contact@privu.tech** ko jaati hain (CF7 **Mail** tab).
7. Visitor ko **attachment wali auto-reply** jaati hai (CF7 **Mail 2**) + success screen par **Download now** button.

**Design/layout:** page builder ka koi section/heading/image/button change nahi kiya. Existing links hi trigger hain.

---

## 2. Files: kya add hua / kya badla

Sab `wp-content/themes/siteorigin-corp-child/` ke andar:

| File | Status | Kaam |
|---|---|---|
| `inc/privu-template-downloads.php` | **New** | Catalogue, modal, styling trigger, download endpoint |
| `inc/privu-cf7-template-form.php` | **New** | CF7 form auto-create, validation, attachment, mail tags |
| `css/privu-template-downloads.css` | **New** | Modal + CF7 fields ki styling (`.ptdl-*` scoped) |
| `js/privu-template-downloads.js` | **New** | Link detect, CF7 submit → success screen, template slug set |
| `assets/templates/` | **New folder** | 5 xlsx + `.htaccess` + `index.php` |
| `functions.php` | **6 lines added (end me)** | `inc/privu-template-downloads.php` include |

> CF7 ke andar form **automatic** ban jata hai — aapko manually fields banane ki zarurat nahi. Chahein to edit kar sakte hain (Section 6).

---

## 3. Kaise kaam karta hai (short technical)

- CSS/JS/modal sirf `assessment-templates` page par load hote hain.
- Modal me `[contact-form-7 id="…"]` shortcode render hota hai — asli CF7 form.
- JS sirf 3 kaam karta hai:
  1. click detect karke sahi **template slug** set karna (hidden field `privu-template`)
  2. current page URL hidden field me daalna
  3. CF7 ke `wpcf7submit` event par success screen + download button dikhana
- Validation CF7 + theme filters se hoti hai (JS bypass bhi kaam nahi karega).
- Attachment `wpcf7_before_send_mail` se **Mail 2** me add hoti hai.
- Download link **HMAC-signed** hai (`?privu_tdl=dpia&key=…`) aur raw `.xlsx` direct URL se blocked hai.
- Agar CF7 deactivate ho jaye, popup apna built-in fallback form + built-in email handler use karta hai — feature band nahi hota.

---

## 4. Install

> Pehle live child theme ka backup le lein.

### Option A — File Manager / FTP (recommended)

`wp-content/themes/siteorigin-corp-child/` me upload/replace:

```
inc/privu-template-downloads.php
inc/privu-cf7-template-form.php
css/privu-template-downloads.css
js/privu-template-downloads.js
assets/templates/  (5 xlsx + .htaccess + index.php)
functions.php      (ya file ke end me diya gaya 6-line block add karein)
```

`.htaccess` hidden file hai — cPanel File Manager → Settings → **Show Hidden Files** ON karein.

### Option B — Theme zip

`privu-child-theme-with-template-downloads.zip` → Appearance → Themes → Add New → Upload → **“Replace current with uploaded”**.

⚠️ Zip me wahi files hain jo aapne di hain. Live theme me baad me koi manual edit hua ho to Option A safest hai.

---

## 5. Cache clear (zaroori)

1. **WP-Optimize** → Cache → Purge all cache
2. Cloudflare/CDN → Purge everything
3. Browser incognito / hard refresh

---

## 6. Contact Form 7 setup

### Automatic (default)

Theme pehli baar admin me load hone par ek CF7 form bana deti hai:

- **Contact → Contact Forms → “Privu Template Download”**
- Uska ID option `privu_tdl_cf7_form_id` me save hota hai.
- Ye form edit kar sakte hain, par **field names change na karein**:

| Field | CF7 tag |
|---|---|
| Full name | `[text* your-name]` |
| Work email | `[email* your-email]` |
| Phone | `[tel* your-phone]` |
| Selected template | `[hidden privu-template]` |
| Page URL | `[hidden privu-page-url]` |

**Mail tab (admin email):**

```
To: eric@privu.tech, contact@privu.tech
Subject: New template download: [privu-template-name] - [your-name]
Reply-To: [your-name] <[your-email]>

Name: [your-name]
Work email: [your-email]
Phone: [your-phone]
Template: [privu-template-name]
Page: [privu-page-url]
IP: [_remote_ip]
```

**Mail 2 tab (visitor auto-reply):**

```
To: [your-email]
Subject: Your [privu-template-name] from Privu
Body: Hi [your-name], ... [privu-download-link] ...
```

Attachment **automatic** add hota hai — Mail 2 ke attachment field me kuch likhne ki zarurat nahi.

### Manual form (agar auto-create na ho)

1. CF7 → Add New → title **Privu Template Download**
2. Fields ke liye `inc/privu-cf7-template-form.php` ke andar `$form_markup` copy karein
3. Mail/Mail 2 upar diye gaye content se bhar dein
4. Form ID `functions.php` me force karein:

```php
add_filter( 'privu_tdl_contact_form_id', function () {
    return 123; // CF7 form ID
} );
```

### CF7 validation rules (theme se)

- `wpcf7_validate_email*` → personal/disposable domain block
- `wpcf7_validate_tel*` → 10 digit + 6–9 rule
- `[privu-template-name]` aur `[privu-download-link]` custom mail tags register hain
- Attachment `wpcf7_before_send_mail` se add hota hai

---

## 7. Test checklist (5 minute)

| # | Step | Expected |
|---|---|---|
| 1 | Page kholein | Page pehle jaisa dikhe |
| 2 | “Download Template” click | Popup + us card ka template name |
| 3 | `test@gmail.com` | CF7 error: work email use karein |
| 4 | Phone `12345` | 10-digit / 6-9 error |
| 5 | Valid details submit | Success screen + Download now |
| 6 | Download now | `.xlsx` download |
| 7 | Visitor inbox | Attachment wali CF7 Mail 2 email |
| 8 | eric@ + contact@ | Lead details (CF7 Mail) |
| 9 | Contact → Contact Forms → form | Submissions/mail settings dikhein |
| 10 | Mobile | Bottom-sheet modal, scrollable |

Agar 7/8 fail ho → Section 9.

---

## 8. Kaunsa card → kaunsi file

`inc/privu-template-downloads.php` ke `privu_tdl_templates()` array me:

| Card | Slug | Attachment |
|---|---|---|
| DPDP Act Compliance Checklist | `dpdp-checklist` | `PrivuTech_Third_Party_Vendor_Assessment_Checklist.xlsx` ⚠️ |
| Data Mapping & Inventory Template | `data-mapping` | `PrivuTech_Data_Mapping_Inventory_Template.xlsx` |
| Consent Management Audit Template | `consent-audit` | `PrivuTech_Consent_Management_Audit_Template.xlsx` |
| Data Protection Impact Assessment (DPIA) | `dpia` | `PrivuTech_DPIA_Template.xlsx` |
| Third-Party Vendor Assessment Checklist | `vendor-checklist` | `PrivuTech_Third_Party_Vendor_Assessment_Checklist.xlsx` |
| Data Breach Response Plan Template | `data-breach` | `PrivuTech_Data_Breach_Response_Plan_Template.xlsx` |

⚠️ Aapke instruction ke mutabik card 1 par vendor checklist file lagi hai — **card 1 aur card 5 same file** bhejte hain. Alag checklist file ho to `assets/templates/` me daal kar `'file'` badal dein.

`PrivuTech_DPDP Readiness Assessment_Sector Agnostic.xlsx` bhi available hai (abhi use nahi ho rahi). Card 1 ke liye wahi chahiye to:

```php
'dpdp-checklist' => array(
    'title'    => 'DPDP Act Compliance Checklist',
    'file'     => 'PrivuTech_DPDP_Readiness_Assessment_Sector_Agnostic.xlsx',
    'keywords' => array( 'compliance checklist', 'dpdp act', 'checklist' ),
),
```

---

## 9. Emails

Emails **CF7 bhejta hai** (native fallback sirf tab jab CF7 off ho).

- Admin mail: `eric@privu.tech, contact@privu.tech` — Reply-To lead ka email
- Visitor mail: auto-reply + `.xlsx` attachment
- From: `Privu <contact@privu.tech>` (CF7 setting), actual delivery aapke **WP Mail SMTP** se
- SPF/DKIM set hona chahiye warna spam me ja sakta hai

**Email na aaye to:**

1. WP Mail SMTP → **Email Test**
2. Contact → Contact Forms → form → **Mail / Mail 2** tab check karein
3. Flamingo active ho to **Flamingo → Inbound Messages** me entry dekhein
4. `wp-content/debug.log` me `[Privu template download]` lines dekhein
5. Cache purge karke dobara test karein

---

## 10. Customize (filters)

> Note: CF7 mode me recipients / mail content **CF7 form** se aata hai. Neeche diye filters native fallback (jab CF7 off ho) aur blocked-domain list ke liye hain.

```php
// Extra admin recipient (native fallback only)
add_filter( 'privu_tdl_admin_recipients', function ( $emails ) {
    $emails[] = 'someone@privu.tech';
    return $emails;
} );

// Extra blocked email domain
add_filter( 'privu_tdl_blocked_email_domains', function ( $domains ) {
    $domains[] = 'example.com';
    return $domains;
} );

// Use a manually created CF7 form
add_filter( 'privu_tdl_contact_form_id', function () {
    return 123;
} );

// Modal on another page
add_filter( 'privu_tdl_is_target_page', function ( $is_target ) {
    return $is_target || is_page( 'resources' );
} );
```

- Kisi link par specific template: `<a href="#" data-template="dpia">Download Template</a>`
- Button text different ho to `data-template="slug"` ya `data-ptdl-download` attribute add karein.

---

## 11. Page XML / “link lagana”

**XML ki zarurat nahi hai.** Popup page ke existing “Download Template” links ko khud detect karta hai — page me kuch add/edit karne ki zarurat nahi.

Agar aap chahte hain ki kisi link par **specific template** khule (heading se detect na ho), to us link me bas `data-template="dpia"` add kar dein — ye ek chhota page edit hai. Warna kuch nahi karna; XML bhejne ki zarurat nahi.

---

## 12. Zaroori notes

1. **Page copy vs files:** page par PDF/Word likha hai, par saari files `.xlsx` hain — abhi Excel hi attach hota hai.
2. **Card 1 mapping** — Section 8 ka warning confirm kar lein.
3. **CF7 fields rename na karein** — warna validation/attachment/mail tags kaam nahi karenge.
4. **Direct file access:** `.htaccess` me `.xlsx` blocked hai (Apache/LiteSpeed). Nginx par ye rule ignore hota hai.
5. **Cache:** install ke baad purge karna zaroori hai.
6. **Optimisation:** script par `data-no-optimize`, `data-no-defer`, `data-no-minify`, `data-cfasync="false"` lage hain. Fir bhi modal pehle click par na khule to `privu-template-downloads.js` ko exclude list me daalein.
7. **Security plugin** `admin-ajax.php` block karta ho to allow karein (native fallback ke liye).
8. **Contact Form 7 5.4+** chahiye (`[hidden]` field support ke liye). Site par CF7 current version hai.

---

## 13. Rollback

1. `functions.php` se aakhir ka 6-line include block hata dein
2. New files delete karein (optional)
3. Cache clear karein

Page builder/design untouched hai, isliye rollback safe hai.

---

## 14. Bina WordPress ke preview

- `preview/index.html` — self-contained demo (CF7-style form, fake submit, no emails)
- `preview/template-download-demo.html` — same demo, external CSS/JS ke saath

Isse live page par koi asar nahi padta.
