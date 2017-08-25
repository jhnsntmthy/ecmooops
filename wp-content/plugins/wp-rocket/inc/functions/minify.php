<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Check if minify cache file exist and create it if not
 *
 * @since 2.1
 *
 * @param string $url 		 The minified URL with Google Minify Code
 * @param string $pretty_url The minified URL cache file
 * @return bool
 */
function rocket_fetch_and_cache_minify( $url, $pretty_url ) {
	// Check if php-curl is enabled
	if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) ) {
		return false;
	}

	$pretty_path = str_replace( WP_ROCKET_MINIFY_CACHE_URL, WP_ROCKET_MINIFY_CACHE_PATH, $pretty_url );

	// If minify cache file is already exist, return to get a coffee :)
	if ( file_exists( $pretty_path ) ) {
		return true;
	}

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt ($ch, CURLOPT_USERAGENT, 'WP-Rocket-Minify');

	$content = curl_exec($ch);
	curl_close($ch);

	if ( $content ) {
		// Create cache folders of the request uri
		$cache_path = WP_ROCKET_MINIFY_CACHE_PATH . get_current_blog_id() . '/';
		if ( ! is_dir( $cache_path ) ) {
			rocket_mkdir_p( $cache_path );
		}
		
		// Apply CDN on CSS properties
		if( strrpos( $pretty_path, '.css' ) ) {
			$content = rocket_cdn_css_properties( $content );	
		}
		
		// Save cache file
		if( rocket_put_content( $pretty_path, $content ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get tag of a group of files or JS minified CSS
 *
 * @since 2.1
 *
 * @param array  $files List of files to minify (CSS or JS)
 * @param bool   $force_pretty_url (default: true)
 * @param string $pretty_filename (default: null) The new filename if $force_pretty_url set to true
 * @return string $tags
 */
function get_rocket_minify_files( $files, $force_pretty_url = true, $pretty_filename = null ) {
	// Get the internal CSS Files
	// To avoid conflicts with file URLs are too long for browsers,
	// cut into several parts concatenated files
	$tags 		= '';
	$data_attr  = 'data-minify="1"';
	$urls 		= array( 0 => '' );
	$base_url 	= WP_ROCKET_URL . 'min/?f=';
	$files  	= is_array( $files ) ? $files : (array) $files;
	$files      = array_filter( $files );

	if ( count( $files ) ) {
		$i = 0;
		foreach ( $files as $file ) {
			$file = parse_url( $file, PHP_URL_PATH );
			$file = trim( $file );
			
            if ( empty( $file ) ) {
                continue;
            }

			// Replace "//" by "/" because it cause an issue with Google Code Minify!
			$file = str_replace( '//' , '/', $file );

			/**
			 * Filter the total number of files generated by the minification
			 *
			 * @since 2.1
			 *
			 * @param string The maximum number of characters in a URL
			 * @param string The file's extension
			*/
			$filename_length = apply_filters( 'rocket_minify_filename_length', 255, pathinfo( $file, PATHINFO_EXTENSION ) );

			// +1 : we count the extra comma
			if ( strlen( $urls[ $i ] . $base_url . $file ) + 1 >= $filename_length ) {
				$i++;
				$urls[ $i ] = '';
			}

			/**
			 * Filter file to add in minification process
			 *
			 * @since 2.4
			 *
			 * @param string $file The file path
			*/
			$file = apply_filters( 'rocket_pre_minify_path', $file );

			$urls[ $i ] .= $file . ',';
		}

		foreach ( $urls as $url ) {
			$url = $base_url . rtrim( $url, ',' );
			$ext = pathinfo( $url, PATHINFO_EXTENSION );

			if ( $force_pretty_url && ( defined( 'SCRIPT_DEBUG' ) && !SCRIPT_DEBUG ) ) {

				/**
				 * Filter the minify URL
				 *
				 * If true returns,
				 * the minify URL like example.com/wp-content/plugins/wp-rocket/min/?f=...
				 *
				 * @since 2.1
				 *
				 * @param bool
				*/
				if ( ! apply_filters( 'rocket_minify_debug', false ) ) {
					$blog_id = get_current_blog_id();
					$pretty_url = ! $pretty_filename ? WP_ROCKET_MINIFY_CACHE_URL . $blog_id . '/' . md5( $url . get_rocket_option( 'minify_' . $ext . '_key', create_rocket_uniqid() ) ) . '.' . $ext : WP_ROCKET_MINIFY_CACHE_URL . $blog_id . '/' . $pretty_filename . '.' . $ext;

					/**
					 * Filter the pretty minify URL
					 *
					 * @since 2.1
					 *
					 * @param string $pretty_url
					 * @param string $pretty_filename
					*/
					$pretty_url = apply_filters( 'rocket_minify_pretty_url', $pretty_url, $pretty_filename );

					$url = rocket_fetch_and_cache_minify( $url, $pretty_url ) ? $pretty_url : $url;
				}
			}

			// If CSS & JS use a CDN
			$url = get_rocket_cdn_url( $url, array( 'all', 'css_and_js', $ext ) );

			if ( 'css' == $ext ) {
				/**
				 * Filter CSS file URL with CDN hostname
				 *
				 * @since 2.1
				 *
				 * @param string $url
				*/
				$url = apply_filters( 'rocket_css_url', $url );

				$tags .= sprintf( '<link rel="stylesheet" href="%s" %s/>', esc_attr( $url ), $data_attr );

			} elseif ( 'js' == $ext ) {
				/**
				 * Filter JavaScript file URL with CDN hostname
				 *
				 * @since 2.1
				 *
				 * @param string $url
				*/
				$url = apply_filters( 'rocket_js_url', $url );

				$tags .= sprintf( '<script src="%s" %s></script>', esc_attr( $url ), $data_attr );
			}
		}
	}

	return $tags;
}

/*
 * Wrapper of get_rocket_minify_files() and echoes the result
 *
 * @since 2.1
 */
function rocket_minify_files( $files, $force_pretty_url = true, $pretty_filename = null ) {
	echo get_rocket_minify_files( $files, $force_pretty_url, $pretty_filename );
}

/*
 * Get all JS externals files to exclude of the minification process
 *
 * @since 2.6
 */
function get_rocket_minify_excluded_external_js() {
	/**
	 * Filter JS externals files to exclude of the minification process (do not move into the header)
	 *
	 * @since 2.2
	 *
	 * @param array Hostname of JS files to exclude
	 */
	$excluded_external_js = apply_filters( 'rocket_minify_excluded_external_js', array( 
		'forms.aweber.com', 
		'video.unrulymedia.com', 
		'gist.github.com', 
		'stats.wp.com', 
		'stats.wordpress.com', 
		'www.statcounter.com', 
		'widget.rafflecopter.com', 
		'widget-prime.rafflecopter.com', 
		'widget.supercounters.com', 
		'releases.flowplayer.org', 
		'tools.meetaffiliate.com', 
		'c.ad6media.fr', 
		'cdn.stickyadstv.com', 
		'www.smava.de', 
		'contextual.media.net', 
		'app.getresponse.com', 
		'ap.lijit.com', 
		'adserver.reklamstore.com', 
		's0.wp.com', 
		'wprp.zemanta.com', 
		'files.bannersnack.com', 
		'smarticon.geotrust.com',
		'js.gleam.io',
		'script.ioam.de',
		'ir-na.amazon-adsystem.com',
		'web.ventunotech.com',
		'verify.authorize.net',
		'ads.themoneytizer.com',
		'embed.finanzcheck.de',
		'imagesrv.adition.com',
		'js.juicyads.com',
		'form.jotformeu.com',
		'speakerdeck.com',
		'content.jwplatform.com',
		'ads.investingchannel.com',
		'app.ecwid.com'
	) );
	
	return $excluded_external_js;		
}