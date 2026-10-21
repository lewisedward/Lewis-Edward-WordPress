<?php
/**
 * One-off copy clean-up (2026-10-21): sentence-case headings, en dashes instead of em dashes
 * and spaced hyphens, British spellings, typos and grammar. Rules apply to stored content only
 * (posts, ACF values, ACF options, terms, menu labels); SEO-plugin and other underscore-prefixed
 * meta are left alone. Runs once per site from wp-admin (option le_copy_fixes_v), or by an
 * administrator with ?le_copy_fix=dry|run; ?le_copy_find=text lists where a string is stored.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LE_COPY_FIXES_VERSION = '1';

/** Substring rules, applied in order. */
function le_copy_fix_subs() {
	return array(
		array( '--- ', '– ' ),
		array( 'Refacoring', 'Refactoring' ),
		array( 'proejcts', 'projects' ),
		array( 'treatements', 'treatments' ),
		array( 'hit the grown running', 'hit the ground running' ),
		array( 'tasks and managements of', 'tasks and management of' ),
		array( 'confidence and control. Whilst knowing', 'confidence and control, whilst knowing' ),
		array( 'functionality needs. Whilst ensuring', 'functionality needs, whilst ensuring' ),
		array( 'on your site, without needing a developer', 'on your site without needing a developer' ),
		array( 'license costs', 'licence costs' ),
		array( 'plugin licenses', 'plugin licences' ),
		array( 'can practice without', 'can practise without' ),
		array( 'environment to practice', 'environment to practise' ),
		array( 'toward a clear', 'towards a clear' ),
		array( 'analyze', 'analyse' ),
		array( 'behavior', 'behaviour' ),
		array( 'fulfill ', 'fulfil ' ),
		array( 'organization', 'organisation' ),
		array( 'To Respond to Inquiries:', 'To respond to enquiries:' ),
		array( 'To Improve Our Website:', 'To improve our website:' ),
		array( 'For Business Operations:', 'For business operations:' ),
		array( 'Legal Requirements:', 'Legal requirements:' ),
		array( 'Right to Access:', 'Right to access:' ),
		array( 'Right to Rectification:', 'Right to rectification:' ),
		array( 'Right to Erasure:', 'Right to erasure:' ),
		array( 'Right to Restrict Processing:', 'Right to restrict processing:' ),
		array( 'Right to Data Portability:', 'Right to data portability:' ),
		array( 'Right to Object:', 'Right to object:' ),
		array( 'Inquiries', 'Enquiries' ),
		array( 'inquiries', 'enquiries' ),
		array( 'inquiry', 'enquiry' ),
		array( 'Hubspot', 'HubSpot' ),
		array( 'Youtube', 'YouTube' ),
		array( 'Shortpixel', 'ShortPixel' ),
		array( 'Woocommerce hop products', 'WooCommerce shop products' ),
		array( 'Woocommerce', 'WooCommerce' ),
		array( 'Wordpress', 'WordPress' ),
		array( 'AdobeXD', 'Adobe XD' ),
		array( 'Invision', 'InVision' ),
		array( 'Google Page Speed Insights', 'Google PageSpeed Insights' ),
		array( 'WooCommerce stores Shopify sites', 'WooCommerce stores, Shopify sites' ),
		array( 'Web Designers, Developers and let\'s face it... Geeks', 'web designers, developers and, let\'s face it… geeks' ),
		array( 'Web Designers, Developers and let’s face it... Geeks', 'web designers, developers and, let’s face it… geeks' ),
		array( 'Web Designers, Developers and let&#039;s face it... Geeks', 'web designers, developers and, let&#039;s face it… geeks' ),
		array( 'AI built sites', 'AI-built sites' ),
		array( 'support Creative Agencies who', 'support creative agencies who' ),
		array( 'all the built in CMS', 'all the built-in CMS' ),
		array( 'Fully-managed', 'Fully managed' ),
		array( 'fully-managed', 'fully managed' ),
		array( 'Seamless Content Management System migration', 'Seamless content management system migration' ),
		array( 'presents itself if you’re interested', 'presents itself. If you’re interested' ),
		array( 'presents itself if you\'re interested', 'presents itself. If you\'re interested' ),
		array( 'able to feedback and collaborate', 'able to give feedback and collaborate' ),
		array( 'So in that sense yes we do offer a white label', 'So in that sense, yes, we do offer a white-label' ),
		array( 'clients and us, there is only a one month notice period', 'clients and us: there is only a one-month notice period' ),
		array( 'such as this please get in touch', 'such as this, please get in touch' ),
		array( 'content management system, however, we’re also confident and have worked with', 'content management system; however, we’re also confident with, and have worked with,' ),
		array( 'content management system, however, we\'re also confident and have worked with', 'content management system; however, we\'re also confident with, and have worked with,' ),
		array( 'A custom designed and animated', 'A custom-designed and animated' ),
		array( 'top tier website services', 'top-tier website services' ),
		array( 'As natural born problem solvers', 'As natural-born problem-solvers' ),
		array( 'or never ending meetings', 'or never-ending meetings' ),
		array( 'However, if asked to for a specific reason we can use these at the', 'However, if there is a specific reason, we can use these at the' ),
		array( 'super fast and secure', 'super-fast and secure' ),
		array( 'coffee fuelled humans', 'coffee-fuelled humans' ),
		array( 'helping you to keep your website secure, up-to-date and evolve it over time', 'helping you keep your website secure and up to date, and evolve it over time' ),
		array( 'and topped-up over time', 'and topped up over time' ),
		array( 'rolls-over to', 'rolls over to' ),
		array( 'A single To Do list', 'A single to-do list' ),
		array( 'a full view on what', 'a full view of what' ),
		array( 'out of hours support', 'out-of-hours support' ),
		array( '12 month lifespan', '12-month lifespan' ),
		array( 'water-tight', 'watertight' ),
		array( 'and other integration for marketing', 'and other integrations for marketing' ),
		array( 'from design, through to development', 'from design through to development' ),
		array( 'complex and fully integrated site for multi-national companies', 'complex and fully integrated sites for multinational companies' ),
		array( 'with a sleeves rolled up approach, we don', 'with a sleeves-rolled-up approach: we don' ),
		array( 'A deep-dive into', 'A deep dive into' ),
		array( 'Mailchimp, E-commerce, Google Analytics and plug those', 'Mailchimp, e-commerce and Google Analytics, and plug those' ),
		array( 'meta data', 'metadata' ),
		array( 'Website code, hosting, database analysis', 'Website code, hosting and database analysis' ),
		array( 'and content. With backups taken first.', 'and content, with backups taken first.' ),
		array( '2-factor authentication', 'Two-factor authentication' ),
		array( 'user-experience and creative flare', 'user experience and creative flair' ),
		array( 'Cohesive visual systems - typography, colour palettes, component libraries to ensure', 'Cohesive visual systems – typography, colour palettes and component libraries – to ensure' ),
		array( 'at what we do so we', 'at what we do, so we' ),
		array( 'many revisions, so it', 'many revisions, which is why it' ),
		array( 'expand your agencies offering', 'expand your agency\'s offering' ),
		array( 'From subscription services and payment gateways, to inventory', 'From subscription services and payment gateways to inventory' ),
		array( 'colour treated video', 'colour-treated video' ),
		array( 'Well defined user journeys depending on if the visitor', 'Well-defined user journeys depending on whether the visitor' ),
		array( 'with option bios modal', 'with optional bios modal' ),
		array( 'ustom styled Google', 'ustom-styled Google' ),
		array( 'Google map API', 'Google Maps API' ),
		array( 'A WordPress Flexible article hero section', 'A flexible WordPress article hero section' ),
		array( 'Ink Studio WordPress website is an animated', 'The Ink Studio WordPress website is an animated' ),
		array( 'Full screen images', 'Full-screen images' ),
		array( 'full width hero image', 'full-width hero image' ),
		array( 'A brand new web design', 'A brand-new web design' ),
		array( 'WordPress Connected to third party APIs', 'WordPress connected to third-party APIs' ),
		array( 'WordPress News posts grid', 'WordPress news posts grid' ),
		array( 'with sign up form', 'with sign-up form' ),
		array( 'for a Physiotherapy clinic', 'for a physiotherapy clinic' ),
		array( 'WordPress Integrated Google reviews', 'WordPress-integrated Google reviews' ),
		array( 'subscription based WordPress', 'subscription-based WordPress' ),
		array( 'paywall protected', 'paywall-protected' ),
		array( 'Image led WordPress', 'Image-led WordPress' ),
		array( 'WordPress Flexible and dynamic modules', 'Flexible and dynamic WordPress modules' ),
		array( 'sub category', 'subcategory' ),
		array( 'TV Show and episodes', 'TV shows and episodes' ),
		array( 'Advertising Agency home page', 'Advertising agency home page' ),
		array( 'Animated call to actions', 'Animated calls to action' ),
		array( 'Sign posting and calls', 'Signposting and calls' ),
		array( 'page sign posting', 'page signposting' ),
		array( 'API to lookup company', 'API to look up company' ),
		array( 'popup modal YouTube Videos', 'popup modal YouTube videos' ),
		array( 'the scale of the business, 19 AD facilities', 'the scale of the business – 19 AD facilities' ),
		array( '120,000 homes, nor their position', '120,000 homes – nor their position' ),
		array( 'animated stat cards, 19 AD facilities', 'animated stat cards: 19 AD facilities' ),
		array( 'calls-to-action', 'calls to action' ),
		array( 'London WordPress Web Designers, Web Developers and AI Geeks specialising', 'London WordPress web designers, web developers and AI geeks specialising' ),
		array( 'The numbers back this up: A dependable', 'The numbers back this up: a dependable' ),
		array( '(e.g., ', '(e.g. ' ),
		array( '50-70%', '50–70%' ),
		array( '200-400ms', '200–400ms' ),
		array( '2-8 people', '2–8 people' ),
		array( '6-10 weeks', '6–10 weeks' ),
		array( '2-6 weeks', '2–6 weeks' ),
		array( '8-16 weeks', '8–16 weeks' ),
		array( '1-2 weeks', '1–2 weeks' ),
		array( '50-80%', '50–80%' ),
		array( '1-2 hours', '1–2 hours' ),
		array( '1-2 months', '1–2 months' ),
		array( '2-4 weeks', '2–4 weeks' ),
		array( '4-6 weeks', '4–6 weeks' ),
		array( 'AI powered web', 'AI-powered web' ),
	);
}

/** Whole-value / whole-heading rules: Title Case → sentence case. */
function le_copy_fix_exact() {
	return array(
		'Bespoke WordPress Websites' => 'Bespoke WordPress websites',
		'WordPress web Design, Development & Support' => 'WordPress web design, development & support',
		'London WordPress Web Designer, Developer & Website Support' => 'London WordPress web designer, developer & website support',
		'Will you sign a Non-Disclosure Agreement?' => 'Will you sign a non-disclosure agreement?',
		'Privacy & Cookie Policy' => 'Privacy & cookie policy',
		'Personal Data via Forms' => 'Personal data via forms',
		'Bot Protection Data' => 'Bot protection data',
		'How We Use Your Information' => 'How we use your information',
		'Cookies & Tracking Technologies' => 'Cookies & tracking technologies',
		'No User Accounts or Payment Processing' => 'No user accounts or payment processing',
		'Changes to This Policy' => 'Changes to this policy',
		'Information We Collect' => 'Information we collect',
		'Analytics Data' => 'Analytics data',
		'Third-Party Services' => 'Third-party services',
		'Data Retention' => 'Data retention',
		'Your Rights' => 'Your rights',
		'Contact Information' => 'Contact information',
		'Our extended Web Design & Development services' => 'Our extended web design & development services',
		'WordPress website Design & Development tailored to you.' => 'WordPress website design & development tailored to you.',
		'Fully-managed WordPress Web Hosting' => 'Fully managed WordPress web hosting',
		'Website Support & Maintenance' => 'Website support & maintenance',
		'London WordPress website Consultants' => 'London WordPress website consultants',
		'WordPress website Performance Optimisation' => 'WordPress website performance optimisation',
		'WordPress Website CMS Training' => 'WordPress website CMS training',
		'WordPress Website Security' => 'WordPress website security',
		'London WordPress Web Design' => 'London WordPress web design',
		'WooCommerce & Shopify Development' => 'WooCommerce & Shopify development',
		'WordPress Search Engine Optimisation (SEO)' => 'WordPress search engine optimisation (SEO)',
		'AI powered web & CRM development' => 'AI-powered web & CRM development',
		'Advanced Search Engine Optimisation (SEO) tailored for WordPress.' => 'Advanced search engine optimisation (SEO) tailored for WordPress.',
		'Field notes, Build notes & Reads' => 'Field notes, build notes & reads',
		'WordPress Website Hover Styles and User Interactions' => 'WordPress website hover styles and user interactions',
		'WordPress Security 101: how to protect your site' => 'WordPress security 101: how to protect your site',
		'Bespoke Theme Development' => 'Bespoke theme development',
		'Custom Plugin Development' => 'Custom plugin development',
		'Free Website Migration' => 'Free website migration',
		'AI-Assisted Web Development' => 'AI-assisted web development',
		'AI Web Apps & SaaS' => 'AI web apps & SaaS',
		'Custom AI Tools & Agents' => 'Custom AI tools & agents',
		'LLM & API Integrations' => 'LLM & API integrations',
		'RAG & Knowledge Bases' => 'RAG & knowledge bases',
		'AI Automation & Workflows' => 'AI automation & workflows',
		'Discovery & AI Strategy' => 'Discovery & AI strategy',
		'Launch, Monitor & Iterate' => 'Launch, monitor & iterate',
		'Flexible Pre-Paid Hours' => 'Flexible prepaid hours',
		'Monthly/Quarterly Retainer' => 'Monthly/quarterly retainer',
		'User Experience (UX)' => 'User experience (UX)',
		'UI/UX Design' => 'UI/UX design',
		'Web Design' => 'Web design',
		'Web Development' => 'Web development',
		'Website Support' => 'Website support',
		'Agency Partnership' => 'Agency partnership',
		'Web Hosting' => 'Web hosting',
		'Your Project' => 'Your project',
		'API & Integrations' => 'API & integrations',
		'Website Audits' => 'Website audits',
		'Website Optimisation' => 'Website optimisation',
		'Website Training' => 'Website training',
		'Website Security' => 'Website security',
		'API Integration' => 'API integration',
		'Performance Optimisation' => 'Performance optimisation',
		'Migration & Launch' => 'Migration & launch',
		'Technical Planning' => 'Technical planning',
		'Development Sprints' => 'Development sprints',
		'Launch & Deployment' => 'Launch & deployment',
		'Hassle-free Management' => 'Hassle-free management',
		'Robust Security' => 'Robust security',
		'Domain Management' => 'Domain management',
		'Design & Prototype' => 'Design & prototype',
		'AI-Assisted Build' => 'AI-assisted build',
		'One-off Support' => 'One-off support',
		'Custom Forms' => 'Custom forms',
		'Marketing Tools' => 'Marketing tools',
		'Payment Gateways' => 'Payment gateways',
		'Technical Tasks' => 'Technical tasks',
		'Content Updates' => 'Content updates',
		'Ongoing Support' => 'Ongoing support',
		'Design & Build' => 'Design & build',
		'Technology Strategy' => 'Technology strategy',
		'Project Roadmapping' => 'Project roadmapping',
		'Vendor Selection' => 'Vendor selection',
		'Performance Reviews' => 'Performance reviews',
		'Training & Workshops' => 'Training & workshops',
		'Discovery Call' => 'Discovery call',
		'Analysis & Research' => 'Analysis & research',
		'Project Plan' => 'Project plan',
		'Editable Content' => 'Editable content',
		'Third-party Integration' => 'Third-party integration',
		'WordPress Training' => 'WordPress training',
		'SEO Preservation' => 'SEO preservation',
		'Design Recreation' => 'Design recreation',
		'Content Transfer' => 'Content transfer',
		'Site Audit' => 'Site audit',
		'Launch & Training' => 'Launch & training',
		'Technical Audit' => 'Technical audit',
		'SEO Analysis' => 'SEO analysis',
		'Competitor Analysis' => 'Competitor analysis',
		'Performance Review' => 'Performance review',
		'Prioritised Recommendations' => 'Prioritised recommendations',
		'Loading Speed' => 'Loading speed',
		'Image Optimisation' => 'Image optimisation',
		'Caching Strategy' => 'Caching strategy',
		'Code Optimisation' => 'Code optimisation',
		'Training Format' => 'Training format',
		'Types of Training' => 'Types of training',
		'Staging Website' => 'Staging website',
		'Team Training' => 'Team training',
		'Training Sessions' => 'Training sessions',
		'Backup Systems' => 'Backup systems',
		'Website Users' => 'Website users',
		'Malware Scanning' => 'Malware scanning',
		'Firewall Protection' => 'Firewall protection',
		'Security Audits' => 'Security audits',
		'Security Audit' => 'Security audit',
		'Wireframes & Prototyping' => 'Wireframes & prototyping',
		'Responsive Design' => 'Responsive design',
		'Brand Systems' => 'Brand systems',
		'Design Audits' => 'Design audits',
		'Motion & Interaction' => 'Motion & interaction',
		'Research & Strategy' => 'Research & strategy',
		'Wireframes & Concepts' => 'Wireframes & concepts',
		'Visual Design' => 'Visual design',
		'Handoff & Build' => 'Handoff & build',
		'White Label' => 'White label',
		'Flexible Hours' => 'Flexible hours',
		'Team Integration' => 'Team integration',
		'Quality Assurance' => 'Quality assurance',
		'Design Handover' => 'Design handover',
		'Website Development' => 'Website development',
		'Website Launch' => 'Website launch',
		'WooCommerce Development' => 'WooCommerce development',
		'Inventory Management' => 'Inventory management',
		'Subscription & Bookings' => 'Subscription & bookings',
		'Checkout Optimisation' => 'Checkout optimisation',
		'Analytics & Reporting' => 'Analytics & reporting',
		'Store Strategy' => 'Store strategy',
		'Build & Configure' => 'Build & configure',
		'Test & Optimise' => 'Test & optimise',
		'Launch & Grow' => 'Launch & grow',
		'Content Strategy' => 'Content strategy',
		'Quarterly Reviews' => 'Quarterly reviews',
		'SEO Audit' => 'SEO audit',
		'Experienced Specialists' => 'Experienced specialists',
		'Proactive & Reliable' => 'Proactive & reliable',
		'E-Commerce' => 'E-commerce',
		'AI Website Development' => 'AI website development',
		'Extended Functionality' => 'Extended functionality',
		'Donation Integration' => 'Donation integration',
		'Custom Gutenberg Blocks' => 'Custom Gutenberg blocks',
		'Online Booking Integration' => 'Online booking integration',
		'Membership Integration' => 'Membership integration',
		'Reservation Integration' => 'Reservation integration',
		'Newsletter Integration' => 'Newsletter integration',
		'Custom Magazine Layout' => 'Custom magazine layout',
		'Embedded Video Hero Background' => 'Embedded video hero background',
		'Clean & Creative' => 'Clean & creative',
		'Business Improvement Districts' => 'Business improvement districts',
		'About You' => 'About you',
		'Quote Request' => 'Quote request',
	);
}

/** Heading fragments that sit mid-heading, keyed by ACF field name (whole value, trimmed). */
function le_copy_fix_keyed() {
	return array(
		'home_hero_title_2_pre' => array( 'Web Designer, Developer' => 'web designer, developer' ),
		'home_hero_title_2_post' => array( 'Website Support' => 'website support', 'Developer & Website Support' => 'developer & website support' ),
		'services_h1_2' => array( 'Design & Development' => 'design & development', 'Design & Development services' => 'design & development services' ),
		'services_sub_h_muted' => array( 'Web Design & Development' => 'web design & development', 'WordPress Web Design & Development' => 'WordPress web design & development' ),
		'journalp_h1_2' => array( 'Build notes' => 'build notes' ),
		'journalp_h1_3' => array( '& Reads' => '& reads' ),
		'home_work_title_post' => array( 'Websites' => 'websites', 'Design & Development Projects' => 'design & development projects', 'Design & Development' => 'design & development' ),
		'workp_archive_eyebrow' => array( 'Websites' => 'websites' ),
	);
}

/**
 * Applies every rule to one string. $hits collects rule => count.
 *
 * @param string $s    Text.
 * @param array  $hits Hit counter (by reference).
 * @return string
 */
function le_copy_fix_text( $s, &$hits ) {
	if ( ! is_string( $s ) || '' === trim( $s ) ) {
		return $s;
	}
	static $exact = null, $subs = null;
	if ( null === $exact ) {
		$exact = le_copy_fix_exact();
		$subs  = le_copy_fix_subs();
	}
	$bump = static function ( $k, $n ) use ( &$hits ) {
		if ( $n ) {
			$hits[ $k ] = ( $hits[ $k ] ?? 0 ) + $n;
		}
	};
	$t     = trim( $s );
	$plain = str_replace( array( '[[', ']]', '{{', '}}' ), '', $t );
	if ( $plain !== $t && isset( $exact[ $plain ] ) ) {
		// Tone markers ([[lime]], {{grey}}): re-case the text and keep the markers where they were.
		$np = $exact[ $plain ];
		if ( strlen( $np ) === strlen( $plain ) ) {
			$out = '';
			$j   = 0;
			for ( $i = 0, $n = strlen( $t ); $i < $n; $i++ ) {
				$two = substr( $t, $i, 2 );
				if ( in_array( $two, array( '[[', ']]', '{{', '}}' ), true ) ) {
					$out .= $two;
					$i++;
					continue;
				}
				$out .= $np[ $j++ ];
			}
			$np = $out;
		}
		$s = str_replace( $t, $np, $s );
		$bump( '= ' . $plain, 1 );
	} elseif ( isset( $exact[ $t ] ) ) {
		$s = str_replace( $t, $exact[ $t ], $s );
		$bump( '= ' . $t, 1 );
	} elseif ( false !== strpos( $s, '<' ) ) {
		foreach ( $exact as $o => $n ) {
			foreach ( array_unique( array( $o, esc_html( $o ), str_replace( '&', '&amp;', $o ) ) ) as $v ) {
				$nv = ( $v === $o ) ? $n : str_replace( '&', '&amp;', $n );
				$c  = 0;
				$s  = preg_replace( '/>(\s*)' . preg_quote( $v, '/' ) . '(\s*)</u', '>${1}' . str_replace( array( '\\', '$' ), array( '\\\\', '\\$' ), $nv ) . '${2}<', $s, -1, $c );
				$bump( '= ' . $o, $c );
			}
		}
	}
	foreach ( $subs as $r ) {
		$c = 0;
		$s = str_replace( $r[0], $r[1], $s, $c );
		$bump( $r[0], $c );
	}
	$c = 0;
	$s = str_replace( '—', '–', $s, $c );
	$bump( 'em dash', $c );
	$c = 0;
	$s = preg_replace( '/(?<= )-(?= |$)/u', '–', $s, -1, $c );
	$bump( 'spaced hyphen', $c );
	return $s;
}

/**
 * Walks unserialised values.
 *
 * @param mixed $v    Value.
 * @param array $hits Hit counter.
 * @return mixed
 */
function le_copy_fix_value( $v, &$hits ) {
	if ( is_string( $v ) ) {
		return le_copy_fix_text( $v, $hits );
	}
	if ( is_array( $v ) ) {
		foreach ( $v as $k => $x ) {
			$v[ $k ] = le_copy_fix_value( $x, $hits );
		}
	}
	return $v;
}

/**
 * Fixes a raw DB value (serialised or not). Returns null when unchanged.
 *
 * @param string $raw  Raw value.
 * @param array  $hits Hit counter.
 * @return string|null
 */
function le_copy_fix_raw( $raw, &$hits ) {
	if ( ! is_string( $raw ) || '' === $raw || is_numeric( $raw ) ) {
		return null;
	}
	if ( is_serialized( $raw ) ) {
		$un = @unserialize( $raw, array( 'allowed_classes' => false ) ); // phpcs:ignore
		if ( false === $un && 'b:0;' !== $raw ) {
			return null;
		}
		$new = le_copy_fix_value( $un, $hits );
		return ( $new === $un ) ? null : serialize( $new ); // phpcs:ignore
	}
	$new = le_copy_fix_text( $raw, $hits );
	return ( $new === $raw ) ? null : $new;
}

/**
 * Runs the clean-up over the database.
 *
 * @param bool $dry Report only.
 * @return array
 */
function le_copy_fix_run( $dry = true ) {
	global $wpdb;
	$hits    = array();
	$changed = array();
	$skip    = "'revision','attachment','acf-field','acf-field-group','customize_changeset','oembed_cache','user_request','wp_global_styles','custom_css','wp_template','wp_template_part','wp_font_family','wp_font_face'";
	$types   = "p.post_type NOT IN ($skip) AND p.post_status NOT IN ('auto-draft','trash','inherit')";

	$rows = $wpdb->get_results( "SELECT p.ID, p.post_title, p.post_content, p.post_excerpt FROM {$wpdb->posts} p WHERE $types" ); // phpcs:ignore
	foreach ( $rows as $r ) {
		$upd = array();
		foreach ( array( 'post_title', 'post_content', 'post_excerpt' ) as $col ) {
			$n = le_copy_fix_raw( $r->$col, $hits );
			if ( null !== $n ) {
				$upd[ $col ] = $n;
			}
		}
		if ( $upd ) {
			$changed[] = 'post ' . $r->ID . ' ' . implode( ',', array_keys( $upd ) );
			if ( ! $dry ) {
				$wpdb->update( $wpdb->posts, $upd, array( 'ID' => $r->ID ) );
				clean_post_cache( (int) $r->ID );
			}
		}
	}

	$rows = $wpdb->get_results( "SELECT m.meta_id, m.post_id, m.meta_key, m.meta_value FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE $types AND m.meta_key NOT LIKE '\\_%'" ); // phpcs:ignore
	$keyed = le_copy_fix_keyed();
	foreach ( $rows as $r ) {
		$val = $r->meta_value;
		if ( isset( $keyed[ $r->meta_key ][ trim( (string) $val ) ] ) ) {
			$val = str_replace( trim( $val ), $keyed[ $r->meta_key ][ trim( $val ) ], $val );
			$hits[ 'key ' . $r->meta_key ] = ( $hits[ 'key ' . $r->meta_key ] ?? 0 ) + 1;
		}
		$n = le_copy_fix_raw( $val, $hits );
		$n = ( null === $n && $val !== $r->meta_value ) ? $val : $n;
		if ( null !== $n ) {
			$changed[] = 'meta ' . $r->post_id . ' ' . $r->meta_key;
			if ( ! $dry ) {
				$wpdb->update( $wpdb->postmeta, array( 'meta_value' => $n ), array( 'meta_id' => $r->meta_id ) );
				wp_cache_delete( (int) $r->post_id, 'post_meta' );
			}
		}
	}

	$rows = $wpdb->get_results( "SELECT option_id, option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'options\\_%'" ); // phpcs:ignore
	foreach ( $rows as $r ) {
		$n = le_copy_fix_raw( $r->option_value, $hits );
		if ( null !== $n ) {
			$changed[] = 'option ' . $r->option_name;
			if ( ! $dry ) {
				$wpdb->update( $wpdb->options, array( 'option_value' => $n ), array( 'option_id' => $r->option_id ) );
			}
		}
	}

	$rows = $wpdb->get_results( "SELECT t.term_id, t.name, tt.term_taxonomy_id, tt.description FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id WHERE tt.taxonomy NOT IN ('nav_menu')" ); // phpcs:ignore
	foreach ( $rows as $r ) {
		$nn = le_copy_fix_raw( $r->name, $hits );
		$nd = le_copy_fix_raw( $r->description, $hits );
		if ( null !== $nn || null !== $nd ) {
			$changed[] = 'term ' . $r->term_id;
			if ( ! $dry ) {
				if ( null !== $nn ) {
					$wpdb->update( $wpdb->terms, array( 'name' => $nn ), array( 'term_id' => $r->term_id ) );
				}
				if ( null !== $nd ) {
					$wpdb->update( $wpdb->term_taxonomy, array( 'description' => $nd ), array( 'term_taxonomy_id' => $r->term_taxonomy_id ) );
				}
				clean_term_cache( (int) $r->term_id );
			}
		}
	}

	$rows = $wpdb->get_results( "SELECT meta_id, term_id, meta_key, meta_value FROM {$wpdb->termmeta} WHERE meta_key NOT LIKE '\\_%'" ); // phpcs:ignore
	foreach ( $rows as $r ) {
		$n = le_copy_fix_raw( $r->meta_value, $hits );
		if ( null !== $n ) {
			$changed[] = 'termmeta ' . $r->term_id . ' ' . $r->meta_key;
			if ( ! $dry ) {
				$wpdb->update( $wpdb->termmeta, array( 'meta_value' => $n ), array( 'meta_id' => $r->meta_id ) );
			}
		}
	}

	$gf     = $wpdb->prefix . 'gf_form_meta';
	$labels = array();
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $gf ) ) === $gf ) {
		foreach ( $wpdb->get_results( "SELECT form_id, display_meta FROM $gf" ) as $r ) { // phpcs:ignore
			$meta = json_decode( (string) $r->display_meta, true );
			if ( ! is_array( $meta ) || empty( $meta['fields'] ) ) {
				continue;
			}
			$dirty = false;
			foreach ( $meta['fields'] as $fi => $f ) {
				foreach ( array( 'label', 'description' ) as $prop ) {
					if ( empty( $f[ $prop ] ) || ! is_string( $f[ $prop ] ) ) {
						continue;
					}
					if ( 'label' === $prop ) {
						$labels[] = $r->form_id . ': ' . $f[ $prop ];
					}
					$nv = le_copy_fix_text( $f[ $prop ], $hits );
					if ( $nv !== $f[ $prop ] ) {
						$meta['fields'][ $fi ][ $prop ] = $nv;
						$dirty                          = true;
					}
				}
			}
			if ( $dirty ) {
				$changed[] = 'gform ' . $r->form_id;
				if ( ! $dry ) {
					$wpdb->update( $gf, array( 'display_meta' => wp_json_encode( $meta ) ), array( 'form_id' => $r->form_id ) );
				}
			}
		}
	}

	if ( ! $dry ) {
		wp_cache_flush();
		update_option( 'le_copy_fixes_v', LE_COPY_FIXES_VERSION, false );
	}
	$all  = array_merge( array_map( static function ( $r ) { return $r[0]; }, le_copy_fix_subs() ), array_map( static function ( $k ) { return '= ' . $k; }, array_keys( le_copy_fix_exact() ) ) );
	$miss = array_values( array_diff( $all, array_keys( $hits ) ) );
	return array( 'dry' => $dry, 'changed' => count( $changed ), 'rows' => $changed, 'hits' => $hits, 'unmatched' => $miss, 'gf_labels' => $labels );
}

/**
 * Lists where a string is stored (admins only).
 *
 * @param string $needle Text.
 * @return array
 */
function le_copy_find( $needle ) {
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $needle ) . '%';
	$ctx  = static function ( $v ) use ( $needle ) {
		$i = strpos( $v, $needle );
		return substr( $v, max( 0, $i - 80 ), strlen( $needle ) + 160 );
	};
	$out = array();
	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_type, post_title, post_content FROM {$wpdb->posts} WHERE post_type <> 'revision' AND ( post_title LIKE %s OR post_content LIKE %s )", $like, $like ) ) as $r ) { // phpcs:ignore
		$out[] = array( 'post', $r->ID, $r->post_type, $ctx( $r->post_title . ' | ' . $r->post_content ) );
	}
	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", $like ) ) as $r ) { // phpcs:ignore
		$out[] = array( 'meta', $r->post_id, $r->meta_key, $ctx( $r->meta_value ) );
	}
	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_value LIKE %s", $like ) ) as $r ) { // phpcs:ignore
		$out[] = array( 'option', $r->option_name, '', $ctx( $r->option_value ) );
	}
	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT term_id, name FROM {$wpdb->terms} WHERE name LIKE %s", $like ) ) as $r ) { // phpcs:ignore
		$out[] = array( 'term', $r->term_id, '', $r->name );
	}
	return $out;
}

add_action(
	'init',
	static function () {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : ''; // phpcs:ignore
		if ( 0 !== strpos( $host, 'staging.' ) || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_GET['le_copy_find'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_send_json( le_copy_find( wp_unslash( (string) $_GET['le_copy_find'] ) ) ); // phpcs:ignore
		}
		if ( isset( $_GET['le_copy_fix'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_send_json( le_copy_fix_run( 'run' !== $_GET['le_copy_fix'] ) ); // phpcs:ignore
		}
	},
	1
);
