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

function vizbang_generatecsv() {	
	//this is where we establish the order we're working with. THIS IS STATIC //(in this version)
	//;;ORDER 1 - THE NAMES OF THE THING e.g page titles
	//;;ORDER 2 - THE NAMES OF ALL OF THE TAGS IN TAXON A
	//;;ORDER 3 - THE NAMES OF ALL OF THE TAGS IN TAXON B
	//---------THIS IS IMPORTANT BECAUSE THIS ORDER MUST BE THE SAME IN THE GENERATED JSON FILE	----------//
	
	//OR ACTUALLY MAYBE i CAN COMPLETE THIS OPERATION ALL IN THE SAME FUNCTION SINCE 
	//vizbang_generatejson already has most of these variables available, and I'm just re-using them
}

function vizbang_generatejson () {
	include "generate_json.php";
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
		$JSON_MASTER	=	array();//will implode this at the end
		
		
		$taxona =	get_option('vizbang-taxonomy-a');
		$taxonb =	get_option('vizbang-taxonomy-b');
		
		//get the full list of terms for each taxonomy
		$alloftaxona	=	wp_tag_cloud( array( 
											'taxonomy' => $taxona, 
											'format' => 'array', 
											'orderby' => 'name',
											'order' => 'ASC',
											'number' => 0) );
		//get the full list of terms for each taxonomy
		$alloftaxonb	=	wp_tag_cloud( array( 
											'taxonomy' => $taxonb, 
											'format' => 'array', 
											'orderby' => 'name',
											'order' => 'ASC',
											'number' => 0) );
		//make one huge array. THIS IS THE MASTER ORDERLIST.
		$everydayimshuffling	=	array_merge($alloftaxona, $alloftaxonb);
		//but now we have a problem. Wordpress puts in link text which will give
		//us negatives. let's go through the array. and scrub out every bit of html
		$MASTER_TAXON_ARRAY = array();
		foreach($everydayimshuffling as $beat)
			{$MASTER_TAXON_ARRAY[]	=	strip_tags($beat);}
		
		$x=0;
		while ($my_query->have_posts()) : $my_query->the_post(); 
			$x++;
			array_unshift($MASTER_TAXON_ARRAY, 'entries'. $x .'');
		endwhile;
		//echo $x;//debug
			
		//FOR THE CSV FILE, WE'RE GOING TO TAKE MASTER_TAXON_ARRAY
		//AND COMBINE IT WITH A DE-LINKED LIST OF EVERY POST TITLE IN THIS CATEGORY\
		$MASTER_TITLE_ARRAY	=	array();
		
		//////////////////////////////////// VAR DUMP DEBUG BLOCK	
		//echo "<br /><br />";//debug	
		//var_dump($MASTER_TAXON_ARRAY);//debug
		//echo "<br /><br />";//debug
		$ccolor	=	get_option('vizbang-category-color');
		
		while ($my_query->have_posts()) : $my_query->the_post(); ?>
			<?php
			
			$MASTER_TITLE_ARRAY[]	=	strip_tags(get_the_title()).','.$ccolor;
			//the_terms( $post->ID, $taxona);
			$termsa	=	get_the_terms( $post->ID, $taxona);
			$termsb	=	get_the_terms( $post->ID, $taxonb);
			
		 	$taxonsa =	taxon_to_array($termsa, 'array');
			$taxonsb =  taxon_to_array($termsb, 'array');
			
			//make one huge array OF ALL OF THIS ITEMS' TAXONOMIES.
			$MASTER_ITEM_TAXON_ARRAY	=	array_merge($taxonsa, $taxonsb);
			
			//////////////////////////////////// VAR DUMP DEBUG BLOCK
			//echo "<br /><br />";//debug
			//var_dump($MASTER_ITEM_TAXON_ARRAY);//debug
			//echo "<br /><br />";//debug
			
			//CREATING THE JSON LIST
			//the d3 visualization we're working requires there to be null values "0" for 
			//each tag in the master_taxon_array which is not in the master_item_taxon_array
			//therefore we're going to talk through one by one, but save a 0 to the new array
			//where no match exists, and save a value of ($matching_bar_width = .0035, but
			//eventually will get written into admin panel) arbitrary nature to designate
			//a match. 
			$countitemtaxon		= count($MASTER_ITEM_TAXON_ARRAY);
			$countmastertaxon	=	count($MASTER_TAXON_ARRAY);
			//echo "$countitemtaxon item tags<br />";//debug
			//echo "$countmastertaxon master tags<br />";//debug
	
			$MASTER_TO_JSON_ARRAY		=  array();
			//iterate through each item in the master array. If it exists in item arrsy
			//mark it with a "yea"
			foreach($MASTER_TAXON_ARRAY as $singled_out)
				{
					if(in_array($singled_out, $MASTER_ITEM_TAXON_ARRAY))
						{
							//echo "we're here point LL";//debug
							//POSITIVE FINDING, INSERT THE NON-0 VALUE.
							$MASTER_TO_JSON_ARRAY[] = "0.0035";
						}
					else
						{
							//echo "we're here point RR";//debug
							//NEGATIVE FINDING, INSERT THE 0 VALUE
							$MASTER_TO_JSON_ARRAY[] = "0";
						}
				}
			//now you should have a JSON_ARRAY with the same number of rosws as the master
			$countjsontaxon	=	count($MASTER_TO_JSON_ARRAY);
			//echo "$countjsontaxon jsonsss<br />";//debug
			if($countjsontaxon != $countmastertaxon)
				{
					//might want to leave this error message in there. 
					echo "<div class='failure'>Something went wrong, the two
					arrays have different sizes. this is bad news bears</div>";
				}
			else 
				{
					//might want to leave this error message in there. 
					echo "<div class='success'>The arrays match; in principle, 
					you're good. And also a good person.</div>";
					
					//////////////////////////////////// VAR DUMP DEBUG BLOCK
					//echo "<br /><br />";//debug
					//var_dump($MASTER_TO_JSON_ARRAY);//debug
					//echo "<br /><br />";//debug
				}
	
			//$newarray	= array($taxonsa, $taxonsb);
			//echo "Non-associative array output as array: ", json_encode($newarray), "\n";//debug
			$JSON_MASTER[] = json_encode($MASTER_TO_JSON_ARRAY);
			endwhile; 
	
	$howmanyentries	=	count($MASTER_TITLE_ARRAY);
	//////////////////////////////////// VAR DUMP DEBUG BLOCK
	//echo "<br /><br />";//debug
	//var_dump($MASTER_TITLE_ARRAY);//debug
	//echo "<br /><br />";//debug
	
	//make one huge array. THIS IS THE MASTER ORDERLIST.
	//$everydayimshuffling	=	array_merge($alloftaxona, $alloftaxonb);
	//but now we have a problem. Wordpress puts in link text which will give
	//us negatives. let's go through the array. and scrub out every bit of html
	$acolor	=	get_option('vizbang-taxonomy-a-color');
	$bcolor =	get_option('vizbang-taxonomy-b-color');
	
	$taxona_forpaired = array();
	foreach($alloftaxona as $beat)
		{$taxona_forpaired []	=	strip_tags($beat).','.$acolor;}
	//////////////////////////////////// VAR DUMP DEBUG BLOCK
	//echo "<br /><br />";//debug
	//var_dump($taxona_forpaired);//debug
	//echo "<br /><br />";//debug
	
	
	$taxonb_forpaired = array();
	foreach($alloftaxonb as $beat)
		{$taxonb_forpaired []	=	strip_tags($beat).','.$bcolor;}
	//////////////////////////////////// VAR DUMP DEBUG BLOCK
	//echo "<br /><br />";//debug
	//var_dump($taxonb_forpaired);//debug
	//echo "<br /><br />";//debug
	
	
	$godfather_part2	=	implode("\r\n",$taxona_forpaired);
	$godfather_part3	=	implode("\r\n",$taxonb_forpaired);
	$godfather_part1	=	implode("\r\n",$MASTER_TITLE_ARRAY);
	$prequel_released_20_years_after_puzos_death	= "name,color";
	$godfather_trilogy	=	$prequel_released_20_years_after_puzos_death . "\r\n" .$godfather_part1 . "\r\n" . $godfather_part2 . "\r\n" . $godfather_part3; 
	
	//echo "<br /><br />";//debug
	//echo $godfather_trilogy;//debug
	//echo "<br /><br />";//debug
	
		
	//is this url going to be static??! in every installation
	$filename = '../wp-content/plugins/vizbang/jsons/file.txt';
	$csvfilename	= '../wp-content/plugins/vizbang/jsons/items.csv';
	//implode all of our json arrays
	$JSON_INSERT	=	implode(",", $JSON_MASTER);
	
	// Let's make sure the file exists and is writable first.
	// and if not, let's give some helpful error messages to the end user, why not- right?
	// STARTS WITH THE JSON FILE
	if (is_writeable($filename)) 
		{
		    if (!$fp = fopen($filename, 'w')) {
		         echo "<div class='failure'>Cannot open file JSON ($filename)</div>";
		         exit;
		    }
			if(fwrite($fp, $JSON_INSERT) === FALSE)
				{
			        echo "<div class='failure'>Cannot write to  JSON  file ($filename)</div>";
			        exit;
				}
			fclose($fp);
		    echo "<div class='success'>Success, wrote to  JSON  file ($filename)</div>";
		} 
		else 
		{
			echo "<div class='failure'>The  JSON  file $filename is not writable</div>";
		}
		
	// Let's make sure the file exists and is writable first.
	// and if not, let's give some helpful error messages to the end user, why not- right?
	// CLOSES WITH THE .CSV FILE
	if (is_writeable($csvfilename)) 
		{
		    if (!$fp = fopen($csvfilename, 'w')) {
		         echo "<div class='failure'>Cannot open file CSV ($csvfilename)</div>";
		         exit;
		    }
			if(fwrite($fp, $godfather_trilogy) === FALSE)
				{
			        echo "<div class='failure'>Cannot write to CSV file ($csvfilename)</div>";
			        exit;
				}
			fclose($fp);
		    echo "<div class='success'>Success, wrote to CSV  file ($csvfilename)</div>";
		} 
		else 
		{
			echo "<div class='failure'>The CSV  file $csvfilename is not writable</div>";
		}		

}

function taxon_to_array($taxonomy, $type='text'){
	//$type can be 'text' or 'array', default is 'text'
	$taxonomical_links = array();
	foreach ($taxonomy as $term) 
		{
		//echo "you have 1<br />";//debug
		$taxonomical_links[] = $term->name;
		}
	//$taxonomical_links is our array!!		
	$taxonomied = join( ", ", $taxonomical_links );
	//type is the switch between echo text and output send us the array
	if($type=="text")
		{return $taxonomied; }
	elseif($type=="array")
		{
			//echo "you are here b";//debug
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
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-b-color' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-a-color' );
	register_setting( 'vizbang-settings-group', 'vizbang-category-color' );
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
    
    <h4><img src="<?php echo plugins_url('vizbang/images/iconography/visualize.png'); ?>"  style="vertical-align: middle; float:left; padding-right: 5px;">Customize Your Visualization</h4>
    <h6>Please enter values in hex code format (do NOT include # sign)</h6>
    <table class="form-table">
        <tr valign="top">
        	<th scope="row">Color for Main Category</th>
        	<td><input type="text" name="vizbang-category-color" 
        		value="<?php echo get_option('vizbang-category-color'); ?>" /></td>
        </tr>
    	<tr valign="top">
        	<th scope="row">Color for <?php echo get_option('vizbang-taxonomy-a'); ?></th>
        	<td><input type="text" name="vizbang-taxonomy-a-color" 
        		value="<?php echo get_option('vizbang-taxonomy-a-color'); ?>" /></td>
        </tr>
        <tr valign="top">
        	<th scope="row">Color for <?php echo get_option('vizbang-taxonomy-b'); ?></th>
        	<td><input type="text" name="vizbang-taxonomy-b-color" 
        		value="<?php echo get_option('vizbang-taxonomy-b-color'); ?>" /></td>
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