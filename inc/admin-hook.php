<?php
/**
 * Created by PhpStorm.
 * User: jimmee
 * Date: 4/18/20
 * Time: 9:05 PM
 */
//sort custom post type
add_filter('pre_get_posts', 'admin_order_post_type', 5 );
function admin_order_post_type( $wp_query ) {
    if (is_admin()) {
        global $current_user;
        $roles = $current_user->roles;
        $post_type = $wp_query->query['post_type'];
        switch($post_type){
            case 'booking':
                $wp_query->set('orderby', 'ID');
                $wp_query->set('order', 'DESC');
                if(in_array('restaurant_own', $roles)){
                    $wp_query->set( 'author', $current_user->ID );
                }
                break;
            case 'restaurant':
                if(in_array('restaurant_own', $roles)){
                    $wp_query->set( 'author', $current_user->ID );
                }
                break;
            case 'course':
            case 'attachment':
                $wp_query->set('orderby', 'ID');
                $wp_query->set('order', 'DESC');
                if(in_array('restaurant_own', $roles)){
                    $wp_query->set( 'author', $current_user->ID );
                }
                break;
            default:
        }
    }
    return;
}
//add title to content editor restaurant
add_action( 'edit_form_after_title', 'myprefix_edit_form_after_title' );
function myprefix_edit_form_after_title() {
    global $post;
    if($post->post_type == 'restaurant'){
        echo '<h2>レストランについてのご紹介</h2>';
    }
}
//show user in master role
add_action( 'pre_get_users', 'get_user_created_by_master' );
function get_user_created_by_master( $query ) {
    //Check that we are in admin otherwise return
    if( !is_admin() ) {
        return;
    }
    global $current_user;
    // get all user of this admin create
    $meta = array(
        array(
            'key'=>'auth_id',
            'value'=> $current_user->ID,
            'compare'=>'='
        ),
    );
    if(in_array('master', $current_user->roles)) {
        $query->set('meta_query', $meta);
    }
    return $query;
}
//add filter field to post type admin
add_action( 'restrict_manage_posts', 'add_filter_by_month');
function add_filter_by_month(){
    if (!is_admin()){
        return;
    }
    global $wpdb, $table_prefix;
    $post_type = (isset($_GET['post_type'])) ? $_GET['post_type'] : 'post';
    //only add filter to post type restaurant
    if ($post_type == 'restaurant'){
        $values = array(
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        );
        //give a unique name in the select field
        ?><select name="admin_filter_month">
        <option value="">All month</option>
        <?php
        $current_v = isset($_GET['admin_filter_month'])? $_GET['admin_filter_month'] : '';
        foreach ($values as $key => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                $key,
                $key == $current_v? ' selected="selected"':'',
                $label
            );
        }
        ?>
        </select>
        <?php
    }
}
//parse query
//add_filter( 'parse_query', 'filter_restaurant');
function filter_restaurant ($query){
    global $pagenow;
    $post_type = (isset($_GET['post_type'])) ? $_GET['post_type'] : 'post';
    if ($post_type == 'restaurant' && $pagenow=='edit.php' && isset($_GET['admin_filter_year']) && !empty($_GET['admin_filter_year'])) {
        $query->query_vars['year'] = $_GET['admin_filter_year'];
    }
}
//insert new user to wp_users
add_action( 'user_register', 'master_create_restaurant_own', 10, 1 );
function master_create_restaurant_own($user_id){
    global $current_user;
    $admin_roles = $current_user->roles;
    $user = get_user_by('ID', $user_id);
    $user_roles = $user->roles;
    if(in_array('restaurant_own', $user_roles)){
        $user_nicename = $user->user_nicename;
        $restaurant = [
            'post_title' => ucfirst($user_nicename) . ' restaurant',
            'post_name' => ucfirst($user_nicename),
            'post_type' => 'restaurant',
            'post_status' => 'publish',
            'post_author' => $user_id
        ];
        $admin_id = $current_user->ID;
        $user->remove_role('subscriber');
        $user->add_role('restaurant_own');
        update_user_meta($user_id, 'auth_id', $admin_id);
        $post_id = wp_insert_post($restaurant);
    }
};
//remove permalink post type booking
add_filter('get_sample_permalink_html', 'my_hide_permalinks', 10, 5);
function my_hide_permalinks($return, $post_id, $new_title, $new_slug, $post)
{
    if($post->post_type == 'booking') {
        return '';
    }
    return $return;
}
function custom_remove_user( $user_id ) {
    //delete post
    $args = [
        'post_type' => array('restaurant', 'course', 'media'),
        'status' => 'published',
        'author' => $user_id,
        'field' => array('ID', 'post_title'),
        'posts_per_page' => -1
    ];
    $data = new WP_Query($args);
    foreach ($data->posts as $key => $post){
        wp_delete_post($post->ID);
    }
    //delete file
    $args = array('post_type' => 'attachment', 'posts_per_page' => -1, 'author' => $user_id);
    $attachments = get_posts($args);
    if($attachments){
        foreach($attachments as $attachment){
            wp_delete_post($attachment->ID);
        }
    }
}
add_action( 'delete_user', 'custom_remove_user', 10 );
//save post update restaurant
add_action( 'save_post', 'set_post_default_category', 10,3 );
function set_post_default_category( $post_id, $post, $update ) {
    // Only set for post_type = post!
    if ( $post->post_type != 'course' ) {
        return;
    }
    global  $current_user;
    $args = array('post_type' => 'restaurant', 'posts_per_page' => 1, 'author' => $current_user->ID);
    $restaurant_id = get_posts($args)[0]->ID;
    update_field('course_related_restaurant', $restaurant_id, $post_id);
}
function wpse_custom_menu_order( $menu_ord ) {
    if ( !$menu_ord ) return true;
    return [
        "index.php",
        "separator1",
        //"edit.php",
        "edit.php?post_type=restaurant",
        'top-header',
        "edit.php?post_type=course",
        "edit.php?post_type=booking",
        "users.php",
        "edit.php?post_type=page",
        "upload.php",
        //"edit-comments.php",
        "separator2",
        "themes.php",
        "plugins.php",

        "tools.php",
        "options-general.php",
        "edit.php?post_type=acf-field-group",
        "separator-last",
    ];
}
add_filter( 'custom_menu_order', 'wpse_custom_menu_order', 10, 1 );
add_filter( 'menu_order', 'wpse_custom_menu_order', 10, 1 );
function remove_admin_menus() {
    global $current_user;
    $roles = $current_user->roles;
    remove_menu_page( 'edit-comments.php' );
    remove_menu_page( 'edit.php' );
    if(in_array('restaurant_own', $roles)){
        remove_menu_page( 'tools.php' );
    }
}
add_action( 'admin_menu', 'remove_admin_menus' );
remove_role( 'subscriber' );
remove_role( 'contributor' );
remove_role( 'author' );
remove_role( 'editor' );
//show message when deny login
add_action('init', function(){
    if($_GET['msg']){
        function my_login_logo_url_title() {
            ?>
            <div id="login_error">
                <strong>エラー</strong>: ログインアカウント情報が正しくありません。管理者に連絡してください。<br>
            </div>
            <?php
        }
        add_filter( 'login_message', 'my_login_logo_url_title' );
    }
});
//deny own private restaurant login
add_action( 'wp_login', 'redirect_on_login', 10, 2); // hook failed login
function redirect_on_login($user_login, $user) {
    $args = [
        'author' => $user->ID,
        'post_type' => 'restaurant',
        'post_status' => 'pending'
    ];
    $restaurant = new WP_Query($args);
    if($restaurant->post_count > 0){
        wp_logout();
        wp_redirect(home_url('/wp-login.php?msg=pending'));
        exit;
    }
}





