<?php
/*
Plugin Name: Related Posts
Description: Displays related posts with the option to set the number and filter.
Version: 1.1
Author: Sajad afsar
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Add settings to the admin menu
include_once plugin_dir_path(__FILE__) . 'admin-page.php';

// Add styles and scripts for related posts
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('jquery'); // Load jQuery
    wp_enqueue_style('related-posts-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('slick-slider', plugin_dir_url(__FILE__) . 'js/slick.min.js', ['jquery'], null, true);
    wp_enqueue_style('slick-slider-style', plugin_dir_url(__FILE__) . 'css/slick.css');
    wp_enqueue_script('slick-init', plugin_dir_url(__FILE__) . 'js/slick-init.js', ['slick-slider'], null, true); // Initialize Slick script
    wp_enqueue_style('slick-slider-theme-style', plugin_dir_url(__FILE__) . 'css/slick-theme.css');
});

// Display related posts with a slider
function show_related_posts($content) {
    if (is_single()) {
        $post_id = get_the_ID();
        $categories = get_the_category($post_id);
        $tags = get_the_tags($post_id);

        // Selected filter
        $filter_type = get_option('related_posts_filter', 'category');
        $filter_ids = [];

        // Get category or tag IDs based on the filter
        if ($filter_type === 'category' && $categories) {
            $filter_ids = array_map(fn($cat) => $cat->term_id, $categories);
        } elseif ($filter_type === 'tag' && $tags) {
            $filter_ids = array_map(fn($tag) => $tag->term_id, $tags);
        }

        // If filter IDs exist
        if ($filter_ids) {
            $args = [
                ($filter_type === 'category' ? 'category__in' : 'tag__in') => $filter_ids,
                'posts_per_page' => get_option('related_posts_count', 5), 
                'post__not_in' => [$post_id], 
            ];
            $related_posts_query = new WP_Query($args);

            if ($related_posts_query->have_posts()) {
                $content .= '<div class="related-posts"><h3>Related Posts</h3><div class="related-posts-slider"><ul>';
                while ($related_posts_query->have_posts()) {
                    $related_posts_query->the_post();

                    // Get post thumbnail
                    $thumbnail = get_the_post_thumbnail(get_the_ID(), 'thumbnail', ['class' => 'related-thumbnail']);

                    // Add title and link to the post along with the thumbnail
                    $content .= '<li><div class="related-thumbnail-wrapper">' . $thumbnail . '</div><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
                }
                $content .= '</ul></div></div>';
                wp_reset_postdata();
            }
        }
    }
    return $content;
}
add_filter('the_content', 'show_related_posts');
