<?php
/*---------------------- Admin Bar ----------------------------- */
add_action('admin_bar_menu', 'mf_remove_items', 999);
function mf_remove_items($wp_admin_bar)
{
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('customize');
    $wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('search');

    $user_id = get_current_user_id();
    if($user_id){
        $user = get_userdata($user_id);
        $user_roles = $user->roles;
        if(in_array('subcriber', $user_roles, true)){
            $wp_admin_bar->remove_node('new-content');
        }
    }
}

/*---------------------- Login Screen ---------------------- */
add_filter('login_headerurl', 'mf_login_header_url');
function mf_login_header_url()
{
    return esc_url(site_url('/'));
}

add_filter('login_headertext', 'mf_login_header_text');
function mf_login_header_text()
{
    return get_bloginfo('name');
}

add_action('login_enqueue_scripts', 'mf_login_script');
function mf_login_script()
{
    ?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: none;
            width: 300px;
            text-indent: 0;
            font-size: 35px;
            font-style: italic;
        }
    </style>
<?php
}

/*--------------------------Admin Dashboard ---------------------- */ 
add_filter('admin_footer_text', 'mf_admin_footer');
function mf_admin_footer($output){
    $output = __("Let's go fishing!", "fishing");
    return $output;
}

add_action('wp_dashboard_setup', 'mf_dashboard_widgets');
function mf_dashboard_widgets()
{
    global $wp_meta_boxes;
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
}


