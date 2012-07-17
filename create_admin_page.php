<?php

/* puts the admin section for Project Pentagon on the pages */
add_action('admin_menu', 'vizbang_menu');

function vizbang_menu() {
	//create new top-level menu
	add_menu_page('VizBang', 'VizBang', 'administrator', __FILE__, 'vizbang_htmlpage',plugins_url('/images/iconography/vizbangicon.png', __FILE__));
	//call register settings function
	add_action( 'admin_init', 'vizbang_mysettings' );
}



function vizbang_mysettings() {
	//register our settings
	register_setting( 'vizbang-settings-group', 'vizbang-which-cat' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-a' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-a-slug' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-b' );
	register_setting( 'vizbang-settings-group', 'vizbang-taxonomy-b-slug' );
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