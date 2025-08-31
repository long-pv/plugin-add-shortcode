<?php

/**
 * Plugin Name: add-shortcode
 * Description: Thêm short code để nhúng vào website
 * Version: 1.0.0
 * Author: LogBear
 * Text Domain: add-shortcode
 */

if (!defined('ABSPATH')) exit;

define('ADD_SC_URL', plugin_dir_url(__FILE__));
define('ADD_SC_PATH', plugin_dir_path(__FILE__));

add_action('wp_enqueue_scripts', function () {
    // đăng ký trước, lấy version từ mtime để bust cache
    $css = ADD_SC_PATH . 'assets/css/main.css';
    $js  = ADD_SC_PATH . 'assets/js/main.js';
    $ver = max(
        file_exists($css) ? filemtime($css) : 0,
        file_exists($js) ? filemtime($js) : 0,
        time()
    );

    wp_register_style('addsc-main-css', ADD_SC_URL . 'assets/css/main.css', [], $ver);
    wp_register_script('addsc-main-js', ADD_SC_URL . 'assets/js/main.js', ['jquery'], $ver, true);

    // enqueue để thực sự load ra ngoài
    wp_enqueue_style('addsc-main-css');
    wp_enqueue_script('addsc-main-js');
}, 99);


// ACF Options Page
add_action('acf/init', function () {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Add shortcode setting',
            'menu_title' => 'Add shortcode setting',
            'menu_slug'  => 'add-shortcode-setting',
            'capability' => 'manage_options',
            'redirect'   => false,
            'position'   => 80,
            'autoload'   => true,
        ]);
    }
});

// Shortcode [lv_service]
function lv_service_shortcode($atts)
{
    // Bắt đầu output
    ob_start();

    // Lấy dữ liệu từ ACF Options
    $title_service = get_field('title_service', 'option');
    $list_service  = get_field('list_service', 'option');
?>

    <section class="lv_service">
        <?php if ($title_service) : ?>
            <h2 class="lv_service_title"><?php echo esc_html($title_service); ?></h2>
        <?php endif; ?>

        <?php if ($list_service) : ?>
            <div class="lv_service_list">
                <?php foreach ($list_service as $row) :
                    $link = $row['link'];
                    if ($link) : ?>
                        <a href="<?php echo esc_url($link['url']); ?>"
                            class="lv_service_item"
                            target="<?php echo esc_attr($link['target'] ?: '_self'); ?>">
                            <?php echo esc_html($link['title']); ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php
    return ob_get_clean();
}
add_shortcode('lv_service', 'lv_service_shortcode');

// Shortcode hiển thị FAQ Block từ ACF Options
function shortcode_lv_faq_block()
{
    $faqs = get_field('list_faqs', 'option'); // lấy từ ACF Options

    if ($faqs && is_array($faqs)) {
        ob_start(); ?>

        <div class="lv_faq_block">
            <?php foreach ($faqs as $faq) :
                $title   = $faq['title'] ?? '';
                $content = $faq['content'] ?? '';
            ?>
                <?php if ($title || $content) : ?>
                    <div class="lv_faq_item">
                        <?php if ($title) : ?>
                            <div class="lv_faq_item_question"><?php echo $title; ?></div>
                        <?php endif; ?>

                        <?php if ($content) : ?>
                            <div class="lv_faq_item_answer"><?php echo $content; ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

    <?php
        return ob_get_clean();
    }
    return ''; // không có dữ liệu thì trả về rỗng
}
add_shortcode('lv_faq_block', 'shortcode_lv_faq_block');

// add_latest_posts
add_shortcode('add_latest_posts', function ($atts = []) {
    $atts = shortcode_atts([
        'cols'      => 3,
        'read_more' => 'Xem thêm',
    ], $atts, 'add_latest_posts');

    // ACF: số bài viết
    $post_count = function_exists('get_field') ? (int) get_field('number_latest_posts', 'option') : 3;
    if ($post_count < 1) $post_count = 3;

    // ACF: link Xem thêm toàn bộ
    $see_more = function_exists('get_field') ? get_field('see_more', 'option') : null; // array: url,title,target

    $q = new WP_Query([
        'posts_per_page'      => $post_count,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ]);
    if (!$q->have_posts()) return '';

    wp_enqueue_style('addsc-main-css');
    wp_enqueue_script('addsc-main-js');

    ob_start(); ?>
    <div class="alp-grid cols-<?php echo (int)$atts['cols']; ?>">
        <?php while ($q->have_posts()): $q->the_post();
            $url   = get_permalink();
            $title = get_the_title();
            $date  = get_the_date(get_option('date_format'));
            $desc  = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 22);
            $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large');
            if (!$thumb) $thumb = ADD_SC_URL . 'assets/img/placeholder.jpg'; ?>
            <article class="alp-card">
                <a class="alp-thumb" href="<?php echo esc_url($url); ?>">
                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                </a>
                <h3 class="alp-title"><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></h3>
                <div class="alp-date"><?php echo esc_html($date); ?></div>
                <div class="alp-desc"><?php echo esc_html($desc); ?></div>
                <a class="alp-btn" href="<?php echo esc_url($url); ?>"><?php echo esc_html($atts['read_more']); ?></a>
            </article>
        <?php endwhile;
        wp_reset_postdata(); ?>
    </div>

    <?php if (is_array($see_more) && !empty($see_more['url'])):
        $sm_url = $see_more['url'];
        $sm_title = $see_more['title'] ?: 'Xem thêm';
        $sm_target = $see_more['target'] ?: '_self';
        $rel = ($sm_target === '_blank') ? 'noopener noreferrer' : ''; ?>
        <div class="alp-footer">
            <a class="alp-btn alp-more-btn"
                href="<?php echo esc_url($sm_url); ?>"
                target="<?php echo esc_attr($sm_target); ?>"
                <?php if ($rel) echo 'rel="' . esc_attr($rel) . '"'; ?>>
                <?php echo esc_html($sm_title); ?>
            </a>
        </div>
    <?php endif; ?>

<?php return ob_get_clean();
});


/**
 * Sticky Footer Menu from ACF Options (không cần shortcode atts)
 * Field: menu_bottom (repeater trong Options Page)
 * Sub-fields: icon (image ID), title (text), url (text)
 */
add_shortcode('tgb_footer_menu', function () {
    if (!function_exists('get_field')) {
        return ''; // chưa có ACF thì bỏ qua
    }

    $items = get_field('menu_bottom', 'option');
    if (empty($items) || !is_array($items)) {
        return '';
    }

    ob_start(); ?>
    <nav class="tgb-footer-menu" aria-label="Footer quick actions">
        <?php foreach ($items as $row):
            $title = isset($row['title']) ? trim($row['title']) : '';
            $url   = isset($row['url']) ? trim($row['url']) : '#';
            $icon  = isset($row['icon']) ? $row['icon'] : 0;
        ?>
            <a class="tgb-footer-menu__item" href="<?= esc_url($url); ?>" target="_blank" rel="noopener nofollow sponsored">
                <span class="tgb-footer-menu__icon" aria-hidden="true">
                    <?php
                    if ($icon) {
                        echo wp_get_attachment_image(
                            $icon,
                            'thumbnail',
                            false,
                            [
                                'class' => 'tgb-footer-menu__icon-image',
                                'alt'   => esc_attr($title ?: 'icon')
                            ]
                        );
                    } else {
                        echo '<span class="tgb-footer-menu__icon-placeholder"></span>';
                    }
                    ?>
                </span>
                <?php if ($title): ?>
                    <span class="tgb-footer-menu__label"><?= esc_html($title); ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
<?php
    return ob_get_clean();
});
