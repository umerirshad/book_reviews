<?php
/**
 * Plugin Name:       Chapter 4 - Book Reviews - v2
 * Description:       Adding a new section to the custom post type editor
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Codes Fix
 */

 add_action( 'init', 'ch4_create_book_post_type' );

 function ch4_create_book_post_type(){
    register_post_type( 'book_reviews',
    array(
    'labels' => array(
    'name' => 'Book Reviews',
    'singular_name' => 'Book Review',
    'add_new' => 'Add New',
    'add_new_item' => 'Add New Book Review',
    'edit' => 'Edit',
    'edit_item' => 'Edit Book Review',
    'new_item' => 'New Book Review',
    'view' => 'View',
    'view_item' => 'View Book Review',
    'search_items' => 'Search Book Reviews',
    'not_found' => 'No Book Reviews found',
    'not_found_in_trash' => 'No Book Reviews found in Trash',
    'parent' => 'Parent Book Review'
    ),
    'public' => true,
    'menu_position' => 20,
    'supports' => array( 'title', 'editor', 'thumbnail', 'revisions', 'comments' ),
    'taxonomies' => array('category', 'post_tag'),
    'menu_icon' => plugins_url( 'books-icon.png', __FILE__ ),
    'has_archive' => true,
    'exclude_from_search' => true,
    ) );
 }

 add_action( 'admin_init', 'ch4_admin_init' );
 function ch4_admin_init(){
    add_meta_box( 'ch4_review_details_meta_box',
                    'Book Review Details',
                    'ch4_display_review_details_meta_box',
                    'book_reviews', 'normal', 'high'
    );  
 }
 function ch4_display_review_details_meta_box($book_review){
    $book_author = esc_html( get_post_meta ($book_review->ID, 'book_author', true) );
    $book_rating = intval( get_post_meta ($book_review->ID, 'book_rating', true) );
    ?>

    <table>
        <tr>
            <td style="width:100%">Book Author</td>
            <td><input type="text" size="80" name="book_review_author_name" value="<?php echo $book_author ?>"></td>
        </tr>
        <tr>
            <td style="width:150px">Book Rating</td>
            <td><select style="width:100px" name="book_review_rating">
            <?php
                for ($rating = 5; $rating>=1; $rating--){ ?>
                 <option value="<?php echo $rating; ?>"
                 <?php echo selected ($rating, $book_rating); ?>>
                 <?php echo $rating; ?> stars   
                <?php } ?>
           
            </select></td>
        </tr>
    </table>

 <?php }

 add_action( 'save_post' , 'ch4_add_book_review_fields', 10, 2 );
 function ch4_add_book_review_fields($book_review_id, $book_review){
        
    if ('book_reviews' == $book_review->post_type){
        if(isset($_POST['book_review_author_name'])){
            update_post_meta( $book_review_id, 'book_author', sanitize_text_field( $_POST['book_review_author_name'] ));
        }
        if(isset($_POST['book_review_rating']) && !empty($_POST['book_review_rating'])){
            update_post_meta($book_review_id, 'book_rating', intval($_POST['book_review_rating']));
        }
    }
 }
 add_filter( 'template_include', 'ch4_template_include', 1 );


 function ch4_template_include($template_path){
        if ( 'book_reviews' == get_post_type() ) {
            if (is_single()) {
            // checks if the file exists in the theme first,
            // otherwise install content filter
            if ( $theme_file = locate_template( array( 'single-book_reviews.php' ) ) ) {
                $template_path = $theme_file;
            } else {
            add_filter( 'the_content', 'ch4_display_single_book_review', 20 );
            }
            }
        }
            return $template_path;
    }


 function ch4_display_single_book_review($content){
  
     if ( !empty( get_the_ID() ) ) {
          
        // Display featured image in right-aligned floating div
        $content = '<div style="float: right; margin: 10px">';
        $content .= get_the_post_thumbnail( get_the_ID(), 'medium' );
        $content .= '</div>';
        $content .= '<div class="entry-content">';
        // Display Author Name
        $content .= '<strong>Author: </strong>';
        $content .= esc_html( get_post_meta( get_the_ID(), 'book_author', true ) );
        $content .= '<br />';
        // Display yellow stars based on rating -->
        $content .= '<strong>Rating: </strong>';
        $nb_stars = intval( get_post_meta( get_the_ID(), 'book_rating', true ) );
        for ( $star_counter = 1; $star_counter <= 5;
        $star_counter++ ) {
        if ( $star_counter <= $nb_stars ) {
        $content .= '<img style="width:16px; height:16px;" src="' .
        plugins_url( 'star-review.png', __FILE__ ) . '" />';
        
        } else {
        $content .= '<img src="' .
        plugins_url( 'star-icon-grey.png', __FILE__ ). '" />';
        }
        }
        // Display book review contents
        $content .= '<br /><br />';
        $content .='<p style="font-size:18px">';
        $content .= get_the_content( get_the_ID() );
        $content .='</p>';
        $content .= '</div>';
        return $content;
    }
 }

 add_shortcode( 'book-review-list', 'ch4_book_review_list' );

 function ch4_book_review_list(){
    $query_params = array('post_type' => 'book_reviews',
    'post_status' => 'publish',
    'posts_per_page' =>  4);

    $page_num = ( get_query_var (  'paged' ) ? get_query_var ('paged') : 1 );
    if ( $page_num !=1){
        $query_params['paged'] = $page_num;
    }

    $book_review_query = new WP_Query;
    $book_review_query->query($query_params);

    if($book_review_query->have_posts(  )){
        $output = '<table>';
        $output .= '<tr>';
        $output .= '<th style="width:350px"><strong>Title: </strong></th>';
        $output .= '<th><strong>Author: </strong></th></tr>';

        while ( $book_review_query->have_posts(  )){
            $book_review_query->the_post(  );
            $output .= '<tr><td><a href="'. get_the_permalink( ) . '">';
            $output .= get_the_title( get_the_ID () ) . '</a></td>';
            $output .= '<td>';
            $output .= esc_html( get_post_meta( get_the_ID(), 'book_author', true));
            $output .= '</td></tr>';
        }
        $output .= '</table>';

        if( $book_review_query->max_num_pages > 1){
            $output .= '<nav id="nav-below">';
            $output .= '<div class="nav-previous">';
            $output .= get_next_posts_link( '<span class="meta-nav">&rarr;</span>'. 'Older reviews', $book_review_query->max_num_pages );
            $output .= '</div>';
            $output .= '<div class="nav-next">';
            $output .= get_previous_posts_link( 'Newer reviews' . '<span class="meta-nav">&rarr;</span>', $book_review_query->max_num_pages );
            $output .= '</div>';
            $output .= '</nav>';
        }
        wp_reset_postdata(  );
    }
    return $output;
 }
?>