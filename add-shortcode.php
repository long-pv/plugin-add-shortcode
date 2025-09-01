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

// Shortcode: [lv_menu_pc]
function lv_menu_pc_shortcode()
{
    $menu_items = get_field('menu_pc', 'option');

    if (!$menu_items || !is_array($menu_items)) {
        return '';
    }

    ob_start();
?>
    <nav class="lv_menu">
        <ul class="lv_menu_list">
            <?php foreach ($menu_items as $item): ?>
                <?php
                $link_primary = isset($item['link_primary']) ? $item['link_primary'] : null;
                $submenu      = isset($item['submenu']) ? $item['submenu'] : null;
                $image        = isset($item['image']) ? $item['image'] : null; // nếu bạn có thêm field hình ảnh
                ?>

                <?php if ($link_primary && isset($link_primary['url'], $link_primary['title'])): ?>
                    <li class="lv_menu_item">
                        <a href="<?php echo $link_primary['url']; ?>"
                            class="lv_menu_link"
                            <?php if (!empty($link_primary['target'])): ?>target="<?php echo $link_primary['target']; ?>" <?php endif; ?>>

                            <?php
                            // Hiển thị ảnh nếu có
                            if ($image) {
                                echo wp_get_attachment_image($image, 'full', false, ['class' => 'lv_menu_icon']);
                            }
                            ?>
                            <?php echo $link_primary['title']; ?>

                            <?php if ($submenu && is_array($submenu)): ?>
                                <span class="lv_menu_arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="12" height="12">
                                        <path d="M480 224C492.9 224 504.6 231.8 509.6 243.8C514.6 255.8 511.8 269.5 502.7 278.7L342.7 438.7C330.2 451.2 309.9 451.2 297.4 438.7L137.4 278.7C128.2 269.5 125.5 255.8 130.5 243.8C135.5 231.8 147.1 224 160 224L480 224z" />
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </a>

                        <?php if ($submenu && is_array($submenu)): ?>
                            <ul class="lv_menu_submenu">
                                <?php foreach ($submenu as $sub): ?>
                                    <?php $link = isset($sub['link']) ? $sub['link'] : null; ?>
                                    <?php if ($link && isset($link['url'], $link['title'])): ?>
                                        <li class="lv_menu_subitem">
                                            <a href="<?php echo $link['url']; ?>"
                                                class="lv_menu_sublink"
                                                <?php if (!empty($link['target'])): ?>target="<?php echo $link['target']; ?>" <?php endif; ?>>
                                                <?php echo $link['title']; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php
    return ob_get_clean();
}
add_shortcode('lv_menu_pc', 'lv_menu_pc_shortcode');



// Shortcode: [lv_menu_mobile]
function render_menu_mobile_shortcode()
{
    ob_start();
?>
    <nav class="menu_mobile">
        <ul class="menu_mobile_list">
            <?php if (have_rows('menu_mobile', 'option')): ?>
                <?php while (have_rows('menu_mobile', 'option')): the_row();
                    $primary = get_sub_field('link_primary');
                    if ($primary):
                        $url    = esc_url($primary['url']);
                        $title  = esc_html($primary['title']);
                        $target = $primary['target'] ? esc_attr($primary['target']) : '_self';
                        $has_sub = have_rows('submenu');
                ?>
                        <li class="menu_mobile_item<?php echo $has_sub ? ' has-sub' : ''; ?>">
                            <a href="<?php echo $url; ?>" target="<?php echo $target; ?>" class="menu_mobile_link">
                                <?php echo $title; ?>
                                <?php if ($has_sub): ?>
                                    <span class="toggle-submenu">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                            <path d="M480 224C492.9 224 504.6 231.8 509.6 243.8C514.6 255.8 511.8 269.5 502.7 278.7L342.7 438.7C330.2 451.2 309.9 451.2 297.4 438.7L137.4 278.7C128.2 269.5 125.5 255.8 130.5 243.8C135.5 231.8 147.1 224 160 224L480 224z" />
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <?php if ($has_sub): ?>
                                <ul class="submenu_list">
                                    <?php while (have_rows('submenu')): the_row();
                                        $sub = get_sub_field('link');
                                        if ($sub):
                                            $sub_url    = esc_url($sub['url']);
                                            $sub_title  = esc_html($sub['title']);
                                            $sub_target = $sub['target'] ? esc_attr($sub['target']) : '_self';
                                    ?>
                                            <li class="submenu_item">
                                                <a href="<?php echo $sub_url; ?>" target="<?php echo $sub_target; ?>" class="submenu_link">
                                                    <?php echo $sub_title; ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="menu_mobile_item"><a href="#" class="menu_mobile_link">Chưa có menu</a></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php
    return ob_get_clean();
}
add_shortcode('lv_menu_mobile', 'render_menu_mobile_shortcode');

// Shortcode: [sidebar_left]
function render_sidebar_left_shortcode()
{
    // Lấy logo
    $logo_id  = get_field('image_logo', 'option');
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : 'https://via.placeholder.com/150x50?text=LOGO';

    // Bắt đầu output
    ob_start();
?>
    <aside class="sidebar">
        <div class="sidebar_logo">
            <a href="/" class="sidebar_logo_link">
                <img src="<?php echo esc_url($logo_url); ?>" alt="Site Logo" class="sidebar_logo_img" />
            </a>
        </div>
        <nav class="sidebar_menu">
            <ul class="sidebar_menu_list">
                <?php if (have_rows('menu_sidebar', 'option')): ?>
                    <?php while (have_rows('menu_sidebar', 'option')): the_row();
                        $link = get_sub_field('link');
                        if ($link):
                            $url    = esc_url($link['url']);
                            $title  = esc_html($link['title']);
                            $target = $link['target'] ? esc_attr($link['target']) : '_self';
                    ?>
                            <li class="sidebar_menu_item">
                                <a href="<?php echo $url; ?>" target="<?php echo $target; ?>" class="sidebar_menu_link">
                                    <?php echo $title; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="sidebar_menu_item"><a href="#" class="sidebar_menu_link">Chưa có menu</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>
<?php
    return ob_get_clean();
}
add_shortcode('lv_sidebar_left', 'render_sidebar_left_shortcode');

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
add_shortcode('lv_latest_posts', function ($atts = []) {
    // Lấy setting từ ACF Options
    $list_title = get_field('list_of_articles_title', 'option');
    $num_posts  = (int) get_field('number_of_posts', 'option');
    $num_posts  = $num_posts > 0 ? $num_posts : 6;

    $num_cols   = (int) get_field('number_of_columns', 'option');
    $num_cols   = in_array($num_cols, [2, 3, 4]) ? $num_cols : 3;

    $category   = get_field('article_categories', 'option');
    $see_more   = get_field('see_more_button', 'option');

    // Query bài viết
    $args = [
        'posts_per_page' => $num_posts,
        'post_status'    => 'publish',
    ];
    if (!empty($category)) {
        $args['cat'] = (int) $category;
    }

    $q = new WP_Query($args);
    if (!$q->have_posts()) {
        return '';
    }

    ob_start(); ?>

    <div class="lv_latest_posts_wrap">
        <?php if (!empty($list_title)): ?>
            <h2 class="lv_latest_posts_heading"><?php echo $list_title; ?></h2>
        <?php endif; ?>

        <div class="lv_latest_posts_grid lv_cols_<?php echo $num_cols; ?>">
            <?php while ($q->have_posts()): $q->the_post();
                $url   = get_permalink();
                $title = get_the_title();
                $date = get_the_date('d/m/Y');
                $desc  = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 22);
            ?>
                <article class="lv_latest_posts_card">
                    <?php if (has_post_thumbnail()): ?>
                        <a class="lv_latest_posts_thumb" href="<?php echo $url; ?>">
                            <?php echo get_the_post_thumbnail(get_the_ID(), 'large', ['loading' => 'lazy']); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($title): ?>
                        <h3 class="lv_latest_posts_title">
                            <a href="<?php echo $url; ?>"><?php echo $title; ?></a>
                        </h3>
                    <?php endif; ?>

                    <?php if ($date): ?>
                        <div class="lv_latest_posts_date"><?php echo $date; ?></div>
                    <?php endif; ?>

                    <?php if ($desc): ?>
                        <div class="lv_latest_posts_desc"><?php echo $desc; ?></div>
                    <?php endif; ?>

                    <a class="lv_latest_posts_btn" href="<?php echo $url; ?>">
                        <?php echo !empty($atts['read_more']) ? $atts['read_more'] : 'Xem thêm'; ?>
                    </a>
                </article>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>

        <?php if (!empty($see_more) && is_array($see_more) && !empty($see_more['url'])):
            $sm_url    = $see_more['url'];
            $sm_title  = $see_more['title'] ?: 'Xem thêm';
            $sm_target = $see_more['target'] ?: '_self'; ?>
            <div class="lv_latest_posts_footer">
                <a class="lv_latest_posts_btn lv_latest_posts_btn_more"
                    href="<?php echo $sm_url; ?>"
                    target="<?php echo $sm_target; ?>"
                    <?php if ($sm_target === '_blank') echo 'rel="noopener noreferrer"'; ?>>
                    <?php echo $sm_title; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

<?php
    return ob_get_clean();
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
