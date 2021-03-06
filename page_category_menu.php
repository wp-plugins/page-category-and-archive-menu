<?php
/*
Plugin Name: Page Category & Archive Menu
Plugin URI: http://tempspace.net/plugins/?page_id=33
Description: This plugin makes menu of page, category, and archive. This is useful when you want to save space on the screen by not displaying lists of pages, categories, archive.
Version: 1.0.0
Author: Atsushi Ueda
Author URI: http://tempspace.net/plugins/
License: GPL2
*/

$pgcatmenu_plugin_URL = get_option( 'siteurl' ) . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));

function pgcatmenu_init() {
	wp_enqueue_script('jquery');
}
add_action('init', 'pgcatmenu_init');


/*  <head>section$B$K!"(J Javascript$B$rDI2C(J   */
add_action( 'wp_head', 'pgcatmenu_header_filter' );

$ocrp_pages_data = array();
$ocrp_cats_data = array();
$ocrp_arcvs_data = array();

function pgcatmenu_header_filter( $title ) {
	global $pgcatmenu_plugin_URL;
	global $ocrp_pages_data;
	global $ocrp_cats_data;
	global $ocrp_arcvs_data;
	global $wpdb, $wp_locale;

	echo  "<link rel=\"stylesheet\" id=\"pgcatmenu_css\" type=\"text/css\" href=\"" . $pgcatmenu_plugin_URL . "/style.css\" media=\"screen\"/>\n";
	echo  "<script type=\"text/javascript\" src=\"" . $pgcatmenu_plugin_URL . "/linkmenu.js\"></script>\n";

	$ocrp_idx = 0;
	$ocrp_pages = get_pages(); 
	$ocrp_pages_data = array();
	foreach( $ocrp_pages as $page ) {
		$p = array(
			'ID' =>  $page->ID,
			'url' => $page->guid,
			'title' => $page->post_title,
			'parent' => $page->post_parent,
			'menu_order' => $page->menu_order
		);
		$ocrp_pages_data[$ocrp_idx++] = $p;
	} 
	echo "<script type=\"text/javascript\"> var orcp_page_data_json='"; echo str_replace("'","\'",json_encode($ocrp_pages_data)); echo "'; </script>";
	$ocrp_idx = 0;
	$ocrp_cats = get_categories(); 
	$ocrp_cats_data = array();
	foreach( $ocrp_cats as $cat ) {
		$p = array(
			'ID' =>  $cat->cat_ID,
			'url' => get_bloginfo('url').'/?cat='.$cat->cat_ID,
			'title' => $cat->name,
			'parent' => $cat->category_parent
		);
		$ocrp_cats_data[$ocrp_idx++] = $p;
	} 
	echo "<script type=\"text/javascript\"> var orcp_cats_data_json='"; echo str_replace("'","\'",json_encode($ocrp_cats_data)); echo "'; </script>";
	$ocrp_idx = 0;
	$ocrp_arcvs_data = array();
	$result = $wpdb->get_results(
		"SELECT DISTINCT ".
		"	SUBSTRING( post_date, 1, 4 ) AS y, ".
		"	SUBSTRING( post_date, 6, 2 ) AS m ".
		"FROM  `wp_posts` ".
		"WHERE ".
		"	post_status =  'publish' ".
		"	AND post_type =  'post' ".
		"ORDER BY y DESC, m DESC ");
	for ($i=0; $i<count($result); $i++) {
		$p = array(
			'ID' =>  1+$i,
			'url' => get_month_link($result[$i]->y, $result[$i]->m),
			'title' => sprintf(__('%1$s %2$d'), $wp_locale->get_month($result[$i]->m), $result[$i]->y),
			'parent' => 0
		);
		$ocrp_arcvs_data[$ocrp_idx++] = $p;
	} 
	echo "<script type=\"text/javascript\"> var orcp_arcvs_data_json='"; echo str_replace("'","\'",json_encode($ocrp_arcvs_data)); echo "'; </script>";

	echo "<script type=\"text/javascript\">\n";
	echo "jQuery(document).ready(function(){\n";

	$pid = get_option("pgcatmenu_pagelink_id","page_list");
	if ($pid!="") {
		echo "	if (document.getElementById('" . $pid . "')) {\n";
		echo "		var orcp_page_data;\n";
		echo "		orcp_page_data = jQuery.parseJSON(orcp_page_data_json);\n";
		echo "		var menu_pages1 = new OrcpLinkMenu('" . $pid . "','pcmn_page_list_menu', orcp_page_data);\n";
		$lid = get_option("pgcatmenu_pagelist_id", "0");
		if ($lid != "0") {
			echo "		var url = '" . get_bloginfo('url').'/?p=' . $lid . "';\n";
			echo "		jQuery('#" . $pid . "').attr('href', url);\n";
			echo "		jQuery('#" . $pid . " a').attr('href', url);\n";
		}
		echo "	}\n";
	}

	$pid = get_option("pgcatmenu_catlink_id","category_list");
	if ($pid!="") {
		echo "	if (document.getElementById('" . $pid . "')) {\n";
		echo "		var orcp_cats_data;\n";
		echo "		orcp_cats_data = jQuery.parseJSON(orcp_cats_data_json);\n";
		echo "		var menu_pages2 = new OrcpLinkMenu('" . $pid . "','pcmn_category_list_menu', orcp_cats_data);\n";
		$lid = get_option("pgcatmenu_categorylist_id", "0");
		if ($lid != "0") {
			echo "		var url = '" . get_bloginfo('url').'/?p=' . $lid . "';\n";
			echo "		jQuery('#" . $pid . "').attr('href', url);\n";
			echo "		jQuery('#" . $pid . " a').attr('href', url);\n";
		}
		echo "	}\n";
	}

	$pid = get_option("pgcatmenu_arclink_id","archive_list");
	if ($pid!="") {
		echo "	if (document.getElementById('" . $pid . "')) {\n";
		echo "		var orcp_arcvs_data;\n";
		echo "		orcp_arcvs_data = jQuery.parseJSON(orcp_arcvs_data_json);\n";
		echo "		var menu_pages3 = new OrcpLinkMenu('" . $pid . "','pcmn_archive_list_menu', orcp_arcvs_data);\n";
		$lid = get_option("pgcatmenu_archivelist_id", "0");
		if ($lid != "0") {
			echo "		var url = '" . get_bloginfo('url').'/?p=' . $lid . "';\n";
			echo "		jQuery('#" . $pid . "').attr('href', url);\n";
			echo "		jQuery('#" . $pid . " a').attr('href', url);\n";
		}
		echo "	}\n";
	}

	echo "});\n";

	echo "var pgcatmenu_font_size = parseInt('" . get_option("pgcatmenu_font_size", "14") . "');\n";
	echo "if (isNaN(pgcatmenu_font_size)) pgcatmenu_font_size=0;\n";
	echo "var pgcatmenu_line_spacing = parseInt('" . get_option("pgcatmenu_line_spacing", "7") . "');\n";
	echo "if (isNaN(pgcatmenu_line_spacing)) pgcatmenu_line_spacing=0;\n";
	echo "var pgcatmenu_border_size = parseInt('" . get_option("pgcatmenu_border_size", "3") . "');\n";
	echo "if (isNaN(pgcatmenu_border_size)) pgcatmenu_border_size=0;\n";
	echo "var pgcatmenu_border_color = '" . get_option("pgcatmenu_border_color", "#222222") . "';\n";
	echo "var pgcatmenu_padding_size_x = parseInt('" . get_option("pgcatmenu_padding_size_x", "10") . "');\n";
	echo "if (isNaN(pgcatmenu_padding_size_x)) pgcatmenu_padding_size_x=0;\n";
	echo "var pgcatmenu_padding_size_y = parseInt('" . get_option("pgcatmenu_padding_size_y", "10") . "');\n";
	echo "if (isNaN(pgcatmenu_padding_size_y)) pgcatmenu_padding_size_y=0;\n";
	echo "var pgcatmenu_padding_size_c = parseInt('" . get_option("pgcatmenu_padding_size_c", "10") . "');\n";
	echo "if (isNaN(pgcatmenu_padding_size_c)) pgcatmenu_padding_size_c=0;\n";
	echo "var pgcatmenu_background_color = '" . get_option("pgcatmenu_background_color", "#ffffff") . "';\n";
	echo "var pgcatmenu_menulink_color = '" . get_option("pgcatmenu_menulink_color", "#000000") . "';\n";
	echo "var pgcatmenu_menulink_color_v = '" . get_option("pgcatmenu_menulink_color_v", "#000000") . "';\n";
	echo "var pgcatmenu_menulink_color_h = '" . get_option("pgcatmenu_menulink_color_h", "#000000") . "';\n";

	echo "</script>\n";

	echo "<style type=\"text/css\">\n";
	echo ".pgcatmenu a:link{color:".get_option("pgcatmenu_menulink_color", "#000000").";}\n";
	echo ".pgcatmenu a:visited{color:".get_option("pgcatmenu_menulink_color_v", "#000000").";}\n";
	echo ".pgcatmenu a:hover{color:".get_option("pgcatmenu_menulink_color_h", "#000000").";}\n";
	echo "</style>\n";
}



function pgcatmenu_MakeLinkMenu_sub($parent, $level, $max_level, &$data, $html)
{
	if ($level > $max_level) {
		return $html;
	}
	$spc="";
	for ($i=0; $i < $level*4; $i++) {
		$spc = $spc . "&nbsp;";
	}
	for ($i=0; $i<count($data); $i++) {
		if ($parent == $data[$i]['parent']) {
			$html = $html . '<li>'.$spc.'<a href="' . $data[$i]['url']. '">' . $data[$i]['title'] . '</a></li>';
			$html = pgcatmenu_MakeLinkMenu_sub($data[$i]['ID'], $level+1, $max_level, $data, $html);
		}
	}
	return $html;
}


function pgcatmenu_pg_shortcode($atts, $content = null) {
	global $ocrp_pages_data;
	$ret = '<ul class="pgcatmenu_list">';
	$ret = pgcatmenu_MakeLinkMenu_sub(0,0,9,$ocrp_pages_data,$ret);
	$ret = $ret . '</ul>';
	return $ret;
}

add_shortcode('page_link_menu', 'pgcatmenu_pg_shortcode',12);

function pgcatmenu_cat_shortcode($atts, $content = null) {
	global $ocrp_cats_data;
	$ret = '<ul class="pgcatmenu_list">';
	$ret = pgcatmenu_MakeLinkMenu_sub(0,0,9,$ocrp_cats_data,$ret);
	$ret = $ret . '</ul>';
	return $ret;
}

add_shortcode('category_link_menu', 'pgcatmenu_cat_shortcode',12);

function pgcatmenu_arc_shortcode($atts, $content = null) {
	global $ocrp_arcvs_data;
	$ret = '<ul class="pgcatmenu_list">';
	$ret = pgcatmenu_MakeLinkMenu_sub(0,0,9,$ocrp_arcvs_data,$ret);
	$ret = $ret . '</ul>';
	return $ret;
}

add_shortcode('archive_link_menu', 'pgcatmenu_arc_shortcode',12);


// $B@_Dj%a%K%e!<$NDI2C(J
add_action('admin_menu', 'pgcatmenu_plugin_menu');
function pgcatmenu_plugin_menu()
{
	/*  $B@_Dj2hLL$NDI2C(J  */
	add_submenu_page('plugins.php', 
		'Page & Category Menu Plugin Configuration', 
		'Page & Category Menu', 
		'manage_options', 
		'pgcatmenu-submenu-handle', 
		'pgcatmenu_magic_function'
	); 
}

$pgcatmenu_col_ar[0] = "#pgcatmenu_border_color";
$pgcatmenu_col_ar[1] = "#pgcatmenu_background_color";
$pgcatmenu_col_ar[2] = "#pgcatmenu_menulink_color";
$pgcatmenu_col_ar[3] = "#pgcatmenu_menulink_color_v";
$pgcatmenu_col_ar[4] = "#pgcatmenu_menulink_color_h";

function pgcatmenu_farbtastic_prepare($ar)
{
	$scr = "";
	for ($i=0; $i<count($ar); $i++) {
		$id = 'colorpicker'.$i;
		echo "<div id=\"" . $id . "\" style=\"position:absolute;\"></div>";
		$scr .= "			jQuery('#".$id."').farbtastic('".$ar[$i]."').hide();\n";
		$scr .= "			SetPosition('".$ar[$i]."','#".$id."');\n";
		$scr .= "			jQuery('".$ar[$i]."').focus(function(){jQuery('#".$id."').show();});\n";
		$scr .= "			jQuery('".$ar[$i]."').blur(function(){jQuery('#".$id."').hide();});\n";
	}
	echo 	"	<script type=\"text/javascript\">\n		jQuery(document).ready(function(){\n" . $scr . "		});\n".
		"		function SetPosition(el, cl)\n".
		"		{\n".
		"			var left = 0;\n".
		"			var top = 0;\n".
		"			left = jQuery(el).offset().left + jQuery(el).width()*1.2;\n".
		"			top = jQuery(el).offset().top -jQuery(cl).height()/2;\n".
		"			var height = jQuery(el).height();\n".
		"			if (!isNaN(parseInt(jQuery(el).css('padding-top')))) height += parseInt(jQuery(el).css('padding-top'));\n".
		"			if (!isNaN(parseInt(jQuery(el).css('margin-top')))) height += parseInt(jQuery(el).css('margin-top'));\n".
		"			var y = Math.floor(top) + height;\n".
		"			var x = Math.floor(left);\n".
		"			jQuery(cl).css('top',y+\"px\");\n".
		"			jQuery(cl).css('left',x+\"px\");\n".
		"		}\n".
		"	</script>\n";
}

/*  $B@_Dj2hLL=PNO(J  */
function pgcatmenu_magic_function()
{
	/*  Save Change$B%\%?%s2!2<$G%3!<%k$5$l$?>l9g!"(J$_POST$B$K3JG<$5$l$?@_Dj>pJs$rJ]B8(J  */
	if ( isset($_POST['updatePgCatMenuSetting'] ) ) {
		echo '<div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>';
		update_option('pgcatmenu_pagelink_id', $_POST['pgcatmenu_pagelink_id']);
		update_option('pgcatmenu_catlink_id', $_POST['pgcatmenu_catlink_id']);
		update_option('pgcatmenu_arclink_id', $_POST['pgcatmenu_arclink_id']);
		update_option('pgcatmenu_pagelist_id', $_POST['pgcatmenu_pagelist_id']);
		update_option('pgcatmenu_categorylist_id', $_POST['pgcatmenu_categorylist_id']);
		update_option('pgcatmenu_archivelist_id', $_POST['pgcatmenu_archivelist_id']);
		update_option('pgcatmenu_font_size', $_POST['pgcatmenu_font_size']);
		update_option('pgcatmenu_line_spacing', $_POST['pgcatmenu_line_spacing']);
		update_option('pgcatmenu_border_size', $_POST['pgcatmenu_border_size']);
		update_option('pgcatmenu_border_color', $_POST['pgcatmenu_border_color']);
		update_option('pgcatmenu_padding_size_x', $_POST['pgcatmenu_padding_size_x']);
		update_option('pgcatmenu_padding_size_y', $_POST['pgcatmenu_padding_size_y']);
		update_option('pgcatmenu_padding_size_c', $_POST['pgcatmenu_padding_size_c']);
		update_option('pgcatmenu_background_color', $_POST['pgcatmenu_background_color']);
		update_option('pgcatmenu_menulink_color', $_POST['pgcatmenu_menulink_color']);
		update_option('pgcatmenu_menulink_color_v', $_POST['pgcatmenu_menulink_color_v']);
		update_option('pgcatmenu_menulink_color_h', $_POST['pgcatmenu_menulink_color_h']);
	}

	global $pgcatmenu_col_ar;
	pgcatmenu_farbtastic_prepare($pgcatmenu_col_ar);

	$plugin = plugin_basename('page_category_menu'); $plugin = dirname(__FILE__);
	?>
	<div class="wrap">
		<h2>Pages & Categories Menu Plugin Configuration</h2>

		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<?php 
		wp_nonce_field('update-options');
		$pagelink_id = get_option("pgcatmenu_pagelink_id", "page_list");
		$catlink_id = get_option("pgcatmenu_catlink_id", "category_list");
		$arclink_id = get_option("pgcatmenu_arclink_id", "archive_list");
		$pagelist_id = get_option("pgcatmenu_pagelist_id", "0");
		$categorylist_id = get_option("pgcatmenu_categorylist_id", "0");
		$archivelist_id = get_option("pgcatmenu_archivelist_id", "0");
		$font_size = get_option("pgcatmenu_font_size", "14"); 
		$line_spacing = get_option("pgcatmenu_line_spacing", "7"); 
		$border_size = get_option("pgcatmenu_border_size", "3"); 
		$border_color = get_option("pgcatmenu_border_color", "#222222"); 
		$padding_size_x = get_option("pgcatmenu_padding_size_x", "10"); 
		$padding_size_y = get_option("pgcatmenu_padding_size_y", "10"); 
		$padding_size_c = get_option("pgcatmenu_padding_size_c", "10"); 
		$background_color = get_option("pgcatmenu_background_color", "#ffffff");
		$menulink_color = get_option("pgcatmenu_menulink_color", "#000000");  
		$menulink_color_v = get_option("pgcatmenu_menulink_color_v", "#000000");  
		$menulink_color_h = get_option("pgcatmenu_menulink_color_h", "#000000");  
		?>
		<table class="form-table">
		<tr>
		<td scope="row">ID of page-link element in HTML <input type="text" name="pgcatmenu_pagelink_id" value="<?php echo $pagelink_id; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">ID of category-link element in HTML <input type="text" name="pgcatmenu_catlink_id" value="<?php echo $catlink_id; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">ID of archive-link element in HTML <input type="text" name="pgcatmenu_arclink_id" value="<?php echo $arclink_id; ?>" /></td>
		</tr>

		<tr >
		<td scope="row">ID of the static page showing the page-list <input type="text" name="pgcatmenu_pagelist_id" value="<?php echo $pagelist_id; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">ID of the static page showing the category-list <input type="text" name="pgcatmenu_categorylist_id" value="<?php echo $categorylist_id; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">ID of the static page showing the archive-list <input type="text" name="pgcatmenu_archivelist_id" value="<?php echo $archivelist_id; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Font size (px) <input type="text" name="pgcatmenu_font_size" value="<?php echo $font_size; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Line space (px) <input type="text" name="pgcatmenu_line_spacing" value="<?php echo $line_spacing; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Border thickness (px) <input type="text" name="pgcatmenu_border_size" value="<?php echo $border_size; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Border color <input type="text" id="pgcatmenu_border_color" name="pgcatmenu_border_color" value="<?php echo $border_color; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Spacing (side) (px) <input type="text" name="pgcatmenu_padding_size_x" value="<?php echo $padding_size_x; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Spacing (top / bottom) (px) <input type="text" name="pgcatmenu_padding_size_y" value="<?php echo $padding_size_y; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Spacing between columns (px) <input type="text" name="pgcatmenu_padding_size_c" value="<?php echo $padding_size_c; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Background_color <input type="text" id="pgcatmenu_background_color" name="pgcatmenu_background_color" value="<?php echo $background_color; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Menu link color <input type="text" id="pgcatmenu_menulink_color" name="pgcatmenu_menulink_color" value="<?php echo $menulink_color; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Menu link color (visited) <input type="text" id="pgcatmenu_menulink_color_v" name="pgcatmenu_menulink_color_v" value="<?php echo $menulink_color_v; ?>" /></td>
		</tr>

		<tr>
		<td scope="row">Menu link color (hover) <input type="text" id="pgcatmenu_menulink_color_h" name="pgcatmenu_menulink_color_h" value="<?php echo $menulink_color_h; ?>" /></td>
		</tr>

		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="pgcatmenu_pagelink_id,pgcatmenu_catlink_id,pgcatmenu_arclink_id,pgcatmenu_pagelist_id,pgcatmenu_categorylist_id,pgcatmenu_archivelist_id,pgcatmenu_font_size,pgcatmenu_line_spacing,pgcatmenu_border_size,pgcatmenu_border_color,pgcatmenu_padding_size_x,pgcatmenu_padding_size_y,pgcatmenu_padding_size_c,pgcatmenu_background_color,pgcatmenu_menulink_color,pgcatmenu_menulink_color_v,pgcatmenu_menulink_color_h" />
		<p class="submit">
			<input type="submit" name="updatePgCatMenuSetting" class="button-primary" value="<?php _e('Save  Changes')?>" onclick="" />
		</p>
		</form>


	</div>
	<?php 
	if ( isset($_POST['updatePgCatMenuSetting'] ) ) {
		//echo '<script type="text/javascript">alert("Options Saved.");</script>';
	}
}

function pgcatmenu_admin_head()
{
	global $pgcatmenu_plugin_URL;
	echo "<link rel='stylesheet' href='". $pgcatmenu_plugin_URL . "/mattfarina-farbtastic/farbtastic.css' type='text/css' media='all' />\n";
	echo "<script type=\"text/javascript\" src=\"" . $pgcatmenu_plugin_URL . "/mattfarina-farbtastic/farbtastic.min.js\"></script>\n";
}
add_action( 'admin_head', 'pgcatmenu_admin_head' ); 



?>
