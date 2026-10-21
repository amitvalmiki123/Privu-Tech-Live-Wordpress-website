# Privu – Assessment Templates: gated downloads + lead form

**Page:** https://www.privu.tech/assessment-templates/
**Theme:** `siteorigin-corp-child` (Privu child theme)
**Date:** 21 Oct 2026

---

## 1. Kya banaya (ek nazar me)

1. Visitor jab bhi page par **“Download Template”** link par click karta hai, ek lead form modal khulta hai — **Full name, Work email, Phone**.
2. **Work email only:** Gmail, Yahoo, Outlook, Hotmail, iCloud, Rediffmail, ProtonMail, Zoho aur disposable inboxes reject hote hain. `name@gmail.com` aur `name@sub.gmail.com` dono block hain.
3. **Phone validation:** sirf 10 digits aur pehla digit **6, 7, 8 ya 9**. `+91`, `91` aur shuru ke `0` ko automatically normalize karta hai.
4. Form submit hote hi lead details **eric@privu.tech** aur **contact@privu.tech** dono par email hoti hain (Reply-To = lead ka email, taaki seedha reply kar sakein).
5. Visitor ko usi waqt **template attachment wali email** jaati hai, aur success screen par **Download now** button bhi milta hai.

**Design/layout:** page builder ka koi section, heading, image, button ya theme CSS change nahi kiya. Existing “Download Template” text links hi trigger hain — page me kuch add/edit karne ki zarurat nahi.

---

## 2. Files: kya add hua / kya badla

Ye sab `wp-content/themes/siteorigin-corp-child/` ke andar hai:

| File | Status | Kaam |
|---|---|---|
| `inc/privu-template-downloads.php` | **New** | Poora logic: form handler, validation, emails, download endpoint, modal HTML |
| `css/privu-template-downloads.css` | **New** | Modal ki styling (sirf `.ptdl-*` classes par scoped) |
| `js/privu-template-downloads.js` | **New** | Click detect, validation, AJAX submit, success screen |
| `assets/templates/` | **New folder** | 5 template files + `.htaccess` + `index.php` |
| `functions.php` | **6 lines added (end me)** | `inc/privu-template-downloads.php` ko include karta hai |

`functions.php` me sirf ye block add hua hai (baaki file bilkul waisi hi hai):

```php
// ================================================================
// ASSESSMENT TEMPLATE DOWNLOADS
// ================================================================
$privu_tdl_include = get_stylesheet_directory() . '/inc/privu-template-downloads.php';
if ( file_exists( $privu_tdl_include ) ) {
    require_once $privu_tdl_include;
}
```

---

## 3. Kaise kaam karta hai (short technical)

- CSS/JS/modal sirf `assessment-templates` page par load hote hain (`is_page('assessment-templates')`).
- Page par koi nonce print nahi hota — JS page load par `admin-ajax.php` se **fresh nonce** leta hai. Isliye **WP-Optimize / page cache** se koi problem nahi hoti.
- Form submit `admin-ajax.php` par jaata hai (`privu_tdl_submit`). Server par bhi saari validation dobara hoti hai — JS bypass karke bhi galat email/phone nahi bhej sakte.
- Download link **HMAC-signed** hai (`?privu_tdl=dpia&key=...`). Email me bheja gaya link hamesha kaam karega, aur raw file direct URL se nahi khulegi.
- Rate limit: ek IP se 15 minute me max 12 submissions. Honeypot field bhi hai (bots ke liye).
- Emails `wp_mail()` se jaate hain, isliye aapka **WP Mail SMTP** setup hi use hota hai.

---

## 4. Install kaise karein

> Pehle current child theme ka backup le lein (File Manager se folder download kar lein).

### Option A — File Manager / FTP (recommended)

Theme folder: `wp-content/themes/siteorigin-corp-child/`

Upload / replace:

```
inc/privu-template-downloads.php
css/privu-template-downloads.css
js/privu-template-downloads.js
assets/templates/PrivuTech_Consent_Management_Audit_Template.xlsx
assets/templates/PrivuTech_DPIA_Template.xlsx
assets/templates/PrivuTech_Data_Breach_Response_Plan_Template.xlsx
assets/templates/PrivuTech_Data_Mapping_Inventory_Template.xlsx
assets/templates/PrivuTech_Third_Party_Vendor_Assessment_Checklist.xlsx
assets/templates/.htaccess
assets/templates/index.php
functions.php   (ya sirf file ke end me diya gaya 6-line block add karein)
```

**Note:** `.htaccess` hidden file hai — cPanel File Manager → Settings → **Show Hidden Files (dotfiles)** ON karein.

### Option B — Theme zip upload

1. WordPress Admin → **Appearance → Themes → Add New → Upload Theme**
2. `privu-child-theme-with-template-downloads.zip` upload karein
3. “Destination folder already exists” aaye to **“Replace current with uploaded”** par click karein

⚠️ Ye zip un files se bana hai jo aapne di hain. Agar live theme me baad me koi manual edit hua hai, to wo is zip se overwrite ho jayega — isliye **Option A (sirf changed files) safest** hai. Zip upload fail ho (2 MB limit) to bhi Option A use karein.

---

## 5. Install ke baad cache clear karein (zaroori)

1. **WP-Optimize** → Cache → **Purge all cache**
2. Cloudflare / hosting CDN use ho raha ho to **Purge everything**
3. Browser me **incognito / hard refresh** (`Ctrl + Shift + R`)

Warna purana cached page serve hoga aur naya modal nahi dikhega.

---

## 6. Test checklist (5 minute)

| # | Step | Expected |
|---|---|---|
| 1 | https://www.privu.tech/assessment-templates/ kholein | Page pehle jaisa hi dikhe |
| 2 | Kisi bhi card ke “Download Template” par click | Modal khule, upar us card ka template name dikhe |
| 3 | Email me `test@gmail.com` daalein | “Please use your work email…” error |
| 4 | Phone me `12345` daalein | 10-digit / 6-9 wala error |
| 5 | Name, corporate email (`you@company.com`), valid phone bharein → submit | Success screen + **Download now** button |
| 6 | **Download now** click | `.xlsx` file download ho |
| 7 | Visitor inbox check | Attachment wali email mile |
| 8 | eric@privu.tech + contact@privu.tech check | Lead details wali email mile |
| 9 | Email me `you@company.co.in`, `+91 98765 43210` try karein | Dono pass hone chahiye |
| 10 | Mobile par khol kar test | Modal bottom-sheet ki tarah khule, scroll ho |

Agar 6/7/8 fail ho to Section 8 dekhein.

---

## 7. Kaunsa card → kaunsi file

`inc/privu-template-downloads.php` ke top par `privu_tdl_templates()` array hai:

| Card | Slug | Attachment |
|---|---|---|
| DPDP Act Compliance Checklist | `dpdp-checklist` | `PrivuTech_Third_Party_Vendor_Assessment_Checklist.xlsx` ⚠️ |
| Data Mapping & Inventory Template | `data-mapping` | `PrivuTech_Data_Mapping_Inventory_Template.xlsx` |
| Consent Management Audit Template | `consent-audit` | `PrivuTech_Consent_Management_Audit_Template.xlsx` |
| Data Protection Impact Assessment (DPIA) | `dpia` | `PrivuTech_DPIA_Template.xlsx` |
| Third-Party Vendor Assessment Checklist | `vendor-checklist` | `PrivuTech_Third_Party_Vendor_Assessment_Checklist.xlsx` |
| Data Breach Response Plan Template | `data-breach` | `PrivuTech_Data_Breach_Response_Plan_Template.xlsx` |

⚠️ **Dhyan dein:** Aapke instruction ke mutabik card 1 par Third-Party Vendor Assessment Checklist file lagayi hai. Iska matlab **card 1 aur card 5 dono same file** bhejte hain. Agar card 1 ke liye alag “DPDP Act Compliance Checklist” file hai, to:

1. File ko `assets/templates/` me upload karein
2. `privu_tdl_templates()` me `'dpdp-checklist'` ke andar `'file'` badal dein — bas.

Aapke paas `PrivuTech_DPDP Readiness Assessment_Sector Agnostic.xlsx` bhi hai (ye abhi page par use nahi ho rahi). Agar card 1 ke liye wahi chahiye:

```php
'dpdp-checklist' => array(
    'title'    => 'DPDP Act Compliance Checklist',
    'file'     => 'PrivuTech_DPDP_Readiness_Assessment_Sector_Agnostic.xlsx',
    'keywords' => array( 'compliance checklist', 'dpdp act', 'checklist' ),
),
```
(Pehle us file ko `assets/templates/` me upload kar lein.)

---

## 8. Emails

- Sender name: **Privu** (From email aapke WP Mail SMTP ki setting hi rahegi — ideally `contact@privu.tech` with SPF/DKIM).
- Visitor email ka **Reply-To**: `contact@privu.tech`
- Admin email ka **Reply-To**: lead ka email (seedha reply = lead ko reply)
- Attachment: `.xlsx` (~28 KB), Spam me na jaye isliye SPF/DKIM check kar lein.

**Agar email na aaye:**

1. WP Mail SMTP → **Email Test** → `contact@privu.tech` par test bhejein
2. SMTP plugin ke **Email Log** me dekhein — visitor/admin email gayi ya fail hui
3. `wp-content/debug.log` check karein (agar `WP_DEBUG_LOG` on hai). Code fail hone par yahan log karta hai:
   `[Privu template download] Visitor email failed for lead: ...`
4. Site par page cache clear karein, phir dobara test karein

> Form fail hone par bhi visitor ko screen par download milta hai (email fail ho to ek warning dikhti hai) — lead kahin na kahin capture ho jaye, iske liye admin email har submit par jaati hai.

---

## 9. Customize karna ho to (filters)

Child theme ke `functions.php` me add karein:

```php
// Extra recipient
add_filter( 'privu_tdl_admin_recipients', function( $emails ) {
    $emails[] = 'someone@privu.tech';
    return $emails;
} );

// Extra blocked (personal) domain
add_filter( 'privu_tdl_blocked_email_domains', function( $domains ) {
    $domains[] = 'example.com';
    return $domains;
} );

// Modal kisi aur page par bhi load karein
add_filter( 'privu_tdl_is_target_page', function( $is_target ) {
    return $is_target || is_page( 'resources' );
} );
```

- **Kisi link par specific template force karna ho:** link me `data-template="dpia"` add kar dein.
  `<a href="#" data-template="dpia">Download Template</a>`
- **Template/file badalna ho:** `privu_tdl_templates()` array edit karein (Section 7).
- **Button text “Download Template” ke alawa kuch ho:** link me `data-template="slug"` ya `data-ptdl-download` attribute daal dein, tab bhi trigger ho jayega.

---

## 10. Zaroori notes / limitations

1. **Page copy vs files:** page par “PDF & Excel”, “Word”, “PDF & Word” likha hai, lekin aapke diye saare files `.xlsx` hain. Abhi sirf Excel file attach hoti hai. PDF/Word versions chahiye to files bhej dein — same tarah `assets/templates/` me daal kar `'file'` update kar denge.
2. **Card 1 file mapping** — Section 7 ka warning ek baar confirm kar lein.
3. **Direct file access:** `.htaccess` me `.xlsx` par direct access band hai; file sirf signed link se milegi. (Apache/LiteSpeed par kaam karta hai. Nginx par `.htaccess` ignore hota hai — us case me files technically direct URL se khul sakti hain.)
4. **Caching:** page cache clear karna mat bhoolen, warna purane HTML me modal nahi hoga.
5. **Security plugins:** koi plugin `admin-ajax.php` block karta ho to usko allow karein, warna form submit fail hoga.
6. **WP Mail SMTP** already configured hai, isliye koi extra mail setup nahi chahiye.
7. **“Delay JavaScript” / defer optimisation:** script par `data-no-optimize`, `data-no-defer`, `data-no-minify` aur `data-cfasync="false"` attributes lagaye gaye hain. Fir bhi agar modal pehle click par na khule, to WP-Optimize (ya jo bhi optimisation plugin hai) me `privu-template-downloads.js` ko exclude list me daal dein.

---

## 11. Rollback (agar kuch bhi issue ho)

1. `functions.php` se last ka 6-line include block hata dein — form/flow turant band, page pehle jaisa.
2. New files (`inc/privu-template-downloads.php`, CSS, JS, `assets/templates/`) delete kar dein.
3. Cache clear karein.

Page builder/theme design is change se bilkul untouched hai, isliye rollback safe hai.

---

## 12. Bina WordPress ke preview

`preview/index.html` (self-contained) ya `preview/template-download-demo.html` — isme same modal, same CSS/JS hai (fake AJAX ke saath). Browser me khol kar design aur validation dekh sakte hain. Isse live page par koi asar nahi padta.
