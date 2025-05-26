<?php

namespace AutoImport;

class CreateShortcode
{
    private function renderPosts($articles, $shortcode_attr)
    {
        if (empty($articles) || !is_array($articles)) {
            return;
        }

        $prefix = 'auto-import-posts';

        $output = '<section class="' . $prefix . '">';
        $output .= '<h2 class="' . $prefix . '__title">' . esc_html($shortcode_attr['title']) . '</h2>';
        $output .= '<div class=" ' . $prefix . '__listing">';

        foreach ($articles as $article) {
            setup_postdata($article);

            $post_id = $article->ID;
            $title = get_the_title($post_id);
            $category_obj = get_the_category($post_id);
            $category = !empty($category_obj) ? $category_obj[0]->name : '';
            $img = get_the_post_thumbnail_url($post_id) ? get_the_post_thumbnail_url($post_id) :
                    AIP_PLUGIN_URL . 'assets/img/aip_thumbnail.png';
            $rating = get_post_meta($post_id, 'rating', true);
            $permalink = get_permalink($post_id);
            $site_link = get_post_meta($post_id, 'site_link', true);

            $output .= '<article class="' . $prefix . '__article">';
            if ($img) {
                $output .= '<div class="' . $prefix . '__article-thumbnail">';
                $output .= '<img src="' . esc_url($img) . '" alt="' . esc_attr($title) . '">';
                $output .= '</div>';
            }

            $output .= '<div class="' . $prefix . '__article-content">';
            $output .= '<header class="' . $prefix . '__article-header">';
            if ($category) {
                $output .= '<p class="' . $prefix . '__label">' . esc_html($category) . '</p>';
            }
            $output .= '<p class="' . $prefix . '__article-title">' . esc_html($title) . '</p>';
            $output .= '</header>';

            $output .= '<footer class="' . $prefix . '__article-footer">';
            $output .= '<a class="' . $prefix . '__link" href="' . esc_url($permalink) . '">' .
                            esc_html__('Read more', 'auto-import-posts') . '</a>';
            $output .= '<div class="' . $prefix . '__external">';
            if (!empty($rating)) {
                $output .= '<p class="' . $prefix . '__label">⭐️&nbsp;' . esc_html($rating) . '</p>';
            }
            if ($site_link !== '') {
                $output .= '<a class="' . $prefix . '__btn" target="_blank" ' .
                           'rel="nofollow noopener noreferrer" href="' . esc_url($site_link) . '">' .
                           esc_html__('Visit Site', 'auto-import-posts') . '</a>';
            }
            $output .= '</div>';
            $output .= '</footer>';
            $output .= '</div>';
            $output .= '</article>';
        }
        $output .= '</div>';
        $output .= '</section>';

        wp_reset_postdata();

        return $output;
    }

    private function enqueueStyles()
    {
        wp_enqueue_style(
            'aip_style',
            AIP_PLUGIN_URL . 'assets/style.css',
            [],
            '1.0'
        );
    }

    public function addShortcode($atts)
    {
        $this->enqueueStyles();

        $shortcode_attr = shortcode_atts(array(
            'title' => 'Articles',
            'count' => -1,
            'sort'  => 'date',
            'ids'   => '',
        ), $atts, 'aip');

        $args = array(
            'post_type'         => 'post',
            'post_status'       => 'publish',
            'posts_per_page'    => intval($shortcode_attr['count']),
            'order'             => 'DESC'
        );

        if (intval($shortcode_attr['count']) == 0) {
            return;
        }

        switch ($shortcode_attr['sort']) {
            case 'title':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'rating':
                $args['meta_key'] = 'rating';
                $args['orderby'] = 'meta_value_num';
                break;
            default:
                $args['orderby'] = 'date';
        }

        if (!empty($shortcode_attr['ids'])) {
            $ids = array_map('intval', explode(',', $shortcode_attr['ids']));
            $args['post__in'] = $ids;
        }

        $articles = get_posts($args);

        if (!empty($articles)) {
            return $this->renderPosts($articles, $shortcode_attr);
        }
    }
}
