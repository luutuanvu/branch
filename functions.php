<?php
session_start();
global $date;
$date = new DateTime("now", new DateTimeZone('Asia/Tokyo'));
//date_default_timezone_set('Asia/Tokyo');
/*---------------------- API KEYS & Thid-Party ------------------------------*/

// GOOGLE MAP API
define('MAP_API_KEY', 'AIzaSyBcfkpMaz14BwINvFMb6lZMxhHtCQ53d7I');
define('CURRENCY', '¥');
/*---------------------- /API KEYS & Third-Party ------------------------------*/

// uncomment in production
// error_reporting(0);

// Apply Google Map API Key ///////////////////////////////////////
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');
function my_acf_google_map_api($api)
{
    $api['key'] = MAP_API_KEY;
    return $api;
}

// Basic theme setup ////////////////////////////////////////////////////
if (!function_exists('mf_setup_theme')) :
    add_action('after_setup_theme', 'mf_setup_theme');
    function mf_setup_theme()
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('automatic-feed-links');
        add_theme_support('custom-logo', [
            'flex-width' => true,
            'flex-height' => true
        ]);

        add_image_size('mf-thumbnail', 360, 200, ['left', 'top']);
    }
endif;

// CSS & JS files ////////////////////////////////////////////////////////////////
if(!function_exists('admin_script')){
    add_action('admin_enqueue_scripts', 'admin_script');
    function admin_script(){
        wp_enqueue_script('jquery');
        wp_enqueue_script('admin.js', get_theme_file_uri('assets/themes/js/admin.js'), NULL, 'all', true);
        global $current_user;
        $roles = $current_user->roles;
        if(in_array('restaurant_own', $roles)) {
            wp_enqueue_script('restaurant.js', get_theme_file_uri('assets/themes/js/restaurant.js'), NULL, 'all', true);
        }
        if(in_array('administrator', $roles)) {
            wp_enqueue_script('administrator.js', get_theme_file_uri('assets/themes/js/administrator.js'), NULL, 'all', true);
        }
    }
}
if (!function_exists('mf_files')) :
    add_action('wp_enqueue_scripts', 'mf_files');
    function mf_files()
    {
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'mfData', [
            'root_url' => get_site_url(),
            'ajax_url'  => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('mf-ajax-security'),
            'checkout_url' => home_url('/check-out'),
            'pay_jp_pk' => pk
        ]);
        wp_enqueue_style('font-awesome-css', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('rest-css', get_theme_file_uri('assets/themes/css/reset.css'));
        wp_enqueue_style('nav-css', get_theme_file_uri('assets/themes/css/nav.css'));
        wp_enqueue_style('slick.css', get_theme_file_uri('assets/themes/css/slick.css'));
        wp_enqueue_style('slick-theme.css', get_theme_file_uri('assets/themes/css/slick-theme.css'));
        wp_enqueue_style('bootstrap.min.css', get_theme_file_uri('assets/themes/css/bootstrap.min.css'));
        wp_enqueue_style('bootstrap-datetimepicker.css', get_theme_file_uri('assets/themes/css/bootstrap-datetimepicker.css'));
        wp_enqueue_style('modal.css', get_theme_file_uri('assets/themes/css/modal.css'));
        wp_enqueue_style('style-css', get_theme_file_uri('assets/themes/css/style.css'));



        //wp_enqueue_style('mf-main', get_stylesheet_uri());
        wp_enqueue_script('slick-js', get_theme_file_uri('assets/themes/js/slick.js'), NULL, 'all', true);
        wp_enqueue_script('modal.min.js', get_theme_file_uri('assets/themes/js/modal.min.js'), NULL, 'all', true);
        wp_enqueue_script('axios-js', 'https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js', NULL, '2.0', true);
        wp_enqueue_script('moment.min.js', get_theme_file_uri('assets/themes/js/moment.min.js'), NULL, 'all', true);
        wp_enqueue_script('bootstrap-datetimepicker.min.js', get_theme_file_uri('assets/themes/js/bootstrap-datetimepicker.min.js'), NULL, 'all', true);
        wp_enqueue_script('theme.js', get_theme_file_uri('assets/themes/js/theme.js'), NULL, 'all', true);
        wp_enqueue_script('booking.js', get_theme_file_uri('assets/themes/js/booking.js'), NULL, 'all', true);
        //wp_enqueue_script('stripe-js', 'https://js.stripe.com/v2/', NULL, '2.0', true);
        wp_enqueue_script('mf-bundle', get_theme_file_uri('bundle.js'), NULL, '1.0', true);
        if(is_singular('restaurant')){
            //src="https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js"
            wp_enqueue_script('booking.js', "https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js", NULL, 'all', true);
        }
        //wp_enqueue_script('map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBcfkpMaz14BwINvFMb6lZMxhHtCQ53d7I', NULL, '1.0', true);
    }
endif;
// Custom Post Types ////////////////////////////////////////////////
// require_once get_theme_file_path('mf-post-types/course.php');
// require_once get_theme_file_path('mf-post-types/restaurant.php');
// require_once get_theme_file_path('mf-post-types/booking.php');
// Lets apply our function to hook.
// require_once get_theme_file_path('inc/pay.php');
// require_once get_theme_file_path('inc/class/booking.php');
// require_once get_theme_file_path('inc/data-function.php');
// require_once get_theme_file_path('inc/admin-hook.php');
// require_once get_theme_file_path('inc/admin-role.php');
// require_once get_theme_file_path('template-functions/template-function.php');
// require_once get_theme_file_path('theme-options/admin-options.php');

?>

