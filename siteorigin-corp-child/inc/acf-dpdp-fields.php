<?php
/**
 * PRIVU HR DPDP — ACF Field Group Registration
 *
 * INSTALLATION:
 * Option 1 (Recommended): Paste in your theme's functions.php
 * Option 2: Save as a separate file e.g. inc/acf-dpdp-fields.php
 *           then add in functions.php:  require_once get_template_directory() . '/inc/acf-dpdp-fields.php';
 *
 * Requires: Advanced Custom Fields PRO (or free version, but Repeater needs PRO)
 */

if ( function_exists('acf_add_local_field_group') ) :

acf_add_local_field_group(array(
    'key'    => 'group_privu_dpdp',
    'title'  => 'Privu HR DPDP — Page Content',
    'fields' => array(

        // ═══════════════════════════════════════════════════════
        // TAB: HERO
        // ═══════════════════════════════════════════════════════
        array(
            'key'   => 'field_dpdp_tab_hero',
            'label' => '🦸 Hero Section',
            'name'  => '',
            'type'  => 'tab',
        ),
        array(
            'key'           => 'field_hero_kicker',
            'label'         => 'Kicker Text (above H1)',
            'name'          => 'hero_kicker',
            'type'          => 'text',
            'default_value' => 'DPDP Act 2023 · For HR Leaders',
        ),
        array(
            'key'           => 'field_hero_h1_line1',
            'label'         => 'H1 — Line 1',
            'name'          => 'hero_h1_line1',
            'type'          => 'text',
            'default_value' => 'Every employee record',
        ),
        array(
            'key'           => 'field_hero_h1_line2',
            'label'         => 'H1 — Line 2',
            'name'          => 'hero_h1_line2',
            'type'          => 'text',
            'default_value' => 'you hold is now',
        ),
        array(
            'key'           => 'field_hero_h1_em',
            'label'         => 'H1 — Italic/Teal Emphasis Line',
            'name'          => 'hero_h1_em',
            'type'          => 'text',
            'default_value' => 'legally regulated.',
        ),
        array(
            'key'           => 'field_hero_sub',
            'label'         => 'Hero Subheading',
            'name'          => 'hero_sub',
            'type'          => 'textarea',
            'rows'          => 3,
            'default_value' => "India's DPDP Act puts HR at the center of compliance. Payroll, biometrics, health records, background checks — your organization is accountable. Not just your IT team.",
        ),
        array(
            'key'           => 'field_hero_btn1_text',
            'label'         => 'Button 1 Text',
            'name'          => 'hero_btn1_text',
            'type'          => 'text',
            'default_value' => 'Start a Free Assessment',
        ),
        array(
            'key'           => 'field_hero_btn1_url',
            'label'         => 'Button 1 URL',
            'name'          => 'hero_btn1_url',
            'type'          => 'text',
            'default_value' => '#start',
        ),
        array(
            'key'           => 'field_hero_btn2_text',
            'label'         => 'Button 2 Text',
            'name'          => 'hero_btn2_text',
            'type'          => 'text',
            'default_value' => "See What's in Scope →",
        ),
        array(
            'key'           => 'field_hero_btn2_url',
            'label'         => 'Button 2 URL',
            'name'          => 'hero_btn2_url',
            'type'          => 'text',
            'default_value' => '#scope',
        ),

        // ═══════════════════════════════════════════════════════
        // TAB: STAT BAR
        // ═══════════════════════════════════════════════════════
        array(
            'key'   => 'field_dpdp_tab_stats',
            'label' => '📊 Stat Bar',
            'name'  => '',
            'type'  => 'tab',
        ),
        array( 'key'=>'field_stat1_num','label'=>'Stat 1 — Number','name'=>'stat1_num','type'=>'text','default_value'=>'₹250 Cr' ),
        array( 'key'=>'field_stat1_lbl','label'=>'Stat 1 — Label', 'name'=>'stat1_lbl','type'=>'text','default_value'=>'Max penalty — breach linked to non-compliance' ),
        array( 'key'=>'field_stat2_num','label'=>'Stat 2 — Number','name'=>'stat2_num','type'=>'text','default_value'=>'₹200 Cr' ),
        array( 'key'=>'field_stat2_lbl','label'=>'Stat 2 — Label', 'name'=>'stat2_lbl','type'=>'text','default_value'=>'Biometric or health data without valid consent' ),
        array( 'key'=>'field_stat3_num','label'=>'Stat 3 — Number','name'=>'stat3_num','type'=>'text','default_value'=>'May 2027' ),
        array( 'key'=>'field_stat3_lbl','label'=>'Stat 3 — Label', 'name'=>'stat3_lbl','type'=>'text','default_value'=>'Full compliance deadline — all 23 rules' ),
        array( 'key'=>'field_stat4_num','label'=>'Stat 4 — Number','name'=>'stat4_num','type'=>'text','default_value'=>'Now' ),
        array( 'key'=>'field_stat4_lbl','label'=>'Stat 4 — Label', 'name'=>'stat4_lbl','type'=>'text','default_value'=>'Rules 1, 2 & 17–21 already in force' ),

        // ═══════════════════════════════════════════════════════
        // TAB: SCOPE
        // ═══════════════════════════════════════════════════════
        array( 'key'=>'field_dpdp_tab_scope','label'=>'🔍 Scope Section','name'=>'','type'=>'tab' ),
        array( 'key'=>'field_scope_label','label'=>'Section Label','name'=>'scope_label','type'=>'text','default_value'=>"What's in Scope" ),
        array( 'key'=>'field_scope_h2',   'label'=>'Section H2 (HTML allowed)','name'=>'scope_h2','type'=>'text','default_value'=>'HR holds the most sensitive<br>data in any organization.' ),
        array(
            'key'           => 'field_scope_tags',
            'label'         => 'Data Tags (one per line)',
            'name'          => 'scope_tags',
            'type'          => 'textarea',
            'rows'          => 8,
            'default_value' => "Aadhaar & PAN\nPayroll & Bank Details\nBiometric Attendance\nBackground Verification\nHealth & Insurance\nPerformance Reviews\nOnboarding Documents\nLeave & Disciplinary Records",
            'instructions'  => 'One tag per line.',
        ),
        array(
            'key'           => 'field_scope_note',
            'label'         => 'Scope Note (HTML allowed)',
            'name'          => 'scope_note',
            'type'          => 'textarea',
            'rows'          => 2,
            'default_value' => '<strong>You are the Data Fiduciary.</strong> Accountability rests with your organization — not your HRMS vendor, not your payroll partner.',
        ),
        // Repeater — Risk Rows (requires ACF PRO)
        array(
            'key'        => 'field_scope_risk_rows',
            'label'      => 'Risk Rows',
            'name'       => 'scope_risk_rows',
            'type'       => 'repeater',
            'layout'     => 'block',
            'button_label'=> 'Add Risk Row',
            'sub_fields' => array(
                array( 'key'=>'field_rr_title',     'label'=>'Title',              'name'=>'title',     'type'=>'text' ),
                array( 'key'=>'field_rr_tag',       'label'=>'Risk Tag',           'name'=>'tag',       'type'=>'text','default_value'=>'High Risk' ),
                array(
                    'key'     => 'field_rr_tag_class',
                    'label'   => 'Tag Style',
                    'name'    => 'tag_class',
                    'type'    => 'select',
                    'choices' => array( 'rtag-h'=>'High Risk (Teal)', 'rtag-m'=>'Medium Risk (Muted)' ),
                    'default_value' => 'rtag-h',
                ),
                array( 'key'=>'field_rr_desc',      'label'=>'Description',        'name'=>'desc',      'type'=>'textarea','rows'=>2 ),
                array(
                    'key'     => 'field_rr_bar_class',
                    'label'   => 'Bar Style',
                    'name'    => 'bar_class',
                    'type'    => 'select',
                    'choices' => array( ''=>'Solid (High Risk)', 'soft'=>'Soft/Muted (Medium Risk)' ),
                    'default_value' => '',
                ),
            ),
        ),

        // ═══════════════════════════════════════════════════════
        // TAB: OBLIGATIONS
        // ═══════════════════════════════════════════════════════
        array( 'key'=>'field_dpdp_tab_ob','label'=>'⚖️ Obligations Section','name'=>'','type'=>'tab' ),
        array( 'key'=>'field_ob_label',   'label'=>'Section Label','name'=>'ob_label',   'type'=>'text','default_value'=>'What the Law Requires of HR' ),
        array( 'key'=>'field_ob_h2_line1','label'=>'H2 Line 1',   'name'=>'ob_h2_line1','type'=>'text','default_value'=>'Four things HR must' ),
        array( 'key'=>'field_ob_h2_em',   'label'=>'H2 Italic Em', 'name'=>'ob_h2_em',  'type'=>'text','default_value'=>'build into operations.' ),
        array(
            'key'        => 'field_obligations',
            'label'      => 'Obligation Cards',
            'name'       => 'obligations',
            'type'       => 'repeater',
            'layout'     => 'block',
            'button_label'=> 'Add Obligation',
            'sub_fields' => array(
                array( 'key'=>'field_ob_num',  'label'=>'Number (e.g. 01)','name'=>'num',  'type'=>'text' ),
                array( 'key'=>'field_ob_title','label'=>'Title',            'name'=>'title','type'=>'text' ),
                array( 'key'=>'field_ob_desc', 'label'=>'Description',      'name'=>'desc', 'type'=>'textarea','rows'=>3 ),
            ),
        ),
        // Penalty bar
        array( 'key'=>'field_pen1_amt','label'=>'Penalty 1 — Amount','name'=>'pen1_amt','type'=>'text','default_value'=>'₹200 Cr' ),
        array( 'key'=>'field_pen1_for','label'=>'Penalty 1 — Label', 'name'=>'pen1_for','type'=>'text','default_value'=>'Biometric / health data without consent' ),
        array( 'key'=>'field_pen2_amt','label'=>'Penalty 2 — Amount','name'=>'pen2_amt','type'=>'text','default_value'=>'₹50 Cr' ),
        array( 'key'=>'field_pen2_for','label'=>'Penalty 2 — Label', 'name'=>'pen2_for','type'=>'text','default_value'=>'Failure to erase data on request' ),
        array( 'key'=>'field_pen3_amt','label'=>'Penalty 3 — Amount','name'=>'pen3_amt','type'=>'text','default_value'=>'₹250 Cr' ),
        array( 'key'=>'field_pen3_for','label'=>'Penalty 3 — Label', 'name'=>'pen3_for','type'=>'text','default_value'=>'Breach where non-compliance contributed' ),

        // ═══════════════════════════════════════════════════════
        // TAB: WHY NOW
        // ═══════════════════════════════════════════════════════
        array( 'key'=>'field_dpdp_tab_wn','label'=>'⏰ Why Now Section','name'=>'','type'=>'tab' ),
        array( 'key'=>'field_wn_label',   'label'=>'Section Label','name'=>'wn_label',   'type'=>'text','default_value'=>'Why Now' ),
        array( 'key'=>'field_wn_h2_line1','label'=>'H2 Line 1',   'name'=>'wn_h2_line1','type'=>'text','default_value'=>'The deadlines are live.' ),
        array( 'key'=>'field_wn_h2_em',   'label'=>'H2 Italic Em', 'name'=>'wn_h2_em',  'type'=>'text','default_value'=>'Some rules already are.' ),
        array( 'key'=>'field_wn_lead',    'label'=>'Lead Paragraph','name'=>'wn_lead',   'type'=>'textarea','rows'=>3,'default_value'=>'Consent frameworks need redesigning. Vendor contracts need new clauses. Employee data needs mapping across every system. That takes months — not weeks.' ),
        array(
            'key'        => 'field_timeline',
            'label'      => 'Timeline Steps',
            'name'       => 'timeline',
            'type'       => 'repeater',
            'layout'     => 'block',
            'button_label'=> 'Add Timeline Step',
            'sub_fields' => array(
                array(
                    'key'     => 'field_tl_dot_class',
                    'label'   => 'Dot Style',
                    'name'    => 'dot_class',
                    'type'    => 'select',
                    'choices' => array( 'td1'=>'Solid Teal (done)', 'td2'=>'Outlined Teal (current)', 'td3'=>'Faint (future)' ),
                ),
                array( 'key'=>'field_tl_dot_text','label'=>'Dot Symbol','name'=>'dot_text','type'=>'text','default_value'=>'✓' ),
                array( 'key'=>'field_tl_date',    'label'=>'Date',       'name'=>'date',    'type'=>'text' ),
                array( 'key'=>'field_tl_title',   'label'=>'Title',      'name'=>'title',   'type'=>'text' ),
                array( 'key'=>'field_tl_desc',    'label'=>'Description','name'=>'desc',    'type'=>'textarea','rows'=>2 ),
            ),
        ),

        // ═══════════════════════════════════════════════════════
        // TAB: CREDENTIALS
        // ═══════════════════════════════════════════════════════
        array( 'key'=>'field_dpdp_tab_cred','label'=>'🏛️ Credentials Section','name'=>'','type'=>'tab' ),
        array( 'key'=>'field_cred_label',    'label'=>'Section Label',      'name'=>'cred_label',    'type'=>'text','default_value'=>"Who You're Working With" ),
        array( 'key'=>'field_cred_h2_line1', 'label'=>'H2 Line 1',          'name'=>'cred_h2_line1', 'type'=>'text','default_value'=>'Legal expertise.' ),
        array( 'key'=>'field_cred_h2_em',    'label'=>'H2 Italic Line',     'name'=>'cred_h2_em',    'type'=>'text','default_value'=>'Technical depth. One team.' ),
        array( 'key'=>'field_cred_lead',     'label'=>'Lead Paragraph',     'name'=>'cred_lead',     'type'=>'textarea','rows'=>2 ),
        array( 'key'=>'field_cred_legal_lbl','label'=>'Left Card — Label',  'name'=>'cred_legal_lbl','type'=>'text','default_value'=>'Legal' ),
        array( 'key'=>'field_cred_legal_h3', 'label'=>'Left Card — H3',    'name'=>'cred_legal_h3', 'type'=>'text' ),
        array( 'key'=>'field_cred_legal_p',  'label'=>'Left Card — Body',  'name'=>'cred_legal_p',  'type'=>'textarea','rows'=>3 ),
        array( 'key'=>'field_cred_tech_lbl', 'label'=>'Right Card — Label','name'=>'cred_tech_lbl', 'type'=>'text','default_value'=>'Technical' ),
        array( 'key'=>'field_cred_tech_h3',  'label'=>'Right Card — H3',   'name'=>'cred_tech_h3',  'type'=>'text' ),
        array( 'key'=>'field_cred_tech_p',   'label'=>'Right Card — Body', 'name'=>'cred_tech_p',   'type'=>'textarea','rows'=>3 ),
        array(
            'key'           => 'field_cred_pills',
            'label'         => 'Credential Pills (one per line)',
            'name'          => 'cred_pills',
            'type'          => 'textarea',
            'rows'          => 6,
            'default_value' => "ISS Certified — Individual Level\nAccredited Auditors — Law Fraternity\nOn-Premise PII Discovery\nDPIA & Gap Assessment\n10–12 Day Turnaround\nNo Data Leaves Your Environment",
            'instructions'  => 'One pill per line.',
        ),

        // ═══════════════════════════════════════════════════════
        // TAB: CTA
        // ═══════════════════════════════════════════════════════
        array( 'key'=>'field_dpdp_tab_cta','label'=>'📞 CTA Section','name'=>'','type'=>'tab' ),
        array( 'key'=>'field_cta_h2_line1',  'label'=>'H2 Line 1',            'name'=>'cta_h2_line1',  'type'=>'text','default_value'=>'Know where you stand.' ),
        array( 'key'=>'field_cta_h2_line2',  'label'=>'H2 Line 2',            'name'=>'cta_h2_line2',  'type'=>'text','default_value'=>'Before the regulator asks.' ),
        array( 'key'=>'field_cta_p',         'label'=>'Body Paragraph',       'name'=>'cta_p',         'type'=>'textarea','rows'=>2 ),
        array( 'key'=>'field_cta_btn_text',  'label'=>'Button Text',          'name'=>'cta_btn_text',  'type'=>'text','default_value'=>'Request Free Consultation →' ),
        array( 'key'=>'field_cta_btn_email', 'label'=>'Button Email',         'name'=>'cta_btn_email', 'type'=>'text','default_value'=>'contact@privu.tech' ),
        array( 'key'=>'field_cta_note',      'label'=>'Disclaimer Note',      'name'=>'cta_note',      'type'=>'text','default_value'=>'Confidential. Covered under NDA from the first call.' ),
        array( 'key'=>'field_cta_email',     'label'=>'Contact Email',        'name'=>'cta_email',     'type'=>'text','default_value'=>'contact@privu.tech' ),
        array( 'key'=>'field_cta_phone_name','label'=>'Phone Contact Name',   'name'=>'cta_phone_name','type'=>'text','default_value'=>'Call Eric Andrade' ),
        array( 'key'=>'field_cta_phone_num', 'label'=>'Phone Number (display)','name'=>'cta_phone_num','type'=>'text','default_value'=>'+91 99-7173-5924' ),
        array( 'key'=>'field_cta_phone_link','label'=>'Phone Number (tel: link)','name'=>'cta_phone_link','type'=>'text','default_value'=>'+919971735924' ),
        array(
            'key'         => 'field_cta_cf7_shortcode',
            'label'       => 'Contact Form 7 Shortcode (optional)',
            'name'        => 'cta_cf7_shortcode',
            'type'        => 'text',
            'placeholder' => '[contact-form-7 id="123" title="DPDP Form"]',
            'instructions'=> 'If filled, replaces the default HTML form with a CF7 form.',
        ),

    ), // end fields

    'location' => array(
        array(
            array(
                'param'    => 'page_template',
                'operator' => '==',
                'value'    => 'page-privu-hr-dpdp.php',
            ),
        ),
    ),
    'menu_order'            => 0,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen'        => array( 'the_content', 'excerpt', 'revisions', 'page_attributes' ),
    'active'                => true,
    'description'           => 'All editable content for the Privu HR DPDP landing page.',
));

endif;
