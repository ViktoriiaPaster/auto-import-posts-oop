<?php

namespace AutoImport;

class InsertPosts
{
    private $fetcher;

    public function __construct(FetchPosts $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    public function autoInsertPosts()
    {
        $posts = $this->fetcher->fetchPosts();

        if (empty($posts) || !is_array($posts)) {
            return;
        }

        foreach ($posts as $post) {
            $title = $post['title'];

            if (!post_exists($title)) {
                $this->insertPost($post);
            }
        }
    }

    private function insertPost($post)
    {

        $author_id = $this->setAuthor();
        $date = $this->setDate();
        $category = $this->setCategory($post['category']);

        $post_data = array(
            'post_title' => $post['title'],
            'post_content' => $post['content'],
            'post_status' => 'publish',
            'post_author' => $author_id,
            'post_category' => array($category),
            'post_date' => $date,
        );

        $post_id = wp_insert_post($post_data);

        if (!$post_id) {
            error_log('Insert post failed:' . $post['title']);
            return;
        }

        if (!empty($post['rating'])) {
            update_post_meta($post_id, 'rating', $post['rating']);
        }

        if (!empty($post['site_link'])) {
            update_post_meta($post_id, 'site_link', $post['site_link']);
        }

        if (!empty($post['image'])) {
            $this->uploadImage($post['image'], $post_id);
        }
    }

    private function setAuthor()
    {
        $args = array(
         'role' => 'administrator',
         'number' => 1
        );

        $authors = get_users($args);

        return !empty($authors) && is_array($authors) ? $authors[0]->ID : 1;
    }

    private function setDate()
    {
        $date = rand(strtotime('-1month'), time());
        $random_date = date('Y-m-d H:i:s', $date);

        return $random_date;
    }

    private function setCategory($category)
    {
        $term = term_exists($category, 'category');

        if (!$term) {
            $category_id = wp_create_category($category);
        } else {
            $category_id = is_array($term) ? $term['term_id'] : $term;
        }

        return $category_id;
    }

    private function uploadImage($image_url, $post_id)
    {
        $attachment_id = media_sideload_image($image_url, $post_id, null, 'id');

        if (is_wp_error($attachment_id)) {
            error_log('Image upload failed:' . $attachment_id->get_error_message());
        }

        set_post_thumbnail($post_id, $attachment_id);

        return $attachment_id;
    }
}
