<?php
if(!function_exists('get_course_of_restaurant')){
    function get_course_of_restaurant($restaurant_id = null){
        $data = get_posts([
            'post_type' => 'course',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'DESC',
            'meta_query' => [
                [
                    'key' => 'course_related_restaurant',
                    'compare' => '=',
                    'value' => $restaurant_id
                ]
            ]
        ]);
        return $data;
    }
}
//write log to debug.log
if (!function_exists('write_log')) {
    function write_log($log) {
        if (WP_DEBUG === true) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

}
function responseSuccess($data = [], $message = null){
    return [
        'success' => 1,
        'data' => $data,
        'message' => $message
    ];
}
function responseFail($data = [], $message = null){
    $data = isset($data['error']) ? $data : ['error' => $data];
    return [
        'success' => 0,
        'data' => $data,
        'message' => $message
    ];
}