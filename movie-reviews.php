<?php
/*
Plugin Name: Movie Reviews
Plugin URI: https://wp.tutsplus.com/
Description: Declares a plugin that will create a custom post type displaying movie reviews.
Version: 1.0
Author: Soumitra Chakraborty
Author URI: http://wp.tutsplus.com/
License: GPLv2
*/


require_once plugin_dir_path( __FILE__ ) . './movie-taxonomy.php';

add_action( 'init', 'create_movie_review' );
function create_movie_review() {
    register_post_type( 'movie_reviews',
        array(
            'labels' => array(
                'name' => 'Movie Reviews',
                'singular_name' => 'Movie Review',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Movie Review',
                'edit' => 'Edit',
                'edit_item' => 'Edit Movie Review',
                'new_item' => 'New Movie Review',
                'view' => 'View',
                'view_item' => 'View Movie Review',
                'search_items' => 'Search Movie Reviews',
                'not_found' => 'No Movie Reviews found',
                'not_found_in_trash' => 'No Movie Reviews found in Trash',
                'parent' => 'Parent Movie Review'
            ),
 
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'editor', 'comments', 'thumbnail', 'custom-fields' ),
            'taxonomies' => array( '' ),
            'menu_icon' => 'dashicons-format-video',
            'has_archive' => true
        )
    );
}

add_action( 'admin_init', 'my_admin' );
function my_admin() {
    add_meta_box( 'movie_review_meta_box',
        'Movie Review Details',
        'display_movie_review_meta_box',
        'movie_reviews', 'normal', 'high'
    );
}



function display_movie_review_meta_box( $movie_review ) {
	
	// var_dump($movie_review);
	// die();
	
    // Retrieve current name of the Director and Movie Rating based on review ID
    $movie_director = esc_html( get_post_meta( $movie_review->ID, 'movie_director', true ) );
    $movie_rating = intval( get_post_meta( $movie_review->ID, 'movie_rating', true ) );
    ?>
    <table>
        <tr>
            <td style="width: 100%">Movie Director</td>
            <td><input type="text" size="80" name="movie_review_director_name" value="<?php echo $movie_director; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 150px">Movie Rating</td>
            <td>
                <select style="width: 100px" name="movie_review_rating">
                <?php
                // Generate all items of drop-down list
                for ( $rating = 5; $rating >= 1; $rating -- ) {
                ?>
                    <option value="<?php echo $rating; ?>" <?php echo selected( $rating, $movie_rating ); ?>>
                    <?php echo $rating; ?> stars <?php } ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}

add_action( 'save_post', 'add_movie_review_fields', 10, 2 );
function add_movie_review_fields( $movie_review_id, $movie_review ) {
    // Check post type for movie reviews
    if ( $movie_review->post_type == 'movie_reviews' ) {
        // Store data in post meta table if present in post data
        if ( isset( $_POST['movie_review_director_name'] ) && $_POST['movie_review_director_name'] != '' ) {
            update_post_meta( $movie_review_id, 'movie_director', $_POST['movie_review_director_name'] );
        }
        if ( isset( $_POST['movie_review_rating'] ) && $_POST['movie_review_rating'] != '' ) {
            update_post_meta( $movie_review_id, 'movie_rating', $_POST['movie_review_rating'] );
        }
    }
}


add_filter( 'template_include', 'include_template_function', 1 );
function include_template_function( $template_path ) {
    if ( get_post_type() == 'movie_reviews' ) {
        if ( is_single() ) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ( $theme_file = locate_template( array ( 'single-movie_reviews.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path( __FILE__ ) . '/single-movie_reviews.php';
            }
        } elseif( is_archive() ) {
			 if ( $theme_file = locate_template( array ( 'archive-movie_reviews.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path( __FILE__ ) . '/archive-movie_reviews.php';
            }
		}
    }
    return $template_path;
}