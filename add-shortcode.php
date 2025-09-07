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

?>
    <style>
        :root {
            --lv-color-primary: <?php echo esc_html($color_primary); ?>;
            --lv-color-menu: <?php echo esc_html($color_menu); ?>;
            --lv-color-countdown: <?php echo esc_html($color_countdown); ?>;
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
    }
});

// Shortcode Tabs [lv_tabs]
function lv_tabs_shortcode()
{
    ob_start();

    $tabs_title = get_field('tabs_title', 'option');
    $list_tabs  = get_field('list_tab', 'option');

    if ($tabs_title || $list_tabs) : ?>
        <section class="lv_tabs">
            <?php if ($tabs_title) : ?>
                <h2 class="lv_tabs_title text_center"><?php echo $tabs_title; ?></h2>
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
?>
    <!-- lv_service_style_2 -->
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
                            <?php echo wp_get_attachment_image($icon, 'full', false, ['class' => 'lv_service_item_icon']); ?>
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
    $faqs_title = get_field('faqs_title', 'option');
    $style_faqs = get_field('style_faqs', 'option') ?? '1';

    if ($faqs && is_array($faqs)) {
        ob_start(); ?>

        <?php if ($faqs_title) : ?>
            <h2 class="lv_faq_title text_center">
                <?php echo $faqs_title; ?>
            </h2>
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
    $layout_post   = get_field('layout_post', 'option'); // grid | slider

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

    <div class="lv_latest_posts_wrap <?php echo $layout_post === 'slider' ? 'is-slider' : 'is-grid'; ?>">
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

                    <?php if (!empty($title)): ?>
                        <h3 class="lv_latest_posts_title">
                            <a href="<?php echo $url; ?>"><?php echo $title; ?></a>
                        </h3>
                    <?php endif; ?>

                    <?php if ($show_date && !empty($date)): ?>
                        <div class="lv_latest_posts_date"><?php echo $date; ?></div>
                    <?php endif; ?>

                    <?php if ($show_excerpt && !empty($desc)): ?>
                        <div class="lv_latest_posts_desc"><?php echo $desc; ?></div>
                    <?php endif; ?>

                    <?php if ($show_button): ?>
                        <a class="lv_latest_posts_btn" href="<?php echo $url; ?>">
                            <?php echo 'Xem thêm'; ?>
                        </a>
                    <?php endif; ?>
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

function lv_countdown_shortcode($atts)
{
    // Thiết lập giá trị mặc định cho tham số "date"
    $atts = shortcode_atts(
        array(
            'date' => '31/12/2025', // Ngày mặc định
        ),
        $atts,
        'lv_countdown'
    );

    // Chuyển đổi định dạng d/m/Y sang Y-m-d để JavaScript hiểu được
    $date_parts = explode('/', $atts['date']);
    $formatted_date = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0] . 'T23:59:59';

    // HTML cấu trúc countdown
    $output = '<div class="lv_timer_countdown">
                <div class="lv_timer_countdown_item lv_timer_countdown_weeks">
                    <span class="lv_timer_countdown_value" id="weeks">0</span>
                    <span class="lv_timer_countdown_label">WEEKS</span>
                </div>
                <div class="lv_timer_countdown_item lv_timer_countdown_days">
                    <span class="lv_timer_countdown_value" id="days">0</span>
                    <span class="lv_timer_countdown_label">DAYS</span>
                </div>
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
            </div>';

    // Đoạn script để tính toán và cập nhật countdown
    $output .= "
        <script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function() {
                // Định dạng ngày đích cho countdown
                var targetDate = new Date('{$formatted_date}');

                function updateCountdown() {
                    var now = new Date();
                    var timeRemaining = targetDate - now;

                    if (timeRemaining <= 0) {
                        clearInterval(countdownInterval);
                        document.getElementById('lv-countdown').innerHTML = 'Countdown Completed';
                    } else {
                        var weeks = Math.floor(timeRemaining / (1000 * 60 * 60 * 24 * 7));
                        var days = Math.floor((timeRemaining % (1000 * 60 * 60 * 24 * 7)) / (1000 * 60 * 60 * 24));
                        var hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        var minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                        var seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

                        document.getElementById('weeks').innerHTML = weeks;
                        document.getElementById('days').innerHTML = days;
                        document.getElementById('hours').innerHTML = hours;
                        document.getElementById('minutes').innerHTML = minutes;
                        document.getElementById('seconds').innerHTML = seconds;
                    }
                }

                // Cập nhật countdown mỗi giây
                var countdownInterval = setInterval(updateCountdown, 1000);
            });
        </script>
    ";

    return $output;
}
add_shortcode('lv_countdown', 'lv_countdown_shortcode');

function lv_card_category_shortcode($atts)
{
    // Get the ACF fields
    $style_category = get_field('style_category', 'option'); // Get the selected style
    $list_category = get_field('list_category', 'option'); // Get the list of categories

    // Start output buffering
    ob_start();

    if ($list_category) {
        // Add the dynamic class for style category if selected
        $style_class = $style_category ? 'lv_block_card_category_style_' . $style_category : '';
        echo '<div class="lv_block_card_category ' . $style_class . '">';

        foreach ($list_category as $category) {
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

            // Check if the description exists, if not, skip the card
            if (!empty($description)) {
                // Start rendering each card with dynamic content
                echo '<a target="' . $link_target . '" href="' . $link_url . '" class="lv_block_card_category_card" style="background-color:' . $background_color . ';">';

                // Display image if available
                if ($image_html) {
                    echo '<div class="lv_block_card_category_icon">' . $image_html . '</div>';
                }

                // Display title and description
                echo '<div class="lv_block_card_category_title">' . $link_title . '</div>';
                echo '<div class="lv_block_card_category_desc">' . $description . '</div>';

                echo '</a>'; // End of card
            }
        }

        echo '</div>'; // End of card category container
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
