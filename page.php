<?php get_header('booking'); ?>
<div class="container">
    <div class="page-content" id="reservation">
        <div >
            <?php while (have_posts()) : the_post(); ?>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </div>
    </div>
</div><!-- wrapper -->

<?php get_footer('booking'); ?>