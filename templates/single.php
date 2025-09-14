<?php
get_header();

$banner_image = get_field('single_post_banner_image', 'option');
$background_content = get_field('single_post_background_content', 'option');
?>

<?php if ($banner_image) : ?>
    <div class="lv_single_post_banner">
        <?php echo wp_get_attachment_image($banner_image, 'full'); ?>
    </div>
<?php endif; ?>

<div class="lv_single_post_bread">
    <div class="container">
        <?php wp_breadcrumbs(); ?>
    </div>
</div>

<div class="lv_single_post_tags">
    <div class="container">
        <div class="tagsList">
            <a class="tagsList__item">Tin tức 8kbet</a>
            <a class="tagsList__item">Thủ thuật soi cầu</a>
            <a class="tagsList__item">Tài Xiu</a>
        </div>

        <h1 class="lv_single_post_title">
            <?php the_title(); ?>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col medium-8 small-12 large-8">
        <div class="col-inner">
            <div class="lv_single_post_content_editor">
                <?php echo wp_get_attachment_image($background_content, 'full', false, ['class' => 'img_bg']); ?>
                <div class="content_editor">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col medium-4 small-12 large-4">
        <div class="col-inner">
            <div class="recentNews__mainContent">
                <div class="recentNews__header">
                    <span class="recentNews__header__text">TIN MỚI NHẤT</span>
                </div>

                <div class="recentNews__content">
                    <div class="recentNews__imageWrapper">
                        <img src="your-image-source.jpg" alt="Content Image" class="recentNews__image" />
                    </div>

                    <div class="recentNews__details">
                        <h2 class="recentNews__details__title">THỦ THUẬT SOI CẦU MIỀN TRUNG MÀ NGƯỜI CHƠI NÊN BIẾT</h2>

                        <div class="recentNews__footer">
                            <span class="recentNews__footer__text">Tài Xỉu</span>
                            <span class="recentNews__footer__author">admin 8kbets</span>
                            <span class="recentNews__footer__date">25/09/2001</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
