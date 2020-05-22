<?php
/**
 * Plugin Name: Stackover Question
 * Plugin URI: https://omukiguy.com
 * Author: TechiePress
 * Author URI: https://omukiguy.com
 * Description: Move stuff from the custom table to CPTs
 * Version: 0.1.0
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: prefix-plugin-name
*/
function create_auto_cpt() {

    $labels = array(
        'name' => _x( 'Auto', 'Post Type General Name', 'Auto' ),
        'singular_name' => _x( 'Auto', 'Post Type Singular Name', 'Auto' ),
        'menu_name' => _x( 'Auto', 'Admin Menu text', 'Auto' ),
        'name_admin_bar' => _x( 'Auto', 'Add New on Toolbar', 'Auto' ),
        'archives' => __( 'Archivi Auto', 'Auto' ),
        'attributes' => __( 'Attributi delle Auto', 'Auto' ),
        'parent_item_colon' => __( 'Genitori Auto:', 'Auto' ),
        'all_items' => __( 'Tutti le Auto', 'Auto' ),
        'add_new_item' => __( 'Aggiungi nuova Auto', 'Auto' ),
        'add_new' => __( 'Nuovo', 'Auto' ),
        'new_item' => __( 'Auto redigere', 'Auto' ),
        'edit_item' => __( 'Modifica Auto', 'Auto' ),
        'update_item' => __( 'Aggiorna Auto', 'Auto' ),
        'view_item' => __( 'Visualizza Auto', 'Auto' ),
        'view_items' => __( 'Visualizza le Auto', 'Auto' ),
        'search_items' => __( 'Cerca Auto', 'Auto' ),
        'not_found' => __( 'Nessun Auto trovato.', 'Auto' ),
        'not_found_in_trash' => __( 'Nessun Auto trovato nel cestino.', 'Auto' ),
        'featured_image' => __( 'Immagine in evidenza', 'Auto' ),
        'set_featured_image' => __( 'Imposta immagine in evidenza', 'Auto' ),
        'remove_featured_image' => __( 'Rimuovi immagine in evidenza', 'Auto' ),
        'use_featured_image' => __( 'Usa come immagine in evidenza', 'Auto' ),
        'insert_into_item' => __( 'Inserisci nelle Auto', 'Auto' ),
        'uploaded_to_this_item' => __( 'Caricato in questo Auto', 'Auto' ),
        'items_list' => __( 'Elenco degli Auto', 'Auto' ),
        'items_list_navigation' => __( 'Navigazione elenco Auto', 'Auto' ),
        'filter_items_list' => __( 'Filtra elenco Auto', 'Auto' ),
    );
    $args = array(
        'label' => __( 'Auto', 'Auto' ),
        'description' => __( 'Auto', 'Auto' ),
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-tools',
        'supports' => array(),
        'taxonomies' => array(),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'exclude_from_search' => false,
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );
    register_post_type( 'auto', $args );

}
add_action( 'init', 'create_auto_cpt', 0 );

add_action( 'admin_init', 'my_admin' );

function my_admin() {
    add_meta_box( 
        'car_review_meta_box',
        'Informazioni Auto',
        'display_car_review_meta_box',
        'auto',
        'normal',
        'high'
    );
}

function display_car_review_meta_box() {
    ?>
    <table>
        <tr>
            <td style="width: 50%">UIID</td>
            <td><input type="text" size="40" name="garage" value="<?php echo get_post_meta( get_the_ID(), 'id', true ); ?>" readonly /></td>
        </tr>
        <tr>
            <td style="width: 50%">Marca</td>
            <td><input type="text" size="40" name="garage" value="<?php echo get_post_meta( get_the_ID(), 'brand', true ); ?>" readonly /></td>
        </tr>
        <tr>
            <td style="width: 50%">Modello</td>
            <td><input type="text" size="40" name="garage" value="<?php echo get_post_meta( get_the_ID(), 'model', true ); ?>" readonly /></td>       
        </tr>
        <tr>
            <td style="width: 50%">Color</td>
            <td><input type="text" size="40" name="garage" value="<?php echo get_post_meta( get_the_ID(), 'color', true ); ?>" readonly /></td>
        </tr>
        <tr>
            <td style="width: 50%">Mileage</td>
            <td><input type="text" size="40" name="garage" value="<?php echo get_post_meta( get_the_ID(), 'km', true ); ?>" readonly /></td>       
        </tr>
    </table>
    <?php
}

add_action( 'wp', 'techiepress_insert_into_auto_cpt' );

function techiepress_check_for_similar_meta_ids() {
    $id_arrays_in_cpt = array();

    $args = array(
        'post_type'      => 'auto',
        'posts_per_page' => -1,
    );

    $loop = new WP_Query($args);
    while( $loop->have_posts() ) {
        $loop->the_post();
        $id_arrays_in_cpt[] = get_post_meta( get_the_ID(), 'id', true );
    }

    return $id_arrays_in_cpt;
}

function techiepress_query_garage_table( $car_available_in_cpt_array ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'garage';

    if ( NULL === $car_available_in_cpt_array || 0 === $car_available_in_cpt_array || '0' === $car_available_in_cpt_array || empty( $car_available_in_cpt_array ) ) {
        $results = $wpdb->get_results("SELECT * FROM $table_name");
        return $results;
    } else {
        $ids = implode( ",", $car_available_in_cpt_array );
        $sql = "SELECT * FROM $table_name WHERE id NOT IN ( $ids )";
        $results = $wpdb->get_results( $sql );
        return $results;
    }
}

function techiepress_insert_into_auto_cpt() {

    $car_available_in_cpt_array = techiepress_check_for_similar_meta_ids();
    $database_results = techiepress_query_garage_table( $car_available_in_cpt_array );

    if ( NULL === $database_results || 0 === $database_results || '0' === $database_results || empty( $database_results ) ) {
        return;
    }

    foreach ( $database_results as $result ) {
        $car_model = array(
            'post_title' => wp_strip_all_tags( $result->Brand . ' ' . $result->Model . ' ' . $result->Km ),
            'meta_input' => array(
                'id'        => $result->id,
                'brand'        => $result->Brand,
                'model'        => $result->Model,
                'color'        => $result->Color,
                'km'           => $result->Km,
            ),
            'post_type'   => 'auto',
            'post_status' => 'publish',
        );
        wp_insert_post( $car_model );
    }
}