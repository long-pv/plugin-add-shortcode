<?php

/**
 * Plugin Name: add-shortcode
 * Description: Thêm short code để nhúng vào website
 * Version: 1.0.0
 * Author: PEPTI-IT
 * Text Domain: add-shortcode
 */

if (!defined('ABSPATH')) exit;

define('ADD_SC_URL', plugin_dir_url(__FILE__));
define('ADD_SC_PATH', plugin_dir_path(__FILE__));

// Hook thêm CSS variable vào <head>
add_action('wp_head', 'mytheme_add_global_colors');
function mytheme_add_global_colors()
{
    // Lấy giá trị từ ACF Options
    $color_primary = get_field('color_primary', 'option') ?: '#FEA800';
    $color_menu = get_field('color_menu', 'option') ?: '#ffffff';
    $color_countdown = get_field('color_countdown', 'option') ?: '#FEA800';
    $color_posts = get_field('color_posts', 'option') ?: '#FEA800';
    $color_breadcrumbs = get_field('color_breadcrumbs', 'option') ?: '#FEA800';
    $padding_block = get_field('padding_block', 'option');

?>
    <style>
        :root {
            --lv-color-primary: <?php echo esc_html($color_primary); ?>;
            --lv-color-menu: <?php echo esc_html($color_menu); ?>;
            --lv-color-countdown: <?php echo esc_html($color_countdown); ?>;
            --lv-color-posts: <?php echo esc_html($color_posts); ?>;
            --lv-color-breadcrumbs: <?php echo esc_html($color_breadcrumbs); ?>;
            --lv-padding-block: <?php echo esc_html($padding_block); ?>px;
        }
    </style>
    <?php
}

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

add_action('wp_enqueue_scripts', function () {
    // Slick CSS/JS
    wp_enqueue_style(
        'slick',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
        [],
        '1.8.1'
    );
    wp_enqueue_script(
        'slick',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
        ['jquery'],
        '1.8.1',
        true
    );

    // MatchHeight
    wp_enqueue_script(
        'matchheight',
        'https://cdnjs.cloudflare.com/ajax/libs/jquery.matchHeight/0.7.2/jquery.matchHeight-min.js',
        ['jquery'],
        '0.7.2',
        true
    );
}, 98);


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


        // Các trang con
        $sub_pages = [
            'CTA'           => 'cta',
            'FAQs'          => 'faqs',
            'Latest posts'  => 'latest-posts',
            'Menu setting'  => 'menu-setting',
            'Service'       => 'service',
            'Tabs'          => 'tabs',
            'Card category' => 'card-category',
            'Partner'       => 'partner',
            'Testimonial'   => 'testimonial',
            'Sidebar'       => 'sidebar',
            'Award'         => 'award',
            'About us'      => 'about-us',
            'Box content'   => 'box-content',
            'Promo banner'  => 'promo-banner',
            'List image'    => 'list-image',
            'Content image' => 'content-image',
            'Tabs category' => 'tabs-category',
            // 'Single post'   => 'single-post',
            // 'Archive post'  => 'archive-post',
        ];

        foreach ($sub_pages as $title => $slug) {
            acf_add_options_sub_page([
                'page_title'  => $title,
                'menu_title'  => $title,
                'parent_slug' => 'add-shortcode-setting',
                'menu_slug'   => $slug,
                'capability'  => 'manage_options',
                'autoload'    => true,
            ]);
        }
    }
});

// Shortcode Tabs [lv_tabs]
function lv_tabs_shortcode()
{
    ob_start();

    $prefix = get_field('prefix', 'option') ?? 'lv';
    $tabs_title = get_field('tabs_title', 'option');
    $list_tabs  = get_field('list_tab', 'option');
    $tabs_content  = get_field('tabs_content', 'option');

    if ($tabs_title || $list_tabs) : ?>
        <section class="<?php echo $prefix; ?>_container <?php echo $prefix; ?>_tabs">
            <?php if ($tabs_title) : ?>
                <h2 class="<?php echo $prefix; ?>_tabs_title text_center"><?php echo $tabs_title; ?></h2>
            <?php endif; ?>

            <?php if ($tabs_content) : ?>
                <div class="<?php echo $prefix; ?>_tabs_content"><?php echo $tabs_content; ?></div>
            <?php endif; ?>

            <?php if ($list_tabs) : ?>
                <div class="<?php echo $prefix; ?>_tabs_wrapper">
                    <div class="<?php echo $prefix; ?>_tabs_links">
                        <?php
                        $i = 1;
                        foreach ($list_tabs as $row) :
                            if (!empty($row['title'])) :
                                $active_class = ($i === 1) ? ' ' . $prefix . '_tabs_link_active' : '';
                        ?>
                                <div class="<?php echo $prefix; ?>_tabs_link<?php echo $active_class; ?>" data-tab="tab<?php echo $i; ?>">
                                    <?php echo $row['title']; ?>
                                </div>
                        <?php
                            endif;
                            $i++;
                        endforeach;
                        ?>
                    </div>

                    <div class="<?php echo $prefix; ?>_tabs_content">
                        <?php
                        $i = 1;
                        foreach ($list_tabs as $row) :
                            if (!empty($row['content'])) :
                                $active_class = ($i === 1) ? ' ' . $prefix . '_tabs_panel_active' : '';
                        ?>
                                <div class="<?php echo $prefix; ?>_tabs_panel<?php echo $active_class; ?>" id="tab<?php echo $i; ?>">
                                    <?php echo $row['content']; ?>
                                </div>
                        <?php
                            endif;
                            $i++;
                        endforeach;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    <?php endif;
    return ob_get_clean();
}
add_shortcode("lv_tabs", "lv_tabs_shortcode");

// Shortcode: [lv_menu_pc]
function lv_menu_pc_shortcode()
{
    $menu_items     = get_field('menu_pc', 'option');
    $menu_use_icon  = get_field('menu_use_icon', 'option') ?? false;
    $prefix         = get_field('prefix', 'option') ?? 'lv';

    if (!$menu_items || !is_array($menu_items)) {
        return '';
    }

    ob_start();
    ?>
    <nav class="<?php echo $prefix; ?>_menu">
        <ul class="<?php echo $prefix; ?>_menu_list">
            <?php foreach ($menu_items as $item): ?>
                <?php
                $link_primary = $item['link_primary'] ?? null;
                $submenu      = $item['submenu']      ?? null;
                $icon         = $item['icon']         ?? null;
                ?>

                <?php if ($link_primary && isset($link_primary['url'], $link_primary['title'])): ?>
                    <li class="<?php echo $prefix; ?>_menu_item <?php echo ($icon && $menu_use_icon) ? $prefix . '_menu_item_has_icon' : ''; ?>">
                        <a href="<?php echo $link_primary['url']; ?>"
                            class="<?php echo $prefix; ?>_menu_link"
                            <?php if (!empty($link_primary['target'])): ?>
                            target="<?php echo $link_primary['target']; ?>"
                            <?php endif; ?>>

                            <?php
                            if ($icon && $menu_use_icon) {
                                echo wp_get_attachment_image(
                                    $icon,
                                    'full',
                                    false,
                                    ['class' => $prefix . '_menu_pc_icon']
                                );
                            }
                            ?>

                            <?php echo $link_primary['title']; ?>

                            <?php if ($submenu && is_array($submenu)): ?>
                                <span class="<?php echo $prefix; ?>_menu_arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="12" height="12">
                                        <path d="M480 224C492.9 224 504.6 231.8 509.6 243.8C514.6 255.8 511.8 269.5 502.7 278.7L342.7 438.7C330.2 451.2 309.9 451.2 297.4 438.7L137.4 278.7C128.2 269.5 125.5 255.8 130.5 243.8C135.5 231.8 147.1 224 160 224L480 224z" />
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </a>

                        <?php if ($submenu && is_array($submenu)): ?>
                            <ul class="<?php echo $prefix; ?>_menu_submenu">
                                <?php foreach ($submenu as $sub): ?>
                                    <?php $link = $sub['link'] ?? null; ?>
                                    <?php if ($link && isset($link['url'], $link['title'])): ?>
                                        <li class="<?php echo $prefix; ?>_menu_subitem">
                                            <a href="<?php echo $link['url']; ?>"
                                                class="<?php echo $prefix; ?>_menu_sublink"
                                                <?php if (!empty($link['target'])): ?>
                                                target="<?php echo $link['target']; ?>"
                                                <?php endif; ?>>
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
    $prefix = get_field('prefix', 'option') ?? 'lv';

    ob_start();
?>
    <nav class="<?php echo $prefix; ?>_menu_mobile">
        <ul class="<?php echo $prefix; ?>_menu_mobile_list">
            <?php if (have_rows('menu_mobile', 'option')): ?>
                <?php while (have_rows('menu_mobile', 'option')): the_row();
                    $primary = get_sub_field('link_primary');
                    if ($primary):
                        $url      = esc_url($primary['url']);
                        $title    = esc_html($primary['title']);
                        $target   = $primary['target'] ? esc_attr($primary['target']) : '_self';
                        $has_sub  = have_rows('submenu');
                ?>
                        <li class="<?php echo $prefix; ?>_menu_mobile_item<?php echo $has_sub ? ' has-sub' : ''; ?>">
                            <a href="<?php echo $url; ?>" target="<?php echo $target; ?>" class="<?php echo $prefix; ?>_menu_mobile_link">
                                <?php echo $title; ?>

                                <?php if ($has_sub): ?>
                                    <span class="<?php echo $prefix; ?>_toggle_submenu">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                            <path d="M480 224C492.9 224 504.6 231.8 509.6 243.8C514.6 255.8 511.8 269.5 502.7 278.7L342.7 438.7C330.2 451.2 309.9 451.2 297.4 438.7L137.4 278.7C128.2 269.5 125.5 255.8 130.5 243.8C135.5 231.8 147.1 224 160 224L480 224z" />
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </a>

                            <?php if ($has_sub): ?>
                                <ul class="<?php echo $prefix; ?>_submenu_list">
                                    <?php while (have_rows('submenu')): the_row();
                                        $sub = get_sub_field('link');
                                        if ($sub):
                                            $sub_url    = esc_url($sub['url']);
                                            $sub_title  = esc_html($sub['title']);
                                            $sub_target = $sub['target'] ? esc_attr($sub['target']) : '_self';
                                    ?>
                                            <li class="<?php echo $prefix; ?>_submenu_item">
                                                <a href="<?php echo $sub_url; ?>" target="<?php echo $sub_target; ?>" class="<?php echo $prefix; ?>_submenu_link">
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
                <li class="<?php echo $prefix; ?>_menu_mobile_item">
                    <a href="#" class="<?php echo $prefix; ?>_menu_mobile_link">Chưa có menu</a>
                </li>
            <?php endif; ?>
        </ul>

        <?php if (have_rows('menu_list_button', 'option')): ?>
            <div class="<?php echo $prefix; ?>_menu_mobile_buttons">
                <ul class="<?php echo $prefix; ?>_menu_mobile_button_list">
                    <?php while (have_rows('menu_list_button', 'option')): the_row();
                        $button_link = get_sub_field('link');
                        if ($button_link):
                            $button_url    = esc_url($button_link['url']);
                            $button_title  = esc_html($button_link['title']);
                            $button_target = $button_link['target'] ? esc_attr($button_link['target']) : '_self';
                    ?>
                            <li class="<?php echo $prefix; ?>_menu_mobile_button_item">
                                <a href="<?php echo $button_url; ?>" target="<?php echo $button_target; ?>" class="<?php echo $prefix; ?>_menu_mobile_button_link">
                                    <?php echo $button_title; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>
    </nav>
<?php
    return ob_get_clean();
}
add_shortcode('lv_menu_mobile', 'render_menu_mobile_shortcode');

// Shortcode: [sidebar_left]
function render_sidebar_left_shortcode()
{
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Lấy logo
    $logo_id  = get_field('image_logo', 'option');
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : 'https://via.placeholder.com/150x50?text=LOGO';

    ob_start();
?>
    <aside class="<?php echo $prefix; ?>_sidebar">
        <div class="<?php echo $prefix; ?>_sidebar_logo">
            <a href="/" class="<?php echo $prefix; ?>_sidebar_logo_link">
                <img src="<?php echo esc_url($logo_url); ?>" alt="Site Logo" class="<?php echo $prefix; ?>_sidebar_logo_img" />
            </a>
        </div>

        <nav class="<?php echo $prefix; ?>_sidebar_menu">
            <ul class="<?php echo $prefix; ?>_sidebar_menu_list">
                <?php if (have_rows('menu_sidebar', 'option')): ?>
                    <?php while (have_rows('menu_sidebar', 'option')): the_row();
                        $link = get_sub_field('link');
                        if ($link):
                            $url    = esc_url($link['url']);
                            $title  = esc_html($link['title']);
                            $target = $link['target'] ? esc_attr($link['target']) : '_self';
                    ?>
                            <li class="<?php echo $prefix; ?>_sidebar_menu_item">
                                <a href="<?php echo $url; ?>" target="<?php echo $target; ?>" class="<?php echo $prefix; ?>_sidebar_menu_link">
                                    <?php echo $title; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="<?php echo $prefix; ?>_sidebar_menu_item">
                        <a href="#" class="<?php echo $prefix; ?>_sidebar_menu_link">Chưa có menu</a>
                    </li>
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
    $prefix = get_field('prefix', 'option') ?? 'lv';

    ob_start();

    $title_service      = get_field('title_service', 'option');
    $list_service       = get_field('list_service', 'option');
    $style_service      = get_field('style_service', 'option') ?? '1';
    $background_button  = get_field('background_button', 'option') ?? '';
?>
    <div class="<?php echo $prefix; ?>_container">
        <section class="<?php echo $prefix; ?>_service <?php echo $prefix; ?>_service_style_<?php echo $style_service; ?>">

            <?php if ($title_service) : ?>
                <h2 class="<?php echo $prefix; ?>_service_title text_center">
                    <?php echo esc_html($title_service); ?>
                </h2>
            <?php endif; ?>

            <?php if ($list_service) : ?>
                <div class="<?php echo $prefix; ?>_service_list">

                    <?php foreach ($list_service as $row) :
                        $link = $row['link'];
                        $icon = $row['icon'];

                        if ($link) : ?>
                            <a href="<?php echo esc_url($link['url']); ?>"
                                class="<?php echo $prefix; ?>_service_item"
                                target="<?php echo esc_attr($link['target'] ?: '_self'); ?>">

                                <?php
                                echo ($style_service == 2 && $icon)
                                    ? wp_get_attachment_image($icon, 'full', false, ['class' => $prefix . '_service_item_icon'])
                                    : '';
                                ?>

                                <?php if ($style_service == '4') : ?>
                                    <img class="<?php echo $prefix; ?>_service_item_bg"
                                        src="<?php echo $background_button; ?>"
                                        alt="background_button">
                                <?php endif; ?>

                                <span class="<?php echo $prefix; ?>_service_item_text">
                                    <?php echo $link['title']; ?>
                                </span>
                            </a>
                        <?php endif; ?>

                    <?php endforeach; ?>

                </div>
            <?php endif; ?>

        </section>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('lv_service', 'lv_service_shortcode');

// Shortcode hiển thị FAQ Block từ ACF Options
function shortcode_lv_faq_block()
{
    $prefix        = get_field('prefix', 'option') ?? 'lv';
    $faqs          = get_field('list_faqs', 'option');
    $faqs_title    = get_field('faqs_title', 'option');
    $style_faqs    = get_field('style_faqs', 'option') ?? '1';
    $faqs_content  = get_field('faqs_content', 'option') ?? '';

    if ($faqs && is_array($faqs)) {
        ob_start(); ?>

        <div class="<?php echo $prefix; ?>_container">

            <?php if ($faqs_title) : ?>
                <h2 class="<?php echo $prefix; ?>_faq_title text_center">
                    <?php echo $faqs_title; ?>
                </h2>
            <?php endif; ?>

            <?php if ($faqs_content) : ?>
                <div class="<?php echo $prefix; ?>_faq_content">
                    <?php echo $faqs_content; ?>
                </div>
            <?php endif; ?>

            <div class="<?php echo $prefix; ?>_faq_block <?php echo $prefix; ?>_faq_block_style_<?php echo $style_faqs; ?>">
                <?php foreach ($faqs as $faq) :
                    $title   = $faq['title'] ?? '';
                    $content = $faq['content'] ?? '';
                ?>

                    <?php if ($title || $content) : ?>
                        <div class="<?php echo $prefix; ?>_faq_item">

                            <?php if ($title) : ?>
                                <div class="<?php echo $prefix; ?>_faq_item_question">
                                    <?php echo $title; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($content) : ?>
                                <div class="<?php echo $prefix; ?>_faq_item_answer">
                                    <?php echo $content; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

    <?php
        return ob_get_clean();
    }
    return '';
}
add_shortcode('lv_faq_block', 'shortcode_lv_faq_block');

// add_latest_posts
add_shortcode('lv_latest_posts', function () {

    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Lấy setting từ ACF Options
    $list_title = get_field('list_of_articles_title', 'option');
    $num_posts  = (int) get_field('number_of_posts', 'option');
    $num_posts  = $num_posts > 0 ? $num_posts : 6;

    $num_cols   = (int) get_field('number_of_columns', 'option');
    $num_cols   = in_array($num_cols, [2, 3, 4]) ? $num_cols : 3;

    $category   = get_field('article_categories', 'option');
    $see_more   = get_field('see_more_button', 'option');

    // Settings mới
    $show_date     = (int) get_field('show_post_date', 'option');
    $show_button   = (int) get_field('show_button_post', 'option');
    $show_excerpt  = (int) get_field('show_excerpt_post', 'option');
    $layout_post   = get_field('layout_post', 'option') ?? "grid";

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

    <div class="<?php echo $prefix; ?>_container">

        <?php if (in_array($layout_post, ['grid', 'slider', 'list'])) : ?>

            <div class="<?php echo $prefix; ?>_latest_posts_wrap <?php echo $prefix; ?>_latest_posts_wrap_<?php echo $layout_post; ?>">

                <?php if (!empty($list_title)): ?>
                    <h2 class="<?php echo $prefix; ?>_latest_posts_heading text_center">
                        <?php echo $list_title; ?>
                    </h2>
                <?php endif; ?>

                <div class="
                    <?php
                    echo $layout_post === 'slider'
                        ? $prefix . '_latest_posts_slider'
                        : $prefix . '_latest_posts_grid ' . $prefix . '_cols_' . $num_cols;
                    ?>
                ">
                    <?php while ($q->have_posts()): $q->the_post();
                        $url   = get_permalink();
                        $title = get_the_title();
                        $date  = get_the_date('d/m/Y');
                        $desc  = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 22);
                    ?>

                        <?php echo $layout_post === 'slider' ? '<div><div data-mh="slide_item" class="' . $prefix . '_latest_posts_slide_item">' : ''; ?>

                        <article class="<?php echo $prefix; ?>_latest_posts_card">

                            <?php if (has_post_thumbnail()): ?>
                                <a class="<?php echo $prefix; ?>_latest_posts_thumb" href="<?php echo $url; ?>">
                                    <?php echo get_the_post_thumbnail(get_the_ID(), 'large', ['loading' => 'lazy']); ?>
                                </a>
                            <?php endif; ?>

                            <div class="<?php echo $prefix; ?>_latest_posts_content">

                                <?php if (!empty($title)): ?>
                                    <h3 class="<?php echo $prefix; ?>_latest_posts_title">
                                        <a href="<?php echo $url; ?>"><?php echo $title; ?></a>
                                    </h3>
                                <?php endif; ?>

                                <?php if ($show_date && !empty($date)): ?>
                                    <div class="<?php echo $prefix; ?>_latest_posts_date"><?php echo $date; ?></div>
                                <?php endif; ?>

                                <?php if ($show_excerpt && !empty($desc) && $layout_post != 'list'): ?>
                                    <div class="<?php echo $prefix; ?>_latest_posts_desc"><?php echo $desc; ?></div>
                                <?php endif; ?>

                                <?php if ($show_button && $layout_post != 'list'): ?>
                                    <a class="<?php echo $prefix; ?>_latest_posts_btn" href="<?php echo $url; ?>">
                                        Xem thêm
                                    </a>
                                <?php endif; ?>

                            </div>
                        </article>

                        <?php echo $layout_post === 'slider' ? '</div></div>' : ''; ?>

                    <?php endwhile;
                    wp_reset_postdata(); ?>

                </div>

                <?php if ($layout_post === 'grid' && !empty($see_more) && !empty($see_more['url'])):
                    $sm_url    = $see_more['url'];
                    $sm_title  = $see_more['title'] ?: 'Xem thêm';
                    $sm_target = $see_more['target'] ?: '_self'; ?>
                    <div class="<?php echo $prefix; ?>_latest_posts_footer">
                        <a class="<?php echo $prefix; ?>_latest_posts_btn <?php echo $prefix; ?>_latest_posts_btn_more"
                            href="<?php echo $sm_url; ?>"
                            target="<?php echo $sm_target; ?>"
                            <?php if ($sm_target === '_blank') echo 'rel="noopener noreferrer"'; ?>>
                            <?php echo $sm_title; ?>
                        </a>
                    </div>
                <?php endif; ?>

            </div>

        <?php elseif ($layout_post == 'box') : ?>

            <div class="<?php echo $prefix; ?>_latest_posts_wrap <?php echo $prefix; ?>_latest_posts_wrap_<?php echo $layout_post; ?>">

                <?php if (!empty($list_title)): ?>
                    <h2 class="<?php echo $prefix; ?>_latest_posts_heading text_center">
                        <?php echo $list_title; ?>
                    </h2>
                <?php endif; ?>

                <div class="<?php echo $prefix; ?>_latest_posts_box_wrap">

                    <?php
                    $background_post = get_field('background_post', 'option');
                    echo wp_get_attachment_image(
                        $background_post,
                        'full',
                        false,
                        ['class' => $prefix . '_latest_posts_box_bg']
                    );
                    ?>

                    <div class="<?php echo $prefix; ?>_latest_posts_box">

                        <?php while ($q->have_posts()): $q->the_post();
                            $url   = get_permalink();
                            $title = get_the_title();
                            $date  = get_the_date('d/m/Y');
                        ?>

                            <article class="<?php echo $prefix; ?>_latest_posts_card">

                                <?php if (has_post_thumbnail()): ?>
                                    <a class="<?php echo $prefix; ?>_latest_posts_thumb" href="<?php echo $url; ?>">
                                        <?php echo get_the_post_thumbnail(get_the_ID(), 'large', ['loading' => 'lazy']); ?>
                                    </a>
                                <?php endif; ?>

                                <div class="<?php echo $prefix; ?>_latest_posts_content">

                                    <?php if (!empty($title)): ?>
                                        <h3 class="<?php echo $prefix; ?>_latest_posts_title">
                                            <a href="<?php echo $url; ?>">
                                                <?php echo $title; ?>
                                            </a>
                                        </h3>
                                    <?php endif; ?>

                                    <?php if (!empty($date)): ?>
                                        <div class="<?php echo $prefix; ?>_latest_posts_date"><?php echo $date; ?></div>
                                    <?php endif; ?>

                                </div>
                            </article>

                        <?php endwhile;
                        wp_reset_postdata(); ?>

                    </div>

                    <?php if (!empty($see_more) && !empty($see_more['url'])): ?>
                        <a class="<?php echo $prefix; ?>_latest_posts_next" href="<?php echo $see_more['url']; ?>">
                            BÀI VIẾT TIẾP THEO
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
});



add_shortcode('lv_menu_bottom', function () {

    $prefix = get_field('prefix', 'option') ?? 'lv';

    $items = get_field('menu_bottom', 'option');

    ob_start();

    if ($items && is_array($items)) : ?>
        <nav class="<?php echo $prefix; ?>_footer_menu" aria-label="Footer quick actions">

            <?php foreach ($items as $row) :
                $icon  = isset($row['icon']) ? $row['icon'] : 0;
                $link  = isset($row['link']) && is_array($row['link']) ? $row['link'] : [];

                $url    = isset($link['url']) ? trim($link['url']) : '#';
                $title  = isset($link['title']) ? trim($link['title']) : '';
                $target = isset($link['target']) && $link['target'] ? $link['target'] : '_self';

                if (!$icon && !$title) {
                    continue;
                }
            ?>

                <a class="<?php echo $prefix; ?>_footer_menu_item"
                    href="<?php echo $url; ?>"
                    target="<?php echo $target; ?>"
                    rel="noopener nofollow sponsored">

                    <span class="<?php echo $prefix; ?>_footer_menu_icon" aria-hidden="true">
                        <?php
                        if ($icon) {
                            echo wp_get_attachment_image(
                                $icon,
                                'thumbnail',
                                false,
                                ['class' => $prefix . '_footer_menu_icon_image']
                            );
                        } else {
                            echo '<span class="' . $prefix . '_footer_menu_icon_placeholder"></span>';
                        }
                        ?>
                    </span>

                    <?php if ($title) : ?>
                        <span class="<?php echo $prefix; ?>_footer_menu_label">
                            <?php echo $title; ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif;

    return ob_get_clean();
});

function lv_countdown_shortcode()
{
    // Prefix từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Lấy ngày giờ hiện tại
    $current_date = new DateTime();
    $current_time = $current_date->format('H:i');

    // Xác định thời gian đếm ngược
    $target_date = clone $current_date;
    $target_date->setTime(18, 30);

    if ($current_time > '18:30') {
        $target_date->modify('+1 day');
    }

    // Format cho JS
    $formatted_date = $target_date->format('Y-m-d\TH:i:s');

    ob_start(); ?>

    <div class="<?php echo $prefix; ?>_timer_countdown">
        <div class="<?php echo $prefix; ?>_timer_countdown_item <?php echo $prefix; ?>_timer_countdown_hours">
            <span class="<?php echo $prefix; ?>_timer_countdown_value" id="hours">0</span>
            <span class="<?php echo $prefix; ?>_timer_countdown_label">HOURS</span>
        </div>

        <div class="<?php echo $prefix; ?>_timer_countdown_item <?php echo $prefix; ?>_timer_countdown_minutes">
            <span class="<?php echo $prefix; ?>_timer_countdown_value" id="minutes">0</span>
            <span class="<?php echo $prefix; ?>_timer_countdown_label">MIN</span>
        </div>

        <div class="<?php echo $prefix; ?>_timer_countdown_item <?php echo $prefix; ?>_timer_countdown_seconds">
            <span class="<?php echo $prefix; ?>_timer_countdown_value" id="seconds">0</span>
            <span class="<?php echo $prefix; ?>_timer_countdown_label">SEC</span>
        </div>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var targetDate = new Date('<?php echo $formatted_date; ?>');

            function updateCountdown() {
                var now = new Date();
                var timeRemaining = targetDate - now;

                if (timeRemaining <= 0) {
                    targetDate.setDate(targetDate.getDate() + 1);
                    timeRemaining = targetDate - now;
                }

                var hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

                document.getElementById('hours').innerHTML = hours;
                document.getElementById('minutes').innerHTML = minutes;
                document.getElementById('seconds').innerHTML = seconds;
            }

            setInterval(updateCountdown, 1000);
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('lv_countdown', 'lv_countdown_shortcode');

function lv_card_category_shortcode($atts)
{
    // Prefix từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Get the ACF fields
    $style_category  = get_field('style_category', 'option');
    $list_category   = get_field('list_category', 'option');
    $title_category  = get_field('title_category', 'option');

    ob_start();

    if ($list_category) {

        echo '<div class="' . $prefix . '_container">';

        if (!empty($title_category)): ?>
            <h2 class="<?php echo $prefix; ?>_block_card_category_title text_center">
                <?php echo $title_category; ?>
            </h2>
        <?php endif;

        if ($style_category == '1' || $style_category == '2') : ?>

            <div class="<?php
                        echo $prefix . '_block_card_category ';
                        echo $style_category ? $prefix . '_block_card_category_style_' . $style_category : '';
                        ?>">
                <?php foreach ($list_category as $category) :

                    $link            = $category['link'];
                    $description     = $category['description'];
                    $image_id        = $category['image'];
                    $background_color = $category['background_color'] ?: '#2563EB';

                    $link_url    = $link['url'] ?? '#';
                    $link_title  = $link['title'] ?? '';
                    $link_target = $link['target'] ?? '';
                    $image_html  = $image_id ? wp_get_attachment_image($image_id, 'medium') : '';
                ?>

                    <a target="<?php echo $link_target; ?>"
                        href="<?php echo $link_url; ?>"
                        class="<?php echo $prefix; ?>_block_card_category_card"
                        style="background-color:<?php echo $background_color; ?>;">

                        <?php if ($style_category == '2'): ?>
                            <div class="<?php echo $prefix; ?>_block_card_category_inner">
                            <?php endif; ?>

                            <?php if ($image_html): ?>
                                <div class="<?php echo $prefix; ?>_block_card_category_icon"><?php echo $image_html; ?></div>
                            <?php endif; ?>

                            <?php if ($style_category == '2'): ?>
                                <div class="<?php echo $prefix; ?>_block_card_category_content">
                                <?php endif; ?>

                                <?php echo $link_title ? '<div class="' . $prefix . '_block_card_category_title">' . $link_title . '</div>' : ''; ?>
                                <?php echo $description ? '<div class="' . $prefix . '_block_card_category_desc">' . $description . '</div>' : ''; ?>

                                <?php if ($style_category == '2'): ?>
                                </div> <!-- close content -->
                            </div> <!-- close inner -->

                            <div class="<?php echo $prefix; ?>_block_card_category_right">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                    <path d="M566.6 342.6C579.1 330.1 579.1 309.8 566.6 297.3L406.6 137.3C394.1 124.8 373.8 124.8 361.3 137.3C348.8 149.8 348.8 170.1 361.3 182.6L466.7 288L96 288C78.3 288 64 302.3 64 320C64 337.7 78.3 352 96 352L466.7 352L361.3 457.4C348.8 469.9 348.8 490.2 361.3 502.7C373.8 515.2 394.1 515.2 406.6 502.7L566.6 342.7z" />
                                </svg>
                            </div>
                        <?php endif; ?>

                    </a>
                <?php endforeach; ?>
            </div>

        <?php elseif ($style_category == '3') : ?>

            <section class="<?php echo $prefix; ?>_category_list <?php echo $prefix; ?>_category_list_<?php echo $style_category; ?>">
                <div class="<?php echo $prefix; ?>_category_list_wrapper">

                    <?php foreach ($list_category as $item):
                        $link        = $item['link'];
                        $image_id    = $item['image'];
                        $description = $item['description'];
                    ?>
                        <?php if ($link && $image_id): ?>
                            <div class="<?php echo $prefix; ?>_category_list_item">
                                <?php echo wp_get_attachment_image($image_id, 'full', false, [
                                    'class' => $prefix . '_category_list_item_img',
                                    'loading' => 'lazy'
                                ]); ?>

                                <div class="<?php echo $prefix; ?>_category_list_item_overlay">
                                    <h3 class="<?php echo $prefix; ?>_category_list_item_title"><?php echo $link['title']; ?></h3>

                                    <?php if ($style_category != 3 && $description): ?>
                                        <p class="<?php echo $prefix; ?>_category_list_item_desc"><?php echo $description; ?></p>
                                    <?php endif; ?>

                                    <a href="<?php echo $link['url']; ?>"
                                        target="<?php echo $link['target']; ?>"
                                        class="<?php echo $prefix; ?>_category_list_item_btn">
                                        Chơi Ngay
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </div>
            </section>

        <?php elseif ($style_category == '4'): ?>

            <section class="<?php echo $prefix; ?>_category_list <?php echo $prefix; ?>_category_list_<?php echo $style_category; ?>">
                <div class="<?php echo $prefix; ?>_category_list_wrapper">

                    <?php foreach ($list_category as $item):

                        $link     = $item['link'] ?? [];
                        $image_id = $item['image'];
                    ?>
                        <?php if ($image_id): ?>
                            <a target="<?php echo $link['target']; ?>"
                                href="<?php echo $link['url'] ?? 'javascript:void(0);'; ?>"
                                class="<?php echo $prefix; ?>_category_list_item">
                                <?php echo wp_get_attachment_image($image_id, 'full', false, [
                                    'class' => $prefix . '_category_list_item_img',
                                    'loading' => 'lazy'
                                ]); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </div>
            </section>

    <?php endif;

        echo '</div>';
    }

    return ob_get_clean();
}

add_shortcode('lv_card_category', 'lv_card_category_shortcode');

function lv_testimonial_shortcode()
{
    // Prefix động từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Lấy giá trị của các trường ACF
    $style = get_field('style_testimonial', 'option');
    $testimonial_title = get_field('testimonial_title', 'option');
    $testimonials = get_field('testimonials', 'option');

    if (empty($testimonials)) {
        return '';
    }

    $output = '';

    $output .= '<div class="' . $prefix . '_container">';

    // Tiêu đề
    if (!empty($testimonial_title)) {
        $output .= '<h2 class="testimonial_title text_center">' . $testimonial_title . '</h2>';
    }

    // Wrapper
    $output .= '<div class="' . $prefix . '_testimonial_section ' . $prefix . '_testimonial_section_style_' . esc_attr($style) . '">';

    foreach ($testimonials as $testimonial) {

        $title      = $testimonial['title'];
        $content    = $testimonial['content'];
        $avatar_id  = $testimonial['avatar'];
        $avatar_url = wp_get_attachment_url($avatar_id);

        if ($style == 1) {

            $output .= '<div class="' . $prefix . '_testimonial_item">';
            $output .= '<p class="' . $prefix . '_testimonial_text">"' . esc_html($content) . '"</p>';
            $output .= '<p class="' . $prefix . '_testimonial_author">' . esc_html($title) . '</p>';
            $output .= '</div>';
        } elseif ($style == 2) {

            $output .= '<div class="' . $prefix . '_testimonial_item">';
            $output .= '<p class="' . $prefix . '_testimonial_text">"' . esc_html($content) . '"</p>';
            $output .= '<p class="' . $prefix . '_testimonial_author">' . esc_html($title) . '</p>';

            if ($avatar_url) {
                $output .= '<img class="' . $prefix . '_testimonial_author_img" src="' . esc_url($avatar_url) . '" alt="' . esc_attr($title) . '">';
            }

            $output .= '</div>';
        } elseif ($style == 3) {

            $output .= '<div class="' . $prefix . '_testimonial_item">';

            if ($avatar_url) {
                $output .= '<div class="' . $prefix . '_testimonial_icon">';
                $output .= '<img src="' . esc_url($avatar_url) . '" alt="' . esc_attr($title) . '">';
                $output .= '</div>';
            }

            $output .= '<div class="' . $prefix . '_testimonial_item_content">';
            $output .= '<p class="' . $prefix . '_testimonial_text">"' . esc_html($content) . '"</p>';
            $output .= '<p class="' . $prefix . '_testimonial_author">' . esc_html($title) . '</p>';
            $output .= '</div>';

            $output .= '</div>';
        }
    }

    $output .= '</div>'; // close testimonial section
    $output .= '</div>'; // close container

    return $output;
}

add_shortcode('lv_testimonial', 'lv_testimonial_shortcode');

add_shortcode('lv_cta', function () {

    if (! function_exists('get_field')) {
        return '';
    }

    // Prefix động từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Lấy dữ liệu từ ACF Options
    $title   = get_field('title_cta', 'option');
    $desc    = get_field('description_cta', 'option');
    $button  = get_field('button_cta', 'option');
    $bg_id   = get_field('background_cta', 'option');

    $has_btn = (is_array($button) && !empty($button['url']) && !empty($button['title']));
    $has_bg  = !empty($bg_id);
    $has_any = ($title || $desc || $has_btn || $has_bg);

    if (! $has_any) {
        return '';
    }

    $bg_img_url = $has_bg ? wp_get_attachment_url($bg_id) : '';

    $btn_url    = $has_btn ? $button['url'] : '';
    $btn_title  = $has_btn ? $button['title'] : '';
    $btn_target = ($has_btn && !empty($button['target'])) ? $button['target'] : '_self';

    ob_start();
    ?>
    <section class="<?php echo $prefix; ?>_cta"
        <?php if ($title) { ?> aria-labelledby="<?php echo $prefix; ?>_cta_title" <?php } ?>>

        <?php if ($has_bg) : ?>
            <div class="<?php echo $prefix; ?>_cta_bg"
                style="background-image: url('<?php echo $bg_img_url; ?>');"></div>
        <?php endif; ?>

        <div class="<?php echo $prefix; ?>_cta_overlay" aria-hidden="true"></div>

        <div class="<?php echo $prefix; ?>_cta_container">
            <div class="<?php echo $prefix; ?>_cta_content">

                <?php if ($title) : ?>
                    <h2 id="<?php echo $prefix; ?>_cta_title"
                        class="<?php echo $prefix; ?>_cta_title">
                        <?php echo $title; ?>
                    </h2>
                <?php endif; ?>

                <?php if ($desc) : ?>
                    <p class="<?php echo $prefix; ?>_cta_desc"><?php echo $desc; ?></p>
                <?php endif; ?>

                <?php if ($has_btn) : ?>
                    <a class="<?php echo $prefix; ?>_cta_button <?php echo $prefix; ?>_cta_button_primary"
                        href="<?php echo $btn_url; ?>"
                        target="<?php echo $btn_target; ?>">
                        <?php echo $btn_title; ?>
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </section>
    <?php

    return ob_get_clean();
});

function lv_partner_shortcode()
{
    // Prefix lấy từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Lấy dữ liệu từ ACF
    $title_partner = get_field('title_partner', 'option');
    $list_partner  = get_field('list_partner', 'option');

    if ($list_partner):
        ob_start();
    ?>
        <div class="<?php echo $prefix; ?>_partner_container">

            <?php if ($title_partner): ?>
                <h2 class="<?php echo $prefix; ?>_partner_title text_center">
                    <?php echo $title_partner; ?>
                </h2>
            <?php endif; ?>

            <div class="<?php echo $prefix; ?>_partner">
                <?php foreach ($list_partner as $partner):
                    $title    = $partner['title'];
                    $logo_id  = $partner['logo'];
                    $logo_html = wp_get_attachment_image($logo_id, 'medium');
                ?>
                    <div class="<?php echo $prefix; ?>_partner_item_wrapper">
                        <div class="<?php echo $prefix; ?>_partner_item"
                            data-mh="<?php echo $prefix; ?>_partner_item">

                            <?php if ($logo_html): ?>
                                <?php echo $logo_html; ?>
                            <?php endif; ?>

                            <p class="<?php echo $prefix; ?>_partner_text">
                                <?php echo $title; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    <?php
        return ob_get_clean();
    endif;

    return '';
}

// Đăng ký shortcode
add_shortcode('lv_partner', 'lv_partner_shortcode');

function lv_award_shortcode()
{
    // Prefix lấy từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Lấy group 'award' từ trang Options
    $award = function_exists('get_field') ? get_field('award', 'option') : null;

    // Không có dữ liệu → return rỗng
    if (
        empty($award) ||
        (empty($award['image']) && empty($award['title']) && empty($award['price']) && empty($award['background']))
    ) {
        return '';
    }

    ob_start();
    ?>
    <div class="<?php echo $prefix; ?>_award">

        <?php if (!empty($award['background'])): ?>
            <?php echo wp_get_attachment_image(
                $award['background'],
                'full',
                false,
                ['class' => $prefix . '_award_bg']
            ); ?>
        <?php endif; ?>

        <div class="<?php echo $prefix; ?>_award_inner">

            <?php if (!empty($award['image']) || !empty($award['title'])): ?>
                <div class="<?php echo $prefix; ?>_award_header">

                    <?php if (!empty($award['image'])): ?>
                        <div class="<?php echo $prefix; ?>_award_header_img_wrap">
                            <?php echo wp_get_attachment_image(
                                $award['image'],
                                'medium',
                                false,
                                ['class' => $prefix . '_award_header_img']
                            ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($award['title'])): ?>
                        <div class="<?php echo $prefix; ?>_award_game_name">
                            <?php echo $award['title']; ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

            <?php if (!empty($award['price'])): ?>
                <div class="<?php echo $prefix; ?>_award_body">
                    <div class="<?php echo $prefix; ?>_award_value">
                        <?php echo $award['price']; ?>
                        <span class="<?php echo $prefix; ?>_award_value_currency">VND</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="<?php echo $prefix; ?>_award_footer">
                <div class="<?php echo $prefix; ?>_award_timer_label">THỜI GIAN CÒN LẠI:</div>
                <div class="<?php echo $prefix; ?>_award_timer">
                    <span class="<?php echo $prefix; ?>_award_timer_digit" id="<?php echo $prefix; ?>_timer-hours">00</span>
                    <span class="<?php echo $prefix; ?>_award_timer_separator">:</span>
                    <span class="<?php echo $prefix; ?>_award_timer_digit" id="<?php echo $prefix; ?>_timer-minutes">00</span>
                    <span class="<?php echo $prefix; ?>_award_timer_separator">:</span>
                    <span class="<?php echo $prefix; ?>_award_timer_digit" id="<?php echo $prefix; ?>_timer-seconds">00</span>
                </div>
            </div>

        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('lv_award', 'lv_award_shortcode');

function lv_about_us()
{
    // Lấy prefix từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    ob_start();

    // Lấy dữ liệu từ ACF Options
    $about_us_group = get_field('about_us', 'option');

    if ($about_us_group) {

        $title = $about_us_group['title'] ?? '';
        $content = $about_us_group['content'] ?? '';
        $list = $about_us_group['list'] ?? [];
    ?>
        <div class="<?php echo $prefix; ?>_container">
            <div class="<?php echo $prefix; ?>_aboutUs">

                <?php if (!empty($title)) : ?>
                    <h2 class="<?php echo $prefix; ?>_aboutUs__title text_center">
                        <?php echo $title; ?>
                    </h2>
                <?php endif; ?>

                <?php if (!empty($content)) : ?>
                    <p class="<?php echo $prefix; ?>_aboutUs__description">
                        <?php echo $content; ?>
                    </p>
                <?php endif; ?>

                <div class="<?php echo $prefix; ?>_aboutUs__boxes">
                    <?php if (!empty($list)) :
                        foreach ($list as $item) :
                            $item_title = $item['title'] ?? '';
                            $item_description = $item['description'] ?? '';
                    ?>
                            <div class="<?php echo $prefix; ?>_aboutUs__box">

                                <?php if (!empty($item_title)) : ?>
                                    <h3 class="<?php echo $prefix; ?>_aboutUs__boxTitle">
                                        <?php echo $item_title; ?>
                                    </h3>
                                <?php endif; ?>

                                <?php if (!empty($item_description)) : ?>
                                    <p class="<?php echo $prefix; ?>_aboutUs__boxContent">
                                        <?php echo $item_description; ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                    <?php
                        endforeach;
                    endif; ?>
                </div>

            </div>
        </div>
    <?php
    }

    return ob_get_clean();
}
add_shortcode('lv_about_us', 'lv_about_us');

function lv_box_content_shortcode()
{
    // Lấy prefix từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Lấy dữ liệu từ ACF option page
    $box_content = get_field('box_content', 'option');

    if (!$box_content) {
        return '';
    }

    // Bắt đầu sinh output
    $output = '';

    // Title
    if (!empty($box_content['title'])) {
        $output .= '<h2 class="' . $prefix . '_contentBox__title text_center">' . $box_content['title'] . '</h2>';
    }

    // Description
    if (!empty($box_content['content'])) {
        $output .= '<div class="' . $prefix . '_contentBox__description">' . $box_content['content'] . '</div>';
    }

    // List items
    if (!empty($box_content['list'])) {
        $output .= '<section class="' . $prefix . '_section__contentBox">';

        foreach ($box_content['list'] as $item) {
            if (!empty($item['title']) && !empty($item['description'])) {

                $background_color = $item['background_color'] ?? '#ffffff';

                $output .= '<div class="' . $prefix . '_contentBox__item" style="background-color:' . $background_color . '">';

                $output .= '<h3 class="' . $prefix . '_contentBox__text text_center">' . $item['title'] . '</h3>';

                $output .= '<div class="' . $prefix . '_contentBox__description">' . $item['description'] . '</div>';

                $output .= '</div>';
            }
        }

        $output .= '</section>';
    }

    return $output;
}
add_shortcode('lv_box_content', 'lv_box_content_shortcode');

function lv_promo_banner_shortcode($atts)
{
    // Lấy prefix từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // Lấy dữ liệu từ ACF Options
    $promo_banner = get_field('promo_banner', 'option');

    // Fallback
    $messages       = $promo_banner['messages'] ?? '';
    $button_url     = $promo_banner['link']['url'] ?? '';
    $button_title   = $promo_banner['link']['title'] ?? '';
    $button_target  = $promo_banner['link']['target'] ?? '_self';

    $raw_msgs  = trim($messages);
    $has_btn   = !empty($button_url);

    ob_start();

    if ($messages || $has_btn) : ?>
        <div class="<?php echo $prefix; ?>_promoBanner__page">
            <div class="<?php echo $prefix; ?>_promoBanner__pill" role="region">

                <?php if ($messages) : ?>
                    <div class="<?php echo $prefix; ?>_promoBanner__ticker" aria-hidden="false">
                        <div class="<?php echo $prefix; ?>_promoBanner__marquee" aria-hidden="true">
                            <div class="<?php echo $prefix; ?>_promoBanner__marqueeItem">
                                <?php echo $raw_msgs; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($has_btn) : ?>
                    <a href="<?php echo esc_url($button_url); ?>"
                        class="<?php echo $prefix; ?>_promoBanner__button"
                        role="link"
                        target="<?php echo esc_attr($button_target); ?>">
                        <?php echo esc_html($button_title); ?>
                    </a>
                <?php endif; ?>

            </div>
        </div>
    <?php endif;

    return ob_get_clean();
}
add_shortcode('lv_promo_banner', 'lv_promo_banner_shortcode');


// Tạo shortcode 'list_image_shortcode' để hiển thị danh sách hình ảnh
function list_image_shortcode()
{
    // Lấy prefix động
    $prefix = get_field('prefix', 'option') ?? 'lv';

    // ACF data
    $list_image       = get_field('list_image', 'option');
    $list_image_title = get_field('list_image_title', 'option');
    $layout           = get_field('list_image_layout', 'option') ?: '1';

    $pc_column = $list_image['pc_column'] ?? 5;
    $sp_column = $list_image['sp_column'] ?? 5;
    $list      = $list_image['list'] ?? [];

    ob_start();

    // Tiêu đề
    if (!empty($list_image_title)) : ?>
        <h2 class="<?php echo $prefix; ?>_block_card_category_title text_center">
            <?php echo esc_html($list_image_title); ?>
        </h2>
    <?php
    endif;

    // Start output
    $output = '';

    /* -------------------------------------
        LAYOUT 1 – GRID
    ------------------------------------- */
    if ($layout == '1') {

        $output .= '<div class="' . $prefix . '_imageList" style="--columns:' . $pc_column . '; --columns-mobile:' . $sp_column . ';">';

        if (!empty($list)) {
            foreach ($list as $item) {
                $img = wp_get_attachment_image_url($item['image'], 'full');
                $url = !empty($item['url']) ? esc_url($item['url']) : '#';

                $output .= '<div class="' . $prefix . '_imageList__item">';
                $output .= '<a href="' . $url . '"><img src="' . $img . '" alt=""></a>';
                $output .= '</div>';
            }
        }

        $output .= '</div>';
    }

    /* -------------------------------------
        LAYOUT 2 – SLIDER
    ------------------------------------- */ elseif ($layout == '2') {

        $output .= '<div class="' . $prefix . '_catSlider">';

        if (!empty($list)) {
            foreach ($list as $item) {
                $img = wp_get_attachment_image_url($item['image'], 'full');
                $url = !empty($item['url']) ? esc_url($item['url']) : '#';

                $output .= '<div class="' . $prefix . '_catSlider__slickSlide">';
                $output .= '<a href="' . $url . '">';
                $output .= '<img src="' . $img . '" alt="">';
                $output .= '</a>';
                $output .= '</div>';
            }
        }

        $output .= '</div>';
    }

    echo $output;

    return ob_get_clean();
}
add_shortcode('lv_list_image', 'list_image_shortcode');


function lv_content_image_shortcode($atts)
{
    // Lấy prefix động từ ACF Options
    $prefix = get_field('prefix', 'option') ?? 'lv';

    ob_start();

    // Lấy dữ liệu từ ACF
    $layout = get_field('layout_content_image', 'options') ?: '1';
    $content_images = get_field('content_image', 'options');

    if ($content_images):

        echo '<div class="' . $prefix . '_content_section_container ' . $prefix . '_container ' . $prefix . '_content_section_layout_' . $layout . '">';

        foreach ($content_images as $index => $img):

            $image_id     = $img['image'];
            $title        = $img['title'];
            $content      = $img['content'];
            $button       = $img['button'];
            $button_url   = $button['url'] ?? '#';
            $button_text  = $button['title'] ?? 'Xem thêm';
            $target       = $button['target'] ?? '';
            $image_title  = $img['image_title'];

            // --- HÀNG CHẴN ---
            if ($index % 2 == 0):

                echo '<div class="' . $prefix . '_content_section">';

                echo '<div class="' . $prefix . '_content_section__image">';
                echo wp_get_attachment_image($image_id, 'full');
                echo '</div>';

                echo '<div class="' . $prefix . '_content_section__text">';

                if ($layout == '1' && $title) {
                    echo '<h2 class="' . $prefix . '_content_section__heading">' . $title . '</h2>';
                }

                if ($layout == '2' && $image_title) {
                    echo '<a href="' . $button_url . '" target="' . $target . '" class="' . $prefix . '_content_section_image_title">';
                    echo wp_get_attachment_image($image_title, 'full');
                    echo '</a>';
                }

                if ($content) {
                    echo '<div class="' . $prefix . '_content_section__description">' . $content . '</div>';
                }

                if ($layout == '1' && $button_text) {
                    echo '<a href="' . $button_url . '" target="' . $target . '" class="' . $prefix . '_content_section__link">' . $button_text . '</a>';
                }

                echo '</div>'; // text
                echo '</div>'; // section

            // --- HÀNG LẺ ---
            else:

                echo '<div class="' . $prefix . '_content_section">';

                echo '<div class="' . $prefix . '_content_section__text">';

                if ($layout == '1' && $title) {
                    echo '<h2 class="' . $prefix . '_content_section__heading">' . $title . '</h2>';
                }

                if ($layout == '2' && $image_title) {
                    echo '<a href="' . $button_url . '" target="' . $target . '" class="' . $prefix . '_content_section_image_title">';
                    echo wp_get_attachment_image($image_title, 'full');
                    echo '</a>';
                }

                if ($content) {
                    echo '<div class="' . $prefix . '_content_section__description">' . $content . '</div>';
                }

                if ($layout == '1' && $button_text) {
                    echo '<a href="' . $button_url . '" target="' . $target . '" class="' . $prefix . '_content_section__link">' . $button_text . '</a>';
                }

                echo '</div>'; // text

                echo '<div class="' . $prefix . '_content_section__image">';
                echo wp_get_attachment_image($image_id, 'full');
                echo '</div>';

                echo '</div>'; // section

            endif;

        endforeach;

        echo '</div>'; // container
    endif;

    return ob_get_clean();
}
add_shortcode('lv_content_image', 'lv_content_image_shortcode');

function shortcode_tabs_category()
{
    // Lấy prefix động từ ACF Options
    $prefix = get_field('prefix', 'option') ?: 'lv';

    ob_start();

    // Lấy dữ liệu từ ACF
    $tabs_category = get_field('tabs_category', 'option');

    if (!empty($tabs_category) && !empty($tabs_category['list'])) :
    ?>

        <div class="<?php echo $prefix; ?>_tabs_wrapper">

            <!-- TABS NAV -->
            <ul class="<?php echo $prefix; ?>_tabs_nav">
                <?php
                $first = true;

                foreach ($tabs_category['list'] as $index => $tab) :
                    if (!empty($tab['title'])) :
                        $tab_id = $prefix . '_tab' . $index;
                ?>
                        <li class="<?php echo $prefix; ?>_tabs_navItem <?php echo $first ? 'active' : ''; ?>"
                            data-tab="<?php echo $tab_id; ?>">

                            <?php
                            if (!empty($tab['title_icon'])) {
                                echo wp_get_attachment_image(
                                    $tab['title_icon'],
                                    'large',
                                    false,
                                    ['class' => $prefix . '_tabs_icon']
                                );
                            }
                            ?>

                            <span class="<?php echo $prefix; ?>_tabs_text">
                                <?php echo esc_html($tab['title']); ?>
                            </span>
                        </li>
                <?php
                        $first = false;
                    endif;
                endforeach;
                ?>
            </ul>

            <!-- CONTENT -->
            <div class="<?php echo $prefix; ?>_tabs_content">
                <?php
                $first = true;

                foreach ($tabs_category['list'] as $index => $tab) :
                    if (!empty($tab['title']) && !empty($tab['list_item'])) :

                        $tab_id = $prefix . '_tab' . $index;
                ?>
                        <div id="<?php echo $tab_id; ?>"
                            class="<?php echo $prefix; ?>_tabs_cat_panel <?php echo $first ? 'active' : ''; ?>">

                            <div class="<?php echo $prefix; ?>_tabs_grid">
                                <?php
                                foreach ($tab['list_item'] as $item) :
                                    if (!empty($item['image'])) :
                                        $url = !empty($item['url']) ? $item['url'] : '#';
                                ?>
                                        <a href="<?php echo esc_url($url); ?>"
                                            class="<?php echo $prefix; ?>_tabs_item">
                                            <?php echo wp_get_attachment_image($item['image'], 'medium'); ?>
                                        </a>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        </div>
                <?php
                        $first = false;
                    endif;
                endforeach;
                ?>
            </div>

        </div>

<?php
    endif;

    return ob_get_clean();
}
add_shortcode('lv_tabs_cat', 'shortcode_tabs_category');
