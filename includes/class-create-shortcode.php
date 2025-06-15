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

        ob_start();
        ?>
        <section class="<?php echo esc_attr($prefix); ?>">
            <h2 class="<?php echo esc_attr($prefix); ?>__title"><?php echo esc_html($shortcode_attr['title']); ?></h2>
            <div class="<?php echo esc_attr($prefix); ?>__listing">

            <?php foreach ($articles as $article) :
                echo $this->renderSinglePost($article, $prefix);
            endforeach; ?>
            </div>
        </section>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    private function renderSinglePost($article, $prefix)
    {
        setup_postdata($article);

        $post_id = $article->ID;
        $title = get_the_title($post_id);
        $category_obj = get_the_category($post_id);
        $category_name = !empty($category_obj) ? $category_obj[0]->name : '';
        $img_url = get_the_post_thumbnail_url($post_id);
        $img = $img_url ? $img_url : AIP_PLUGIN_URL . 'assets/img/aip_thumbnail.png';
        $rating = get_post_meta($post_id, 'rating', true);
        $permalink = get_permalink($post_id);
        $site_link = get_post_meta($post_id, 'site_link', true);

        ob_start();
        ?>
        <article class="<?php echo $prefix ?>__article">
            <div class="<?php echo $prefix ?>__article-thumbnail">
                <img src="<?php echo esc_url($img) ?>" alt="<?php echo esc_attr($title) ?>">
            </div>
            <div class="<?php echo $prefix ?>__article-content">
                <header class="<?php echo $prefix ?>__article-header"> 
                    <?php if ($category_name) : ?>
                        <p class="<?php echo $prefix ?>__label"><?php echo esc_html($category_name) ?></p>
                    <?php endif ?>
                    <p class="<?php echo $prefix ?>__article-title">
                        <?php echo esc_html($title) ?>
                    </p>
                </header>
    
                <footer class="<?php echo $prefix ?>__article-footer">
                    <a class="<?php echo $prefix ?>__link" href="<?php echo esc_url($permalink) ?>">
                        <?php echo esc_html__('Read more', 'auto-import-posts') ?>
                    </a>
    
                    <?php if (!empty($rating)) : ?>
                            <p class="<?php echo $prefix ?>__label">
                                ⭐️ <?php echo esc_html($rating) ?>
                            </p>
                    <?php endif; ?>
    
                    <?php if (!empty($site_link)) : ?>
                        <a class="<?php echo $prefix ?>__btn" target="_blank" 
                            href="<?php echo esc_url($site_link)  ?>" 
                            rel="nofollow noopener noreferrer">
                            <?php echo esc_html__('Visit Site', 'auto-import-posts') ?>
                        </a>
                    <?php endif; ?>
                </footer>
            </div>
        </article>
        <?php
        return ob_get_clean();
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
            return '';
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

        return !empty($articles) ? $this->renderPosts($articles, $shortcode_attr) : '';
    }
}
