<?php
//data modal single course
//add booking
class Booking {
    public function __construct()
    {
        //single course
        add_action('wp_ajax_single_course', array($this, 'get_single_course'));
        add_action('wp_ajax_nopriv_single_course', array($this, 'get_single_course'));
        //add booking
        add_action('wp_ajax_add_booking', array($this, 'add_booking'));
        add_action('wp_ajax_nopriv_add_booking', array($this, 'add_booking'));
        //validate card
        add_action('wp_ajax_validate_card', array($this, 'validate_card'));
        add_action('wp_ajax_nopriv_validate_card', array($this, 'validate_card'));
        //add booking
        add_action('init', array($this, 'store_booking'));
        add_action('init', function(){
            if(isset($_GET['publish'])){
                $post_id = $_GET['publish'];
                $args = [
                    'ID' => $post_id,
                    'post_status' => 'pending'
                ];
                wp_update_post($args);
            }
            if(isset($_GET['pending'])){
                $post_id = $_GET['pending'];
                $args = [
                    'ID' => $post_id,
                    'post_status' => 'publish'
                ];
                wp_update_post($args);
            }
        });
        //mail
        add_action('phpmailer_init', array($this, 'send_smtp_email'));
        //check booking in admin list
        add_filter('manage_booking_posts_columns', array($this, 'add_columns_list_booking'));
        add_action('manage_booking_posts_custom_column', array($this, 'show_data_booking_column'),10,2);
        //restaurant
        add_filter('manage_restaurant_posts_columns', array($this, 'add_columns_list_restaurant'));
        add_action('manage_restaurant_posts_custom_column', array($this, 'show_data_restaurant_column'),10,2);
        //update or save booking
        add_action( 'acf/save_post', array($this, 'add_category_acf'), 20);
        //calendar
        add_action( 'wp_ajax_get_calendar', array($this, 'get_calendar'), 20);
        add_action( 'wp_ajax_nopriv_get_calendar', array($this, 'get_calendar'), 20);
    }
    public function get_single_course(){
        $course_id = $_REQUEST['id'];
        $course = get_post($course_id);
        ob_start();
        ?>
        <div class="wrap">
            <div id="data-modal">
                <div class="modal-header" style="height: 300px; background-image: url('<?php echo esc_url(get_field('banner_image', $course->ID)['sizes']['medium_large']);?>');background-size: cover;background-repeat: no-repeat; background-position: center center"></div>
                <div class="modal-body">
                    <div class="data">
                        <p class="txt01"><?php echo esc_html($course->post_title); ?></p>
                        <ul class="yen-data">
                            <li><p class="yen-icon01"><?php echo get_field('course_price_day', $course->ID);?>円(税別) / 人</p></li>
                            <li><p class="yen-icon02"><?php echo get_field('course_price_night', $course->ID);?>円(税別) / 人</p></li>
                        </ul>
                        <div class="content">
                            <?php echo wp_kses_post($course->post_content);?>
                        </div>
                        <a href="#" class="btn open-add-card" data-id="<?php echo esc_attr($course_id);?>">このお店を予約する</a>
                    </div>
                </div>
            </div>
            <div id="data-slick-html">
                <ul id="slider">
                    <?php if (get_field('gallery_image', $course_id)) :?>
                        <?php foreach (get_field('gallery_image', $course_id) as $key => $image):?>
                            <li class="slide-item">
                                <img data-lazy="<?php echo esc_url($image['url']); ?>" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($course->post_title); ?>">
                            </li>
                        <?php endforeach;?>
                    <?php endif;?>
                </ul>
                <ul id="thumbnail_slider">
                    <?php if (get_field('gallery_image', $course_id)) :?>
                        <?php foreach (get_field('gallery_image', $course_id) as $key => $image):?>
                            <li class="thumbnail-item">
                                <img data-lazy="<?php echo esc_url($image['url']); ?>"  src="<?php echo esc_url($image['url']); ?>"  alt="<?php echo esc_attr($course->post_title); ?>">
                            </li>
                        <?php endforeach;?>
                    <?php endif;?>
                </ul>
            </div>
            <div id="course-text">
                <p class="txt01"><?php echo $course->post_title;?></p>
                <ul class="yen-data">
                    <li><p class="yen-icon01"><?php echo get_field('course_price_day', $course_id);?>円〜 / 人</p></li>
                    <li><p class="yen-icon02"><?php echo get_field('course_price_night', $course_id);?>円〜 / 人</p></li>
                </ul>
                <div class="content">
                    <?php echo wp_kses_post($course->post_content); ?>
                </div>
            </div>
        </div>
        <?php
        $data = ob_get_clean();
        wp_send_json_success($data);
    }
    public function add_booking(){
        $postData = $_REQUEST;
        $validator = $this->validatePostBooking($postData);
        if(!$validator['success']){
            wp_send_json_success($validator);
        }
        $data = $validator['data'];
        $date = new DateTime("now", new DateTimeZone('Asia/Tokyo') );
        $time_booking = $date->format('m/d/Y h:i a');
        $customer_id = 1;
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $customer_id = $user->ID;
        }
        $post_data = array(
            'post_title'    => '#Booking | ' . $time_booking,
            'post_type'     => 'booking',
            'post_status'   => 'publish'
        );
        $post_id = wp_insert_post( $post_data );
        update_field('time_booking', $time_booking, $post_id);
        update_field('time', $data['time'], $post_id);
        update_field('number_people', $data['number_people'], $post_id);
        update_field('stay_hour', $data['stay_hour'], $post_id);
        update_field('course', $data['course'], $post_id);
        update_field('restaurant', $data['restaurant'], $post_id);
        update_field('customer_name', $data['customer_name'], $post_id);
        update_field('customer_phone', $data['customer_phone'], $post_id);
        update_field('customer', $customer_id, $post_id);

        wp_send_json_success([
            'success' => 1,
            'data' => [
                'order_id' => $post_id,
                'message' => 'Create booking success !',
                'checkout_data' => base64_encode(json_encode(array('customer_id' => $customer_id)))
            ]
        ]);
    }
    public function store_booking(){
        try {
            if (isset($_POST['add-booking']) && isset($_POST['hash'])) {
                $data = json_decode(base64_decode($_POST['hash']));
                $booking = json_decode(base64_decode($data->booking));
                $customer = json_decode(base64_decode($data->customer));
                $card = json_decode(base64_decode($data->card));
                $additional = json_decode(base64_decode($data->additional));
                global $date;
                $time_booking = $date->format('Y-m-d H:i');
                $post_data = array(
                    'post_title' => '#Booking | ' . $time_booking,
                    'post_type' => 'booking',
                    'post_status' => 'publish',
                    'post_author' => get_post_field( 'post_author', $booking->restaurant_id )
                );
                $bookingData = [
                    ['key' => 'restaurant', 'value' => $booking->restaurant_id, 'required' => true],
                    ['key' => 'course', 'value' => $booking->course_id, 'required' => true],
                    ['key' => 'number_people_booking', 'value' => $booking->number_people, 'required' => true],
                    ['key' => 'coming_date', 'value' => $booking->time, 'required' => true],
                    ['key' => 'coming_time_booking', 'value' => $booking->coming_time, 'required' => true],
                    ['key' => 'time_booking', 'value' => $time_booking, 'required' => true],
                    ['key' => 'amount', 'value' => $booking->amount, 'required' => true],
                    ['key' => 'total', 'value' => $booking->total * 1.1, 'required' => true]
                ];
                $this->validateMeta($bookingData);
                $customerData = [
                    ['key' => 'customer_name_1', 'value' => $customer->name1, "required" => true],
                    ['key' => 'customer_name_2', 'value' => $customer->name2, "required" => true],
                    ['key' => 'phone_number', 'value' => $customer->phone_number, "required" => true],
                    ['key' => 'email', 'value' => $customer->email, "required" => true],
                ];
                $this->validateMeta($customerData);
                //var_dump($bookingData);
                //die();
                $additionalData = [
                    ['key' => 'note', 'value' => $additional->note],
                ];
                $this->validateMeta($additionalData);
                //validate card
                $validate_card = $this->validate_card($card);
                $card_id = $validate_card['data']['token_id'];
                //charge booking
                $is_charge = $this->charge_booking($card_id, $booking->total * 1.1);
                $charge_id = $is_charge['data']['charge_id'];

                $post_id = wp_insert_post($post_data);

                update_post_meta($post_id, 'charge_id', $charge_id);
                update_post_meta($post_id, 'hidden_status', 'planned');
                $this->updateMeta($post_id, $bookingData);
                $this->updateMeta($post_id, $customerData);
                $this->updateMeta($post_id, $additionalData);

                $_SESSION['booking-notice'] = true;
                $_SESSION['success'] = [
                    'message' => 'Booking success!'
                ];

                $this->sendMail($customer, $booking, $additional);

                $this->redirect();
            }
        }catch(Exception $ex){
            write_log($ex->getMessage());

            $_SESSION['booking-notice'] = true;
            $_SESSION['error'] = [
                'message' => '申し込み手続き中にエラーが発生しました。しばらくしてからもう一度お試しください。'
            ];

            $this->redirect();
        }
    }
    public function updateMeta($post_id, $meta = []){
        foreach ($meta as $key => $field){
            update_field($field['key'], $field['value'], $post_id);
        }
    }
    public function add_columns_list_booking($columns){
        $columns = array_merge($columns, array(
            'customer_name1' => esc_html__('お名前', 'fishing'),
            'customer_name2' => wp_kses_post('お名前<br/>（カナ）', 'fishing'),
            'phone' => esc_html__('電話番号', 'fishing'),
            'email' => esc_html__('メールアドレス', 'fishing'),
            'coming_date' => esc_html('日程', 'fishing'),
            'number_people' => esc_html('人数', 'fishing'),
            'course_name' => esc_html('コース', 'fishing'),
            'amount' => esc_html('小計', 'fishing'),
            'total_amount' => wp_kses_post('合計金額<br/>（税別）', 'fishing'),
            'status' => esc_html__('ステータス', 'fishing'),
        ));
        unset($columns['date']);
        unset($columns['author']);
        //$columns = array_merge($columns, array('author' => 'Author', 'date' => 'Date'));
        return $columns;
    }
    public function show_data_booking_column($column, $post_id)
    {
        //global $post;
        if($column == 'customer_name1'){
            echo get_field('customer_name_1', $post_id);
        }
        if($column == 'customer_name2'){
            echo get_field('customer_name_2', $post_id);
        }
        if($column == 'phone'){
            echo get_field('phone_number', $post_id);
        }
        if($column == 'email'){
            echo get_field('email', $post_id);
        }
        if($column == 'coming_date'){
            echo get_field('coming_date', $post_id) . ' ' . get_field('coming_time_booking', $post_id);
        }
        if($column == 'number_people'){
            echo get_field('number_people_booking', $post_id) . ' 名';
        }
        if($column == 'course_name'){
            echo get_the_title(get_field('course', $post_id));
        }
        if($column == 'amount'){
            echo get_field('amount', $post_id) . ' 円';
        }
        if($column == 'total_amount'){
            echo get_field('total', $post_id) . ' 円';
        }
        if($column == 'status'){
            if(!get_field('status', $post_id)){
                update_field('status', 'planned', $post_id);
            }
            $hidden_status = $status = 'planned';
            $title = '予約済み';
            $fieldStatus = get_field('status', $post_id);
            if(isset($fieldStatus['value'])){
                $status = $fieldStatus['value'];
            }
            if(isset($fieldStatus['label'])){
                $title = $fieldStatus['label'];
                if($status == 'cancel'){
                    $title = 'キャンセル済み';
                }
            }
            if(!get_post_meta($post_id, 'hidden_status', true)){
                update_post_meta($post_id, 'hidden_status', $status);
            }
            if($hidden = get_post_meta($post_id, 'hidden_status', true)){
                $hidden_status = $hidden;
            }

            if($status == 'planned' && $hidden_status == 'planned'){
                global $date;
                $now = $date->format('Y-m-d H:i');
                $coming_date = get_field('coming_date', $post_id) .' 23:58';
                $charge_id = base64_decode(get_post_meta($post_id, 'charge_id', true));
                if($now >= $coming_date){
                    $data = PayJp('Charges@confirmCharge',['charge_id' => $charge_id]);
                    if($data['success']){
                        $charge_id_response = base64_decode($data['data']['charge_id']);
                        if($charge_id == $charge_id_response){
                            update_field('status', 'paid', $post_id);
                            update_post_meta($post_id, 'hidden_status', 'paid');
                            $title = '支払い済み';
                        }
                    }
                    if(!$data['success']){
                        update_field('status', 'cancel', $post_id);
                        update_post_meta($post_id, 'hidden_status', 'cancel');
                        $title = 'キャンセル';
                        $time_checked = $date->format('Y-m-d H:i');
                        $data['data']['error']['notice'] = 'check at: ' . $time_checked . ', Order_id: ' . $post_id;
                        write_log($data);
                    }
                }
            }
            echo $title;
        }

    }
    public function add_columns_list_restaurant($columns){
        global $current_user;
        $roles = $current_user->roles;
        $columns = array_merge($columns, array(
            'title' => esc_html__('レストラン名', 'fishing'),
            'total_order' => esc_html__('合計注文', 'fishing'),
            'total_cancel' => esc_html__('番号予約をキャンセル', 'fishing'),
            'total_planned' => esc_html__('計画された', 'fishing'),
            'total_success' => wp_kses_post('数成功予約', 'fishing'),
            'total_refund' => esc_html__('払い戻し', 'fishing'),
            'revenue' => esc_html__('総収入', 'fishing'),
        ));
        if(in_array('administrator', $roles)){
            $columns = array_merge(
                ['titles' => esc_html__('レストラン名', 'fishing')],
                $columns
            );
            unset($columns['title']);
        }
        unset($columns['date']);
        unset($columns['author']);
        unset($columns['tags']);
        unset($columns['cb']);
        //$columns = array_merge($columns, array('title' => 'レストラン名'));
        return $columns;
    }
    public function show_data_restaurant_column($column, $post_id)
    {
        global $post;
        $author_id = $post->post_author;
        $current_v = isset($_GET['admin_filter_month'])? $_GET['admin_filter_month'] : '';
        if($column == 'titles'){
            $status = [
                'publish' => '非公開にする',
                'pending' => '公開にする'
            ];
            echo get_the_title($post_id) . '<br/>';
            echo '<a class="restaurant-deactive ' . $post->post_status . '" href="?post_type=restaurant&'.$post->post_status.'='. $post_id.'">'.$status[$post->post_status].'</a>';
        }
        if($column == 'total_order'){
            $args = array(
                'author' => $author_id,
                'post_type'   => 'booking',
                'post_status' => 'publish',
                'fields' => array('ID'),
                'date_query' => array(
                    array(
                        'year'  => date('Y'),
                        'month' => $current_v,
                    ),
                ),
            );
            $data = new WP_Query($args);
            echo esc_html($data->found_posts);
        }
        if($column == 'total_cancel'){
            $args = array(
                'author' => $author_id,
                'post_type'   => 'booking',
                'post_status' => 'publish',
                'fields' => array('ID'),
                'meta_query' => array(
                    array(
                        'key' => 'status',
                        'value' => 'cancel',
                        'compare' => '=',
                    )
                ),
                'date_query' => array(
                    array(
                        'year'  => date('Y'),
                        'month' => $current_v,
                    ),
                ),
            );
            $data = new WP_Query($args);
            echo esc_html($data->found_posts);
        }
        if($column == 'total_planned'){
            $args = array(
                'author' => $author_id,
                'post_type'   => 'booking',
                'post_status' => 'publish',
                'fields' => array('ID'),
                'meta_query' => array(
                    array(
                        'key' => 'status',
                        'value' => 'planned',
                        'compare' => '=',
                    )
                ),
                'date_query' => array(
                    array(
                        'year'  => date('Y'),
                        'month' => $current_v,
                    ),
                ),
            );
            $data = new WP_Query($args);
            echo esc_html($data->found_posts);
        }
        if($column == 'total_success'){
            $args = array(
                'author' => $author_id,
                'post_type'   => 'booking',
                'post_status' => 'publish',
                'fields' => array('ID'),
                'meta_query' => array(
                    array(
                        'key' => 'status',
                        'value' => 'paid',
                        'compare' => '=',
                    )
                ),
                'date_query' => array(
                    array(
                        'year'  => date('Y'),
                        'month' => $current_v,
                    ),
                ),
            );
            $data = new WP_Query($args);
            echo esc_html($data->found_posts);
        }
        if($column == 'total_refund'){
            $args = array(
                'author' => $author_id,
                'post_type'   => 'booking',
                'post_status' => 'publish',
                'fields' => array('ID'),
                'meta_query' => array(
                    array(
                        'key' => 'status',
                        'value' => 'refund',
                        'compare' => '=',
                    )
                ),
                'date_query' => array(
                    array(
                        'year'  => date('Y'),
                        'month' => $current_v,
                    ),
                ),
            );
            $data = new WP_Query($args);
            echo esc_html($data->found_posts);
        }
        if($column == 'revenue'){
            $args = array(
                'author' => $author_id,
                'post_type'   => 'booking',
                'post_status' => 'publish',
                'fields' => array('ID'),
                'meta_query' => array(
                    array(
                        'key' => 'status',
                        'value' => 'paid',
                        'compare' => '=',
                    )
                ),
                'date_query' => array(
                    array(
                        'year'  => date('Y'),
                        'month' => $current_v,
                    ),
                ),
            );
            $data = new WP_Query($args);
            $total = 0;
            foreach ($data->posts as $key => $value){
                $total += intval(get_field('total', $value->ID));
            }
            echo $total . ' 円';
        }
    }
    public function add_category_acf( $post_id ) {
        $post_type = get_post_type( $post_id );
        if ( $post_type == 'booking') {
            $hidden_status = get_post_meta($post_id, 'hidden_status', true);
            $status = 'planned';
            if(get_field('status', $post_id)){
                $status = get_field('status', $post_id)['value'];
            }
            if($hidden_status == 'planned'){
                //not paid
                //not refund
                $update_hidden = 'planned';
                if($status == 'paid'){
                    update_field('status', $update_hidden, $post_id);
                }
                //cancel
                if($status == 'cancel'){
                    $charge_id = base64_decode(get_post_meta($post_id, 'charge_id', true));
                    $is_cancel = $this->updateCharge($post_id, $charge_id);
                    if($is_cancel){
                        $update_hidden = 'cancel';
                    }
                    if(!$is_cancel){
                        update_field('status', 'planned', $post_id);
                    }
                }
                update_post_meta($post_id, 'hidden_status', $update_hidden);
            }
            if($hidden_status == 'paid'){
                $update_hidden = 'paid';
                //not planned
                //not cancel
                if($status == 'planned' || $status == 'cancel'){
                    update_field('status', $update_hidden, $post_id);
                }
            }
            if($hidden_status == 'cancel'){
                $update_hidden = 'cancel';
                //not planned
                //not paid
                if($status == 'planned' || $status == 'paid'){
                    update_field('status', $update_hidden, $post_id);
                }
            }
        }
    }
    public function refund($post_id, $charge_id, $amount){
        $data = PayJp('Charges@refundCharge',['charge_id' => $charge_id, 'amount' => $amount]);
        if(!$data['success']){
            global $date;
            $time_checked = $date->format('Y-m-d H:i');
            $data['data']['error']['notice'] = 'refund at: ' . $time_checked . ', Order_id: ' . $post_id;
            write_log($data);
        }
        return $data['success'];
    }
    public function updateCharge($post_id, $charge_id){
        $data = PayJp('Charges@cancelCharge',['charge_id' => $charge_id]);
        if(!$data['success']){
            global $date;
            $time_checked = $date->format('Y-m-d H:i');
            $data['data']['error']['notice'] = 'cancel at: ' . $time_checked . ', Order_id: ' . $post_id;
            write_log($data);
        }
        return $data['success'];
    }
    public function validateMeta($meta = []){
        foreach ($meta as $key => $field){
            if(isset($field['required']) && !$field['value']){
                $_SESSION['booking-notice'] = true;
                $message = "Field " . $field['$key'] . " is required but empty!.";
                $_SESSION['error'] = [
                    'message' => $message
                ];
                $field['message'] = $message;
                write_log($field);
                $this->redirect();
                break;
            }
        }
    }
    public function redirect($page = 'notice'){
        $page = get_page_by_path($page);
        wp_redirect(get_permalink($page));
        exit();
    }
    public function charge_booking($card, $total){
        $token_id = base64_decode($card);
        $charge = [
            "card" => $token_id,
            "amount" => $total
        ];
        $data = PayJp('Charges@makeCharge', $charge);
        if(!$data['success']){
            $error = $data['data']['error'];
            $_SESSION['booking-notice'] = true;
            $error['message'] = esc_html("Booking false, Please check your card information or amount in your card!");
            $_SESSION['error'] = $error;
            write_log($data);
            $this->redirect();
        }
        if($data['success']) {
            return $data;
        }
    }
    public function sendMail($customer, $booking, $additional){
        global $current_user;
        $course_name = get_the_title($booking->course_id);
        $course_content = $post = get_post($booking->course_id)->post_content;
        $body = "
            <p>-----------------------------------------------------------------------</p>
            <p>※本メールは、自動的に配信しています。</p>
            <p>こちらのメールは送信専用のため、直接ご返信いただいてもお問い合わせにはお答えできませんので、あらかじめご了承ください。</p>
            <p>-----------------------------------------------------------------------</p>
            <br>
            <p>このたびは、釣りたべをご予約いただき誠にありがとうございます。</p>
            <p>ご予約いただいた内容をお知らせします。</p>
            <br>
            <p>日程：$booking->time $booking->coming_time</p>
            <p>人数：$booking->number_people 人</p>
            <p>コース：$course_name</p>
            <div>$course_content</div>
            <p>お名前：$customer->name1</p>
            <p>お名前（カナ)：$customer->name2</p>
            <p>電話番号：$customer->phone_number</p>
            <p>メールアドレス：$customer->email</p>
            <br>
            <p>お店へのご要望</p>
            <p>" . wp_kses_post($additional->note). "</p>
            <br>
            <p>料金明細</p>
            <p>・小計：$booking->amount 円 x $booking->number_people 人</p>
            <p>・合計金額（税別) ：$booking->total 円</p>
            <br>
            <div>".wp_kses_post(get_field('caution_i', $booking->restaurant_id))."</div>
            <br>
            <div>".wp_kses_post(get_field('caution_ii', $booking->restaurant_id))."</div>
            <h3>釣りたべ</h3>";
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($customer->email, esc_html('釣りたべ 予約確認'), $body, $headers);
        wp_mail($current_user->user_email, esc_html('釣りたべ 予約確認'), $body, $headers);
    }
    public function send_smtp_email($mailer)
    {
        $mailer->isSMTP();
        $mailer->SMTPAuth = true;
        $mailer->Host = get_option('mailserver_url', 'smtp.googlemail.com');

        $mailer->Port = get_option('mailserver_port', '465');

        $mailer->Username = get_option('mailserver_login', 'luutuanvutest@gmail.com');

        $mailer->Password = get_option('mailserver_pass', 'alolebmeixvictpg');

        $mailer->SMTPSecure = 'ssl';

        $mailer->From = get_option('send_receive_email', 'turitabeinc@gmail.com');

        $mailer->FromName = get_bloginfo();
    }
    public function validate_card($card){
        $cardData = [
            "number" => $card->number,
            "exp_month" => substr($card->expire, 0,2),
            "exp_year" => substr($card->expire, 2,4),
            "name" => $card->name
        ];
        $data = PayJp('Token@genToken', $cardData);
        if(!$data['success']){
            $error = $data['data']['error'];
            $_SESSION['booking-notice'] = true;
            $error['message'] = esc_html("注文できませんでした。クレジットカード情報をご確認ください。");
            $_SESSION['error'] = $error;
            write_log($data);
            $this->redirect();
        }
        if($data['success']) {
            return $data;
        }
    }
    public function get_calendar(){
        $year = $_REQUEST['year'];
        $month = $_REQUEST['month'];
        $restaurant_id = $_REQUEST['restaurant_id'];
        $current = $_REQUEST['current'];
        $html = calendar($restaurant_id, $month, $year, $current);
        wp_send_json_success(json_encode($html));
    }
    public function validatePostBooking($postData){
        $response = [];
        foreach ($postData as $index =>  $field){
            foreach ($field as $key => $value){
                if(!$postData[$index][$key]){
                    return [
                        'success' => 0,
                        'message' => "Field '$key' is required but empty!",
                        'data' => null
                    ];
                    break;
                }
                $response[$key] = esc_html($value);
            }
        }
        return [
            'success' => 1,
            'message' => null,
            'data' => $response
        ];
    }

}
new Booking();