<?php
/*
Plugin Name: VizBang D3 Javascript Visualization
Plugin URI: http://aaronknoll.com/vizbang
Description: Creates a D3 Visualization by creating two taxonomies.
Version: 0.1
Author: Aaron Knoll
Author URI: http://aaronknoll.com
License: GPL
*/
include "create_admin_page.php"; //code to make the page in the admin panel where options are set

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'projectpentagon_install'); 
/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'projectpentagon_remove' );


function projectpentagon_install() {
/* Creates new database field */
add_option("projectpentagon_title", 'fgfhgThis it the Title of my Pentagram', '', 'yes');
add_option("vizbang-which-cat", 'pages', '', 'yes');
add_option("vizbang-taxonomy-a", 'Taxon A', '', 'yes');
add_option("vizbang-taxonomy-a-slug", 'Taxon A slug', '', 'yes');
add_option("vizbang-taxonomy-b", 'Taxon B', '', 'yes');
add_option("vizbang-taxonomy-b-slug", 'Taxon B slug', '', 'yes');
}



function projectpentagon_remove() {
/* Deletes the database field */
delete_option('vizbang-which-cat');
delete_option('vizbang-taxonomy-a');
delete_option('vizbang-taxonomy-a-slug');
delete_option('vizbang-taxonomy-b');
delete_option('vizbang-taxonomy-b-slug');
}

function category1_init() {
	// create a new taxonomy
	$taxon 		=	get_option('vizbang-taxonomy-a');
	$taxonslug 	=	get_option('vizbang-taxonomy-a-slug');
	
	 $labels = array(
	'name' => _x( $taxon, 'taxonomy general name' ),
	'singular_name' => _x( $taxon, 'taxonomy singular name' ),
	'search_items' =>  __( 'Search'. $taxon ),
	'popular_items' => __( 'Popular'. $taxon ),
	'all_items' => __( 'All'. $taxon ),
	'parent_item' => null,
	'parent_item_colon' => null,
	'edit_item' => __( 'Edit'. $taxon ), 
	'update_item' => __( 'Update'. $taxon ),
	'add_new_item' => __( 'Add New'. $taxon ),
	'new_item_name' => __( 'New'. $taxon .'Name' ),
	'separate_items_with_commas' => __( 'Separate '.$taxon.' with commas' ),
	'add_or_remove_items' => __( 'Add or remove'.$taxon ),
	'choose_from_most_used' => __( 'Choose from the most used '.$taxon ),
	'menu_name' => __( $taxon ),
	 	 ); 

	register_taxonomy(
		$taxon,
		'page',
		array(
	    'hierarchical' => false,
	    'labels' => $labels,
	    'show_ui' => true,
	    'update_count_callback' => '_update_post_term_count',
	    'query_var' => true
		)
	);
}

function category2_init() {
	// create a new taxonomy
	$taxon 		=	get_option('vizbang-taxonomy-b');
	$taxonslug 	=	get_option('vizbang-taxonomy-b-slug');
	
	 $labels = array(
	'name' => _x( $taxon, 'taxonomy general name' ),
	'singular_name' => _x( $taxon, 'taxonomy singular name' ),
	'search_items' =>  __( 'Search'. $taxon ),
	'popular_items' => __( 'Popular'. $taxon ),
	'all_items' => __( 'All'. $taxon ),
	'parent_item' => null,
	'parent_item_colon' => null,
	'edit_item' => __( 'Edit'. $taxon ), 
	'update_item' => __( 'Update'. $taxon ),
	'add_new_item' => __( 'Add New'. $taxon ),
	'new_item_name' => __( 'New'. $taxon .'Name' ),
	'separate_items_with_commas' => __( 'Separate '.$taxon.' with commas' ),
	'add_or_remove_items' => __( 'Add or remove'.$taxon ),
	'choose_from_most_used' => __( 'Choose from the most used '.$taxon ),
	'menu_name' => __( $taxon ),
	 	 ); 

	register_taxonomy(
		$taxon,
		'page',
		array(
	    'hierarchical' => false,
	    'labels' => $labels,
	    'show_ui' => true,
	    'update_count_callback' => '_update_post_term_count',
	    'query_var' => true
		)
	);
}

add_action( 'init', 'category1_init' );
add_action( 'init', 'category2_init' );
?>