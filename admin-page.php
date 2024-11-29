<?php
/*
Description: A simple plugin to display related posts based on categories.
Version: 1.1
Author: Sajad afsar
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Register action to display related posts
function show_related_posts($content) {
    if (is_single()) {
        $post_id = get_the_ID();
        $categories = get_the_category($post_id);
        if ($categories) {
            $category_ids = array_map(function($cat) {
                return $cat->term_id;
            }, $categories);

            // Get number of posts from settings
            $posts_per_page = get_option('related_posts_count', 5); // Number of posts from settings

            // Get related posts based on category
            $args = array(
                'category__in' => $category_ids,
                'posts_per_page' => $posts_per_page, // Number of related posts from settings
                'post__not_in' => array($post_id), // Exclude the current post
            );
            $related_posts_query = new WP_Query($args);

            if ($related_posts_query->have_posts()) {
                $content .= '<h3>Related Posts</h3><ul>';
                while ($related_posts_query->have_posts()) {
                    $related_posts_query->the_post();
                    $content .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
                }
                $content .= '</ul>';
                wp_reset_postdata();
            }
        }
    }
    return $content;
}


// Add filter to the content
add_filter('the_content', 'show_related_posts');

// Define shortcode to display related posts
function related_posts_shortcode($atts) {
    if (!is_single()) {
        return '<p>This shortcode is only usable in posts.</p>';
    }

    $post_id = get_the_ID();
    $categories = get_the_category($post_id);

    if ($categories) {
        $category_ids = array_map(function($cat) {
            return $cat->term_id;
        }, $categories);

        // Get number of posts from settings
        $posts_per_page = get_option('related_posts_count', 5); // Number of posts from settings

        // Get related posts
        $args = array(
            'category__in' => $category_ids,
            'posts_per_page' => $posts_per_page, // Number of related posts from settings
            'post__not_in' => array($post_id), // Exclude the current post
        );
        $related_posts_query = new WP_Query($args);

        $output = '<h3>Related Posts</h3><ul>';
        if ($related_posts_query->have_posts()) {
            while ($related_posts_query->have_posts()) {
                $related_posts_query->the_post();
                $output .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }
        } else {
            $output .= '<li>No related posts found.</li>';
        }
        $output .= '</ul>';
        wp_reset_postdata();

        return $output;
    }
    return '<p>This post does not have any categories.</p>';
}

add_shortcode('related_posts', 'related_posts_shortcode');

// Register action for creating plugin settings page
function related_posts_settings_menu() {
    add_options_page(
        'Related Posts Settings',
        'Related Posts',
        'manage_options',
        'related-posts-settings',
        'related_posts_settings_page'
    );
}
add_action('admin_menu', 'related_posts_settings_menu');

// Settings page function
function related_posts_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings
    if (isset($_POST['save_related_posts_settings'])) {
        update_option('related_posts_count', intval($_POST['related_posts_count']));
        update_option('related_posts_filter', sanitize_text_field($_POST['related_posts_filter']));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $count = get_option('related_posts_count', 5);
    $filter = get_option('related_posts_filter', 'category');
    ?>
    <div class="wrap">
        <h1>Related Posts Settings</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="related_posts_count">Number of related posts:</label></th>
                    <td><input type="number" id="related_posts_count" name="related_posts_count" value="<?php echo esc_attr($count); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="related_posts_filter">Filter by:</label></th>
                    <td>
                        <select id="related_posts_filter" name="related_posts_filter">
                            <option value="category" <?php selected($filter, 'category'); ?>>Category</option>
                            <option value="tag" <?php selected($filter, 'tag'); ?>>Tag</option>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="save_related_posts_settings" class="button button-primary">Save Settings</button>
            </p>
        </form>
    </div>
    <?php
}

// Load scripts only on plugin settings page
function related_posts_enqueue_admin_scripts($hook) {
    // Ensure it's loaded only on the plugin settings page
    if ($hook !== 'settings_page_related-posts-settings') {
        return;
    }

    // Load JavaScript file
    wp_enqueue_script(
        'related-posts-admin-script', 
        plugin_dir_url(__FILE__) . 'js/script.js', 
        ['jquery'], 
        '1.0', 
        true
    );

    // Load style if necessary
    wp_enqueue_style(
        'related-posts-admin-style',
        plugin_dir_url(__FILE__) . 'css/style.css', // If CSS file is needed
        [],
        '1.0'
    );
}
add_action('admin_enqueue_scripts', 'related_posts_enqueue_admin_scripts');
