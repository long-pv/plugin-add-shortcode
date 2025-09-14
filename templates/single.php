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
            <div class="newsSection">
                <div class="newsSection__header">
                    <img src="<?php echo ADD_SC_URL . 'assets/img/img_1.png' ?>" class="newsSection__header__img" alt="">
                </div>
                <div class="newsSection__content">
                    <img src="<?php echo ADD_SC_URL . 'assets/img/img_3.png' ?>" class="newsSection__content__bg" alt="">
                    <a href="#" class="newsSection__content__inner">
                        <img src="<?php echo ADD_SC_URL . 'assets/img/img_4.png' ?>" class="newsSection__content__img" alt="">
                        <div class="newsSection__content__info">
                            <h3 class="newsSection__content__info__title">THỦ THUẬT SOI CẦU MIỀN TRUNG MÀ NGƯỜI CHƠI NÊN BIẾT</h3>
                            <div class="newsSection__content__info__footer">
                                <span class="newsSection__content__info__footer__type">Tài Xỉu</span>
                                <span class="newsSection__content__info__footer__author">admin 8kbet</span>
                                <span class="newsSection__content__info__footer__date">25/09/2001</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
