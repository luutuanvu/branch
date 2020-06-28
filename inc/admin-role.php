<?php
/**
 * Created by PhpStorm.
 * User: jimmee
 * Date: 4/18/20
 * Time: 9:17 PM
 */
remove_role('restaurant_own');
add_role('restaurant_own', esc_html__('レストラン管理者', 'fishing'), [
    'read' => true,
    'edit_posts' => true,
    'delete_posts' => true,
    'upload_files' => true,

    'edit_course' => true,
    'delete_course' => true,
    'read_course' => true,
    'edit_courses' => true,
    'publish_courses' => true,
    'delete_published_courses' => true,
    'edit_published_courses' => true,
    'delete_courses' => true,

    'edit_restaurant' => true,
    'read_restaurant' => true,
    'edit_restaurants' => true,

    'edit_booking' => true,
    'delete_booking' => true,
    'read_booking' => true,
    'edit_bookings' => true,
    'publish_bookings' => true,
    'delete_published_bookings' => true,
    'edit_published_bookings' => true,
    'delete_bookings' => true,
]);
//remove_role('master');