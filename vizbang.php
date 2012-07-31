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
register_activation_hook(__FILE__,'vizbang_install'); 
/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'vizbang_remove' );


function vizbang_install() {
/* Creates new database field */
add_option("vizbang-which-cat", 'page', '', 'yes');
add_option("vizbang-taxonomy-a", 'Taxon A', '', 'yes');
add_option("vizbang-taxonomy-a-slug", 'Taxon A slug', '', 'yes');
add_option("vizbang-taxonomy-b", 'Taxon B', '', 'yes');
add_option("vizbang-taxonomy-b-slug", 'Taxon B slug', '', 'yes');
add_option("vizbang-taxonomy-if-post-cat", 'If Post Cat', '', '1');
}



function vizbang_remove() {
/* Deletes the database field */
delete_option('vizbang-which-cat');
delete_option('vizbang-taxonomy-a');
delete_option('vizbang-taxonomy-a-slug');
delete_option('vizbang-taxonomy-b');
delete_option('vizbang-taxonomy-b-slug');
delete_option('vizbang-taxonomy-if-post-cat');
}

function category1_init() {
	// create a new taxonomy
	$taxon 		=	get_option('vizbang-taxonomy-a');
	$taxonslug 	=	get_option('vizbang-taxonomy-a-slug');
	$wcat 		=	get_option('vizbang-which-cat');

	
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
		$wcat,
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
	$wcat 		=	get_option('vizbang-which-cat');
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
		$wcat,
		array(
	    'hierarchical' => false,
	    'labels' => $labels,
	    'show_ui' => true,
	    'update_count_callback' => '_update_post_term_count',
	    'query_var' => true
		)
	);
}
//and we add a special category, a static secondary taxnomy for pages
// which will allow us to create a visualization which only pretains
//to a small set of pages.

function create_vizbang_pages_cats() {
  $labels = array(
    'name' => _x( 'VizBang Cat', 'taxonomy general name' ),
    'singular_name' => _x( 'VizBang Cat', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search VizBang Cat' ),
    'all_items' => __( 'All VizBang Cat' ),
    'parent_item' => __( 'Parent VizBang Cat' ),
    'parent_item_colon' => __( 'Parent VizBang Cat' ),
    'edit_item' => __( 'Edit VizBang Cat' ), 
    'update_item' => __( 'Update VizBang Cat' ),
    'add_new_item' => __( 'Add New VizBang Cat' ),
    'new_item_name' => __( 'New VizBang Cat' ),
    'menu_name' => __( 'VizBang Cat' ),
  ); 	

  register_taxonomy('vizbangcat',page, array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'vizbangcat' ),
  ));
}

function vizbang_init_term(){
	//for posts the user needs to select a category they wish to use
	// but as categories do not exist for pages, and we cannot
	//safely assume that EVERY page will be visualizer (though they well
	//might be), here is where we offer a way out.
	$parent_term = term_exists( 'fruits', 'vizbangcat' ); // array is returned if taxonomy is given
	$parent_term_id = $parent_term['term_id']; // get numeric term id
	wp_insert_term(
	  'Visualizer', // the term 
	  'vizbangcat', // the taxonomy
	  array(
	    'description'=> 'The default category for choosing the subset
	    of pages that you want to be appear in the visualization. If you are
	    making a visualization out of posts, you do not need to use
	    this category',
	    'slug' => 'visualizer',
	    'parent'=> $parent_term_id
	  )
	);
}

function vizbang_uicolors(){
		echo  "<link type='text/css' rel='stylesheet' href='";
		echo plugins_url('/css/vizbang.css', __FILE__);
		echo  "' />";
}

add_action( 'init', 'category1_init' );
add_action( 'init', 'category2_init' );
add_action( 'init', 'create_vizbang_pages_cats' );
add_action( 'init', 'vizbang_init_term' );
add_action('admin_head', 'vizbang_uicolors');

include("vizbang_shortcode.php");//creates the shortcode for inserting a vis

?>