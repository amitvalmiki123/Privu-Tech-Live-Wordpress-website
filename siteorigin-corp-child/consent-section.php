<?php
/**
 * Consent widget — self-contained template.
 *
 * Fill in the four values below with the REAL (non-redacted) IDs from Privu.
 * Everything else in this file works out of the box once these are set —
 * no separate .js file to include, no build step.
 */
$privu_config = [
    'tenantId'        => '65359585-f5f6-48a5-9271-b1cb8d6c48d2',    // X-Tenant-ID
    'businessId'      => '65359585-f5f6-48a5-9271-b1cb8d6c48d2',  // business-id
    'templateId'      => '083142a1-05c9-4c0a-b718-126d1e6d5e4d',  // templateId
    'templateVersion' => 1,                    // templateVersion
    'secureCode'      => '',                   // leave blank unless Privu confirms /consent-handle/create needs X-Secure-Code
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>KaveriCloud — Modern Cloud & Data Solutions</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <style>
    /* Reset & Base Styles */
    *, *::before, *::after { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body { font-family: system-ui, -apple-system, sans-serif; color: #0f172a; background: #f8fafc; line-height: 1.5; }
    a { color: #2563eb; text-decoration: none; }
    .container { max-width: 1100px; margin: 0 auto; padding: 0 16px; }
    .jio-banner .jio-action-btn {
  color: var(--teal) !important;
  border: 1px solid var(--teal) !important;
  font-family: "Montserrat";
  font-weight: 400 !important;
}

.jio-banner .jio-button-wrap .jio-action-btn:last-of-type {
  color: #fff !important;
  background: var(--gradiantblue) !important;
}
.jio-right-wrap .jio-lang-label {
  color: #000 !important;
  font-size: 1rem !important;
  font-family: "Montserrat";}

    /* Layout Components */
    header { position: sticky; top: 0; z-index: 50; background: rgba(248,250,252,0.85); backdrop-filter: blur(6px); border-bottom: 1px solid #e2e8f0; }
    .nav { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; }
    .brand { display: flex; align-items: center; gap: 10px; }
    .brand svg { width: 36px; height: 36px; }
    .btn { display: inline-block; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 10px; cursor: pointer; background: white; }
    .btn.primary { background: #2563eb; color: white; border-color: #2563eb; }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Hero & Sections */
    .hero { background: radial-gradient(1200px 600px at 20% -10%, #e0f2fe, transparent 60%); padding: 64px 0; }
    .card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.05); }
    .muted { color: #64748b; }
    .contact { padding: 40px 0 80px; }
    input, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 10px; margin-bottom: 12px; font: inherit; }

    /* Mandatory Consent Checkbox on the contact form */
    .form-consent { display: flex; align-items: center; gap: 10px; padding: 12px; background: #f1f5f9; border-radius: 10px; margin-bottom: 12px; }
    .form-consent input { width: auto; margin: 0; }

    /*
      Note: the old hand-built black consent popup (manage-fab / backdrop /
      consent-banner / tab-nav / toggle CSS) has been removed. The widget
      below builds and styles its own banner + preferences modal at runtime
      (blue/white theme, class prefix "jio-"), driven by the real Privu API
      instead of hardcoded strings in localStorage.
    */
  </style>
</head>
<body>

  <!--
    NOTE: this section still has style="display:none" from the original
    template. Remove that when you're ready to show the contact form —
    the consent checkbox inside it is wired to open the preferences modal.
  -->
  <section id="contact" class="contact container" style="display:none">
    <div class="card">
      <form id="contactForm">
        <input type="text" placeholder="Name" required />
        <input type="email" placeholder="Email" required />
        <textarea placeholder="Message"></textarea>

        <div class="form-consent">
          <input type="checkbox" id="contactConsent" required />
          <label for="contactConsent" id="formConsentLabel">Consent: I authorize processing of my personal data.</label>
        </div>

        <button type="submit" id="submitBtn" class="btn primary" disabled>Send Message</button>
      </form>
    </div>
  </section>

  <script type="module">
    // ---------------------------------------------------------------------
    // Config injected from PHP above (kept server-side so it's one place to
    // edit, but nothing here is secret — it's the same info the curl sends).
    // ---------------------------------------------------------------------
    const PRIVU_CONFIG = <?php echo json_encode($privu_config, JSON_UNESCAPED_SLASHES); ?>;

    // ---------------------------------------------------------------------
    // CookieIntegration (inlined — no separate file, no import needed)
    // ---------------------------------------------------------------------
    const BASE_URL = "https://cms.privu.tech/cookie";
    const TEMPLATE_VERSION = 1;

    /* Language options (condensed) */
    const LANG_OPTIONS = [
      { value: "ENGLISH", label: "English", apiValue: "en" },
      { value: "HINDI", label: "Hindi", apiValue: "hi" },
      { value: "BENGALI", label: "Bengali", apiValue: "bn" },
      { value: "TAMIL", label: "Tamil", apiValue: "ta" },
      { value: "TELUGU", label: "Telugu", apiValue: "te" },
      { value: "KANNADA", label: "Kannada", apiValue: "kn" },
      { value: "MALAYALAM", label: "Malayalam", apiValue: "ml" },
      { value: "GUJARATI", label: "Gujarati", apiValue: "gu" },
      { value: "MARATHI", label: "Marathi", apiValue: "mr" },
      { value: "PUNJABI", label: "Punjabi", apiValue: "pa" },
      { value: "ODIA", label: "Odia", apiValue: "or" },
      { value: "ASSAMESE", label: "Assamese", apiValue: "as" },
      { value: "URDU", label: "Urdu", apiValue: "ur" },
      { value: "NEPALI", label: "Nepali", apiValue: "ne" },
      { value: "SANSKRIT", label: "Sanskrit", apiValue: "sa" },
      { value: "SINDHI", label: "Sindhi", apiValue: "sd" },
      { value: "KASHMIRI", label: "Kashmiri", apiValue: "ks" },
      { value: "KONKANI", label: "Konkani", apiValue: "kok" },
      { value: "MAITHILI", label: "Maithili", apiValue: "mai" },
      { value: "BHOJPURI", label: "Bhojpuri", apiValue: "bho" },
      { value: "DOGRI", label: "Dogri", apiValue: "doi" },
      { value: "SANTALI", label: "Santali", apiValue: "sat" },
      { value: "BODO", label: "Bodo", apiValue: "brx" },
    ];

    function el(tag, cls = "", style = {}, html = "") {
      const e = document.createElement(tag);
      if (cls) e.className = cls;
      Object.assign(e.style, style);
      if (html) e.innerHTML = html;
      return e;
    }

    function generateUUIDv4() {
      if (typeof crypto !== "undefined" && crypto.getRandomValues) {
        const buf = new Uint8Array(16);
        crypto.getRandomValues(buf);
        buf[6] = (buf[6] & 0x0f) | 0x40;
        buf[8] = (buf[8] & 0x3f) | 0x80;
        const hex = Array.from(buf, (b) => b.toString(16).padStart(2, "0")).join("");
        return `${hex.slice(0,8)}-${hex.slice(8,12)}-${hex.slice(12,16)}-${hex.slice(16,20)}-${hex.slice(20)}`;
      }
      let d = Date.now();
      if (typeof performance !== "undefined" && performance.now) d += performance.now();
      return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
        const r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c === "x" ? r : (r & 0x3) | 0x8).toString(16);
      });
    }

    const VENDOR_PRETTY = {
      ".facebook.com": "Meta Platforms, Inc",
      "facebook.com": "Meta Platforms, Inc",
      ".google.com": "Google",
      ".googletagmanager.com": "Google Tag Manager",
      ".doubleclick.net": "Google Ads",
      ".linkedin.com": "LinkedIn",
      ".youtube.com": "YouTube",
      ".instagram.com": "Instagram (Meta Platforms, Inc)",
      ".twitter.com": "Twitter, Inc",
      ".amazon-adsystem.com": "Amazon Ads",
    };
    function prettifyVendor(domain) {
      if (!domain) return "Unknown";
      const lower = domain.toLowerCase();
      for (const k of Object.keys(VENDOR_PRETTY)) {
        if (lower.endsWith(k) || lower === k.replace(/^\./, "")) return VENDOR_PRETTY[k];
      }
      return domain.replace(/^\./, "");
    }
    function extractHost(cookie) {
      if (!cookie) return "Unknown";
      if (cookie.domain) return cookie.domain;
      if (cookie.url) {
        try { return new URL(cookie.url).hostname; } catch {}
      }
      return cookie.provider || cookie.subdomainName || "Unknown";
    }

    async function safeFetchJson(url, opts = {}, timeoutMs = 15000) {
      const controller = new AbortController();
      const timer = setTimeout(() => controller.abort(), timeoutMs);
      try {
        const res = await fetch(url, { ...opts, signal: controller.signal });
        clearTimeout(timer);
        const txt = await res.text().catch(() => "");
        const data = txt ? JSON.parse(txt) : {};
        if (!res.ok) {
          const msg = data?.message || `HTTP ${res.status}`;
          console.warn("safeFetchJson non-ok", url, msg);
          throw new Error(msg);
        }
        return data;
      } catch (err) {
        clearTimeout(timer);
        console.warn("safeFetchJson error", url, err && err.message ? err.message : err);
        return null;
      }
    }

    /* ---------- create a new consent handle ----------
       Mirrors the client's curl exactly:
         POST {BASE_URL}/consent-handle/create
         Headers: Content-Type, X-Tenant-ID, business-id
         Body: { templateId, templateVersion, url, customerIdentifiers: { type: "DEVICE_ID", value } }
    */
    async function createConsentHandle({ tenantId, businessId, templateId, templateVersion, url, deviceId, secure_code }) {
      const body = {
        templateId,
        templateVersion: templateVersion || TEMPLATE_VERSION,
        url,
        customerIdentifiers: {
          type: "DEVICE_ID",
          value: deviceId,
        },
      };

      const headers = {
        "Content-Type": "application/json",
        "X-Tenant-ID": tenantId,
        "business-id": businessId,
      };
      if (secure_code) headers["X-Secure-Code"] = secure_code;

      const res = await safeFetchJson(`${BASE_URL}/consent-handle/create`, {
        method: "POST",
        headers,
        body: JSON.stringify(body),
      });

      return res; // expected shape: { consentHandleId, ... }
    }

    function createLanguageIconSVG(size = 16) {
      const ns = "http://www.w3.org/2000/svg";
      const svg = document.createElementNS(ns, "svg");
      svg.setAttribute("width", size);
      svg.setAttribute("height", size);
      svg.setAttribute("viewBox", "0 0 24 24");
      svg.setAttribute("fill", "none");
      svg.innerHTML =
        '<path d="M12 2a10 10 0 100 20 10 10 0 000-20zM4 12h16M12 4c1.3 2.3 2 5 2 8s-.7 5.7-2 8M4 12c1.5-2.6 4-4 8-4s6.5 1.4 8 4" stroke="#141414" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>';
      return svg;
    }

    /* ---------- hardcoded translations (no external translate API) ----------
       Order in each array: title, description, managePrefs, rejectAll, acceptNecessary, acceptAll.
       `cats` maps purpose name -> [label, description]. Unknown purposes fall back to English. */
    const STATIC_TRANSLATIONS = {
      HINDI: {
        t: ["यह साइट आपके अनुभव को बेहतर बनाने के लिए कुकीज़ का उपयोग करती है",
            "हम अपनी साइट को चलाने के लिए आवश्यक कुकीज़ का उपयोग करते हैं। आपकी सहमति से, हम उपयोगकर्ता अनुभव को बेहतर बनाने और वेबसाइट ट्रैफ़िक का विश्लेषण करने के लिए गैर-आवश्यक कुकीज़ का भी उपयोग कर सकते हैं।",
            "प्राथमिकताएँ प्रबंधित करें", "सभी अस्वीकार करें", "आवश्यक कुकीज़ स्वीकार करें", "सभी स्वीकार करें"],
        cats: {
          Necessary: ["आवश्यक", "ये कुकीज़ साइट की बुनियादी सुविधाओं को चलाने के लिए आवश्यक हैं।"],
          Functional: ["कार्यात्मक", "ये कुकीज़ उन्नत कार्यक्षमता और वैयक्तिकरण सक्षम करती हैं।"],
          Analytics: ["विश्लेषण", "ये कुकीज़ हमें साइट के उपयोग का विश्लेषण करने और प्रदर्शन सुधारने में मदद करती हैं।"],
          Advertisement: ["विज्ञापन", "इन कुकीज़ का उपयोग आपकी रुचियों से संबंधित विज्ञापन दिखाने के लिए किया जाता है।"],
          Others: ["अन्य", "ये कुकीज़ अवर्गीकृत हैं और अन्य उद्देश्यों के लिए उपयोग की जा सकती हैं।"],
        },
      },
      BENGALI: {
        t: ["এই সাইটটি আপনার অভিজ্ঞতা উন্নত করতে কুকি ব্যবহার করে",
            "আমাদের সাইট চালানোর জন্য আমরা প্রয়োজনীয় কুকি ব্যবহার করি। আপনার সম্মতিতে, ব্যবহারকারীর অভিজ্ঞতা উন্নত করতে এবং ওয়েবসাইটের ট্রাফিক বিশ্লেষণ করতে আমরা অপ্রয়োজনীয় কুকিও ব্যবহার করতে পারি।",
            "পছন্দ পরিচালনা করুন", "সব প্রত্যাখ্যান করুন", "প্রয়োজনীয় কুকি গ্রহণ করুন", "সব গ্রহণ করুন"],
        cats: {
          Necessary: ["প্রয়োজনীয়", "সাইটের মৌলিক বৈশিষ্ট্য চালু রাখতে এই কুকিগুলি প্রয়োজনীয়।"],
          Functional: ["কার্যকরী", "এই কুকিগুলি উন্নত কার্যকারিতা ও ব্যক্তিগতকরণ সক্ষম করে।"],
          Analytics: ["বিশ্লেষণ", "এই কুকিগুলি সাইটের ব্যবহার বিশ্লেষণ ও কার্যক্ষমতা উন্নত করতে সাহায্য করে।"],
          Advertisement: ["বিজ্ঞাপন", "আপনার আগ্রহ অনুযায়ী বিজ্ঞাপন দেখাতে এই কুকিগুলি ব্যবহৃত হয়।"],
          Others: ["অন্যান্য", "এই কুকিগুলি অশ্রেণিবদ্ধ এবং অন্যান্য উদ্দেশ্যে ব্যবহৃত হতে পারে।"],
        },
      },
      TAMIL: {
        t: ["உங்கள் அனுபவத்தை மேம்படுத்த இந்தத் தளம் குக்கீகளைப் பயன்படுத்துகிறது",
            "எங்கள் தளம் செயல்பட அத்தியாவசிய குக்கீகளைப் பயன்படுத்துகிறோம். உங்கள் ஒப்புதலுடன், பயனர் அனுபவத்தை மேம்படுத்தவும் இணையதள போக்குவரத்தை பகுப்பாய்வு செய்யவும் அத்தியாவசியமற்ற குக்கீகளையும் பயன்படுத்தலாம்.",
            "விருப்பங்களை நிர்வகி", "அனைத்தையும் நிராகரி", "அத்தியாவசிய குக்கீகளை ஏற்கவும்", "அனைத்தையும் ஏற்கவும்"],
        cats: {
          Necessary: ["அத்தியாவசியம்", "தளத்தின் அடிப்படை அம்சங்கள் செயல்பட இந்த குக்கீகள் அவசியம்."],
          Functional: ["செயல்பாட்டு", "இந்த குக்கீகள் மேம்பட்ட செயல்பாடுகளையும் தனிப்பயனாக்கத்தையும் இயக்குகின்றன."],
          Analytics: ["பகுப்பாய்வு", "தளப் பயன்பாட்டை பகுப்பாய்வு செய்து செயல்திறனை மேம்படுத்த இந்த குக்கீகள் உதவுகின்றன."],
          Advertisement: ["விளம்பரம்", "உங்கள் ஆர்வங்களுக்கு ஏற்ற விளம்பரங்களைக் காட்ட இந்த குக்கீகள் பயன்படுகின்றன."],
          Others: ["மற்றவை", "இந்த குக்கீகள் வகைப்படுத்தப்படாதவை; பிற நோக்கங்களுக்குப் பயன்படுத்தப்படலாம்."],
        },
      },
      TELUGU: {
        t: ["మీ అనుభవాన్ని మెరుగుపరచడానికి ఈ సైట్ కుకీలను ఉపయోగిస్తుంది",
            "మా సైట్ పనిచేయడానికి అవసరమైన కుకీలను ఉపయోగిస్తాము. మీ సమ్మతితో, వినియోగదారు అనుభవాన్ని మెరుగుపరచడానికి మరియు వెబ్‌సైట్ ట్రాఫిక్‌ను విశ్లేషించడానికి అనవసరమైన కుకీలను కూడా ఉపయోగించవచ్చు.",
            "ప్రాధాన్యతలను నిర్వహించండి", "అన్నింటినీ తిరస్కరించండి", "అవసరమైన కుకీలను అంగీకరించండి", "అన్నింటినీ అంగీకరించండి"],
        cats: {
          Necessary: ["అవసరమైనవి", "సైట్ ప్రాథమిక లక్షణాలు పనిచేయడానికి ఈ కుకీలు అవసరం."],
          Functional: ["ఫంక్షనల్", "ఈ కుకీలు మెరుగైన కార్యాచరణను మరియు వ్యక్తిగతీకరణను అందిస్తాయి."],
          Analytics: ["విశ్లేషణ", "సైట్ వినియోగాన్ని విశ్లేషించి పనితీరును మెరుగుపరచడంలో ఈ కుకీలు సహాయపడతాయి."],
          Advertisement: ["ప్రకటనలు", "మీ ఆసక్తులకు తగిన ప్రకటనలను చూపడానికి ఈ కుకీలు ఉపయోగించబడతాయి."],
          Others: ["ఇతరాలు", "ఈ కుకీలు వర్గీకరించబడలేదు మరియు ఇతర ప్రయోజనాల కోసం ఉపయోగించబడవచ్చు."],
        },
      },
      KANNADA: {
        t: ["ನಿಮ್ಮ ಅನುಭವವನ್ನು ಉತ್ತಮಗೊಳಿಸಲು ಈ ಸೈಟ್ ಕುಕೀಗಳನ್ನು ಬಳಸುತ್ತದೆ",
            "ನಮ್ಮ ಸೈಟ್ ಕಾರ್ಯನಿರ್ವಹಿಸಲು ಅಗತ್ಯ ಕುಕೀಗಳನ್ನು ಬಳಸುತ್ತೇವೆ. ನಿಮ್ಮ ಒಪ್ಪಿಗೆಯೊಂದಿಗೆ, ಬಳಕೆದಾರರ ಅನುಭವವನ್ನು ಸುಧಾರಿಸಲು ಮತ್ತು ವೆಬ್‌ಸೈಟ್ ಟ್ರಾಫಿಕ್ ವಿಶ್ಲೇಷಿಸಲು ಅನಗತ್ಯ ಕುಕೀಗಳನ್ನೂ ಬಳಸಬಹುದು.",
            "ಆದ್ಯತೆಗಳನ್ನು ನಿರ್ವಹಿಸಿ", "ಎಲ್ಲವನ್ನೂ ತಿರಸ್ಕರಿಸಿ", "ಅಗತ್ಯ ಕುಕೀಗಳನ್ನು ಸ್ವೀಕರಿಸಿ", "ಎಲ್ಲವನ್ನೂ ಸ್ವೀಕರಿಸಿ"],
        cats: {
          Necessary: ["ಅಗತ್ಯ", "ಸೈಟ್‌ನ ಮೂಲ ವೈಶಿಷ್ಟ್ಯಗಳು ಕಾರ್ಯನಿರ್ವಹಿಸಲು ಈ ಕುಕೀಗಳು ಅಗತ್ಯ."],
          Functional: ["ಕಾರ್ಯಾತ್ಮಕ", "ಈ ಕುಕೀಗಳು ಉತ್ತಮ ಕಾರ್ಯಕ್ಷಮತೆ ಮತ್ತು ವೈಯಕ್ತೀಕರಣವನ್ನು ಸಕ್ರಿಯಗೊಳಿಸುತ್ತವೆ."],
          Analytics: ["ವಿಶ್ಲೇಷಣೆ", "ಸೈಟ್ ಬಳಕೆಯನ್ನು ವಿಶ್ಲೇಷಿಸಿ ಕಾರ್ಯಕ್ಷಮತೆ ಸುಧಾರಿಸಲು ಈ ಕುಕೀಗಳು ಸಹಾಯ ಮಾಡುತ್ತವೆ."],
          Advertisement: ["ಜಾಹೀರಾತು", "ನಿಮ್ಮ ಆಸಕ್ತಿಗೆ ಸಂಬಂಧಿಸಿದ ಜಾಹೀರಾತುಗಳನ್ನು ತೋರಿಸಲು ಈ ಕುಕೀಗಳನ್ನು ಬಳಸಲಾಗುತ್ತದೆ."],
          Others: ["ಇತರೆ", "ಈ ಕುಕೀಗಳು ವರ್ಗೀಕರಿಸಲ್ಪಟ್ಟಿಲ್ಲ ಮತ್ತು ಇತರ ಉದ್ದೇಶಗಳಿಗೆ ಬಳಸಬಹುದು."],
        },
      },
      MALAYALAM: {
        t: ["നിങ്ങളുടെ അനുഭവം മെച്ചപ്പെടുത്താൻ ഈ സൈറ്റ് കുക്കികൾ ഉപയോഗിക്കുന്നു",
            "ഞങ്ങളുടെ സൈറ്റ് പ്രവർത്തിക്കാൻ അത്യാവശ്യ കുക്കികൾ ഉപയോഗിക്കുന്നു. നിങ്ങളുടെ സമ്മതത്തോടെ, ഉപയോക്തൃ അനുഭവം മെച്ചപ്പെടുത്താനും വെബ്‌സൈറ്റ് ട്രാഫിക് വിശകലനം ചെയ്യാനും അനാവശ്യ കുക്കികളും ഉപയോഗിച്ചേക്കാം.",
            "മുൻഗണനകൾ നിയന്ത്രിക്കുക", "എല്ലാം നിരസിക്കുക", "അത്യാവശ്യ കുക്കികൾ സ്വീകരിക്കുക", "എല്ലാം സ്വീകരിക്കുക"],
        cats: {
          Necessary: ["അത്യാവശ്യം", "സൈറ്റിന്റെ അടിസ്ഥാന സവിശേഷതകൾ പ്രവർത്തിക്കാൻ ഈ കുക്കികൾ ആവശ്യമാണ്."],
          Functional: ["പ്രവർത്തനപരം", "ഈ കുക്കികൾ മെച്ചപ്പെട്ട പ്രവർത്തനക്ഷമതയും വ്യക്തിഗതമാക്കലും സാധ്യമാക്കുന്നു."],
          Analytics: ["അനലിറ്റിക്സ്", "സൈറ്റ് ഉപയോഗം വിശകലനം ചെയ്ത് പ്രകടനം മെച്ചപ്പെടുത്താൻ ഈ കുക്കികൾ സഹായിക്കുന്നു."],
          Advertisement: ["പരസ്യം", "നിങ്ങളുടെ താൽപ്പര്യങ്ങൾക്ക് അനുയോജ്യമായ പരസ്യങ്ങൾ കാണിക്കാൻ ഈ കുക്കികൾ ഉപയോഗിക്കുന്നു."],
          Others: ["മറ്റുള്ളവ", "ഈ കുക്കികൾ വർഗ്ഗീകരിക്കപ്പെടാത്തവയാണ്, മറ്റ് ആവശ്യങ്ങൾക്ക് ഉപയോഗിച്ചേക്കാം."],
        },
      },
      GUJARATI: {
        t: ["આ સાઇટ તમારા અનુભવને બહેતર બનાવવા કૂકીઝનો ઉપયોગ કરે છે",
            "અમારી સાઇટ ચલાવવા માટે અમે આવશ્યક કૂકીઝનો ઉપયોગ કરીએ છીએ. તમારી સંમતિથી, વપરાશકર્તા અનુભવ સુધારવા અને વેબસાઇટ ટ્રાફિકનું વિશ્લેષણ કરવા અમે બિન-આવશ્યક કૂકીઝનો પણ ઉપયોગ કરી શકીએ છીએ.",
            "પસંદગીઓ સંચાલિત કરો", "બધું નકારો", "આવશ્યક કૂકીઝ સ્વીકારો", "બધું સ્વીકારો"],
        cats: {
          Necessary: ["આવશ્યક", "સાઇટની મૂળભૂત સુવિધાઓ કાર્ય કરવા માટે આ કૂકીઝ જરૂરી છે."],
          Functional: ["કાર્યાત્મક", "આ કૂકીઝ ઉન્નત કાર્યક્ષમતા અને વ્યક્તિગતકરણ સક્ષમ કરે છે."],
          Analytics: ["વિશ્લેષણ", "આ કૂકીઝ સાઇટના ઉપયોગનું વિશ્લેષણ કરી પ્રદર્શન સુધારવામાં મદદ કરે છે."],
          Advertisement: ["જાહેરાત", "તમારી રુચિ મુજબની જાહેરાતો બતાવવા માટે આ કૂકીઝનો ઉપયોગ થાય છે."],
          Others: ["અન્ય", "આ કૂકીઝ અવર્ગીકૃત છે અને અન્ય હેતુઓ માટે વપરાઈ શકે છે."],
        },
      },
      MARATHI: {
        t: ["हे संकेतस्थळ तुमचा अनुभव सुधारण्यासाठी कुकीज वापरते",
            "आमचे संकेतस्थळ चालवण्यासाठी आम्ही आवश्यक कुकीज वापरतो. तुमच्या संमतीने, वापरकर्ता अनुभव सुधारण्यासाठी आणि वेबसाइट ट्रॅफिकचे विश्लेषण करण्यासाठी आम्ही अनावश्यक कुकीजही वापरू शकतो.",
            "प्राधान्ये व्यवस्थापित करा", "सर्व नाकारा", "आवश्यक कुकीज स्वीकारा", "सर्व स्वीकारा"],
        cats: {
          Necessary: ["आवश्यक", "संकेतस्थळाची मूलभूत वैशिष्ट्ये चालण्यासाठी या कुकीज आवश्यक आहेत."],
          Functional: ["कार्यात्मक", "या कुकीज सुधारित कार्यक्षमता आणि वैयक्तिकरण सक्षम करतात."],
          Analytics: ["विश्लेषण", "या कुकीज संकेतस्थळाच्या वापराचे विश्लेषण करून कामगिरी सुधारण्यास मदत करतात."],
          Advertisement: ["जाहिरात", "तुमच्या आवडीनुसार जाहिराती दाखवण्यासाठी या कुकीज वापरल्या जातात."],
          Others: ["इतर", "या कुकीज अवर्गीकृत आहेत आणि इतर हेतूंसाठी वापरल्या जाऊ शकतात."],
        },
      },
      PUNJABI: {
        t: ["ਇਹ ਸਾਈਟ ਤੁਹਾਡੇ ਅਨੁਭਵ ਨੂੰ ਬਿਹਤਰ ਬਣਾਉਣ ਲਈ ਕੁਕੀਜ਼ ਵਰਤਦੀ ਹੈ",
            "ਸਾਡੀ ਸਾਈਟ ਚਲਾਉਣ ਲਈ ਅਸੀਂ ਜ਼ਰੂਰੀ ਕੁਕੀਜ਼ ਵਰਤਦੇ ਹਾਂ। ਤੁਹਾਡੀ ਸਹਿਮਤੀ ਨਾਲ, ਉਪਭੋਗਤਾ ਅਨੁਭਵ ਸੁਧਾਰਨ ਅਤੇ ਵੈੱਬਸਾਈਟ ਟ੍ਰੈਫਿਕ ਦਾ ਵਿਸ਼ਲੇਸ਼ਣ ਕਰਨ ਲਈ ਅਸੀਂ ਗੈਰ-ਜ਼ਰੂਰੀ ਕੁਕੀਜ਼ ਵੀ ਵਰਤ ਸਕਦੇ ਹਾਂ।",
            "ਤਰਜੀਹਾਂ ਦਾ ਪ੍ਰਬੰਧਨ ਕਰੋ", "ਸਭ ਰੱਦ ਕਰੋ", "ਜ਼ਰੂਰੀ ਕੁਕੀਜ਼ ਸਵੀਕਾਰ ਕਰੋ", "ਸਭ ਸਵੀਕਾਰ ਕਰੋ"],
        cats: {
          Necessary: ["ਜ਼ਰੂਰੀ", "ਸਾਈਟ ਦੀਆਂ ਬੁਨਿਆਦੀ ਵਿਸ਼ੇਸ਼ਤਾਵਾਂ ਚਲਾਉਣ ਲਈ ਇਹ ਕੁਕੀਜ਼ ਜ਼ਰੂਰੀ ਹਨ।"],
          Functional: ["ਕਾਰਜਸ਼ੀਲ", "ਇਹ ਕੁਕੀਜ਼ ਉੱਨਤ ਕਾਰਜਸ਼ੀਲਤਾ ਅਤੇ ਵਿਅਕਤੀਗਤਕਰਨ ਨੂੰ ਸਮਰੱਥ ਕਰਦੀਆਂ ਹਨ।"],
          Analytics: ["ਵਿਸ਼ਲੇਸ਼ਣ", "ਇਹ ਕੁਕੀਜ਼ ਸਾਈਟ ਦੀ ਵਰਤੋਂ ਦਾ ਵਿਸ਼ਲੇਸ਼ਣ ਕਰਕੇ ਕਾਰਗੁਜ਼ਾਰੀ ਸੁਧਾਰਨ ਵਿੱਚ ਮਦਦ ਕਰਦੀਆਂ ਹਨ।"],
          Advertisement: ["ਇਸ਼ਤਿਹਾਰ", "ਤੁਹਾਡੀਆਂ ਰੁਚੀਆਂ ਅਨੁਸਾਰ ਇਸ਼ਤਿਹਾਰ ਦਿਖਾਉਣ ਲਈ ਇਹ ਕੁਕੀਜ਼ ਵਰਤੀਆਂ ਜਾਂਦੀਆਂ ਹਨ।"],
          Others: ["ਹੋਰ", "ਇਹ ਕੁਕੀਜ਼ ਅਵਰਗੀਕ੍ਰਿਤ ਹਨ ਅਤੇ ਹੋਰ ਉਦੇਸ਼ਾਂ ਲਈ ਵਰਤੀਆਂ ਜਾ ਸਕਦੀਆਂ ਹਨ।"],
        },
      },
    };

    /* Languages shown in the picker: English + everything we have hardcoded text for. */
    const AVAILABLE_LANGS = LANG_OPTIONS.filter((o) => o.value === "ENGLISH" || STATIC_TRANSLATIONS[o.value]);

    async function ensureLanguageLoaded(langKey, languageSpecificContentMap, preferences, englishBase) {
      if (languageSpecificContentMap[langKey]) return;
      if (langKey === "ENGLISH") { languageSpecificContentMap.ENGLISH = englishBase; return; }

      const tr = STATIC_TRANSLATIONS[langKey];
      if (!tr) { languageSpecificContentMap[langKey] = englishBase; return; }

      const [title, description, managePrefs, rejectAll, acceptNecessary, acceptAll] = tr.t;
      const newMap = { title, description, managePrefs, rejectAll, acceptNecessary, acceptAll, categoryDescriptions: {} };
      preferences.forEach((p, idx) => {
        const c = tr.cats[p.purpose];
        newMap[`purpose_${idx}`] = c ? c[0] : p.purpose;
        newMap[`purpose_desc_${idx}`] = c ? c[1] : (englishBase.categoryDescriptions?.[p.purpose] || getCategoryDesc(p.purpose));
      });
      languageSpecificContentMap[langKey] = newMap;
    }

    function getCategoryDesc(purpose) {
      const map = {
        Necessary: "These cookies are necessary to enable the basic features of this site to function.",
        Analytics: "These cookies help us analyze site usage and improve performance.",
        Advertisement: "These cookies are used to show ads relevant to your interests.",
        Functional: "These cookies enable enhanced functionality and personalization.",
        Others: "These cookies are unclassified and may be used for other purposes.",
      };
      return map[purpose] || "";
    }

    function showSavedToast(message = "Preferences saved") {
      const t = el("div", "", {
        position: "fixed", right: "28px", bottom: "28px", background: "#0A2885",
        color: "#fff", padding: "12px 16px", borderRadius: "10px", boxShadow: "0 8px 24px rgba(2,6,23,0.2)",
        zIndex: 300000, opacity: "0", transform: "translateY(10px)", transition: "all 260ms ease",
        fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, Arial",
      }, message);
      document.body.appendChild(t);
      requestAnimationFrame(()=> { t.style.opacity = "1"; t.style.transform = "translateY(0)"; });
      setTimeout(()=> { t.style.opacity = "0"; t.style.transform = "translateY(10px)"; setTimeout(()=> t.remove(), 320); }, 2800);
    }

    /* ---------- Exported loader ---------- */
    async function loadCookieCenter(opts = {}) {
      let openedFromFloatingIcon = false;
      const {
        tenantId, businessId, cookieTemplateId, templateId, templateVersion,
        consentHandleId, secure_code, deviceUUID, url,
      } = opts || {};

      const effectiveTemplateId = templateId || cookieTemplateId;
      let currentConsentVersionData = null;

      try {
        if (!tenantId || !businessId) {
          console.warn("loadCookieCenter: missing tenantId/businessId — will not call APIs that require them.");
          return null;
        }
        if (!consentHandleId && !effectiveTemplateId) {
          console.warn("loadCookieCenter: need either a consentHandleId or a templateId (to create one) — cannot proceed.");
          return null;
        }

        let deviceuuid = deviceUUID || localStorage.getItem("device_uuid");
        if (!deviceuuid) {
          deviceuuid = (crypto && crypto.randomUUID && crypto.randomUUID()) || `dev-${Date.now()}`;
          localStorage.setItem("device_uuid", deviceuuid);
        }

        let cookieId = consentHandleId || localStorage.getItem("jio_consent_handle_id") || null;

        if (!cookieId) {
          const created = await createConsentHandle({
            tenantId, businessId, templateId: effectiveTemplateId,
            templateVersion: templateVersion || TEMPLATE_VERSION,
            url: url || window.location.href, deviceId: deviceuuid, secure_code,
          });
          if (!created || !created.consentHandleId) {
            console.warn("loadCookieCenter: consent-handle/create failed or returned no consentHandleId.");
            return null;
          }
          cookieId = created.consentHandleId;
          localStorage.setItem("jio_consent_handle_id", cookieId);
        }

        const consentData = await safeFetchJson(`${BASE_URL}/consent-handle/get/${cookieId}`, {
          headers: {
            "Content-Type": "application/json",
            "X-Tenant-ID": tenantId,
            "business-id": businessId,
            "X-Secure-Code": secure_code
          },
        });

        if (!consentData) { console.warn("Consent handle fetch returned no data. Exiting."); return; }

        let languageSpecificContentMap = (consentData.multilingual && consentData.multilingual.languageSpecificContentMap) || {};
        const preferences = consentData.preferences || [
          { purpose: "Necessary", isMandatory: true, cookies: [] },
          { purpose: "Functional", isMandatory: false, cookies: [] },
          { purpose: "Analytics", isMandatory: false, cookies: [] },
        ];

        if (!languageSpecificContentMap.ENGLISH) {
          languageSpecificContentMap.ENGLISH = {
            title: consentData?.label || "This site uses cookies to make your experience better",
            description: (consentData?.multilingual && consentData.multilingual.defaultDescription) || consentData?.description || "We use essential cookies to make our site work. With your consent, we may also use non-essential cookies to improve user experience and analyse website traffic.",
            managePrefs: "Manage preferences",
            rejectAll: "Reject all",
            acceptNecessary: "Accept necessary cookies",
            acceptAll: "Accept all",
            categoryDescriptions: {},
          };
        }

        try {
          const raw = localStorage.getItem("jio_lang_map_v1");
          if (raw) {
            const parsed = JSON.parse(raw);
            if (parsed && typeof parsed === "object") {
              languageSpecificContentMap = { ...languageSpecificContentMap, ...parsed };
            }
          }
        } catch {}

        let availableLangKeys = Object.keys(languageSpecificContentMap);
        if (!availableLangKeys || availableLangKeys.length === 0) availableLangKeys = LANG_OPTIONS.map(o => o.value);

        let selectedLang = localStorage.getItem("selected_lang") || "ENGLISH";
        if (!availableLangKeys.includes(selectedLang)) selectedLang = availableLangKeys.includes("ENGLISH") ? "ENGLISH" : availableLangKeys[0];

        const banner = el("div", "jio-banner", {
          position: "fixed", bottom: "30px", left: "24px", right: "24px",
          background: "#F9FAFB", borderTop: "1px solid #E5E7EB",
          boxShadow: "0 -6px 30px rgba(2,6,23,0.06)", padding: "22px 28px",
          display: "flex", justifyContent: "space-between", alignItems: "center",
          zIndex: "99999", fontFamily: "system-ui", minHeight: "188px",
          boxSizing: "border-box", borderRadius: "12px",
        });

        const leftWrap = el("div", "jio-left-wrap", { display: "flex", alignItems: "flex-start", gap: "16px", flex: "1", minWidth: 0 });
        const textCol = el("div", "jio-text-col", { display: "flex", flexDirection: "column", gap: "8px", minWidth: 0, maxWidth: "1376px" });

        const titleEl = el("div", "jio-banner-title", { fontWeight: 800, fontSize: "20px", color: "#111827", margin: 0 }, languageSpecificContentMap[selectedLang]?.title || languageSpecificContentMap.ENGLISH.title);
        const descEl = el("div", "jio-banner-desc", { fontWeight: 500, fontSize: "14px", color: "#6B7280", marginTop: "6px", maxHeight: "max-content", overflow: "hidden" }, languageSpecificContentMap[selectedLang]?.description || languageSpecificContentMap.ENGLISH.description);

        textCol.append(titleEl, descEl);
        leftWrap.append(textCol);

        const rightWrap = el("div", "jio-right-wrap", { display: "flex", flexDirection: "column", alignItems: "center", gap: "50px", minWidth: "320px", marginLeft: "24px" });

        const langBtn = el("button", "jio-lang-btn", { display: "flex", alignItems: "center", gap: "8px", border: "1px solid #E5E7EB", borderRadius: "24px", padding: "6px 10px", cursor: "pointer", height: "30px", background: "#fff", minWidth: "140px", fontWeight: 600, fontSize: "14px" });
        langBtn.setAttribute("aria-haspopup", "true");
        langBtn.setAttribute("aria-label", "Select language");

        function createCookieIconSVG() {
          const ns = "http://www.w3.org/2000/svg";
          const svg = document.createElementNS(ns, "svg");
          svg.setAttribute("width", "24");
          svg.setAttribute("height", "24");
          svg.setAttribute("viewBox", "0 0 24 24");
          svg.setAttribute("fill", "none");
          svg.innerHTML = `
            <path d="M17.5 6C18.3284 6 19 5.32843 19 4.5C19 3.67157 18.3284 3 17.5 3C16.6716 3 16 3.67157 16 4.5C16 5.32843 16.6716 6 17.5 6Z" fill="#FFFFFF"/>
            <path d="M22 11.95C21.84 11.97 21.67 12 21.5 12C20.2 12 19.06 11.37 18.33 10.41C18.06 10.47 17.79 10.5 17.5 10.5C15.29 10.5 13.5 8.71 13.5 6.5C13.5 6.07 13.59 5.66 13.71 5.27C12.68 4.55 12 3.35 12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 11.98 22 11.97 22 11.95ZM7.5 16C6.67 16 6 15.33 6 14.5C6 13.67 6.67 13 7.5 13C8.33 13 9 13.67 9 14.5C9 15.33 8.33 16 7.5 16ZM8.5 10.5C7.4 10.5 6.5 9.6 6.5 8.5C6.5 7.4 7.4 6.5 8.5 6.5C9.6 6.5 10.5 7.4 10.5 8.5C10.5 9.6 9.6 10.5 8.5 10.5ZM17 17C16.17 17 15.5 16.33 15.5 15.5C15.5 14.67 16.17 14 17 14C17.83 14 18.5 14.67 18.5 15.5C18.5 16.33 17.83 17 17 17Z" fill="#FFFFFF"/>
          `;
          return svg;
        }

        const svgIcon = createLanguageIconSVG(16);
        svgIcon.style.flex = "0 0 18px";
        svgIcon.style.marginRight = "4px";
        const langLabel = el("span", "jio-lang-label", { fontWeight: 600, fontSize: "14px" }, selectedLang);
        const langCaret = el("span", "jio-lang-caret", { marginLeft: "auto", fontSize: "12px", color: "#6B7280" }, "▾");
        langBtn.append(svgIcon, langLabel, langCaret);

        const buttonWrap = el("div", "jio-button-wrap", { display: "flex", gap: "12px", alignItems: "center", justifyContent: "center", flexWrap: "wrap" });
        const makeBtn = (label, type = "primary") => {
          return el("button", "jio-action-btn", {
            padding: "8px 16px", borderRadius: "250px", fontWeight: 700, fontSize: "14px", cursor: "pointer",
            border: type === "primary" ? "none" : "1px solid #0F3CC9", background: type === "primary" ? "#0A2885" : "#fff", color: type === "primary" ? "#fff" : "#0F3CC9", height: "40px", minWidth: "140px"
          }, label);
        };

        const manageBtn = makeBtn(languageSpecificContentMap.ENGLISH.managePrefs || "Manage preferences", "secondary");
        manageBtn.setAttribute("aria-label", "Manage preferences");
        const rejectBtn = makeBtn(languageSpecificContentMap.ENGLISH.rejectAll || "Reject all", "secondary");
        const acceptNecBtn = makeBtn(languageSpecificContentMap.ENGLISH.acceptNecessary || "Accept necessary cookies", "secondary");
        const acceptAllBtn = makeBtn(languageSpecificContentMap.ENGLISH.acceptAll || "Accept all", "primary");

        buttonWrap.append(manageBtn, rejectBtn, acceptNecBtn, acceptAllBtn);
        rightWrap.append(langBtn, buttonWrap);
        banner.append(leftWrap, rightWrap);
        document.body.appendChild(banner);

        let floatingIcon = el("div", "jio-floating-cookie");
        floatingIcon.appendChild(createCookieIconSVG());
        document.body.appendChild(floatingIcon);
        floatingIcon.style.display = "none";

        floatingIcon.onclick = async () => {
          openedFromFloatingIcon = true;
          if (currentModal && currentModal.overlayEl && document.body.contains(currentModal.overlayEl)) {
            currentModal.overlayEl.remove();
            currentModal = null;
            openedFromFloatingIcon = false;
            return;
          }
          const cid = localStorage.getItem("jio_consent_id");
          const ver = localStorage.getItem("jio_consent_version");
          if (cid && ver) {
            const latest = await loadConsentVersion(cid, ver);
            if (latest) currentConsentVersionData = latest;
          }
          manageBtn.click();
        };

        function showFloatingIcon() { floatingIcon.style.display = "flex"; }

        acceptAllBtn.style.background = "#0A2885"; acceptAllBtn.style.color = "#fff";
        rejectBtn.style.background = "#fff"; rejectBtn.style.color = "#0F3CC9";
        acceptNecBtn.style.background = "#fff"; acceptNecBtn.style.color = "#0F3CC9";
        manageBtn.style.background = "#fff"; manageBtn.style.color = "#0F3CC9";

        (function injectStyles() {
          if (document.getElementById("jio-cookie-styles-v2")) return;
          const css = `
            .jio-banner{max-width:1600px;transition:box-shadow 200ms ease}
            .jio-left-wrap{padding-right:12px}
            .jio-banner-title{font-weight:800;font-size:20px;color:#111827}
            .jio-banner-desc{color:#6B7280;font-size:14px}
            .jio-lang-btn{background:#fff;border:1px solid #E5E7EB;padding:6px 10px;cursor:pointer}
            .jio-action-btn{min-width:120px;transition:transform 160ms ease}
            .jio-action-btn:hover{transform:translateY(-2px)}
            .jio-toggle{appearance:none;width:44px;height:24px;background:#D9D9D9;border-radius:12px;position:relative;cursor:pointer;border:none}
            .jio-toggle:checked{background:#0F3CC9}
            .jio-toggle::before{content:"";position:absolute;width:18px;height:18px;top:3px;left:3px;background:#fff;border-radius:50%;transition:transform .22s}
            .jio-toggle:checked::before{transform:translateX(20px)}
            .jio-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);z-index:100000;display:flex;align-items:center;justify-content:center}
            .jio-modal{width:584px;max-height:80vh;border-radius:24px;background:#fff;box-shadow:0 18px 50px rgba(2,6,23,0.18);display:flex;flex-direction:column;overflow:hidden}
            .jio-modal .jio-modal-header{padding:28px 28px 12px;border-bottom:1px solid #E6E9EF;display:flex;justify-content:space-between}
            .jio-modal .jio-modal-content{padding:20px 28px;overflow:auto;flex:1}
            .jio-modal .jio-modal-footer{padding:16px 28px 20px;border-top:1px solid #E6E9EF;text-align:right}
            .jio-floating-cookie {
              position: fixed; bottom: 26px; left: 28px; width: 54px; height: 54px;
              background: #0A2885; border-radius: 50%; display: none; align-items: center;
              justify-content: center; cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,0.18);
              z-index: 200000; transition: transform 160ms ease, box-shadow 200ms ease;
            }
            .jio-floating-cookie:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,0.22); }
            .jio-floating-cookie svg { width: 26px; height: 26px; fill: #fff; }
          `;
          const s = document.createElement("style");
          s.id = "jio-cookie-styles-v2";
          s.textContent = css;
          document.head.appendChild(s);
        })();

        async function checkConsentStatus() {
          const deviceId = deviceuuid;
          const currentUrl = window.location.href;
          const consentIdStored = localStorage.getItem("jio_consent_id") || "";
          const apiUrl = `${BASE_URL}/consent/check?deviceId=${encodeURIComponent(deviceId)}&url=${encodeURIComponent(currentUrl)}&consentId=${encodeURIComponent(consentIdStored)}`;
          return await safeFetchJson(apiUrl, {
            method: "GET",
            headers: { "X-Tenant-ID": tenantId, "business-id": businessId, "X-Secure-Code": secure_code }
          });
        }

        async function loadConsentVersion(consentId, version) {
          if (!consentId || version == null) return null;
          const url = `${BASE_URL}/consent/${encodeURIComponent(consentId)}/versions/${encodeURIComponent(version)}`;
          const res = await safeFetchJson(url, {
            method: "GET",
            headers: { "Content-Type": "application/json", "X-Tenant-ID": tenantId, "business-id": businessId, "X-Secure-Code": secure_code }
          });
          if (res) {
            if (res.preferencesStatus && !res.preferences) {
              res.preferences = Object.keys(res.preferencesStatus).map(key => ({ purpose: key, preferenceStatus: res.preferencesStatus[key] }));
            }
            if (Array.isArray(res.preferences)) {
              res.preferences = res.preferences.map(p => ({
                ...p,
                purpose: p.purpose || p.purposeName || p.purposeLabel,
                preferenceStatus: p.preferenceStatus || p.preferencesStatus || p.preference_status || p.preferenceStatusValue || "NOTACCEPTED"
              }));
            }
          }
          return res;
        }

        async function sendConsent(prefStatus) {
          const body = { consentHandleId: consentHandleId || cookieId, languagePreference: selectedLang, preferencesStatus: prefStatus };
          const res = await safeFetchJson(`${BASE_URL}/consent/create`, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-Tenant-ID": tenantId, "business-id": businessId, "X-Secure-Code": secure_code },
            body: JSON.stringify(body),
          });
          if (res?.consentId) localStorage.setItem("jio_consent_id", res.consentId);
          const version = res?.version ?? res?.newVersion;
          if (version != null) localStorage.setItem("jio_consent_version", String(version));
          if (banner && banner.parentNode) banner.remove();
          showFloatingIcon();
          if (res?.consentId && version != null) {
            const latest = await loadConsentVersion(res.consentId, version);
            if (latest) currentConsentVersionData = latest;
          }
          return res;
        }

        async function updateConsent(prefStatus) {
          const consentId = localStorage.getItem("jio_consent_id");
          if (!consentId) { console.warn("Consent ID missing — cannot update. Falling back to create."); return sendConsent(prefStatus); }
          const body = { consentHandleId: consentHandleId || cookieId, languagePreference: selectedLang, preferencesStatus: prefStatus };
          const res = await safeFetchJson(`${BASE_URL}/consent/${encodeURIComponent(consentId)}/update`, {
            method: "PUT",
            headers: { "Content-Type": "application/json", "X-Tenant-ID": tenantId, "business-id": businessId, "X-Secure-Code": secure_code },
            body: JSON.stringify(body),
          });
          if (res?.consentId) localStorage.setItem("jio_consent_id", res.consentId);
          if (res?.newVersion != null) localStorage.setItem("jio_consent_version", String(res.newVersion));
          else if (res?.version != null) localStorage.setItem("jio_consent_version", String(res.version));
          return res;
        }

        acceptAllBtn.onclick = async () => {
          const allAccepted = {};
          preferences.forEach((p) => (allAccepted[p.purpose] = "ACCEPTED"));
          const res = await sendConsent(allAccepted);
          if (res?.consentId) {
            const cid = res.consentId, ver = res.version ?? res.newVersion;
            if (cid && ver != null) { const latest = await loadConsentVersion(cid, ver); if (latest) currentConsentVersionData = latest; }
          }
          showSavedToast("Accepted all cookies");
        };

        rejectBtn.onclick = async () => {
          const allRejected = {};
          preferences.forEach((p) => (allRejected[p.purpose] = "NOTACCEPTED"));
          const res = await sendConsent(allRejected);
          if (res?.consentId) {
            const cid = res.consentId, ver = res.version ?? res.newVersion;
            if (cid && ver != null) { const latest = await loadConsentVersion(cid, ver); if (latest) currentConsentVersionData = latest; }
          }
          showSavedToast("Rejected non-essential cookies");
        };

        acceptNecBtn.onclick = async () => {
          const mandatoryOnly = {};
          preferences.forEach((p) => { mandatoryOnly[p.purpose] = p.isMandatory ? "ACCEPTED" : "NOTACCEPTED"; });
          const res = await sendConsent(mandatoryOnly);
          if (res?.consentId) {
            const cid = res.consentId, ver = res.version ?? res.newVersion;
            if (cid && ver != null) { const latest = await loadConsentVersion(cid, ver); if (latest) currentConsentVersionData = latest; }
          }
          showSavedToast("Accepted necessary cookies");
        };

        function createPreferencesModal() {
          const overlay = el("div", "jio-modal-overlay", {});
          const modal = el("div", "jio-modal", {});
          const headerWrap = el("div", "jio-modal-header", {});
          const headerLeft = el("div", "", { display: "flex", flexDirection: "column" });
          const h2 = el("h2", "", { margin: 0, fontWeight: 900, fontSize: "24px", color: "#141414" }, "Manage preferences");
          const p = el("p", "", { margin: 0, marginTop: "6px", color: "#6B7280" }, "Choose the cookies you allow. You can update your preferences anytime.");
          headerLeft.append(h2, p);

          const headerRight = el("div", "", { display: "flex", gap: "8px", alignItems: "center" });
          const modalLangBtn = langBtn.cloneNode(true);
          if (!modalLangBtn.querySelector("svg")) modalLangBtn.insertBefore(createLanguageIconSVG(16), modalLangBtn.firstChild);
          const closeBtn = el("button", "", { width: "44px", height: "44px", border: "none", background: "transparent", fontSize: "20px", cursor: "pointer" }, "✕");
          headerRight.append(modalLangBtn, closeBtn);
          headerWrap.append(headerLeft, headerRight);

          const scrollArea = el("div", "scroll-area", { flex: "1", overflowY: "auto", padding: "20px 28px", background: "#fff" });
          const footer = el("div", "", { padding: "16px 28px 28px", borderTop: "1px solid #E6E9EF", textAlign: "right", background: "#fff" });
          const saveBtn = el("button", "", { padding: "8px 16px", borderRadius: "250px", fontWeight: 700, fontSize: "14px", cursor: "pointer", border: "none", background: "#0A2885", color: "#fff", height: "40px" }, "Save preferences");

          footer.append(saveBtn);
          modal.append(headerWrap, scrollArea, footer);
          overlay.appendChild(modal);
          document.body.appendChild(overlay);
          closeBtn.onclick = () => overlay.remove();

          return { overlay, modal, header: { h2, p, modalLangBtn }, scrollArea, saveBtn, closeBtn, overlayEl: overlay };
        }

        function buildModalContent(scrollAreaEl, langKey, languageSpecificContentMapLocal, consentVersionData = null) {
          scrollAreaEl.innerHTML = "";
          const map = (languageSpecificContentMapLocal && languageSpecificContentMapLocal[langKey]) || languageSpecificContentMap.ENGLISH;

          let prefStatusFromVersion = null;
          if (consentVersionData) {
            prefStatusFromVersion = {};
            if (consentVersionData.preferencesStatus && !consentVersionData.preferences) {
              Object.keys(consentVersionData.preferencesStatus).forEach((purpose) => { prefStatusFromVersion[purpose] = consentVersionData.preferencesStatus[purpose]; });
            }
            if (Array.isArray(consentVersionData.preferences)) {
              consentVersionData.preferences.forEach((p) => {
                const purpose = p.purpose || p.purposeName || p.purposeLabel;
                const status = p.preferenceStatus || p.preferencesStatus || p.preference_status || p.preferenceStatusValue || "NOTACCEPTED";
                if (purpose) prefStatusFromVersion[purpose] = status;
              });
            }
          }

          preferences.forEach((p, idx) => {
            let initial = p.isMandatory ? true : false;
            if (prefStatusFromVersion) {
              const backendValue = prefStatusFromVersion[p.purpose];
              if (backendValue === "ACCEPTED") initial = true;
              if (backendValue === "NOTACCEPTED") initial = false;
            }

            const section = el("div", "jio-cat-section", { borderBottom: "1px solid #E6E9EF", marginBottom: "16px", paddingBottom: "16px" });
            const head = el("div", "", { display: "flex", justifyContent: "space-between", alignItems: "center" });
            const purposeLabel = (map && map[`purpose_${idx}`]) || map.purposeMap?.[p.purpose] || p.purpose;

            const title = el("h3", "", {}, "");
            title.appendChild(el("span", "", {}, purposeLabel));
            if (p.isMandatory) {
              title.appendChild(el("span", "mandatory-lock", {
                fontSize: "12px", color: "#6B7280", background: "#F3F4F6", padding: "2px 6px", borderRadius: "8px", marginLeft: "8px", fontWeight: "700",
              }, "🔒 Required"));
            }

            const toggleWrap = el("div", "", { display: "flex", alignItems: "center", gap: "8px" });
            const toggle = el("input");
            toggle.type = "checkbox";
            toggle.className = "jio-toggle";
            toggle.checked = initial;
            toggle.disabled = p.isMandatory;
            toggle.setAttribute("aria-label", `${purposeLabel} toggle`);
            toggleWrap.append(toggle);
            head.append(title, toggleWrap);
            section.append(head);

            const descText = (map && map[`purpose_desc_${idx}`]) || map.categoryDescriptions?.[p.purpose] || getCategoryDesc(p.purpose);
            section.append(el("p", "jio-cat-desc", {}, descText));

            const cookies = p.cookies || [];
            const vendors = {};
            cookies.forEach((c) => {
              const host = extractHost(c);
              const vname = prettifyVendor(host);
              if (!vendors[vname]) vendors[vname] = [];
              vendors[vname].push(c);
            });

            Object.entries(vendors).forEach(([vendor, clist]) => {
              const acc = el("div", "vendor-accordion", { marginTop: "10px", border: "1px solid #F1F4F8", borderRadius: "8px", overflow: "hidden" });
              const header = el("button", "jio-vendor-header", {
                width: "100%", textAlign: "left", background: "#fff", border: "none", padding: "12px", cursor: "pointer", fontWeight: "500", fontSize: "16px",
              }, vendor);
              header.setAttribute("aria-expanded", "false");
              header.appendChild(el("span", "jio-vendor-chevron", {}, "▾"));

              const body = el("div", "", { display: "none", background: "#fff", padding: "8px 12px" });
              header.onclick = () => {
                const shown = body.style.display === "block";
                body.style.display = shown ? "none" : "block";
                header.setAttribute("aria-expanded", shown ? "false" : "true");
              };

              const table = el("table", "jio-cookie-table", { width: "100%", borderCollapse: "collapse" });
              const thead = el("thead", "", {}, `<tr><th>Cookie name</th><th>Description</th></tr>`);
              const tbody = el("tbody");
              clist.forEach((ck) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `<td>${ck.name || "-"}</td><td>${ck.description || ck.description_gpt || "-"}</td>`;
                tbody.append(tr);
              });
              table.append(thead, tbody);
              body.append(table);
              acc.append(header, body);
              section.append(acc);
            });

            scrollAreaEl.append(section);
          });

          return {
            readStates: () => {
              const states = {};
              const sections = Array.from(scrollAreaEl.querySelectorAll(".jio-cat-section"));
              preferences.forEach((p, idx) => {
                const sec = sections[idx];
                if (sec) {
                  const input = sec.querySelector(".jio-toggle");
                  states[p.purpose] = input && input.checked ? "ACCEPTED" : "NOTACCEPTED";
                } else {
                  states[p.purpose] = p.isMandatory ? "ACCEPTED" : "NOTACCEPTED";
                }
              });
              return states;
            },
          };
        }

        let currentModal = null;
        manageBtn.onclick = async () => {
          currentModal = createPreferencesModal();
          currentModal.header.h2.textContent = "Manage preferences";
          const modalLangLabel = currentModal.header.modalLangBtn.querySelector("span");
          if (modalLangLabel) modalLangLabel.textContent = selectedLang;

          currentModal.header.modalLangBtn.onclick = () => {
            showLanguagePicker(currentModal.header.modalLangBtn, async (newLang) => {
              if (!languageSpecificContentMap[newLang]) {
                await ensureLanguageLoaded(newLang, languageSpecificContentMap, preferences, languageSpecificContentMap.ENGLISH, tenantId, businessId, secure_code);
              }
              selectedLang = newLang;
              applyLanguageToBanner(selectedLang, languageSpecificContentMap);
              const map2 = languageSpecificContentMap[selectedLang] || languageSpecificContentMap.ENGLISH;
              currentModal.header.p.textContent = map2.description || languageSpecificContentMap.ENGLISH.description;
              buildModalContent(currentModal.scrollArea, selectedLang, languageSpecificContentMap, currentConsentVersionData);
            });
          };

          const cid = localStorage.getItem("jio_consent_id");
          const ver = localStorage.getItem("jio_consent_version");
          currentConsentVersionData = null;
          if (cid && ver) {
            const loaded = await loadConsentVersion(cid, ver);
            if (loaded && loaded.preferences) currentConsentVersionData = loaded;
          }

          const builder = buildModalContent(currentModal.scrollArea, selectedLang, languageSpecificContentMap, currentConsentVersionData);

          currentModal.saveBtn.onclick = async () => {
            const prefStatus = builder.readStates();
            if (openedFromFloatingIcon) {
              const res = await updateConsent(prefStatus);
              if (res) {
                if (res.consentId) localStorage.setItem("jio_consent_id", res.consentId);
                const v = res.newVersion != null ? res.newVersion : res.version;
                if (v != null) localStorage.setItem("jio_consent_version", String(v));
                const cid2 = localStorage.getItem("jio_consent_id");
                const ver2 = localStorage.getItem("jio_consent_version");
                if (cid2 && ver2) { const latest = await loadConsentVersion(cid2, ver2); if (latest) currentConsentVersionData = latest; }
              }
              openedFromFloatingIcon = false;
            } else {
              const res = await sendConsent(prefStatus);
              if (res) {
                const cid2 = localStorage.getItem("jio_consent_id");
                const ver2 = localStorage.getItem("jio_consent_version");
                if (cid2 && ver2) { const latest = await loadConsentVersion(cid2, ver2); if (latest) currentConsentVersionData = latest; }
              }
            }
            if (currentModal && currentModal.overlayEl) currentModal.overlayEl.remove();
            showSavedToast("Your cookie preferences have been saved");
          };
        };

        function showLanguagePicker(positionElement, onSelect) {
          const existing = document.getElementById("jio-lang-picker");
          if (existing) existing.remove();

          const rect = positionElement.getBoundingClientRect();
          const picker = el("div", "", {
            position: "absolute", top: `${rect.bottom + window.scrollY + 8}px`, left: `${Math.max(12, rect.left + window.scrollX)}px`,
            background: "#fff", border: "1px solid #E5E7EB", borderRadius: "8px", boxShadow: "0 8px 20px rgba(0,0,0,0.12)",
            zIndex: "200000", maxHeight: "320px", overflow: "auto", width: "220px", padding: "8px 8px"
          });
          picker.id = "jio-lang-picker";
          document.body.appendChild(picker);

          AVAILABLE_LANGS.forEach((opt) => {
            const item = el("div", "", { padding: "8px 10px", cursor: "pointer", borderRadius: "6px", display: "flex", alignItems: "center", gap: "8px" }, opt.label);
            if (opt.value === selectedLang) item.style.background = "#F3F4F6";
            item.onclick = async (e) => {
              e.stopPropagation();
              if (onSelect) await onSelect(opt.value);
              picker.remove();
              document.removeEventListener("click", handleDocClick);
            };
            picker.appendChild(item);
          });

          const handleDocClick = (ev) => {
            if (!picker.contains(ev.target) && !positionElement.contains(ev.target)) {
              picker.remove();
              document.removeEventListener("click", handleDocClick);
            }
          };
          setTimeout(() => { document.addEventListener("click", handleDocClick); }, 50);
        }

        langBtn.onclick = () => {
          showLanguagePicker(langBtn, async (newLang) => {
            if (!languageSpecificContentMap[newLang]) {
              await ensureLanguageLoaded(newLang, languageSpecificContentMap, preferences, languageSpecificContentMap.ENGLISH, tenantId, businessId, secure_code);
            }
            selectedLang = newLang;
            applyLanguageToBanner(selectedLang, languageSpecificContentMap);
            if (currentModal) buildModalContent(currentModal.scrollArea, selectedLang, languageSpecificContentMap, currentConsentVersionData);
          });
        };

        if (!languageSpecificContentMap[selectedLang]) {
          await ensureLanguageLoaded(selectedLang, languageSpecificContentMap, preferences, languageSpecificContentMap.ENGLISH, tenantId, businessId, secure_code);
        }

        function applyLanguageToBanner(langKey, languageSpecificContentMapLocal) {
          const map = (languageSpecificContentMapLocal && languageSpecificContentMapLocal[langKey]) || languageSpecificContentMap.ENGLISH;
          titleEl.textContent = map.title || languageSpecificContentMap.ENGLISH.title;
          descEl.textContent = map.description || languageSpecificContentMap.ENGLISH.description;
          manageBtn.textContent = map.managePrefs || languageSpecificContentMap.ENGLISH.managePrefs || "Manage preferences";
          rejectBtn.textContent = map.rejectAll || languageSpecificContentMap.ENGLISH.rejectAll || "Reject all";
          acceptNecBtn.textContent = map.acceptNecessary || languageSpecificContentMap.ENGLISH.acceptNecessary || "Accept necessary cookies";
          acceptAllBtn.textContent = map.acceptAll || languageSpecificContentMap.ENGLISH.acceptAll || "Accept all";
          document.querySelectorAll(".jio-lang-label").forEach(l => l.textContent = langKey);
          localStorage.setItem("selected_lang", langKey);
        }

        applyLanguageToBanner(selectedLang, languageSpecificContentMap);

        const status = await checkConsentStatus();
        if (status && status.consentStatus === "ACTIVE") {
          if (status.consentHandleId && status.consentHandleId !== "No_Record") {
            localStorage.setItem("jio_consent_id", status.consentHandleId);
          }
          const cid = localStorage.getItem("jio_consent_id");
          const ver = localStorage.getItem("jio_consent_version");
          if (cid && ver) { const loaded = await loadConsentVersion(cid, ver); if (loaded) currentConsentVersionData = loaded; }
          if (banner && banner.parentNode) banner.remove();
          showFloatingIcon();
          return {
            consentHandleId: cookieId, deviceUUID: deviceuuid,
            close: () => { if (banner && banner.parentNode) banner.remove(); },
            openPreferences: () => manageBtn.click(),
          };
        }

        return {
          consentHandleId: cookieId, deviceUUID: deviceuuid,
          close: () => { if (banner && banner.parentNode) banner.remove(); },
          openPreferences: () => manageBtn.click(),
        };

      } catch (err) {
        console.error("cookie center init failed", err);
        return null;
      }
    }

    // ---------------------------------------------------------------------
    // Boot the widget on this page + wire the contact form's consent checkbox
    // ---------------------------------------------------------------------
    document.addEventListener("DOMContentLoaded", async () => {
      window.__privuCookieCenter = await loadCookieCenter({
        tenantId: PRIVU_CONFIG.tenantId,
        businessId: PRIVU_CONFIG.businessId,
        templateId: PRIVU_CONFIG.templateId,
        templateVersion: PRIVU_CONFIG.templateVersion,
        secure_code: PRIVU_CONFIG.secureCode || undefined,
      });

      const contactCheck = document.getElementById("contactConsent");
      const submitBtn = document.getElementById("submitBtn");
      if (contactCheck && submitBtn) {
        contactCheck.addEventListener("change", (e) => {
          submitBtn.disabled = !e.target.checked;
          if (e.target.checked && window.__privuCookieCenter) {
            window.__privuCookieCenter.openPreferences();
          }
        });
      }
    });
  </script>

</body>
</html>