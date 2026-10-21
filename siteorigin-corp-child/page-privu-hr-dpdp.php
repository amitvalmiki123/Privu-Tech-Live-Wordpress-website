<?php
/**
 * Template Name: Privu HR DPDP Page
 *
 * Yeh file apne WordPress theme ke root folder mein rakho.
 * WordPress Admin → Pages → Add New → Page Attributes → Template → "Privu HR DPDP Page"
 * Page slug set karo: privu-hr-dpdp-draft
 */

get_header();

// ACF Fields — sab editable hain WP Admin se

// ── HERO ──────────────────────────────────────────────
$hero_kicker        = get_field('hero_kicker')        ?: 'DPDP Act 2023 · For HR Leaders';
$hero_h1_line1      = get_field('hero_h1_line1')      ?: 'Every employee record';
$hero_h1_line2      = get_field('hero_h1_line2')      ?: 'you hold is now';
$hero_h1_em         = get_field('hero_h1_em')         ?: 'legally regulated.';
$hero_sub           = get_field('hero_sub')            ?: "India's DPDP Act puts HR at the center of compliance. Payroll, biometrics, health records, background checks — your organization is accountable. Not just your IT team.";
$hero_btn1_text     = get_field('hero_btn1_text')     ?: 'Start a Free Assessment';
$hero_btn1_url      = get_field('hero_btn1_url')      ?: '#start';
$hero_btn2_text     = get_field('hero_btn2_text')     ?: 'See What\'s in Scope →';
$hero_btn2_url      = get_field('hero_btn2_url')      ?: '#scope';

// ── STAT BAR ──────────────────────────────────────────
$stat1_num  = get_field('stat1_num')  ?: '₹250 Cr';
$stat1_lbl  = get_field('stat1_lbl')  ?: 'Max penalty — breach linked to non-compliance';
$stat2_num  = get_field('stat2_num')  ?: '₹200 Cr';
$stat2_lbl  = get_field('stat2_lbl')  ?: 'Biometric or health data without valid consent';
$stat3_num  = get_field('stat3_num')  ?: 'May 2027';
$stat3_lbl  = get_field('stat3_lbl')  ?: 'Full compliance deadline — all 23 rules';
$stat4_num  = get_field('stat4_num')  ?: 'Now';
$stat4_lbl  = get_field('stat4_lbl')  ?: 'Rules 1, 2 & 17–21 already in force';

// ── SCOPE SECTION ─────────────────────────────────────
$scope_label        = get_field('scope_label')        ?: "What's in Scope";
$scope_h2           = get_field('scope_h2')            ?: 'HR holds the most sensitive<br>data in any organization.';
$scope_tags         = get_field('scope_tags')          ?: [
    'Aadhaar & PAN','Payroll & Bank Details','Biometric Attendance',
    'Background Verification','Health & Insurance','Performance Reviews',
    'Onboarding Documents','Leave & Disciplinary Records',
];
$scope_note         = get_field('scope_note')          ?: '<strong>You are the Data Fiduciary.</strong> Accountability rests with your organization — not your HRMS vendor, not your payroll partner.';

// Risk rows — ACF Repeater field "scope_risk_rows"
$risk_rows_default = [
    ['title'=>'Payroll & Financial Data','tag'=>'High Risk','tag_class'=>'rtag-h','desc'=>'Bank accounts, PF details, salary slips — shared with vendors and auditors often without formal data agreements.','bar_class'=>''],
    ['title'=>'Biometric Attendance','tag'=>'High Risk','tag_class'=>'rtag-h','desc'=>'Fingerprints and facial recognition require explicit, withdrawable consent. An offer letter clause does not qualify.','bar_class'=>''],
    ['title'=>'Health & Insurance Records','tag'=>'High Risk','tag_class'=>'rtag-h','desc'=>'Medical certificates, insurance nominations — stored in HRMS and shared with brokers. All in scope.','bar_class'=>''],
    ['title'=>'Background Verification','tag'=>'High Risk','tag_class'=>'rtag-h','desc'=>'Your BGV vendor processes this on your behalf — you remain accountable for how they handle it.','bar_class'=>''],
    ['title'=>'Informal Channels','tag'=>'Medium Risk','tag_class'=>'rtag-m','desc'=>'CVs on WhatsApp, Excel trackers on laptops, candidate data in personal inboxes — first things an audit flags.','bar_class'=>'soft'],
];
$risk_rows = get_field('scope_risk_rows') ?: $risk_rows_default;

// ── OBLIGATIONS ───────────────────────────────────────
$ob_label   = get_field('ob_label')   ?: 'What the Law Requires of HR';
$ob_h2_line1= get_field('ob_h2_line1')?: 'Four things HR must';
$ob_h2_em   = get_field('ob_h2_em')   ?: 'build into operations.';
$obligations_default = [
    ['num'=>'01','title'=>'Consent before collecting','desc'=>'Every form and attendance system must state why data is collected. Collecting Aadhaar without a disclosed purpose is a violation from day one.'],
    ['num'=>'02','title'=>'Valid consent for biometrics','desc'=>"Blanket offer-letter clauses don't qualify. Biometric systems need standalone, withdrawable consent — and you need to prove it exists."],
    ['num'=>'03','title'=>'Delete when purpose ends','desc'=>'Once the reason for holding data is over, it must go. Documented retention schedules and deletion processes are mandatory — not optional.'],
    ['num'=>'04','title'=>'Vendor contracts & breach readiness','desc'=>'Every BGV firm and payroll platform needs a data processing clause. When a breach happens, you must notify the Board — the clock starts from discovery.'],
];
$obligations = get_field('obligations') ?: $obligations_default;

// Penalty bar
$pen1_amt   = get_field('pen1_amt')   ?: '₹200 Cr';
$pen1_for   = get_field('pen1_for')   ?: 'Biometric / health data without consent';
$pen2_amt   = get_field('pen2_amt')   ?: '₹50 Cr';
$pen2_for   = get_field('pen2_for')   ?: 'Failure to erase data on request';
$pen3_amt   = get_field('pen3_amt')   ?: '₹250 Cr';
$pen3_for   = get_field('pen3_for')   ?: 'Breach where non-compliance contributed';

// ── WHY NOW ───────────────────────────────────────────
$wn_label   = get_field('wn_label')   ?: 'Why Now';
$wn_h2_line1= get_field('wn_h2_line1')?: 'The deadlines are live.';
$wn_h2_em   = get_field('wn_h2_em')   ?: 'Some rules already are.';
$wn_lead    = get_field('wn_lead')    ?: 'Consent frameworks need redesigning. Vendor contracts need new clauses. Employee data needs mapping across every system. That takes months — not weeks.';
$timeline_default = [
    ['dot_class'=>'td1','dot_text'=>'✓','date'=>'13 Nov 2025','title'=>'Rules Notified','desc'=>'Rules 1, 2, 17–21 in force. Live law.'],
    ['dot_class'=>'td2','dot_text'=>'!','date'=>'13 Nov 2026','title'=>'Core Framework Due','desc'=>'Consent, governance & initial safeguards must be operational.'],
    ['dot_class'=>'td3','dot_text'=>'→','date'=>'13 May 2027','title'=>'Full Compliance','desc'=>'All 23 rules effective. No exceptions.'],
];
$timeline = get_field('timeline') ?: $timeline_default;

// ── CREDENTIALS ───────────────────────────────────────
$cred_label     = get_field('cred_label')     ?: 'Who You\'re Working With';
$cred_h2_line1  = get_field('cred_h2_line1')  ?: 'Legal expertise.';
$cred_h2_em     = get_field('cred_h2_em')     ?: 'Technical depth. One team.';
$cred_lead      = get_field('cred_lead')      ?: "Most compliance advisors are lawyers who don't understand your systems, or IT people who don't understand the law. Our team holds both.";
$cred_legal_lbl = get_field('cred_legal_lbl') ?: 'Legal';
$cred_legal_h3  = get_field('cred_legal_h3')  ?: "Certified privacy auditors from India's law fraternity.";
$cred_legal_p   = get_field('cred_legal_p')   ?: 'Each team member holds individual ISS certification and is an accredited data protection auditor recognized within India\'s legal professional bodies. The rigour of a legal audit, not a consultant\'s checklist.';
$cred_tech_lbl  = get_field('cred_tech_lbl')  ?: 'Technical';
$cred_tech_h3   = get_field('cred_tech_h3')   ?: "We find the data your IT team doesn't know exists.";
$cred_tech_p    = get_field('cred_tech_p')    ?: "On-premise PII discovery tool scans databases, HRMS exports, file shares, and endpoints — classifying Aadhaar, PAN, biometrics, and bank data without anything leaving your walls. Full assessment in 10–12 days.";
$cred_pills_default = [
    'ISS Certified — Individual Level','Accredited Auditors — Law Fraternity',
    'On-Premise PII Discovery','DPIA & Gap Assessment',
    '10–12 Day Turnaround','No Data Leaves Your Environment',
];
$cred_pills = get_field('cred_pills') ?: $cred_pills_default;

// ── CTA ───────────────────────────────────────────────
$cta_h2_line1   = get_field('cta_h2_line1')   ?: 'Know where you stand.';
$cta_h2_line2   = get_field('cta_h2_line2')   ?: 'Before the regulator asks.';
$cta_p          = get_field('cta_p')           ?: "A free 30-minute call. No commitment. We'll walk through how DPDP applies to your operations and what it takes to get compliant.";
$cta_btn_text   = get_field('cta_btn_text')   ?: 'Request Free Consultation →';
$cta_btn_email  = get_field('cta_btn_email')  ?: 'contact@privu.tech';
$cta_note       = get_field('cta_note')       ?: 'Confidential. Covered under NDA from the first call.';
$cta_email      = get_field('cta_email')      ?: 'contact@privu.tech';
$cta_phone_name = get_field('cta_phone_name') ?: 'Call Eric Andrade';
$cta_phone_num  = get_field('cta_phone_num')  ?: '+91 99-7173-5924';
$cta_phone_link = get_field('cta_phone_link') ?: '+919971735924';

?>

<div class="privu-dpdp-page">

  <!-- ══ HERO ══════════════════════════════════════════════ -->
  <div class="pdpdp-hero">
    <div class="pdpdp-hero-dots"></div>
    <div class="pdpdp-hero-body">
      <div class="pdpdp-kicker"><?php echo esc_html($hero_kicker); ?></div>
      <h1 class="pdpdp-h1">
        <?php echo esc_html($hero_h1_line1); ?><br>
        <?php echo esc_html($hero_h1_line2); ?><br>
        <em><?php echo esc_html($hero_h1_em); ?></em>
      </h1>
      <p class="pdpdp-hero-sub"><?php echo esc_html($hero_sub); ?></p>
      <div class="pdpdp-hero-btns">
        <a class="pdpdp-btn-teal" href="<?php echo esc_url($hero_btn1_url); ?>"><?php echo esc_html($hero_btn1_text); ?></a>
        <a class="pdpdp-btn-ghost" href="<?php echo esc_url($hero_btn2_url); ?>"><?php echo esc_html($hero_btn2_text); ?></a>
      </div>
    </div>
    <div class="pdpdp-stat-bar">
      <div class="pdpdp-sb">
        <div class="pdpdp-sb-num pdpdp-teal"><?php echo esc_html($stat1_num); ?></div>
        <div class="pdpdp-sb-lbl"><?php echo esc_html($stat1_lbl); ?></div>
      </div>
      <div class="pdpdp-sb">
        <div class="pdpdp-sb-num pdpdp-teal"><?php echo esc_html($stat2_num); ?></div>
        <div class="pdpdp-sb-lbl"><?php echo esc_html($stat2_lbl); ?></div>
      </div>
      <div class="pdpdp-sb">
        <div class="pdpdp-sb-num pdpdp-white"><?php echo esc_html($stat3_num); ?></div>
        <div class="pdpdp-sb-lbl"><?php echo esc_html($stat3_lbl); ?></div>
      </div>
      <div class="pdpdp-sb pdpdp-sb-last">
        <div class="pdpdp-sb-num pdpdp-white"><?php echo esc_html($stat4_num); ?></div>
        <div class="pdpdp-sb-lbl"><?php echo esc_html($stat4_lbl); ?></div>
      </div>
    </div>
  </div>

  <!-- ══ SCOPE ═════════════════════════════════════════════ -->
  <section class="pdpdp-sec pdpdp-sec-white" id="scope">
    <div class="pdpdp-mw">
      <div class="pdpdp-label pdpdp-rev"><?php echo esc_html($scope_label); ?></div>
      <h2 class="pdpdp-h2 pdpdp-rev"><?php echo wp_kses_post($scope_h2); ?></h2>

      <div class="pdpdp-scope-wrap">
        <div class="pdpdp-rev">
          <div class="pdpdp-tag-cloud">
            <?php
            $tags = is_array($scope_tags) ? $scope_tags : explode("\n", $scope_tags);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if ($tag) echo '<span class="pdpdp-dtag">' . esc_html($tag) . '</span>';
            }
            ?>
          </div>
          <div class="pdpdp-scope-note"><?php echo wp_kses_post($scope_note); ?></div>
        </div>

        <div class="pdpdp-risk-rows pdpdp-rev pdpdp-d1">
          <?php foreach ($risk_rows as $row):
            $bar_class = '';
            $tag_class = 'rtag-h';
            if (is_array($row)) {
                $bar_class = isset($row['bar_class']) ? $row['bar_class'] : '';
                $tag_class = isset($row['tag_class']) ? $row['tag_class'] : 'rtag-h';
                $rtitle    = isset($row['title']) ? $row['title'] : '';
                $rtag      = isset($row['tag'])   ? $row['tag']   : '';
                $rdesc     = isset($row['desc'])  ? $row['desc']  : '';
            } else {
                $rtitle = $row; $rtag = ''; $rdesc = '';
            }
          ?>
          <div class="pdpdp-rr">
            <div class="pdpdp-rr-bar <?php echo esc_attr($bar_class); ?>"></div>
            <div class="pdpdp-rr-body">
              <h4><?php echo esc_html($rtitle); ?>
                <?php if ($rtag): ?>
                  <span class="pdpdp-rtag <?php echo esc_attr($tag_class); ?>"><?php echo esc_html($rtag); ?></span>
                <?php endif; ?>
              </h4>
              <p><?php echo esc_html($rdesc); ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ══ OBLIGATIONS ═══════════════════════════════════════ -->
  <div class="pdpdp-sdark" id="obligations">
    <div class="pdpdp-mw">
      <div class="pdpdp-label pdpdp-label-wh pdpdp-rev"><?php echo esc_html($ob_label); ?></div>
      <h2 class="pdpdp-h2 pdpdp-lt pdpdp-rev">
        <?php echo esc_html($ob_h2_line1); ?><br><em><?php echo esc_html($ob_h2_em); ?></em>
      </h2>

      <div class="pdpdp-ob-grid">
        <?php foreach ($obligations as $i => $ob):
          $delay_class = ($i % 2 === 1) ? 'pdpdp-d1' : '';
          $onum   = is_array($ob) ? ($ob['num']   ?? sprintf('%02d', $i+1)) : sprintf('%02d', $i+1);
          $otitle = is_array($ob) ? ($ob['title'] ?? '') : $ob;
          $odesc  = is_array($ob) ? ($ob['desc']  ?? '') : '';
        ?>
        <div class="pdpdp-ob pdpdp-rev <?php echo $delay_class; ?>">
          <div class="pdpdp-ob-n"><?php echo esc_html($onum); ?></div>
          <h4><?php echo esc_html($otitle); ?></h4>
          <p><?php echo esc_html($odesc); ?></p>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="pdpdp-pen-bar pdpdp-rev">
        <div class="pdpdp-pb">
          <div class="pdpdp-pb-amt"><?php echo esc_html($pen1_amt); ?></div>
          <div class="pdpdp-pb-for"><?php echo esc_html($pen1_for); ?></div>
        </div>
        <div class="pdpdp-pb">
          <div class="pdpdp-pb-amt"><?php echo esc_html($pen2_amt); ?></div>
          <div class="pdpdp-pb-for"><?php echo esc_html($pen2_for); ?></div>
        </div>
        <div class="pdpdp-pb pdpdp-pb-last">
          <div class="pdpdp-pb-amt"><?php echo esc_html($pen3_amt); ?></div>
          <div class="pdpdp-pb-for"><?php echo esc_html($pen3_for); ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ WHY NOW ════════════════════════════════════════════ -->
  <section class="pdpdp-soff" id="why-now">
    <div class="pdpdp-mw">
      <div class="pdpdp-label pdpdp-rev"><?php echo esc_html($wn_label); ?></div>
      <h2 class="pdpdp-h2 pdpdp-rev">
        <?php echo esc_html($wn_h2_line1); ?><br><em><?php echo esc_html($wn_h2_em); ?></em>
      </h2>
      <p class="pdpdp-lead pdpdp-rev"><?php echo esc_html($wn_lead); ?></p>

      <div class="pdpdp-tl-wrap pdpdp-rev">
        <?php foreach ($timeline as $tl):
          $dc    = is_array($tl) ? ($tl['dot_class'] ?? 'td1') : 'td1';
          $dt    = is_array($tl) ? ($tl['dot_text']  ?? '✓')   : '✓';
          $tdate = is_array($tl) ? ($tl['date']  ?? '')         : '';
          $ttile = is_array($tl) ? ($tl['title'] ?? '')         : $tl;
          $tdesc = is_array($tl) ? ($tl['desc']  ?? '')         : '';
        ?>
        <div class="pdpdp-tl">
          <div class="pdpdp-tldot <?php echo esc_attr($dc); ?>"><?php echo esc_html($dt); ?></div>
          <div class="pdpdp-tl-date"><?php echo esc_html($tdate); ?></div>
          <div class="pdpdp-tl-title"><?php echo esc_html($ttile); ?></div>
          <div class="pdpdp-tl-desc"><?php echo esc_html($tdesc); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ══ CREDENTIALS ═══════════════════════════════════════ -->
  <section class="pdpdp-sec pdpdp-sec-white" id="credentials">
    <div class="pdpdp-mw">
      <div class="pdpdp-label pdpdp-rev"><?php echo esc_html($cred_label); ?></div>
      <h2 class="pdpdp-h2 pdpdp-rev">
        <?php echo esc_html($cred_h2_line1); ?><br><em><?php echo esc_html($cred_h2_em); ?></em>
      </h2>
      <p class="pdpdp-lead pdpdp-rev"><?php echo esc_html($cred_lead); ?></p>

      <div class="pdpdp-cred-block pdpdp-rev">
        <div class="pdpdp-cred-top">
          <div class="pdpdp-cred-half">
            <div class="pdpdp-clbl"><?php echo esc_html($cred_legal_lbl); ?></div>
            <h3><?php echo esc_html($cred_legal_h3); ?></h3>
            <p><?php echo esc_html($cred_legal_p); ?></p>
          </div>
          <div class="pdpdp-cred-half">
            <div class="pdpdp-clbl"><?php echo esc_html($cred_tech_lbl); ?></div>
            <h3><?php echo esc_html($cred_tech_h3); ?></h3>
            <p><?php echo esc_html($cred_tech_p); ?></p>
          </div>
        </div>
        <div class="pdpdp-cred-pills">
          <?php
          $pills = is_array($cred_pills) ? $cred_pills : explode("\n", $cred_pills);
          foreach ($pills as $pill) {
              $pill = trim($pill);
              if ($pill) echo '<span class="pdpdp-cpill">' . esc_html($pill) . '</span>';
          }
          ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ══ CTA ════════════════════════════════════════════════ -->
  <div class="pdpdp-cta-sec" id="start">
    <div class="pdpdp-cta-in">
      <h2 class="pdpdp-cta-h2">
        <?php echo esc_html($cta_h2_line1); ?><br>
        <?php echo esc_html($cta_h2_line2); ?>
      </h2>
      <p class="pdpdp-cta-p"><?php echo esc_html($cta_p); ?></p>

      <?php
      // CTA Form — use Contact Form 7 shortcode if set, else default HTML form
      $cf7_shortcode = get_field('cta_cf7_shortcode');
      if ($cf7_shortcode):
          echo do_shortcode(wp_kses_post($cf7_shortcode));
      else:
      ?>
      <form class="pdpdp-cform" method="post" action="">
        <?php wp_nonce_field('pdpdp_cta_form','pdpdp_nonce'); ?>
        <input class="pdpdp-cinput" type="text"  name="pdpdp_name"  placeholder="Name &amp; role" required>
        <input class="pdpdp-cinput" type="text"  name="pdpdp_org"   placeholder="Organization" required>
        <input class="pdpdp-cinput" type="email" name="pdpdp_email" placeholder="Work email" required>
        <button class="pdpdp-btn-ink" type="submit"><?php echo esc_html($cta_btn_text); ?></button>
      </form>
      <?php endif; ?>

      <p class="pdpdp-cnote"><?php echo esc_html($cta_note); ?></p>

      <div class="pdpdp-ccons">
        <div class="pdpdp-cc">
          <div class="pdpdp-cc-i">✉</div>
          <div>
            <strong>Email</strong>
            <a href="mailto:<?php echo esc_attr($cta_email); ?>"><?php echo esc_html($cta_email); ?></a>
          </div>
        </div>
        <div class="pdpdp-cc">
          <div class="pdpdp-cc-i">📞</div>
          <div>
            <strong><?php echo esc_html($cta_phone_name); ?></strong>
            <a href="tel:<?php echo esc_attr($cta_phone_link); ?>"><?php echo esc_html($cta_phone_num); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>

</div><!-- .privu-dpdp-page -->

<script>
(function(){
  var io = new IntersectionObserver(
    function(entries){ entries.forEach(function(x){ if(x.isIntersecting) x.target.classList.add('pdpdp-on'); }); },
    { threshold: 0.08 }
  );
  document.querySelectorAll('.pdpdp-rev').forEach(function(el){ io.observe(el); });
  document.querySelectorAll('a[href^="#"]').forEach(function(a){
    a.addEventListener('click', function(e){
      var t = document.querySelector(a.getAttribute('href'));
      if(t){ e.preventDefault(); t.scrollIntoView({ behavior:'smooth' }); }
    });
  });
})();
</script>

<?php get_footer(); ?>
