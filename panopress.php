<?php
/***********************************************************************
Plugin Name: PanoPress
Plugin URI:  http://www.panopress.org/
Description: Embed Flash & HTML5 360° Panoramas & Virtual Tours, 360° Video, Gigapixel Panoramas etc, created using KRPano, Pano2VR, PanoTour Pro, Flashificator, Saladoplayer, and similar panorama applications  on your WordPress site using a simple shortcode.
Version:     1.2
Author:      <a href="http://www.omercalev.com">Omer Calev</a> & <a href="http://www.samrohn.com">Sam Rohn</a>
************************************************************************
	Copyright 2011-2014 by the authors.
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
************************************************************************
	USAGE: [pano file="pano file name or url"]
	
	Optional Parameter:
		width/w    = "100%"
		height/h   = "450px"
		title/t    = "title text"
		alt/a      = "alt text"
		preview/p  = "preview image url"
		panobox/b  = "on/off"
		button/n   = "on/off"
***/
// CONFIG
define( 'PP_APP_NAME',     'PanoPress' );
define( 'PP_APP_VERSION', '1.2' );
// defaults
define( 'PP_DEFAULT_WIDTH',         '640px' );
define( 'PP_DEFAULT_HEIGHT',        '480px' );
define( 'PP_DEFAULT_FLASH_VERSION', '9.0.28' );
define( 'PP_DEFAULT_PHP_VERSION',   '5.1.3' );
// options
define( 'PP_FILE_TYPE_FILTERING',   true ); // prevent unknown types from being open as html
define( 'PP_PANOBOX_IMAGES',        true ); // enable images to be open in panobox
// viewers
define( 'PP_VIEWER_NAME_KRPANO',  'krpano' );
define( 'PP_VIEWER_NAME_PANO2VR', 'pano2vr' );
define( 'PP_VIEWER_NAME_FPP',     'fpp' );
define( 'PP_VIEWER_NAME_CUTY',    'cuty' );
define( 'PP_VIEWER_TYPE_FLASH',   'flash' );
define( 'PP_VIEWER_TYPE_HTML',    'html' );
define( 'PP_VIEWER_TYPE_LINK',    'link' );
// file types
define( 'PP_FILE_TYPE_SWF',       'swf' );
define( 'PP_FILE_TYPE_XML',       'xml' );
define( 'PP_FILE_TYPE_MOV',       'mov' );
define( 'PP_FILE_TYPE_HTML',      'html' );
define( 'PP_FILE_TYPE_UNKNOWN',   'unknown' );
// user agents 
define( 'PP_USER_AGENT_IPHONE',   strpos( $_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false );
define( 'PP_USER_AGENT_IPAD',     strpos( $_SERVER['HTTP_USER_AGENT'], 'iPad') !== false );
define( 'PP_USER_AGENT_IPOD',     strpos( $_SERVER['HTTP_USER_AGENT'], 'iPod') !== false );
define( 'PP_USER_AGENT_ANDROID',  strpos( $_SERVER['HTTP_USER_AGENT'], 'Android') !== false );
define( 'PP_USER_AGENT_IDEVICE',  PP_USER_AGENT_IPHONE || PP_USER_AGENT_IPAD || PP_USER_AGENT_IPOD );
define( 'PP_USER_AGENT_MODILE',   PP_USER_AGENT_IDEVICE || PP_USER_AGENT_ANDROID );
// setting keys, DO NOT EDIT
define( 'PP_SETTINGS',                'panopress_settings' );
define( 'PP_SETTINGS_ID',             'id' );
define( 'PP_SETTINGS_FILE',           'file' );
define( 'PP_SETTINGS_PARAMS',         'params' );
define( 'PP_SETTINGS_VIEWER_NAME',    'viewer' );
define( 'PP_SETTINGS_VIEWER_TYPE',    'type' );
define( 'PP_SETTINGS_VIEWER_VRSION',  'version' );
define( 'PP_SETTINGS_WIDTH',          'width' );
define( 'PP_SETTINGS_HEIGHT',         'height' ); 
define( 'PP_SETTINGS_ALT',            'alt' );
define( 'PP_SETTINGS_TITLE',          'title' );
define( 'PP_SETTINGS_PREVIEW',        'preview' );
define( 'PP_SETTINGS_PLAY_BUTTON',    'button' );
define( 'PP_SETTINGS_UPLOAD_DIR',     'upload_dir' );
define( 'PP_SETTINGS_UPLOAD_WP',      'upload_wp' );
define( 'PP_SETTINGS_WMODE',          'wmode' );
define( 'PP_SETTINGS_PANOBOX',        'panobox' );
define( 'PP_SETTINGS_PANOBOX_WMODE',  'pbwmode' );
define( 'PP_SETTINGS_PANOBOX_ACTIVE', 'pbactive' );
define( 'PP_SETTINGS_PANOBOX_MOBILE', 'pbmobile' );
define( 'PP_SETTINGS_VIEWER_DIR',     'viewer_dir' );
define( 'PP_SETTINGS_USE_VIEWER_DIR', 'use_viewer_dir' );
define( 'PP_SETTINGS_OPPP',           'oppp' );
define( 'PP_SETTINGS_CSS',            'css' );
// panobox
define( 'PB_SETTINGS_FULLSCREEN',    'fullscreen' );
define( 'PB_SETTINGS_WIDTH',         'width' );
define( 'PB_SETTINGS_HEIGHT',        'height' );
define( 'PB_SETTINGS_FADE',          'fade' );
define( 'PB_SETTINGS_ANIMATE',       'animate' );
define( 'PB_SETTINGS_SHADOW',        'shadow' );
define( 'PB_SETTINGS_RESIZE',        'resize' );
define( 'PB_SETTINGS_STYLE',         'style' );
define( 'PB_SETTINGS_STYLE_BOX',     'box' );
define( 'PB_SETTINGS_STYLE_OVERLAY', 'overlay' );
define( 'PB_SETTINGS_GALLERIES',     'galleries' );
// one pano pre page
define( 'PP_OPPP_ALL',      'all' );
define( 'PP_OPPP_MOBILE',   'mobile' );
define( 'PP_OPPP_DISABLED', 'disabled' );
/**/
$pp_wp_upload_arr = wp_upload_dir();
$pp_wp_upload_dir = trim( substr( $pp_wp_upload_arr['basedir'], strlen($_SERVER['DOCUMENT_ROOT'] ) ), '/' );
$pp_krpano_js     = false;
$pp_pano2vr_js    = false;
$pp_settings      = get_option( PP_SETTINGS );
$pp_id_counter    = 0;
/************************  set defaults *******************************/
function pp_default_settings() { 
	global $pp_settings, $pp_wp_upload_dir;
	// panopress
	$pp_settings[PP_SETTINGS_WIDTH]          = PP_DEFAULT_WIDTH;
	$pp_settings[PP_SETTINGS_HEIGHT]         = PP_DEFAULT_HEIGHT;
	$pp_settings[PP_SETTINGS_UPLOAD_WP]      = true;
	$pp_settings[PP_SETTINGS_UPLOAD_DIR]     = $pp_wp_upload_dir;
	$pp_settings[PP_SETTINGS_ALT]            = '';
	$pp_settings[PP_SETTINGS_TITLE]          = '';
	$pp_settings[PP_SETTINGS_PLAY_BUTTON]    = true;
	$pp_settings[PP_SETTINGS_USE_VIEWER_DIR] = false;
	$pp_settings[PP_SETTINGS_WMODE]          = 'auto';
	$pp_settings[PP_SETTINGS_PANOBOX_ACTIVE] = false;
	$pp_settings[PP_SETTINGS_PANOBOX_WMODE]  = 'auto';
	$pp_settings[PP_SETTINGS_PANOBOX_MOBILE] = true;
	$pp_settings[PP_SETTINGS_OPPP]           = PP_OPPP_MOBILE;
	// panobox
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FULLSCREEN] = true;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FADE]       = true;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_ANIMATE]    = true;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_STYLE]      = 'light';
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_SHADOW]     = true;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_BG_OPACITY] = 0.6;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_GALLERIES]  = false;
}
if ( ! $pp_settings ) {
	$pp_settings = array();
	pp_default_settings();
}
/**
 * pp_get_url( $url ) add @ 1.1
 * get url
 * @param url: the url to get
 * @param allowSSL: if true will allow use of ssl
 * return: [string]
 **/ 
function pp_get_url( $url, $allowSSL = false ) {
	$respnse = array ( 'status' => null, 'content' => null );
	$curl = curl_init( $url );
	if ( $allowSSL ) {
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // trust all sites
	}
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly. (http://www.php.net/manual/en/function.curl-setopt.php)
	if ( !ini_get( 'open_basedir' ) && !ini_get( 'safe_mode' ) ) {
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true);// alow redirec
		curl_setopt( $curl, CURLOPT_MAXREDIRS, 6 ); // max redirects
	}
	$respnse['content'] = curl_exec( $curl );
	$respnse['status']  = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
	curl_close( $curl );
	return $respnse;
}
/**
 * pp_get_viewr_name ( $xml_path ) add @ 1.1
 * get viewer name
 * @param xml_url: the url of xml file
 * @param ignore_errors: if true will print error msg for anmin
 * return: array( 'status' => $status , 'content' => $content ) status: 1 - ok, 0 - failed
 **/ 
function pp_get_viewr_name ( $xml_url ) {
	$status  = 0;
	$content = '';
	// error reporting
	libxml_use_internal_errors( is_user_logged_in() );
	
	// test allow_url_fopen
	if( ini_get( 'allow_url_fopen' ) == 1 ){
		$xml =  @ simplexml_load_file( $xml_url );
		
	} else if ( function_exists( 'curl_init' ) ) { // try curl
		$results = pp_get_url( $xml_url );
		if ( $results['status'] == 200 ) {
			$xml = simplexml_load_string( $results['content'] );
		} 
	} 
	
	if ( $xml ) {
		if ( $xml -> getName() == 'krpano' ) {     
			$content = PP_VIEWER_NAME_KRPANO;      // krpano xml
			$status  = 1;
		} elseif ( $xml -> getName() == 'panorama' ) {
			$content = PP_VIEWER_NAME_PANO2VR;     // pano2vr xml
			foreach ( $xml -> children() as $second ) {
				if ( $second-> getName() ==  'parameters' ) {
					$content = PP_VIEWER_NAME_FPP; // fpp xml
				}
			}
			$status = 1;
		} elseif ( $xml -> getName() == 'tour' ) {
			foreach ( $xml -> children() as $second ) {
				if ( $second-> getName() ==  'panorama' ) {
					$content = PP_VIEWER_NAME_PANO2VR;     // pano2vr tour xml
					$status = 1;
				}
			}
		}
	}
	return array( 'status' => $status , 'content' => $content );
}
/**
 * inject code into head
 **/
function pp_headers() {
global $pp_settings;
$oppp = $pp_settings[PP_SETTINGS_OPPP] == PP_OPPP_ALL || ( $pp_settings[PP_SETTINGS_OPPP] == PP_OPPP_MOBILE && PP_USER_AGENT_MODILE )? 'true' : 'false';

// add resize default to pp settings
$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_RESIZE] = 1;

echo '<!-- ' . PP_APP_NAME . ' [' . PP_APP_VERSION . '] -->
<script type="text/javascript">
pp_oppp=' . $oppp . ';
pb_options=' . json_encode( $pp_settings[PP_SETTINGS_PANOBOX] ) . ';
</script>
<script type="text/javascript"  src="' . plugins_url( '/js/panopress.js',  __FILE__  )  . '?v='. PP_APP_VERSION .'"></script>
<link rel="stylesheet" type="text/css" media="all" href="' . plugins_url( '/css/panopress.css?v='. PP_APP_VERSION ,  __FILE__  )  . '" />	
';
if( strlen(  $pp_settings[PP_SETTINGS_CSS] ) > 1 ) {
echo '<style type="text/css">
' .  $pp_settings[PP_SETTINGS_CSS] . '
</style>
';
}
echo '<!-- /' . PP_APP_NAME . ' -->
';
}
add_action( 'wp_head', 'pp_headers' );

/**
 * inject code into footer
 **/
function pp_footer() {
	if( PP_PANOBOX_IMAGES || $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_GALLERIES] ) {
		echo '<script type="text/javascript">panopress.imagebox();</script>';
	}
}
add_action( 'wp_footer', 'pp_footer');

/**
 * override gallery_shortcode and change link to 'file'
 **/
function pp_gallery_shortcode ( $atrr ) {
	$atrr['link'] = 'file';
	return gallery_shortcode ( $atrr );
}

if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_GALLERIES] ) {
	add_shortcode( 'gallery', 'pp_gallery_shortcode' );
}

// admin page
if ( is_admin() ) {
	require_once( dirname( __FILE__ ) . '/includes/admin.php' );
}

//add settings link on plugin page (added @ 1.0)
function pp_settings_link( $links ) { 
  array_unshift( $links, '<a href="options-general.php?page=panopress">' . pp__( 'Settings' ) . '</a>' ); 
  return $links; 
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__) , 'pp_settings_link' );

//add Instructions $ forums links on plugin page (added @ 1.0)
function pp_set_plugin_meta( $links, $file ) {
	if ( $file == plugin_basename(__FILE__) ) {
		array_push( $links, '<a href="http://www.panopress.org/instructions/" target="_blank">' . PP_APP_NAME . ' ' . pp__( 'Instructions' ) . '</a>' );
		array_push( $links, '<a href="http://www.panopress.org/forums/" target="_blank">' . PP_APP_NAME . ' ' . pp__( 'Forums' ) . '</a>' );
		array_push( $links, '<a href="http://wordpress.org/extend/plugins/panopress/" target="_blank">' . pp__( 'WordPress Plugin Page' ) . '</a>' );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pp_set_plugin_meta', 10, 2 );

/**
 * language support (not implemented)
 **/
function pp__( $msg ) {
	return __( $msg );
}
function pp_e( $msg ) {
	echo pp__( $msg );
}

/**
 * return error html code for $msg
 **/
function pp_error( $msg ) {
return '<div class="pp-error-msg"><strong>' . pp__( 'Error' ) . '</strong><br/>' . $msg . '</div>';
}

/**
 * validate and formate width & height values enterd by user
 * return formated size if ok or null if failed
  * @param size: [string] the size being checked
 **/
function pp_check_size ( $size ) {
	if ( strlen ( $size ) < 1 ) {
		return null;
	}
	$size  = trim ( $size );
	if ( preg_match ( '/[^0-9]/' , $size ) === 0 ) { 
		return $size . 'px';
	}
	
	$len   = strlen( $size ) - 1;
	$parts = array( substr ( $size, 0, $len ), substr ( $size, $len ) ); // [0] = value, [1] = units
	
	// check for %
	if ( $parts[1] != '%' ){
		$len--;
		$parts = array( substr ( $size, 0, $len ), substr ( $size, $len ) );
	}

	// check value to be number
	if ( preg_match ( '/[^0-9]/' , $parts[0] ) !== 0 ) {
		return null;
	}
	
	// validate units, use px as default
	if ( preg_match ( '/px|em|ex|%|in|cm|mm|pt|pc/' , $parts[ 1 ] ) != 1 ) {
		$parts[ 1 ] = 'px';
	}

	return  implode ( $parts );
}

/**
 * return html code for embbeding
 * @param settings: [array] pp settings
 * @param params: [array] flash params (optinal).
 * @param type: [string] the type of viewer (flash or iDevice)
 * @param version: [string] minimal version
 **/
function pp_embed( $settings, $params = null, $type = PP_VIEWER_TYPE_FLASH, $version = PP_DEFAULT_FLASH_VERSION ){
	global $pp_js, $pp_id_counter;
	$id = 'pp_' . $pp_id_counter++;
	if ( PP_USER_AGENT_MODILE && $settings[PP_SETTINGS_PANOBOX_ACTIVE] ) {
		$settings[PP_SETTINGS_PANOBOX_ACTIVE] = $settings[PP_SETTINGS_PANOBOX_MOBILE];
	}
	if ( $type == PP_VIEWER_TYPE_FLASH ) {
		$params['wmode'] = $settings[PP_SETTINGS_PANOBOX_ACTIVE] ? $settings[PP_SETTINGS_PANOBOX_WMODE] : $settings[PP_SETTINGS_WMODE];
	}
	$embed = array(
		PP_SETTINGS_ID             => $id,
		PP_SETTINGS_VIEWER_TYPE    => $type,
		PP_SETTINGS_VIEWER_VRSION  => $version,
		PP_SETTINGS_VIEWER_NAME    => $settings[PP_SETTINGS_VIEWER_NAME],
		PP_SETTINGS_WIDTH          => $settings[PP_SETTINGS_WIDTH],
		PP_SETTINGS_HEIGHT         => $settings[PP_SETTINGS_HEIGHT],
		PP_SETTINGS_TITLE          => $settings[PP_SETTINGS_TITLE],
		PP_SETTINGS_ALT            => $settings[PP_SETTINGS_ALT],
		PP_SETTINGS_PLAY_BUTTON    => $settings[PP_SETTINGS_PLAY_BUTTON],
		PP_SETTINGS_PANOBOX        => $settings[PP_SETTINGS_PANOBOX_ACTIVE],
		PP_SETTINGS_PREVIEW        => $settings[PP_SETTINGS_PREVIEW],
		PP_SETTINGS_FILE           => $settings[PP_SETTINGS_FILE],
		PP_SETTINGS_PARAMS         => $params
	);
	$html = '
<!-- ' . PP_APP_NAME . ' [' . PP_APP_VERSION . '] -->
';
	if ( strlen( $settings[PP_SETTINGS_PREVIEW] ) < 1 && $settings[PP_SETTINGS_PANOBOX_ACTIVE] ){
		$html .= '<div class="pp-embed">
<div id="' . $id . '">' . $settings[PP_SETTINGS_ALT] . '</div>
';
	}else{
		// 1.2 - support @media queries
		$html .= '<div class="pp-embed" style="position:relative;">
<div id="' . $id . '" style="width:' . $settings[PP_SETTINGS_WIDTH] . '; height:' . $settings[PP_SETTINGS_HEIGHT] . '">' . ( strlen( $settings[PP_SETTINGS_PREVIEW] ) > 0 ? '<img src="' . $settings[PP_SETTINGS_PREVIEW] . '" style="width:' . $settings[PP_SETTINGS_WIDTH] . '; height:' . $settings[PP_SETTINGS_HEIGHT] . '"/>' : '' ) . '<p>' . $settings[PP_SETTINGS_ALT] . '</p></div>
';
	}
	$html  .= '<script type="text/javascript">panopress.embed(' . json_encode( $embed ) . ')</script>
<noscript>' . pp_error( pp__( 'Javascript not activated' ) ) . '</noscript>
</div>
<!-- /' . PP_APP_NAME . ' -->
';
	return $html;
}

/**
 * return html code for unknown type
 * @param setting: [array] pp settings
 **/
function pp_unknown( $settings ) {
	if ( PP_ALLOW_UNKNOWN_FILE_TYPES ) {
		pp_html( $settings );
	}
	else { 
		$settings[PP_SETTINGS_PANOBOX_ACTIVE] = false;
		return  pp_embed( $settings, null, PP_VIEWER_TYPE_LINK, '0'  );
	}
}

/**
 * return html code for html type
 * @param setting: [array] pp settings
 **/
function pp_html( $settings ) {
	$base = substr( $settings[PP_SETTINGS_FILE], 0, strrpos($settings[PP_SETTINGS_FILE], '/' ) + 1 );
	return  pp_embed( $settings, array( 'base' => $base), PP_VIEWER_TYPE_HTML, '4.0'  );
}

/**
 * return html code for swf type
 * @param setting: [array] pp settings
 **/
function pp_swf( $settings ) {
	// (try to ) get viewr name
	$got_name = pp_get_viewr_name( str_ireplace ( '.swf', '.xml' , $settings[PP_SETTINGS_FILE] ) );
	$settings[PP_SETTINGS_VIEWER_NAME] = $got_name[ 'status' ] == 1 ? $got_name[ 'content' ] : 0;
	
	$base = substr( $settings[PP_SETTINGS_FILE], 0, strrpos($settings[PP_SETTINGS_FILE], '/' ) + 1 );
	return  pp_embed( $settings, array( 'base' => $base), PP_VIEWER_TYPE_FLASH, '9.0.0'  );
}

/**
 * return html code for mov type
 * @param setting: [array] pp settings
 **/
function pp_mov( $settings ) {
	// 1.0
	//$settings[PP_SETTINGS_FILE] = plugins_url( '/flash/cuty.swf',  __FILE__  ) . '?mov=' . $settings[PP_SETTINGS_FILE];
	/**/
	// 1.1
	$cutyURL =  plugins_url( '/flash/cuty.swf',  __FILE__  );
	$rq      = pp_get_url( $cutyURL, true ); // looking for cuty in flash folder 
	if ( $rq['status'] != 200 ) {
		$cutyURL =  site_url( '/' . $settings[PP_SETTINGS_UPLOAD_DIR] . '/' . 'cuty.swf' ); 
		$rq      = pp_get_url( $cutyURL, true ); // looking for cuty in viewer folder 		
	}
	if ( $rq['status'] != 200 ) {
		return is_user_logged_in() ? pp_error( pp__( 'Can\'t find CuTy' ) ) : '';
	}
	$settings[PP_SETTINGS_FILE] = $cutyURL . '?mov=' . $settings[PP_SETTINGS_FILE];
	//
	
	$settings[PP_SETTINGS_VIEWER_NAME] = PP_VIEWER_NAME_CUTY;
	return  pp_embed( $settings, null, PP_VIEWER_TYPE_FLASH, '10.0.0' );
}

/**
 * return html code for xml pano2vr
 * @param setting: [array] pp settings
 **/
function pp_xml_pano2vr( $settings ) {
	global $pp_pano2vr_js;
	$base = substr( $settings[PP_SETTINGS_FILE], 0, strrpos($settings[PP_SETTINGS_FILE], '/') + 1 );
	$html = '';
	// if user agent is not iPhone/Pad/Pod, use swf
	if( PP_USER_AGENT_MODILE ) {
		$xml = $settings[PP_SETTINGS_FILE];
		$xml = substr( $xml, 7 );
		$xml = substr( $xml, strpos( $xml, '/' )  + 1);
		$settings[PP_SETTINGS_FILE] =  plugins_url( 'pano2vr.php',  __FILE__  );
		$html .= pp_embed( $settings, array( 'xml' => $xml ), PP_VIEWER_TYPE_HTML, '5.0' );	
	}
	else{
		$settings[PP_SETTINGS_FILE] = substr( $settings[PP_SETTINGS_FILE], 0, strrpos($settings[PP_SETTINGS_FILE], '.') + 1)  . 'swf';
		$html .= pp_embed( $settings, array( 'base' => $base), PP_VIEWER_TYPE_FLASH, '9.0.0'  );
	}
return $html;
}

/**
 * return html code for xml krpano
 * @param setting: [array] pp settings
 **/
function pp_xml_krpano( $settings ) {
	global $pp_krpano_js;
	$html = '';
	$id  = 'pp_' . rand( 1000, 9999 );
	$xml = $settings[PP_SETTINGS_FILE];
	if( PP_USER_AGENT_MODILE ){	
		$xml = substr( $xml, 7 );
		$xml = substr( $xml, strpos( $xml, '/' )  + 1);
	}
	if ( $settings[PP_SETTINGS_USE_VIEWER_DIR] ) {
		$swf = site_url( '/' . $settings[PP_SETTINGS_VIEWER_DIR] . '/' . 'krpano.swf' );
		$js  = site_url( '/' . $settings[PP_SETTINGS_VIEWER_DIR] . '/' . 'krpano.js' );
	} else {
		$str = substr( $xml, 0, strlen( $settings[PP_SETTINGS_FILE] ) - 3 );
		$swf = $str . 'swf';
	}
	if( PP_USER_AGENT_MODILE ){
		$settings[PP_SETTINGS_FILE] =  plugins_url( 'krpano.php',  __FILE__  );
		$html .= pp_embed( $settings, array( 'xml' => $xml ), PP_VIEWER_TYPE_HTML, '5.0' );	
	}
	else {
		$settings[PP_SETTINGS_FILE] = $swf;
		$html .= pp_embed( $settings, array( 'flashvars' => array( 'xml' => $xml ) ), PP_VIEWER_TYPE_FLASH, '9.0.28' );
	}
	return $html;
}

/**
 * return html code for xml fpp
 * @param setting: [array] pp settings
 **/
function pp_xml_fpp( $settings ) {
	$id  = 'pp_' . rand( 1000, 9999 );
	$xml = $settings[PP_SETTINGS_FILE];
	if ( $settings[PP_SETTINGS_USE_VIEWER_DIR] ) {
		$swf = site_url( '/' . $settings[PP_SETTINGS_VIEWER_DIR] . '/' . 'fpp.swf' );
		//$js  = site_url( '/' . $settings[PP_SETTINGS_VIEWER_DIR] . '/' . 'fpp.js' );
	} else {
		$swf = substr( $xml, 0, strlen( $xml ) - 3 ) . 'swf';
		//$js  = $str . 'js';
	}
	// use panopress swf function
	$settings[PP_SETTINGS_FILE] = $swf;
	return pp_embed( $settings, array( 'base' => substr( $xml, 0, strrpos($xml, '/') + 1 ), 'flashvars' =>  array( 'xml_file' => $xml ) ), PP_VIEWER_TYPE_FLASH, '9.0.0' );
}

/**
 * return the html code for the pano type
 * @param setting: [array] pp settings
 **/
function pp_select( $settings ) {
	// test width
	$settings[PP_SETTINGS_WIDTH] = pp_check_size( $settings[PP_SETTINGS_WIDTH] );

	// test height
	$settings[PP_SETTINGS_HEIGHT] = pp_check_size( $settings[PP_SETTINGS_HEIGHT] );
	
	// test file format
	if ( $settings[PP_SETTINGS_TYPE] == PP_FILE_TYPE_SWF )
		return pp_swf( $settings );
	
	elseif( $settings[PP_SETTINGS_TYPE] == PP_FILE_TYPE_MOV )
		return pp_mov( $settings );
	
	elseif( $settings[PP_SETTINGS_TYPE] == PP_FILE_TYPE_XML ) {
		switch( $settings[PP_SETTINGS_VIEWER_NAME] ) {
			case PP_VIEWER_NAME_PANO2VR: return pp_xml_pano2vr( $settings ); 
			case PP_VIEWER_NAME_KRPANO : return pp_xml_krpano( $settings );
			case PP_VIEWER_NAME_FPP    : return pp_xml_fpp( $settings );
			default: return pp_error( pp__( 'Viewer is not supported' ) );
		}
	}

	// if type filtering is not on, go to html action
	if ( !PP_FILE_TYPE_FILTERING ) {
		$settings[PP_SETTINGS_TYPE] = PP_FILE_TYPE_HTML;
		return pp_html( $settings );	
	}
	
	// trailing slash (www.domain.com/dir/)
	if ( substr( $settings[PP_SETTINGS_FILE], -1 ) == '/' ) {
			$settings[PP_SETTINGS_TYPE] = PP_FILE_TYPE_HTML;
			return pp_html( $settings );		
	}
	
	// file types
	$ext = array('htm','php','asp','jsp','cfm','cgi','pl');
	foreach( $ext as $e ){
		if( strstr( $settings[PP_SETTINGS_TYPE], $e) ){
			$settings[PP_SETTINGS_TYPE] = PP_FILE_TYPE_HTML;
			return pp_html( $settings );
		}
	}

	// unknown
	$settings[PP_SETTINGS_TYPE] = PP_FILE_TYPE_UNKNOWN;
	return pp_unknown( $settings );
}

/**
 * shortcode handler
 * @param attributes: [array]  shortcode attributes
 **/
function pp_sohrtcode_handler( $attributes ) {
	global $pp_settings;
	/* user can use short keys, eg. 'w' for 'width' etc.
	   only items in this array allowed to pass into settings */
	$att   = array(
		'f' => PP_SETTINGS_FILE,
		'w' => PP_SETTINGS_WIDTH,
		'h' => PP_SETTINGS_HEIGHT,
		'a' => PP_SETTINGS_ALT,
		't' => PP_SETTINGS_TITLE,
		'p' => PP_SETTINGS_PREVIEW,
		'b' => PP_SETTINGS_PANOBOX,
		'n' => PP_SETTINGS_PLAY_BUTTON
	);
	// clean attributes and use short keys
	$clean = array();
	foreach( $att as $key => $val ) {
		if ( array_key_exists( $val, $attributes ) ) $clean[$val] = $attributes[$val];     /* test for full length key first */
		elseif ( array_key_exists( $key, $attributes ) ) $clean[$val] = $attributes[$key]; /* test for short key */
	}
	// check play button
	if ( isset( $clean[PP_SETTINGS_PLAY_BUTTON] ) ) {
		$clean[PP_SETTINGS_PLAY_BUTTON] = pp_bool( $clean[PP_SETTINGS_PLAY_BUTTON] );
	}
	// check panobox
	if ( isset( $clean[PP_SETTINGS_PANOBOX] ) ) {
		$clean[PP_SETTINGS_PANOBOX_ACTIVE] = pp_bool( $clean[PP_SETTINGS_PANOBOX]  );
		unset( $clean[PP_SETTINGS_PANOBOX] );
	}
	// combine the shortcode attribute with default settings
	$settings = array_merge( $pp_settings, $clean );
	// check if the file name was set
	if ( ! $settings[PP_SETTINGS_FILE] ) {
		return pp_error( pp__( 'Please enter file name or URL' ) );
	}	
	// set type by file ext 
	$filestr = strtolower( $settings[PP_SETTINGS_FILE] );
	if( strstr( $filestr, '?' ) ){
		$filestr = substr ($filestr, 0, strpos( $filestr, '?') );
	}

	$file_name = substr( $filestr,  strrpos( $filestr, '/' ) );
	$settings[PP_SETTINGS_TYPE] = substr ( $file_name, strrpos( $file_name, '.' ) + 1 );//substr ( $filestr, strrpos( $filestr, '.') + 1 );	
	// if file is not full url, craete a local url
	if ( strtolower( substr( $settings[PP_SETTINGS_FILE], 0, 4 ) ) != 'http' )
		$settings[PP_SETTINGS_FILE] =  site_url( '/' . $pp_settings[PP_SETTINGS_UPLOAD_DIR] . '/' . $settings[PP_SETTINGS_FILE] );
	// if poster is not full url, craete a local url
	if ( strlen( $settings[PP_SETTINGS_PREVIEW]) > 0 && strtolower( substr( $settings[PP_SETTINGS_PREVIEW], 0, 4 ) ) != 'http' )
		$settings[PP_SETTINGS_PREVIEW] = site_url( '/' . $pp_settings[PP_SETTINGS_UPLOAD_DIR] . '/' . $settings[PP_SETTINGS_PREVIEW] );
	// replace spaces in url with %20
	$settings[PP_SETTINGS_PREVIEW] = str_replace ( ' ' , '%20' , $settings[PP_SETTINGS_PREVIEW] );
	$settings[PP_SETTINGS_FILE]    = str_replace ( ' ' , '%20' , $settings[PP_SETTINGS_FILE] );
	
	// parse xml
	if ( $settings[PP_SETTINGS_TYPE] == PP_FILE_TYPE_XML ) {
		$got_name = pp_get_viewr_name ( $settings[ PP_SETTINGS_FILE ] );
		if ( $got_name[ 'status' ] == 1 ) {
			$settings[ PP_SETTINGS_VIEWER_NAME ] = 	$got_name[ 'content' ];
		} elseif ( is_user_logged_in() ) {
			return pp_error ( $got_name[ 'content' ] );
		}
		
		// error report (admin only)
		libxml_use_internal_errors( is_user_logged_in() );
		// test allow_url_fopen
		if( ini_get( 'allow_url_fopen' ) == 1 ){ 
			$xml = is_user_logged_in() ? simplexml_load_file( $settings[PP_SETTINGS_FILE] ) :  @ simplexml_load_file( $settings[PP_SETTINGS_FILE] );
		}
		// try curl
		else if ( function_exists('curl_init') ) {

			//1.1
			$results = pp_get_url( $settings[PP_SETTINGS_FILE] );
			if ( $results['status'] == 200 ) {
				$xml = simplexml_load_string( $results['content'] );
			} elseif ( is_user_logged_in() ) {
				return pp_error ( pp__( 'Can\'t find XML file' ) . ' ' . $settings[PP_SETTINGS_FILE] );
			}
			
		}
		// TODO: ask input from user (admin only)
		elseif ( is_user_logged_in() ) {
			return pp_error( '<p>' . pp__( '"allow_url_fopen" option is not enabled in the php.ini file on this server & cURL is not installed.' ) . '.</p>');
		}
		// xml errors (admin only)
		if ( $xml === false && is_user_logged_in() ) {
			$err = '';
			foreach( libxml_get_errors() as $error )
				$err .=  $error->message . '(line ' . $error->line . ' in ' . $error->file . ')<br />';
			return pp_error( '<p>' . $err . '</p>' );
		}
		elseif ( $xml ) {
			if ( $xml -> getName() == 'krpano' )      
				$settings[PP_SETTINGS_VIEWER_NAME] = PP_VIEWER_NAME_KRPANO;      // krpano xml
			elseif ( $xml -> getName() == 'panorama' ) {
				$settings[PP_SETTINGS_VIEWER_NAME] = PP_VIEWER_NAME_PANO2VR;     // pano2vr xml
				foreach ( $xml -> children() as $second ) 
					if ( $second-> getName() ==  'parameters' )
						$settings[PP_SETTINGS_VIEWER_NAME] = PP_VIEWER_NAME_FPP; // fpp xml
			}
		}
		else
			return $settings[PP_SETTINGS_ALT];
	}
	// call select with settings array
	return pp_select( $settings );
	
}

function pp_bool( $subject ){
	$subject = strtolower( $subject );
	return $subject === 'true'  || $subject === 'on'  || $subject === 'yes' || $subject === '1' ? true : false ;
}

// add pano shortcode
add_shortcode( 'pano', 'pp_sohrtcode_handler' );
?>
