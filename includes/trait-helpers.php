<?php
trait FGE_Helpers {

    public function get_unique_acf_values($post_type, $acf_key) {
        $values = [];

        $posts = get_posts([
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ]);

        foreach ($posts as $post_id) {
            $val = get_field($acf_key, $post_id);
            if (is_array($val)) {
                $values = array_merge($values, $val);
            } elseif (!empty($val)) {
                $values[] = $val;
            }
        }

        $values = array_unique($values);
        sort($values);
        return $values;
    }
}
