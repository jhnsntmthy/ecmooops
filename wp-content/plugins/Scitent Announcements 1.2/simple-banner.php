<?php
/**
 * Plugin Name: Scitent Announcements
 * Plugin URI: https://github.com/rpetersen29/simple-banner
 * Description: Display a simple banner at the top of your website.
 * Version: 1.2
 * Author: Ryan Petersen ... Comendeered from v1.0.4 by sDiamond for Scitent
 * Author URI: http://rpetersen29.github.io/
 * License: GPL2
 *
 * @package Simple Banner
 * @version 1.2
 * @author Ryan Petersen <rpetersen@arcwebtech.com>
 */

add_action( 'wp_enqueue_scripts', 'simple_banner' );
function simple_banner() {
    // Enqueue the style
	wp_register_style('simple-banner-style',  plugin_dir_url( __FILE__ ) .'simple-banner.css');
    wp_enqueue_style('simple-banner-style');
    // Enqueue the script
    wp_register_script('simple-banner-script', plugin_dir_url( __FILE__ ) . 'simple-banner.js',
		array( 'jquery' ));
    wp_enqueue_script('simple-banner-script');
}

//add custom CSS colors
add_action( 'wp_head', 'simple_banner_custom_color');
function simple_banner_custom_color() 
{
	if (get_option('simple_banner_color') != ""){
		echo '<style type="text/css" media="screen">.simple-banner{background:' . get_option('simple_banner_color') . "};</style>";
	}

	if (get_option('simple_banner_text_color') != ""){
		echo '<style type="text/css" media="screen">.simple-banner .simple-banner-text{color:' . get_option('simple_banner_text_color') . "};</style>";
	}

	if (get_option('simple_banner_link_color') != ""){
		echo '<style type="text/css" media="screen">.simple-banner .simple-banner-text a{color:' . get_option('simple_banner_link_color') . "};</style>";
	}

	// if (get_option('simple_banner_custom_css') != ""){
	// 	echo '<style type="text/css" media="screen">.simple-banner{'. get_option('simple_banner_custom_css') . "};</style>";
	// }
}

//check if home page only or site wide
add_action( 'wp_head', 'simple_banner_home_only');
function simple_banner_home_only()
{
	if ( get_option( 'simple_banner_homepage_only' ) === '1' ) {
		 if(is_front_page()){ 
			simple_banner_custom_text();
		 }
	}
	else{
		simple_banner_custom_text();
	}
}


//add custom banner text
//add_action( 'wp_head', 'simple_banner_custom_text');
function simple_banner_custom_text()
{
	if (get_option('simple_banner_text') !== ""){
		if(is_front_page()){ 
			$originalText = get_option('simple_banner_text');
			//error_log($originalText);
			 $cleanText = str_replace("\r\n",'<br/>', $originalText);
			//error_log($cleanText);
			?> 
			<script type="text/javascript">
				jQuery(document).ready(function() {
					var propel_bannerSpan = document.getElementById("simple-banner");
					propel_bannerSpan.innerHTML = "<div class='simple-banner-text'><span> <?php echo $cleanText; ?></span></div>"
					var propel_bannerHeight = document.getElementById('simple-banner').offsetHeight;
			        var propel_bannerHeightPX =  '' + propel_bannerHeight +'px';
			        document.getElementById('header-secondary-outer').style.marginTop = propel_bannerHeightPX;
			        document.getElementById('header-outer').style.marginTop = propel_bannerHeightPX;
			        document.getElementById('ajax-content-wrap').style.marginTop = propel_bannerHeightPX;
				});
			</script>
			<?php
		}
	}
}

add_action('admin_menu', 'simple_banner_menu');
function simple_banner_menu() {
add_menu_page('Simple Banner Settings', 'Announcements', 'administrator', 'simple-banner-settings', 'simple_banner_settings_page', 'dashicons-flag');
}

add_action( 'admin_init', 'simple_banner_settings' );
function simple_banner_settings() {
register_setting( 'simple-banner-settings-group', 'simple_banner_color' );
register_setting( 'simple-banner-settings-group', 'simple_banner_text_color' );
register_setting( 'simple-banner-settings-group', 'simple_banner_link_color' );
register_setting( 'simple-banner-settings-group', 'simple_banner_text' );
//register_setting( 'simple-banner-settings-group', 'simple_banner_homepage_only' );
//register_setting( 'simple-banner-settings-group', 'simple_banner_custom_css' );
}

function simple_banner_settings_page() {
?>

<div class="wrap">
	<h2>Announcement Bar Settings</h2>
	<p>Use Hex color values for the color fields.</p>
	<p>Links in the banner text must be typed in with HTML <code>&lt;a&gt;</code> tags with single quoted links. 
	<br />e.g. <code>&lt;a href=&#39;http:&#47;&#47;www.wordpress.com&#39;&gt;Link to Wordpress&lt;&#47;a&gt;</code>.</p>

<!-- Preview Banner -->
	<div id="preview_banner" class="simple-banner" style="width: 100%;background: <?php echo ((get_option('simple_banner_color') == '') ? '#e91f00' : esc_attr( get_option('simple_banner_color') )); ?>;text-align: center;">
		<div id="preview_banner_text" class="simple-banner-text" style="color: <?php echo ((get_option('simple_banner_text_color') == '') ? '#ffffff' : esc_attr( get_option('simple_banner_text_color') )); ?>;font-size: 1.1em;font-weight: 700;padding: 10px; }">
			<span>This is what your banner will look like with a <a id="preview_banner_link" style="color:<?php echo ((get_option('simple_banner_link_color') == '') ? '#ffffff' : esc_attr( get_option('simple_banner_link_color') )); ?>;" href="/">link</a>.*</span>
		</div>
	</div>
	<span><b>*Note: Font and text styles subject to change based on chosen theme CSS.</b></span>

<!-- Settings Form -->
	<form method="post" action="options.php">
		<?php settings_fields( 'simple-banner-settings-group' ); ?>
		<?php do_settings_sections( 'simple-banner-settings-group' ); ?>
		<table class="form-table">
<!-- Background Color -->
		<tr valign="top">
			<th scope="row">Simple Banner Background Color<br><span style="font-weight:400;">(Leaving this blank sets the color to the default value red #e91f00)</span></th>
			<td style="vertical-align:top;    width: 300px;">
			<input type="text" id="simple_banner_color" name="simple_banner_color" placeholder="Hex value" 
							value="<?php echo esc_attr( get_option('simple_banner_color') ); ?>" 
							onchange="(document.getElementById('simple_banner_color').value == '') ? document.getElementById('simple_banner_color_show').value = '#e91f00' : document.getElementById('simple_banner_color_show').value = document.getElementById('simple_banner_color').value;
							document.getElementById('preview_banner').style.background = ((document.getElementById('simple_banner_color').value == '') ? '#e91f00' : document.getElementById('simple_banner_color').value);" />
			<input style="height: 30px;width: 100px;" type="color" id="simple_banner_color_show" 
							value="<?php echo ((get_option('simple_banner_color') == '') ? '#e91f00' : esc_attr( get_option('simple_banner_color') )); ?>" 
							onchange="javascript:document.getElementById('simple_banner_color').value = document.getElementById('simple_banner_color_show').value;
							document.getElementById('preview_banner').style.background = document.getElementById('simple_banner_color_show').value;">
			</td>
		</tr>
<!-- Text Color -->
	<tr valign="top">
		<th scope="row">Simple Banner Text Color<br><span style="font-weight:400;">(Leaving this blank sets the color to the default value white #ffffff)</span></th>
		<td style="vertical-align:top;">
		<input type="text" id="simple_banner_text_color" name="simple_banner_text_color" placeholder="Hex value" 
						value="<?php echo esc_attr( get_option('simple_banner_text_color') ); ?>" 
						onchange="javascript:(document.getElementById('simple_banner_text_color').value == '') ? document.getElementById('simple_banner_text_color_show').value = '#ffffff' : document.getElementById('simple_banner_text_color_show').value = document.getElementById('simple_banner_text_color').value;
						document.getElementById('preview_banner_text').style.color = ((document.getElementById('simple_banner_text_color').value == '') ? '#ffffff' : document.getElementById('simple_banner_text_color').value);" />
		<input style="height: 30px;width: 100px;" type="color" id="simple_banner_text_color_show" 
						value="<?php echo ((get_option('simple_banner_text_color') == '') ? '#ffffff' : esc_attr( get_option('simple_banner_text_color') )); ?>" 
						onchange="javascript:document.getElementById('simple_banner_text_color').value = document.getElementById('simple_banner_text_color_show').value;
						document.getElementById('preview_banner_text').style.color = document.getElementById('simple_banner_text_color_show').value;">
		</td>
	</tr>
<!-- Link Color-->
	<tr valign="top">
		<th scope="row">Simple Banner Link Color<br><span style="font-weight:400;">(Leaving this blank sets the color to the default value white #ffffff)</span></th>
		<td style="vertical-align:top;">
		<input type="text" id="simple_banner_link_color" name="simple_banner_link_color" placeholder="Hex value" 
						value="<?php echo esc_attr( get_option('simple_banner_link_color') ); ?>" 
						onchange="javascript:(document.getElementById('simple_banner_link_color').value == '') ? document.getElementById('simple_banner_link_color_show').value = '#ffffff' : document.getElementById('simple_banner_link_color_show').value = document.getElementById('simple_banner_link_color').value;
						document.getElementById('preview_banner_link').style.color = document.getElementById('simple_banner_link_color').value;
						document.getElementById('preview_banner_link').style.color = ((document.getElementById('simple_banner_link_color').value == '') ? '#ffffff' : document.getElementById('simple_banner_link_color').value);" />
		<input style="height: 30px;width: 100px;" type="color" id="simple_banner_link_color_show" 
						value="<?php echo ((get_option('simple_banner_link_color') == '') ? '#ffffff' : esc_attr( get_option('simple_banner_link_color') )); ?>" 
						onchange="javascript:document.getElementById('simple_banner_link_color').value = document.getElementById('simple_banner_link_color_show').value;
						document.getElementById('preview_banner_link').style.color = document.getElementById('simple_banner_link_color_show').value;">
		</td>
	</tr>
<!-- Text Contents -->
	<tr valign="top">
		<th scope="row">Simple Banner Text<br><span style="font-weight:400;">(Leaving this blank removes the banner)</span></th>
		<td><textarea style="height: 150px;width: 265px;resize: none;" name="simple_banner_text"><?php echo get_option('simple_banner_text'); ?></textarea></td>
		<td style="color:blue;">
			Use single quotes with the href links.<br/>
			<i>&#60;a href='www.my-url.com'> link text &#60;/a> </i>

		</td>
	</tr>
<!-- Custom CSS -->
	<!-- <tr valign="top">
		<th scope="row">Simple Banner Custom CSS
		<br><span style="font-weight:400;">Styles will be applied directly to the <code>simple-banner</code> class.</span>
		<br><span style="font-weight:400;color:red;">Be very careful, bad CSS can break the banner.</span></th>
		<td><textarea id="simple_banner_custom_css" style="height: 150px;width: 265px;resize: none;" name="simple_banner_custom_css"><?php //echo get_option('simple_banner_custom_css'); ?></textarea>
		</td>
	</tr> -->

<!-- Home Page Only -->
<!-- 	<tr valign="top">
		<th scope="row">Home page only?<br><span style="font-weight:400;">(If unchecked, this banner will appear on all pages.)</span></th>
		<td>
			<input type='checkbox' name='simple-banner-homepage-only'['homeOnly]' id='simple-banner-homepage-only' value='1'
			<?php // checked( 'simple-banner-homepage-only'['homeOnly'], 1 ) ;?> /> 
		</td>
	</tr> -->


<!-- Save Changes Button -->
	</table>

	<?php submit_button(); ?>
	</form>
	</div><!--end .wrap -->

<script type="text/javascript">
	// var style = document.createElement('style');
	// var style_dynamic = null;

	// style.type = 'text/css';
	// style.id = 'preview_banner_custom_stylesheet'
	// style.appendChild(document.createTextNode('.simple-banner{'+document.getElementById('simple_banner_custom_css').value+'}'));
	// document.getElementsByTagName('head')[0].appendChild(style);

	// document.getElementById('simple_banner_custom_css').onblur=function(){
	// 	var child = document.getElementById('preview_banner_custom_stylesheet');
	// 	if (child){child.innerText = "";child.id='';}

	// 	var style_dynamic = document.createElement('style');
	// 	style_dynamic.type = 'text/css';
	// 	style_dynamic.id = 'preview_banner_custom_stylesheet';
	// 	style_dynamic.appendChild(document.createTextNode('.simple-banner{'+document.getElementById('simple_banner_custom_css').value+'}'));
	// 	document.getElementsByTagName('head')[0].appendChild(style_dynamic);
	// };
</script>
<?php
}
