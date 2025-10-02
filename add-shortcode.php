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

    $tabs_title = get_field('tabs_title', 'option');
    $list_tabs  = get_field('list_tab', 'option');
    $tabs_content  = get_field('tabs_content', 'option');

    if ($tabs_title || $list_tabs) : ?>
        <section class="lv_container lv_tabs">
            <?php if ($tabs_title) : ?>
                <h2 class="lv_tabs_title text_center"><?php echo $tabs_title; ?></h2>
            <?php endif; ?>

            <?php if ($tabs_content) : ?>
                <div class="lv_tabs_content"><?php echo $tabs_content; ?></div>
            <?php endif; ?>

            <?php if ($list_tabs) : ?>
                <div class="lv_tabs_wrapper">
                    <div class="lv_tabs_links">
                        <?php
                        $i = 1;
                        foreach ($list_tabs as $row) :
                            if (!empty($row['title'])) :
                                $active_class = ($i === 1) ? ' lv_tabs_link_active' : '';
                        ?>
                                <div class="lv_tabs_link<?php echo $active_class; ?>" data-tab="tab<?php echo $i; ?>">
                                    <?php echo $row['title']; ?>
                                </div>
                        <?php
                            endif;
                            $i++;
                        endforeach;
                        ?>
                    </div>

                    <div class="lv_tabs_content">
                        <?php
                        $i = 1;
                        foreach ($list_tabs as $row) :
                            if (!empty($row['content'])) :
                                $active_class = ($i === 1) ? ' lv_tabs_panel_active' : '';
                        ?>
                                <div class="lv_tabs_panel<?php echo $active_class; ?>" id="tab<?php echo $i; ?>">
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
    $menu_items = get_field('menu_pc', 'option');
    $menu_use_icon = get_field('menu_use_icon', 'option') ?? false;

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
                $icon         = isset($item['icon']) ? $item['icon'] : null;   // ADDED: lấy image ID từ field 'icon'
                ?>

                <?php if ($link_primary && isset($link_primary['url'], $link_primary['title'])): ?>
                    <li class="lv_menu_item <?php echo $icon && $menu_use_icon ? 'lv_menu_item_has_icon' : ''; ?>">
                        <a href="<?php echo $link_primary['url']; ?>"
                            class="lv_menu_link"
                            <?php if (!empty($link_primary['target'])): ?>target="<?php echo $link_primary['target']; ?>" <?php endif; ?>>

                            <?php
                            // Hiển thị ảnh nếu có
                            if ($icon && $menu_use_icon) {
                                echo wp_get_attachment_image($icon, 'full', false, ['class' => 'lv_menu_pc_icon']); // ADDED: hiển thị 'icon'
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

        <!-- Thêm danh sách các nút ở phía dưới -->
        <?php if (have_rows('menu_list_button', 'option')): ?>
            <div class="menu_mobile_buttons">
                <ul class="menu_mobile_button_list">
                    <?php while (have_rows('menu_list_button', 'option')): the_row();
                        $button_link = get_sub_field('link');
                        if ($button_link):
                            $button_url = esc_url($button_link['url']);
                            $button_title = esc_html($button_link['title']);
                            $button_target = $button_link['target'] ? esc_attr($button_link['target']) : '_self';
                    ?>
                            <li class="menu_mobile_button_item">
                                <a href="<?php echo $button_url; ?>" target="<?php echo $button_target; ?>" class="menu_mobile_button_link">
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
    $style_service  = get_field('style_service', 'option') ?? '1';
    $background_button = get_field('background_button', 'option') ?? '';
?>
    <!-- lv_service_style_2 -->
    <div class="lv_container">
        <section class="lv_service lv_service_style_<?php echo $style_service; ?>">
            <?php if ($title_service) : ?>
                <h2 class="lv_service_title text_center"><?php echo esc_html($title_service); ?></h2>
            <?php endif; ?>

            <?php if ($list_service) : ?>
                <div class="lv_service_list">
                    <?php foreach ($list_service as $row) :
                        $link = $row['link'];
                        $icon = $row['icon'];
                        if ($link) : ?>
                            <a href="<?php echo esc_url($link['url']); ?>"
                                class="lv_service_item"
                                target="<?php echo esc_attr($link['target'] ?: '_self'); ?>">
                                <?php echo $style_service == 2 && $icon ? wp_get_attachment_image($icon, 'full', false, ['class' => 'lv_service_item_icon']) : ''; ?>

                                <?php if ($style_service == '4') : ?>
                                    <img class="lv_service_item_bg" src="<?php echo $background_button; ?>" alt="background_button">
                                <?php endif; ?>

                                <span class="lv_service_item_text">
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
    $faqs = get_field('list_faqs', 'option'); // lấy từ ACF Options
    $faqs_title = get_field('faqs_title', 'option');
    $style_faqs = get_field('style_faqs', 'option') ?? '1';
    $faqs_content = get_field('faqs_content', 'option') ?? '';

    if ($faqs && is_array($faqs)) {
        ob_start(); ?>

        <div class="lv_container">

            <?php if ($faqs_title) : ?>
                <h2 class="lv_faq_title text_center">
                    <?php echo $faqs_title; ?>
                </h2>
            <?php endif; ?>

            <?php if ($faqs_content) : ?>
                <div class="lv_faq_content">
                    <?php echo $faqs_content; ?>
                </div>
            <?php endif; ?>

            <div class="lv_faq_block lv_faq_block_style_<?php echo $style_faqs; ?>">
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
        </div>

    <?php
        return ob_get_clean();
    }
    return ''; // không có dữ liệu thì trả về rỗng
}
add_shortcode('lv_faq_block', 'shortcode_lv_faq_block');

// add_latest_posts
add_shortcode('lv_latest_posts', function () {
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
    $layout_post   = get_field('layout_post', 'option') ?? "grid"; // grid | slider | list  // ADDED (chỉ ghi chú)

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

    <div class="lv_container">
        <?php
        if ($layout_post == 'grid' || $layout_post == 'slider' || $layout_post == 'list') :
        ?>
            <div class="lv_latest_posts_wrap <?php echo 'lv_latest_posts_wrap_' . $layout_post; ?>">
                <?php if (!empty($list_title)): ?>
                    <h2 class="lv_latest_posts_heading text_center">
                        <?php echo $list_title; ?>
                    </h2>
                <?php endif; ?>

                <div class="<?php echo $layout_post === 'slider' ? 'lv_latest_posts_slider' : 'lv_latest_posts_grid lv_cols_' . $num_cols; ?>">
                    <?php while ($q->have_posts()): $q->the_post();
                        $url   = get_permalink();
                        $title = get_the_title();
                        $date  = get_the_date('d/m/Y');
                        $desc  = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 22);
                    ?>
                        <?php echo $layout_post === 'slider' ? '<div><div data-mh="slide_item" class="lv_latest_posts_slide_item">' : ''; ?>
                        <article class="lv_latest_posts_card">
                            <?php if (has_post_thumbnail()): ?>
                                <a class="lv_latest_posts_thumb" href="<?php echo $url; ?>">
                                    <?php echo get_the_post_thumbnail(get_the_ID(), 'large', ['loading' => 'lazy']); ?>
                                </a>
                            <?php endif; ?>

                            <div class="lv_latest_posts_content">
                                <?php if (!empty($title)): ?>
                                    <h3 class="lv_latest_posts_title">
                                        <a href="<?php echo $url; ?>"><?php echo $title; ?></a>
                                    </h3>
                                <?php endif; ?>

                                <?php if ($show_date && !empty($date)): ?>
                                    <div class="lv_latest_posts_date"><?php echo $date; ?></div>
                                <?php endif; ?>

                                <?php if ($show_excerpt && !empty($desc) && $layout_post != 'list'): ?>
                                    <div class="lv_latest_posts_desc"><?php echo $desc; ?></div>
                                <?php endif; ?>

                                <?php if ($show_button && $layout_post != 'list'): ?>
                                    <a class="lv_latest_posts_btn" href="<?php echo $url; ?>">
                                        <?php echo 'Xem thêm'; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </article>
                        <?php echo $layout_post === 'slider' ? '</div></div>' : ''; ?>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>

                <?php if ($layout_post === 'grid' && !empty($see_more) && is_array($see_more) && !empty($see_more['url'])):
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
        <?php elseif ($layout_post == 'box') : ?>
            <div class="lv_latest_posts_wrap <?php echo 'lv_latest_posts_wrap_' . $layout_post; ?>">
                <?php if (!empty($list_title)): ?>
                    <h2 class="lv_latest_posts_heading text_center">
                        <?php echo $list_title; ?>
                    </h2>
                <?php endif; ?>

                <div class="lv_latest_posts_box_wrap">
                    <?php
                    $background_post = get_field('background_post', 'option');
                    echo wp_get_attachment_image($background_post, 'full', false, ['class' => 'lv_latest_posts_box_bg'])
                    ?>

                    <div class="lv_latest_posts_box">
                        <?php while ($q->have_posts()): $q->the_post();
                            $url   = get_permalink();
                            $title = get_the_title();
                            $date  = get_the_date('d/m/Y');
                        ?>
                            <article class="lv_latest_posts_card">
                                <?php if (has_post_thumbnail()): ?>
                                    <a class="lv_latest_posts_thumb" href="<?php echo $url; ?>">
                                        <?php echo get_the_post_thumbnail(get_the_ID(), 'large', ['loading' => 'lazy']); ?>
                                    </a>
                                <?php endif; ?>

                                <div class="lv_latest_posts_content">
                                    <?php if (!empty($title)): ?>
                                        <h3 class="lv_latest_posts_title">
                                            <a href="<?php echo $url; ?>">
                                                <?php echo $title; ?>
                                            </a>
                                        </h3>
                                    <?php endif; ?>

                                    <?php if (!empty($date)): ?>
                                        <div class="lv_latest_posts_date"><?php echo $date; ?></div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </div>

                    <?php
                    if (!empty($see_more) && is_array($see_more) && !empty($see_more['url'])):
                        $sm_url    = $see_more['url'];
                    ?>
                        <a class="lv_latest_posts_next" href="<?php echo $sm_url; ?>">
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
    $items = get_field('menu_bottom', 'option');

    ob_start();

    if ($items && is_array($items)) : ?>
        <nav class="lv_footer_menu" aria-label="Footer quick actions">
            <?php foreach ($items as $row) :
                $icon  = isset($row['icon']) ? $row['icon'] : 0;
                $link  = isset($row['link']) && is_array($row['link']) ? $row['link'] : [];

                $url    = isset($link['url']) ? trim($link['url']) : '#';
                $title  = isset($link['title']) ? trim($link['title']) : '';
                $target = isset($link['target']) && $link['target'] ? $link['target'] : '_self';

                if (!$icon && !$title) {
                    continue; // bỏ qua nếu trống hết
                }
            ?>
                <a class="lv_footer_menu_item"
                    href="<?php echo $url; ?>"
                    target="<?php echo $target; ?>"
                    rel="noopener nofollow sponsored">
                    <span class="lv_footer_menu_icon" aria-hidden="true">
                        <?php
                        if ($icon) {
                            echo wp_get_attachment_image(
                                $icon,
                                'thumbnail',
                                false,
                                ['class' => 'lv_footer_menu_icon_image']
                            );
                        } else {
                            echo '<span class="lv_footer_menu_icon_placeholder"></span>';
                        }
                        ?>
                    </span>
                    <?php if ($title) : ?>
                        <span class="lv_footer_menu_label"><?php echo $title; ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif;

    return ob_get_clean();
});

function lv_countdown_shortcode()
{
    // Lấy ngày giờ hiện tại
    $current_date = new DateTime();
    $current_time = $current_date->format('H:i'); // Lấy giờ phút hiện tại

    // Xác định thời gian đếm ngược
    $target_date = clone $current_date;
    $target_date->setTime(18, 30); // Đặt thời gian đếm ngược là 18:30

    // Nếu giờ hiện tại đã qua 18:30, thì chọn 18:30 ngày hôm sau
    if ($current_time > '18:30') {
        $target_date->modify('+1 day');
    }

    // Lấy ngày giờ đếm ngược theo định dạng Y-m-d H:i:s cho JavaScript
    $formatted_date = $target_date->format('Y-m-d\TH:i:s');

    // HTML động cấu trúc countdown
    ob_start();  // Bắt đầu ghi đệm nội dung
    ?>
    <div class="lv_timer_countdown">
        <div class="lv_timer_countdown_item lv_timer_countdown_hours">
            <span class="lv_timer_countdown_value" id="hours">0</span>
            <span class="lv_timer_countdown_label">HOURS</span>
        </div>
        <div class="lv_timer_countdown_item lv_timer_countdown_minutes">
            <span class="lv_timer_countdown_value" id="minutes">0</span>
            <span class="lv_timer_countdown_label">MIN</span>
        </div>
        <div class="lv_timer_countdown_item lv_timer_countdown_seconds">
            <span class="lv_timer_countdown_value" id="seconds">0</span>
            <span class="lv_timer_countdown_label">SEC</span>
        </div>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Định dạng ngày đích cho countdown
            var targetDate = new Date('<?php echo $formatted_date; ?>');

            function updateCountdown() {
                var now = new Date();
                var timeRemaining = targetDate - now;

                if (timeRemaining <= 0) {
                    // Nếu thời gian đếm ngược đã kết thúc, tính toán lại đếm ngược cho ngày hôm sau
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

            // Cập nhật countdown mỗi giây
            var countdownInterval = setInterval(updateCountdown, 1000);
        });
    </script>
    <?php
    // Lấy toàn bộ nội dung đã ghi đệm và trả về
    return ob_get_clean();
}
add_shortcode('lv_countdown', 'lv_countdown_shortcode');

function lv_card_category_shortcode($atts)
{
    // Get the ACF fields
    $style_category = get_field('style_category', 'option'); // Get the selected style
    $list_category = get_field('list_category', 'option'); // Get the list of categories
    $title_category = get_field('title_category', 'option'); // Get the list of categories

    // Start output buffering
    ob_start();

    if ($list_category) {
        echo '<div class="lv_container">';
        if (!empty($title_category)): ?>
            <h2 class="lv_block_card_category_title text_center">
                <?php echo $title_category; ?>
            </h2>
        <?php endif;

        if ($style_category == '1' || $style_category == '2') : ?>
            <!-- Add the dynamic class for style category if selected -->
            <div class="lv_block_card_category <?php echo $style_category ? 'lv_block_card_category_style_' . $style_category : ''; ?>">
                <?php foreach ($list_category as $category) : ?>
                    <?php
                    // Extract the values from the ACF repeater sub-fields
                    $link = $category['link']; // Get the link
                    $description = $category['description']; // Get the description
                    $image_id = $category['image']; // Get the image ID
                    $background_color = $category['background_color']; // Get the background color

                    // Check if the link exists
                    $link_url = !empty($link) ? $link['url'] : '#';
                    $link_title = !empty($link) ? $link['title'] : '';
                    $link_target = !empty($link) ? $link['target'] : '';

                    // Set the default background color if not provided
                    $background_color = !empty($background_color) ? $background_color : '#2563EB';

                    // Check if an image exists and get its HTML
                    $image_html = '';
                    if (!empty($image_id)) {
                        $image_html = wp_get_attachment_image($image_id, 'medium'); // Get image HTML using default WP function
                    }
                    ?>

                    <!-- Check if the description exists, if not, skip the card -->
                    <a target="<?php echo $link_target; ?>" href="<?php echo $link_url; ?>" class="lv_block_card_category_card" style="background-color:<?php echo $background_color; ?>;">

                        <?php if ($style_category == '2') : ?>
                            <div class="lv_block_card_category_inner">
                            <?php endif; ?>

                            <?php if ($image_html) : ?>
                                <div class="lv_block_card_category_icon"><?php echo $image_html; ?></div>
                            <?php endif; ?>

                            <?php if ($style_category == '2') : ?>
                                <div class="lv_block_card_category_content">
                                <?php endif; ?>

                                <?php echo $link_title ? '<div class="lv_block_card_category_title">' . $link_title . '</div>' : ''; ?>
                                <?php echo $description ? '<div class="lv_block_card_category_desc">' . $description . '</div>' : ''; ?>

                                <?php if ($style_category == '2') : ?>
                                </div> <!-- End of lv_block_card_category_content -->
                            </div> <!-- End of lv_block_card_category_inner -->

                            <div class="lv_block_card_category_right">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                    <path d="M566.6 342.6C579.1 330.1 579.1 309.8 566.6 297.3L406.6 137.3C394.1 124.8 373.8 124.8 361.3 137.3C348.8 149.8 348.8 170.1 361.3 182.6L466.7 288L96 288C78.3 288 64 302.3 64 320C64 337.7 78.3 352 96 352L466.7 352L361.3 457.4C348.8 469.9 348.8 490.2 361.3 502.7C373.8 515.2 394.1 515.2 406.6 502.7L566.6 342.7z" />
                                </svg>
                            </div>
                        <?php endif; ?>
                    </a> <!-- End of card -->
                <?php endforeach; ?>
            </div> <!-- End of card category container -->
        <?php elseif ($style_category == '3') : ?>
            <section class="lv_category_list lv_category_list_<?php echo $style_category; ?>">
                <div class="lv_category_list_wrapper">
                    <?php foreach ($list_category as $item) :
                        $link = $item['link'];
                        $image_id = $item['image'];
                        $description = $item['description'];
                    ?>
                        <?php if ($link && $image_id) : ?>
                            <div class="lv_category_list_item">
                                <?php echo wp_get_attachment_image($image_id, 'full', false, ['class' => 'lv_category_list_item_img', 'loading' => 'lazy']); ?>
                                <div class="lv_category_list_item_overlay">
                                    <h3 class="lv_category_list_item_title"><?php echo $link['title']; ?></h3>

                                    <?php if ($style_category != 3 && $description) : ?>
                                        <p class="lv_category_list_item_desc"><?php echo $description; ?></p>
                                    <?php endif; ?>

                                    <a href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>" class="lv_category_list_item_btn">
                                        Chơi Ngay
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php elseif ($style_category == '4'): ?>
            <section class="lv_category_list lv_category_list_<?php echo $style_category; ?>">
                <div class="lv_category_list_wrapper">
                    <?php
                    foreach ($list_category as $item) :
                        $link = $item['link'] ?? [];
                        $image_id = $item['image'];
                    ?>
                        <?php if ($image_id) : ?>
                            <a target="<?php echo $link['target']; ?>" href="<?php echo $link['url'] ?? 'javascript:void(0);'; ?>" class="lv_category_list_item">
                                <?php echo wp_get_attachment_image($image_id, 'full', false, ['class' => 'lv_category_list_item_img', 'loading' => 'lazy']); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>
    <?php
        endif;
        echo '</div>';
    }

    // Return the output
    return ob_get_clean();
}

add_shortcode('lv_card_category', 'lv_card_category_shortcode');

function lv_testimonial_shortcode()
{
    // Lấy giá trị của các trường ACF
    $style = get_field('style_testimonial', 'option'); // Style testimonial (1, 2, 3)
    $testimonial_title = get_field('testimonial_title', 'option'); // Tiêu đề testimonial
    $testimonials = get_field('testimonials', 'option'); // Danh sách testimonials (repeater)

    // Kiểm tra xem có testimonial nào không
    if (empty($testimonials)) {
        return ''; // Không có testimonial, không hiển thị gì
    }

    // Khởi tạo output của shortcode
    $output = '';

    $output .= '<div class="lv_container">';

    // Hiển thị tiêu đề nếu có
    if (!empty($testimonial_title)) {
        $output .= '<h2 class="testimonial_title text_center">' . $testimonial_title . '</h2>';
    }

    // Bắt đầu phần testimonial
    $output .= '<div class="lv_testimonial_section lv_testimonial_section_style_' . esc_attr($style) . '">';

    // Lặp qua các testimonial và tạo HTML
    foreach ($testimonials as $testimonial) {
        $title = $testimonial['title']; // Tiêu đề testimonial
        $content = $testimonial['content']; // Nội dung testimonial
        $avatar_id = $testimonial['avatar']; // Avatar (ID ảnh)
        $avatar_url = wp_get_attachment_url($avatar_id); // Lấy URL của ảnh từ ID

        // Kiểm tra style và hiển thị theo kiểu tương ứng
        if ($style == 1) {
            // Style 1: Hiển thị dạng bình thường
            $output .= '<div class="lv_testimonial_item">';
            $output .= '<p class="lv_testimonial_text">"' . esc_html($content) . '"</p>';
            $output .= '<p class="lv_testimonial_author">' . esc_html($title) . '</p>';
            $output .= '</div>';
        } elseif ($style == 2) {
            // Style 2: Hiển thị ảnh và nội dung dạng hình tròn
            $output .= '<div class="lv_testimonial_item">';
            $output .= '<p class="lv_testimonial_text">"' . esc_html($content) . '"</p>';
            $output .= '<p class="lv_testimonial_author">' . esc_html($title) . '</p>';
            if ($avatar_url) {
                $output .= '<img class="lv_testimonial_author_img" src="' . esc_url($avatar_url) . '" alt="' . esc_attr($title) . '">';
            }
            $output .= '</div>';
        } elseif ($style == 3) {
            // Style 3: Hiển thị ảnh và nội dung theo layout ngang
            $output .= '<div class="lv_testimonial_item">';
            if ($avatar_url) {
                $output .= '<div class="lv_testimonial_icon">';
                $output .= '<img src="' . esc_url($avatar_url) . '" alt="' . esc_attr($title) . '" />';
                $output .= '</div>';
            }
            $output .= '<div class="lv_testimonial_item_content">';
            $output .= '<p class="lv_testimonial_text">"' . esc_html($content) . '"</p>';
            $output .= '<p class="lv_testimonial_author">' . esc_html($title) . '</p>';
            $output .= '</div>';
            $output .= '</div>';
        }
    }

    // Đóng phần testimonial
    $output .= '</div>';
    $output .= '</div>';

    // Trả về output để hiển thị
    return $output;
}

// Đăng ký shortcode
add_shortcode('lv_testimonial', 'lv_testimonial_shortcode');

add_shortcode('lv_cta', function () {

    if (! function_exists('get_field')) {
        return '';
    }

    // Lấy dữ liệu từ ACF Options
    $title   = get_field('title_cta', 'option');
    $desc    = get_field('description_cta', 'option');
    $button  = get_field('button_cta', 'option');        // array: url, title, target
    $bg_id   = get_field('background_cta', 'option');    // image ID

    // Kiểm tra có gì để hiển thị không
    $has_btn = (is_array($button) && !empty($button['url']) && !empty($button['title']));
    $has_bg  = !empty($bg_id);
    $has_any = ($title || $desc || $has_btn || $has_bg);

    if (! $has_any) {
        return '';
    }

    // Ảnh nền
    $bg_img_url = $has_bg ? wp_get_attachment_url($bg_id) : '';

    // Button
    $btn_url    = $has_btn ? $button['url'] : '';
    $btn_title  = $has_btn ? $button['title'] : '';
    $btn_target = ($has_btn && !empty($button['target'])) ? $button['target'] : '_self';

    ob_start();
    ?>
    <section class="lv_cta" <?php if ($title) { ?> aria-labelledby="lv_cta_title" <?php } ?>>
        <?php if ($has_bg) : ?>
            <div class="lv_cta_bg" style="background-image: url('<?php echo $bg_img_url; ?>');">
            </div>
        <?php endif; ?>

        <div class="lv_cta_overlay" aria-hidden="true"></div>

        <div class="lv_cta_container">
            <div class="lv_cta_content">
                <?php if ($title) : ?>
                    <h2 id="lv_cta_title" class="lv_cta_title"><?php echo $title; ?></h2>
                <?php endif; ?>

                <?php if ($desc) : ?>
                    <p class="lv_cta_desc"><?php echo $desc; ?></p>
                <?php endif; ?>

                <?php if ($has_btn) : ?>
                    <a class="lv_cta_button lv_cta_button_primary" href="<?php echo $btn_url; ?>" target="<?php echo $btn_target; ?>">
                        <?php echo $btn_title; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    // Return the output
    return ob_get_clean();
});

function lv_partner_shortcode()
{
    // Lấy dữ liệu từ ACF
    $title_partner = get_field('title_partner', 'option'); // Lấy tiêu đề đối tác
    $list_partner = get_field('list_partner', 'option'); // Lấy danh sách đối tác

    // Kiểm tra xem có dữ liệu trong list_partner không
    if ($list_partner):
        ob_start(); // Bắt đầu ghi dữ liệu vào bộ đệm đầu ra
    ?>
        <div class="lv_partner_container">
            <?php if ($title_partner): // Kiểm tra nếu có tiêu đề thì mới hiển thị 
            ?>
                <h2 class="lv_partner_title text_center"><?php echo $title_partner; ?></h2>
            <?php endif; ?>
            <div class="lv_partner">
                <?php
                // Duyệt qua danh sách đối tác
                foreach ($list_partner as $partner):
                    $title = $partner['title']; // Lấy tiêu đề đối tác
                    $logo_id = $partner['logo']; // Lấy ID hình ảnh logo
                    $logo_html = wp_get_attachment_image($logo_id, 'medium'); // Lấy HTML của hình ảnh logo
                ?>
                    <div class="lv_partner_item_wrapper">
                        <div class="lv_partner_item" data-mh="lv_partner_item">
                            <?php if ($logo_html): ?>
                                <?php echo $logo_html; // Hiển thị HTML hình ảnh 
                                ?>
                            <?php endif; ?>
                            <p class="lv_partner_text"><?php echo $title; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
        return ob_get_clean(); // Trả về nội dung bộ đệm đầu ra
    endif;

    return ''; // Nếu không có dữ liệu trong list_partner, trả về chuỗi rỗng
}

// Đăng ký shortcode
add_shortcode('lv_partner', 'lv_partner_shortcode');

function lv_award_shortcode()
{
    // Lấy group 'award' từ trang Options
    $award = function_exists('get_field') ? get_field('award', 'option') : null;

    // Nếu không có gì để hiển thị thì trả về rỗng
    if (
        empty($award) ||
        (empty($award['image']) && empty($award['title']) && empty($award['price']) && empty($award['background']))
    ) {
        return '';
    }

    ob_start();
    ?>
    <div class="lv_award">
        <?php
        // Background image (nếu có)
        if (!empty($award['background'])) {
            echo wp_get_attachment_image($award['background'], 'full', false, array(
                'class' => 'lv_award_bg'
            ));
        }
        ?>
        <div class="lv_award_inner">
            <?php if (!empty($award['image']) || !empty($award['title'])): ?>
                <div class="lv_award_header">
                    <?php if (!empty($award['image'])): ?>
                        <div class="lv_award_header_img_wrap">
                            <?php
                            echo wp_get_attachment_image($award['image'], 'medium', false, array(
                                'class' => 'lv_award_header_img'
                            ));
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($award['title'])): ?>
                        <div class="lv_award_game_name"><?php echo $award['title']; ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($award['price'])): ?>
                <div class="lv_award_body">
                    <div class="lv_award_value">
                        <?php echo $award['price']; ?>
                        <span class="lv_award_value_currency">VND</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="lv_award_footer">
                <div class="lv_award_timer_label">THỜI GIAN CÒN LẠI:</div>
                <div class="lv_award_timer">
                    <span class="lv_award_timer_digit" id="timer-hours">00</span>
                    <span class="lv_award_timer_separator">:</span>
                    <span class="lv_award_timer_digit" id="timer-minutes">00</span>
                    <span class="lv_award_timer_separator">:</span>
                    <span class="lv_award_timer_digit" id="timer-seconds">00</span>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('lv_award', 'lv_award_shortcode');

function wp_breadcrumbs()
{
    $delimiter = '
	<span class="icon">
		<svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M18.3337 10.5013C18.3337 12.7114 17.4557 14.8311 15.8929 16.3939C14.3301 17.9567 12.2105 18.8346 10.0003 18.8346C8.90598 18.8346 7.82234 18.6191 6.8113 18.2003C5.80025 17.7815 4.88159 17.1677 4.10777 16.3939C2.54497 14.8311 1.66699 12.7114 1.66699 10.5013C1.66699 8.29116 2.54497 6.17155 4.10777 4.60875C5.67057 3.04594 7.79019 2.16797 10.0003 2.16797C11.0947 2.16797 12.1783 2.38352 13.1894 2.80231C14.2004 3.2211 15.1191 3.83492 15.8929 4.60875C16.6667 5.38257 17.2805 6.30123 17.6993 7.31227C18.1181 8.32332 18.3337 9.40695 18.3337 10.5013ZM5.00033 11.3346H11.667L8.75033 14.2513L9.93366 15.4346L14.867 10.5013L9.93366 5.56797L8.75033 6.7513L11.667 9.66797H5.00033V11.3346Z" fill="white"/>
        </svg>
	</span>
	';

    $home = __('Home', 'basetheme');
    $before = '<span class="current">';
    $after = '</span>';
    if (!is_admin() && !is_home() && (!is_front_page() || is_paged())) {

        global $post;

        echo '<nav>';
        echo '<div id="breadcrumbs" class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">';

        $homeLink = home_url();
        echo '<a href="' . $homeLink . '">' . $home . '</a>' . $delimiter . ' ';

        switch (true) {
            case is_category() || is_archive():
                $cat_obj = get_queried_object();
                echo $before . $cat_obj->name . $after;
                break;

            case is_single() && !is_attachment():
                $post_type = $post->post_type;

                if ($post_type == 'post') {
                    $categories = get_the_category($post->ID);

                    if (!empty($categories)) {
                        $first_category = $categories[0];
                        echo '<a aria-label="' . $first_category->name . '" href="' . get_category_link($first_category->term_id) . '">' . $first_category->name . '</a>' . $delimiter . ' ';
                    }
                }

                if ($post_type == 'product') {
                    $categories = get_the_terms($post->ID, 'product_cat');

                    if (!empty($categories)) {
                        $first_category = $categories[0];
                        echo '<a aria-label="' . $first_category->name . '" href="' . get_term_link($first_category->term_id, 'product_cat') . '">' . $first_category->name . '</a>' . $delimiter . ' ';
                    }
                }

                echo $before . $post->post_title . $after;
                break;

            case is_page():
                if ($post->post_parent) {
                    $parent_id = $post->ID;
                    echo generate_page_parent($parent_id, $delimiter);
                }

                echo $before . get_the_title() . $after;
                break;

            case is_search():
                echo $before . 'Search' . $after;
                break;

            case is_404():
                echo $before . 'Error 404' . $after;
                break;
        }

        echo '</div>';
        echo '</nav>';
    }
} // end wp_breadcrumbs()

// Generate breadcrumbs ancestor page
function generate_page_parent($parent_id, $delimiter)
{
    $breadcrumbs = [];
    $output = '';

    while ($parent_id) {
        $page = get_post($parent_id);
        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
        $parent_id = $page->post_parent;
    }


    $breadcrumbs = array_reverse($breadcrumbs);
    array_pop($breadcrumbs);

    foreach ($breadcrumbs as $crumb) {
        $output .= $crumb . $delimiter;
    }

    return rtrim($output);
}

function lv_about_us()
{
    ob_start();

    // Lấy dữ liệu từ ACF
    $about_us_group = get_field('about_us', 'option'); // 'option' để lấy giá trị từ options page
    if ($about_us_group) {
        // Kiểm tra xem có dữ liệu không
        $title = isset($about_us_group['title']) ? $about_us_group['title'] : '';
        $content = isset($about_us_group['content']) ? $about_us_group['content'] : '';
        $list = isset($about_us_group['list']) ? $about_us_group['list'] : [];

    ?>
        <div class="lv_container">
            <div class="lv_aboutUs">
                <!-- Hiển thị tiêu đề nếu có -->
                <?php if (!empty($title)) : ?>
                    <h2 class="lv_aboutUs__title text_center"><?php echo $title; ?></h2>
                <?php endif; ?>

                <!-- Hiển thị nội dung nếu có -->
                <?php if (!empty($content)) : ?>
                    <p class="lv_aboutUs__description"><?php echo $content; ?></p>
                <?php endif; ?>

                <div class="lv_aboutUs__boxes">
                    <?php
                    // Hiển thị danh sách nếu có
                    if (!empty($list)) :
                        foreach ($list as $item) :
                            $item_title = isset($item['title']) ? $item['title'] : '';
                            $item_description = isset($item['description']) ? $item['description'] : '';
                    ?>
                            <div class="lv_aboutUs__box">
                                <?php if (!empty($item_title)) : ?>
                                    <h3 class="lv_aboutUs__boxTitle"><?php echo $item_title; ?></h3>
                                <?php endif; ?>

                                <?php if (!empty($item_description)) : ?>
                                    <p class="lv_aboutUs__boxContent"><?php echo $item_description; ?></p>
                                <?php endif; ?>
                            </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
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
    // Get values from ACF option page
    $box_content = get_field('box_content', 'option');

    // Check if box content is set
    if (!$box_content) {
        return ''; // If no content, return empty string
    }

    // Initialize output
    $output = '';

    // Check if title is set and append to output
    if (!empty($box_content['title'])) {
        $output .= '<h2 class="lv_contentBox__title text_center">' . $box_content['title'] . '</h2>';
    }

    // Check if content is set and append to output
    if (!empty($box_content['content'])) {
        $output .= '<div class="lv_contentBox__description">' . $box_content['content'] . '</div>';
    }

    // Check if list is set and append each item
    if (!empty($box_content['list'])) {
        $output .= '<section class="lv_section__contentBox">';

        foreach ($box_content['list'] as $item) {
            // Check if the list item has the necessary values
            if (!empty($item['title']) && !empty($item['description'])) {
                $background_color = !empty($item['background_color']) ? $item['background_color'] : '#ffffff';
                $output .= '<div class="lv_contentBox__item" style="background-color: ' . $background_color . '">';
                $output .= '<h3 class="lv_contentBox__text text_center">' . $item['title'] . '</h3>';
                $output .= '<div class="lv_contentBox__description">' . $item['description'] . '</div>';
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
    // Retrieve ACF settings for the Promo Banner
    $promo_banner = get_field('promo_banner', 'option'); // Assuming the 'promo_banner' field is under the 'option' page

    // Fallbacks for when no ACF value is set
    $messages = isset($promo_banner['messages']) ? $promo_banner['messages'] : '';
    $button_url = isset($promo_banner['link']) ? $promo_banner['link']['url'] : '';
    $button_title = isset($promo_banner['link']) ? $promo_banner['link']['title'] : ''; // Title of the button
    $button_target = isset($promo_banner['link']) ? $promo_banner['link']['target'] : '_self'; // Target attribute for the button

    // Split messages by the pipe symbol
    $raw_msgs = trim($messages);
    $has_button = isset($button_url) && $button_url !== '';

    ob_start();

    if ($messages || $has_button) : ?>
        <div class="lv_promoBanner__page">
            <div class="lv_promoBanner__pill" role="region">
                <?php if ($messages) : ?>
                    <div class="lv_promoBanner__ticker" aria-hidden="false">
                        <div class="lv_promoBanner__marquee" aria-hidden="true">
                            <div class="lv_promoBanner__marqueeItem"><?php echo $raw_msgs; ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($has_button) : ?>
                    <a href="<?php echo $button_url; ?>" class="lv_promoBanner__button" role="link" target="<?php echo $button_target; ?>">
                        <?php echo $button_title; ?>
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
    // Lấy dữ liệu từ ACF
    $list_image = get_field('list_image', 'option'); // Lấy nhóm các trường từ options page
    $list_image_title = get_field('list_image_title', 'option'); // Lấy nhóm các trường từ options page
    $layout = get_field('list_image_layout', 'option') ?: '1';
    $pc_column = isset($list_image['pc_column']) ? $list_image['pc_column'] : 5; // Số cột trên PC
    $sp_column = isset($list_image['sp_column']) ? $list_image['sp_column'] : 5; // Số cột trên mobile
    $list = isset($list_image['list']) ? $list_image['list'] : array(); // Danh sách hình ảnh

    if (!empty($list_image_title)):
    ?>
        <h2 class="lv_block_card_category_title text_center">
            <?php echo $list_image_title; ?>
        </h2>
        <?php
    endif;

    // Bắt đầu HTML cho Shortcode
    $output = '';
    if ($layout == '1') {
        $output .= '<div class="lv_imageList" style="--columns: ' . $pc_column . '; --columns-mobile: ' . $sp_column . ';">';

        if (!empty($list)) {
            foreach ($list as $item) {
                $image_url = wp_get_attachment_image_url($item['image'], 'full'); // Lấy URL của ảnh
                $url = isset($item['url']) ? esc_url($item['url']) : '#'; // Lấy URL (nếu có, nếu không sẽ là #)
                $output .= '<div class="lv_imageList__item">';
                $output .= '<a href="' . $url . '"><img src="' . $image_url . '" alt="Image"></a>';
                $output .= '</div>';
            }
        }

        // Close the lv_imageList div
        $output .= '</div>';
    } else if ($layout == '2') {
        $output .= '<div class="lv_catSlider">';

        // Check if there is a list of images and iterate through them for the slider
        if (!empty($list)) {
            foreach ($list as $item) {
                $image_url = wp_get_attachment_image_url($item['image'], 'full'); // Lấy URL của ảnh
                $url = isset($item['url']) ? esc_url($item['url']) : '#'; // Lấy URL (nếu có, nếu không sẽ là #)

                $output .= '<div class="lv_catSlider__slickSlide">';
                $output .= '<a href="' . $url . '">';
                $output .= '<img src="' . $image_url . '" alt="Item Image" />';
                $output .= '</a>';
                $output .= '</div>';
            }
        }

        // Closing the div for the slider
        $output .= '</div>';
    }

    // Return the final HTML
    return $output; // Trả về HTML để hiển thị
}
add_shortcode('lv_list_image', 'list_image_shortcode');


function lv_content_image_shortcode($atts)
{
    ob_start();

    // Lấy dữ liệu từ ACF
    $layout = get_field('layout_content_image', 'options') ?: '1';
    $content_images = get_field('content_image', 'options');

    // Kiểm tra nếu có dữ liệu
    if ($content_images):
        echo '<div class="lv_content_section_container lv_container lv_content_section_layout_' . $layout . '">';  // Mở thẻ container

        // Duyệt qua mảng dữ liệu bằng vòng lặp foreach
        foreach ($content_images as $index => $image_data):
            $image_id = $image_data['image']; // ID của hình ảnh
            $title = $image_data['title'];
            $content = $image_data['content'];
            $button = $image_data['button'];
            $button_url = isset($button['url']) ? $button['url'] : '#';
            $button_text = isset($button['title']) ? $button['title'] : 'Xem thêm';
            $target = isset($button['target']) ? $button['target'] : '';
            $image_title = $image_data['image_title'];

            if ($index % 2 == 0):
        ?>
                <div class="lv_content_section">
                    <div class="lv_content_section__image">
                        <?php
                        echo wp_get_attachment_image($image_id, 'full');
                        ?>
                    </div>
                    <div class="lv_content_section__text">
                        <?php if ($layout == '1' && $title) : ?>
                            <h2 class="lv_content_section__heading"><?php echo $title; ?></h2>
                        <?php endif; ?>

                        <?php if ($layout == '2' && $image_title) : ?>
                            <a href="<?php echo $button_url; ?>" target="<?php echo $target; ?>" class="lv_content_section_image_title">
                                <?php
                                echo wp_get_attachment_image($image_title, 'full');
                                ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($content) : ?>
                            <div class="lv_content_section__description"><?php echo $content; ?></div>
                        <?php endif; ?>

                        <?php if ($layout == '1' && $button_url && $button_text) : ?>
                            <a target="<?php echo $target; ?>" href="<?php echo $button_url; ?>" class="lv_content_section__link"><?php echo $button_text; ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php
            else:
            ?>
                <div class="lv_content_section">
                    <div class="lv_content_section__text">
                        <?php if ($layout == '1' && $title) : ?>
                            <h2 class="lv_content_section__heading"><?php echo $title; ?></h2>
                        <?php endif; ?>

                        <?php if ($layout == '2' && $image_title) : ?>
                            <a href="<?php echo $button_url; ?>" target="<?php echo $target; ?>" class="lv_content_section_image_title">
                                <?php
                                echo wp_get_attachment_image($image_title, 'full');
                                ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($content) : ?>
                            <div class="lv_content_section__description"><?php echo $content; ?></div>
                        <?php endif; ?>

                        <?php if ($layout == '1' && $button_url && $button_text) : ?>
                            <a target="<?php echo $target; ?>" href="<?php echo $button_url; ?>" class="lv_content_section__link"><?php echo $button_text; ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="lv_content_section__image">
                        <?php
                        echo wp_get_attachment_image($image_id, 'full');
                        ?>
                    </div>
                </div>
        <?php
            endif; // End kiểm tra chẵn/lẻ
        endforeach;

        echo '</div>';  // Đóng thẻ container
    endif;

    return ob_get_clean();
}
add_shortcode('lv_content_image', 'lv_content_image_shortcode');

function shortcode_tabs_category()
{
    ob_start();

    // Lấy dữ liệu từ ACF Options
    $tabs_category = get_field('tabs_category', 'option');

    if (!empty($tabs_category) && !empty($tabs_category['list'])) :
        ?>
        <div class="lv_tabs_wrapper">
            <!-- Tabs -->
            <ul class="lv_tabs_nav">
                <?php
                $first = true;
                foreach ($tabs_category['list'] as $index => $tab) :
                    if (!empty($tab['title'])) :
                        $tab_id = 'tab' . $index;
                ?>
                        <li class="lv_tabs_navItem <?php echo $first ? 'active' : ''; ?>" data-tab="<?php echo $tab_id; ?>">
                            <?php
                            if (!empty($tab['title_icon'])) {
                                echo wp_get_attachment_image($tab['title_icon'], 'thumbnail', false, ['class' => 'lv_tabs_icon']);
                            }
                            ?>
                            <span class="lv_tabs_text"><?php echo $tab['title']; ?></span>
                        </li>
                <?php
                        $first = false;
                    endif;
                endforeach;
                ?>
            </ul>

            <!-- Content -->
            <div class="lv_tabs_content">
                <?php
                $first = true;
                foreach ($tabs_category['list'] as $index => $tab) :
                    if (!empty($tab['title']) && !empty($tab['list_item'])) :
                        $tab_id = 'tab' . $index;
                ?>
                        <div id="<?php echo $tab_id; ?>" class="lv_tabs_cat_panel <?php echo $first ? 'active' : ''; ?>">
                            <div class="lv_tabs_grid">
                                <?php foreach ($tab['list_item'] as $item) :
                                    if (!empty($item['image'])) :
                                        $url = !empty($item['url']) ? $item['url'] : '#';
                                ?>
                                        <a href="<?php echo $url; ?>" class="lv_tabs_item">
                                            <?php echo wp_get_attachment_image($item['image'], 'medium'); ?>
                                        </a>
                                <?php endif;
                                endforeach; ?>
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
