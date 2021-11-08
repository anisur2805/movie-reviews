<?php
add_action( 'init', 'create_my_taxonomies', 0 );

function create_my_taxonomies() {
    register_taxonomy(
        'movie_reviews_movie_genre',
        'movie_reviews',
        array(
            'labels'        => array(
                'name'          => 'Movie Genre',
                'add_new_item'  => 'Add New Movie Genre',
                'new_item_name' => "New Movie Type Genre",
            ),
            'show_ui'       => true,
            'show_tagcloud' => false,
            'hierarchical'  => true,
        )
    );
}

add_filter( 'manage_edit-movie_reviews_columns', 'my_columns' );
function my_columns( $columns ) {
    $columns['movie_reviews_director'] = 'Director';
    $columns['movie_reviews_rating']   = 'Rating';
    unset( $columns['comments'] );
    return $columns;
}

add_action( 'manage_posts_custom_column', 'populate_columns' );
function populate_columns( $column ) {
    if ( 'movie_reviews_director' == $column ) {
        $movie_director = esc_html( get_post_meta( get_the_ID(), 'movie_director', true ) );
        echo $movie_director;
    } elseif ( 'movie_reviews_rating' == $column ) {
        $movie_rating = get_post_meta( get_the_ID(), 'movie_rating', true );
        echo $movie_rating . ' stars';
    }
}

add_filter( 'manage_edit-movie_reviews_sortable_columns', 'sort_me' );
function sort_me( $columns ) {
    $columns['movie_reviews_director'] = 'movie_reviews_director';
    $columns['movie_reviews_rating']   = 'movie_reviews_rating';

    return $columns;
}

// add_filter( 'request', 'column_ordering' );
add_filter( 'request', 'column_orderby' );

function column_orderby( $vars ) {
	// echo '<pre>';
	// print_r( $vars );
	
    if ( !is_admin() ) {
        return $vars;
    }

    if ( isset( $vars['orderby'] ) && 'movie_reviews_director' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array( 'meta_key' => 'movie_director', 'orderby' => 'meta_value' ) );
    } elseif ( isset( $vars['orderby'] ) && 'movie_reviews_rating' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array( 'meta_key' => 'movie_rating', 'orderby' => 'meta_value_num' ) );
    }
    return $vars;
}

add_action( 'restrict_manage_posts', 'my_filter_list' );
function my_filter_list() {
    $screen = get_current_screen();
    global $wp_query;
    if ( $screen->post_type == 'movie_reviews' ) {
        wp_dropdown_categories( array(
            'show_option_all' => 'Show All Movie Genres',
            'taxonomy' => 'movie_reviews_movie_genre',
            'name' => 'movie_reviews_movie_genre',
            'orderby' => 'name',
            'selected' => ( isset( $wp_query->query['movie_reviews_movie_genre'] ) ? $wp_query->query['movie_reviews_movie_genre'] : '' ),
            'hierarchical' => false,
            'depth' => 3,
            'show_count' => false,
            'hide_empty' => true,
        ) );
    }
}

add_filter( 'parse_query','perform_filtering' );
function perform_filtering( $query ) {
    $qv = &$query->query_vars;
    if ( ( $qv['movie_reviews_movie_genre'] ) && is_numeric( $qv['movie_reviews_movie_genre'] ) ) {
        $term = get_term_by( 'id', $qv['movie_reviews_movie_genre'], 'movie_reviews_movie_genre' );
        $qv['movie_reviews_movie_genre'] = $term->slug;
    }
}
