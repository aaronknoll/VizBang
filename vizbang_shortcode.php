<?php
//this feature is deprecated, but in an alternate future this might
//be a useful hook. I'll leave it in here for someone to recusitate. - AK 7 30 12
//ideally, this code will eventually generate the json file
// we/ll use to make the visualization...

// [vizbang category="category-value" reserved="hold"]
function displayvisualization( $atts ) {
	$wcat 		=	get_option('vizbang-which-cat');
	if($wcat = "post")
		{
		//if its based on posts, we can display a viz of only a single category
		extract( shortcode_atts( array(
			'category' => 'something',
			'reserved' => 'something else',
		), $atts ) );
	
		$my_query = new WP_Query('cat='.$category.'&showposts=100'); ?>
		<?php while ($my_query->have_posts()) : $my_query->the_post(); ?>
		<h5><a href="<?php the_permalink(); ?>" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h5>
		<?php
		$taxona =	get_option('vizbang-taxonomy-a');
		$taxonb =	get_option('vizbang-taxonomy-b');
		the_terms( $post->ID, $taxona, 'People: ', ', ', ' ' );
		the_terms( $post->ID, $taxonb, 'People: ', ', ', ' ' );
		endwhile; 
		//return "foo = {$foo}";
		}
	else 
		{
		//and if its based on page, let's offer a different filtering tool
		//null
		}
}
add_shortcode( 'vizbang', 'displayvisualization' );
?>