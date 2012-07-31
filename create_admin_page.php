<?php

/* puts the admin section for Project Pentagon on the pages */
add_action('admin_menu', 'vizbang_menu');

function vizbang_menu() {
	//create new top-level menu
	add_menu_page('VizBang', 'VizBang', 'administrator', 'vizbanghtml', 'vizbang_htmlpage',plugins_url('/images/iconography/vizbangicon.png', __FILE__));
	//create a new sub-menu page
	add_submenu_page('vizbanghtml', 'Generate JSON', 'Generate JSON', 'administrator', 'vizbang-generatejson', 'vizbang_generatejson'  );
	//call register settings function
	add_action( 'admin_init', 'vizbang_mysettings' );
}


function vizbang_generatejson () {
	include "generate_json.php";
	//if its based on posts, we can display a viz of only a single category
		extract( shortcode_atts( array(
			'category' => 'something',
			'reserved' => 'something else',
		), $atts ) );
		//setup the switch: DIFFERENT FOR POSTS, THAN FOR PAGES.
		$postorpage	=	get_option('vizbang-which-cat');
		$wha_cat 	=	get_option('vizbang-taxonomy-if-post-cat');
		//trigger a different query for each; but in the end, we're going to iterate whichever cat
		if($postorpage	== "post")
			{
			//echo "$wha_cat <br />";//debug
			$my_query = new WP_Query('cat='.$wha_cat.'&showposts=100'); 
			}
		else
			{
			//$my_query = new WP_Query('cat='.$category.'&showposts=100'); 
			}
		?>
		<?php while ($my_query->have_posts()) : $my_query->the_post(); ?>
		<?php //echo the_title(); ?>
		<?php //echo "<br />"; ?>
		<?php
		$taxona =	get_option('vizbang-taxonomy-a');
		$taxonb =	get_option('vizbang-taxonomy-b');
		$termsa	=	get_the_terms( $post->ID, $taxona);
		$termsb	=	get_the_terms( $post->ID, $taxonb);
		
	 	$taxonsa =	taxon_to_array($termsa, 'array');
		$taxonsb =  taxon_to_array($termsb, 'array');
		//echo $taxonsa;
		//foreach ($taxonsa as $thiskl)
		//	{echo $thiskl;}
			
//var_dump(
// $taxonsa,
 //json_encode($taxonsa)
//);
		//okay, I'm going a function up here to ebcompass the above commands soon.
		
		//$newarray	= array($taxonsa, $taxonsb);
		//echo "Non-associative array output as array: ", json_encode($newarray), "\n";//debug
		endwhile; 
		
		 //is this url going to be static??! in every installation
		$filename = '../wp-content/plugins/vizbang/jsons/file.txt';

	// Let's make sure the file exists and is writable first.
	// and if not, let's give some helpful error messages to the end user, why not- right?
	if (is_writeable($filename)) 
		{
		    if (!$fp = fopen($filename, 'w')) {
		         echo "<div class='failure'>Cannot open file ($filename)</div>";
		         exit;
		    }
			if(fwrite($fp, json_encode($taxonsa)) === FALSE)
				{
			        echo "<div class='failure'>Cannot write to file ($filename)</div>";
			        exit;
				}
			fclose($fp);
		    echo "<div class='success'>Success, wrote to file ($filename)</div>";
		} 
		else 
		{
			echo "<div class='failure'>The file $filename is not writable</div>";
		}
}

function taxon_to_array($taxonomy, $type='text'){
	//$type can be 'text' or 'array', default is 'text'
	$taxonomical_links = array();
	foreach ($taxonomy as $term) 
		{
		echo "you have 1<br />";
		$taxonomical_links[] = $term->name;
		}
	//$taxonomical_links is our array!!		
	$taxonomied = join( ", ", $taxonomical_links );
	//type is the switch between echo text and output send us the array
	if($type=="text")
		{return $taxonomied; }
	elseif($type=="array")
		{echo "you are here b";
			return $taxonomical_links;}
	else 
		{//return "invalid taxon_to_array_type";
		return $taxonomied; }
}

function vizbang_mysettings() {
	//register our settings
	register_setting( 'vizbang-settings-group', 'vizbang-which-cat' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-a' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-a-slug' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-b' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-b-slug' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-if-post-cat' );
}



function vizbang_htmlpage() {
?>

<div class="wrap">
<h2>Settings for the VizBang Visualization</h2>


<form method="post" action="options.php">

    <?php settings_fields( 'vizbang-settings-group' ); ?>

<h4>First, Post or Page?</h4>
    <table class="form-table">
    	<tr valign="top">
        <th scope="row">Post/Page?</th>
        <td><input type="text" name="vizbang-which-cat" value="<?php echo get_option('vizbang-which-cat'); ?>" /></td>
        </tr>
        <tr valign="top">
        	<th scope="row">If Post; Which Category (number)</th>
        	<td><input type="text" name="vizbang-taxonomy-if-post-cat" 
        		value="<?php echo get_option('vizbang-taxonomy-if-post-cat'); ?>" /></td>
        </tr>
    </table>
    
 <h4><img src="<?php echo plugins_url('vizbang/images/iconography/taxon2.png'); ?>" style="float:left; padding-right: 5px;">What do you want to call the first Taxonomy?</h4>
    <table class="form-table">
    	<tr valign="top">
        	<th scope="row">Name of first taxonomy</th>
        	<td><input type="text" name="vizbang-taxonomy-a" 
        		value="<?php echo get_option('vizbang-taxonomy-a'); ?>" /></td>
        </tr>
        <tr valign="top">
        	<th scope="row">What is this Taxonomy's Slug?</th>
        	<td><input type="text" name="vizbang-taxonomy-a-slug" 
        		value="<?php echo get_option('vizbang-taxonomy-a-slug'); ?>" /></td>
        </tr>
    </table>

  <h4><img src="<?php echo plugins_url('vizbang/images/iconography/taxon1.png'); ?>" style="float:left; padding-right: 5px;">What do you want to call the second Taxonomy?</h4>
    <table class="form-table">
    	<tr valign="top">
        	<th scope="row">Name of second taxonomy</th>
        	<td><input type="text" name="vizbang-taxonomy-b" 
        		value="<?php echo get_option('vizbang-taxonomy-b'); ?>" /></td>
        </tr>
        <tr valign="top">
        	<th scope="row">What is this Taxonomy's Slug?</th>
        	<td><input type="text" name="vizbang-taxonomy-b-slug" 
        		value="<?php echo get_option('vizbang-taxonomy-b-slug'); ?>" /></td>
        </tr>
    </table>
  
    <p class="submit">
    	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>

<?php

}


?>