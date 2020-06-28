<?php
function my_relationship_query( $args, $field, $post ) {
    $args['author'] = get_current_user_id();
    return $args;
}
add_filter('acf/fields/relationship/query/key=<field_key>', 'my_relationship_query', 10, 3);