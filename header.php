<?php ob_start(); ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="google-site-verification" content="iKJdQFZdK-KSnoh1b6oygwCSuGlQIxcDOTvMxsWq2P4" />
        <?php if(is_singular('restaurant') || is_home() || is_front_page()){?>
            <meta name="title" content="<?php echo get_field('top_seo_title','top_options');?>">
            <meta name="description" content="<?php echo get_field('top_seo_content','top_options');?>">
        <?php } ?>
        <?php wp_head(); ?>
        <?php
        global $post;
        $page_name = $post->post_name;
        $open_sidebar_page = ['booking-confirm', 'payment-confirm', 'booking-notice'];
        if(is_home() || is_front_page()){
            ?>
            <title>釣りたべ-釣った魚を調理してくれる店を探せる</title>
        <?php } ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-163769354-1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'UA-163769354-1');
        </script>
    </head>

<body <?php body_class(); ?>>
<?php if(is_singular('restaurant')){?>
    <header>
        <h1><a href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo get_template_directory_uri() .'/assets/themes/img/logo.svg'; ?>" width="100%"></a></h1>
    </header>
    <div class="overlay" id="js__overlay"></div>
    <nav class="side-menu" id="js__sideMenu">
        <?php
        switch($page_name){
            case 'booking-confirm':
                booking_confirm();
                break;
            case 'payment-confirm':
                payment_confirm();
                break;
            case 'booking-notice':
                booking_success();
                break;
            default:
                echo booking_form();
                break;
        }
        ?>
    </nav>
    <div class="side-menu-btn" id="js__sideMenuBtn">
        <div class="ellipsis-v">
            <span class="point top"></span>
            <span class="point mid"></span>
            <span class="point bot"></span>
        </div>
        <p class="ellipsis-txt">予約する</p>
    </div>
<?php }?>
    <div class="wrapper <?php echo is_home() || is_front_page() ? 'top-page':''; ?>">
    <?php if(is_singular('restaurant')){?><div class="bg-dark"></div><?php }?>
<?php echo !is_singular('restaurant') ? fishing_header() : ''; ?>