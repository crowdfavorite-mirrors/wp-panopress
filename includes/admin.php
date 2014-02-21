<?php
$pp_option_page = null;
/***********************************************************************
 * admin init action
 **********************************************************************/
function pp_admin_init() {
	wp_register_script( 'pp_admin_js', plugins_url( '/js/admin.js?v='. PP_APP_VERSION , dirname( __FILE__ ) ) );
	pp_add_tynymce_button();
}
/***********************************************************************
 * add admin_menu
 **********************************************************************/
function pp_admin_menu() {
	global $pp_option_page;
	$pp_option_page = add_options_page( PP_APP_NAME . ' ' . pp__( 'Settings'), PP_APP_NAME, 'manage_options', strtolower( PP_APP_NAME ), 'pp_edit_settings' );
	add_action( 'admin_print_scripts-' . $pp_option_page, 'pp_admin_headers' );
}
/***********************************************************************
 * print admin headers
 **********************************************************************/
function pp_admin_headers() {
	 wp_enqueue_script( 'pp_admin_js' );
}
/***********************************************************************
 * print admin scripts
 **********************************************************************/
function pp_admin_print_scripts(){
	global $pp_settings;
	echo '<script type="text/javascript">
var PP_SETTINGS_UPLOAD_DIR = "' . site_url( '/' . $pp_settings[PP_SETTINGS_UPLOAD_DIR] . '/' ) . '";
var PP_SETTINGS_WIDTH = \'' . $pp_settings[PP_SETTINGS_WIDTH] . '\';
var PP_SETTINGS_HEIGHT = \'' . $pp_settings[PP_SETTINGS_HEIGHT] . '\';
</script>
';
}
/***********************************************************************
 * add tynymce plugin
 **********************************************************************/
function pp_load_tinymce_plugin($plugin_array) {
	$plugin_array['panopress'] = plugins_url( '/js/tinymce/editor_plugin.js', dirname( __FILE__ ) );
	return $plugin_array;
}
/***********************************************************************
 * load tynymce button
 **********************************************************************/
function pp_load_tynymce_button($buttons) {
   array_push($buttons, 'separator', 'pp_button');
   return $buttons;
}
/***********************************************************************
 * add tynymce button
 **********************************************************************/
function pp_add_tynymce_button() {
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
		return;
	if ( get_user_option('rich_editing') == 'true') {
		add_filter( 'mce_external_plugins', 'pp_load_tinymce_plugin' );
		add_filter( 'mce_buttons', 'pp_load_tynymce_button' );
	}
}
/***********************************************************************
 * add contextual help
 **********************************************************************/
function pp_contextual_help( $contextual_help, $screen_id, $screen ) {
	global $pp_option_page;
	if ( $screen_id == $pp_option_page )
		$contextual_help = '<a href="http://www.panopress.org/instructions/" target="_help">PanoPress ' . pp__('Documentation') . '</a>';
	return $contextual_help;
}
/***********************************************************************
 * uninstall plugin
 **********************************************************************/
function pp_uninstall() {
    delete_option( PP_SETTINGS );
}
/***********************************************************************
 * register actions
 **********************************************************************/
if (is_admin()) {
	add_action( 'admin_init', 'pp_admin_init' );
	add_action( 'admin_menu', 'pp_admin_menu' );
	add_action( 'admin_print_scripts', 'pp_admin_print_scripts' );
	add_filter( 'contextual_help', 'pp_contextual_help', 10, 3 );
	register_uninstall_hook( __FILE__, 'pp_uninstall' );
}
/***********************************************************************
 * get wp root directory
 * @path: path to add to root
 **********************************************************************/
function pp_wp_root($path = '') {
	return substr( $_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'wp-admin' ) ) . trim( strtolower( $path ), '/' );
}
/***********************************************************************
 * create the edit page
 **********************************************************************/
function pp_edit_settings() {
	global $pp_wp_upload_dir, $pp_settings;
	/* hadle post */
	if ( ! empty( $_POST ) && $_POST['pp_action'] && is_admin() ) {
		/* check nonce */
		if ( ! wp_verify_nonce( $_POST['pp-nonce'], 'pp-settings-action') )
		   die( pp__( 'Sorry, you can not post to this page (nonce did not verify).' ) );
		 /* reset */
		if ( $_POST['pp_action'] == 'reset' ) {
			delete_option( PP_SETTINGS );
			pp_default_settings();
			delete_option( PP_CSS );
		} elseif ( $_POST['pp_action'] == 'update' ) { // update
			$style = array();
			$e = explode(',' , $_POST[PP_SETTINGS_PANOBOX  . '_' . PB_SETTINGS_STYLE]);
			for($i = 0; $i < count($e); $i++){
				$t = explode(':', $e[$i]);
				$style[$t[0]] =  $t[1];
			}
			// pp settings
			$pp_settings = array( );
			$pp_settings[ PP_SETTINGS_WIDTH ]          = pp_check_size( $_POST[PP_SETTINGS_WIDTH] );
			$pp_settings[ PP_SETTINGS_HEIGHT ]         = pp_check_size( $_POST[PP_SETTINGS_HEIGHT] );
			$pp_settings[ PP_SETTINGS_UPLOAD_WP ]      = $_POST[PP_SETTINGS_UPLOAD_WP];
			$pp_settings[ PP_SETTINGS_UPLOAD_DIR ]     = $pp_settings[PP_SETTINGS_UPLOAD_WP] ? $pp_wp_upload_dir : trim( strtolower( $_POST[PP_SETTINGS_UPLOAD_DIR] ), '/' );
			$pp_settings[ PP_SETTINGS_VIEWER_DIR ]     = trim( strtolower( $_POST[PP_SETTINGS_VIEWER_DIR] ), '/' );
			$pp_settings[ PP_SETTINGS_USE_VIEWER_DIR ] = $_POST[PP_SETTINGS_USE_VIEWER_DIR];
			$pp_settings[ PP_SETTINGS_WMODE ]          = $_POST[PP_SETTINGS_WMODE];
			$pp_settings[ PP_SETTINGS_OPPP ]           = $_POST[PP_SETTINGS_OPPP];
			$pp_settings[ PP_SETTINGS_PANOBOX_WMODE ]  = $_POST[PP_SETTINGS_PANOBOX_WMODE];
			$pp_settings[ PP_SETTINGS_PLAY_BUTTON ]    = $_POST[PP_SETTINGS_PLAY_BUTTON]    == '1';
			$pp_settings[ PP_SETTINGS_PANOBOX_ACTIVE ] = $_POST[PP_SETTINGS_PANOBOX_ACTIVE] == '1';
			$pp_settings[ PP_SETTINGS_PANOBOX_MOBILE ] = $_POST[PP_SETTINGS_PANOBOX_MOBILE] != '1';
			$pp_settings[ PP_SETTINGS_CSS ]            = $_POST[PP_SETTINGS_CSS];
			// pb settings
			$pp_settings[ PP_SETTINGS_PANOBOX ]        = array( );
			$pp_settings[ PP_SETTINGS_PANOBOX ][ PB_SETTINGS_FULLSCREEN ] = $_POST[PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_FULLSCREEN] == '1';
			$pp_settings[ PP_SETTINGS_PANOBOX ][ PB_SETTINGS_FADE ]       = $_POST[PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_FADE]       == '1';
			$pp_settings[ PP_SETTINGS_PANOBOX ][ PB_SETTINGS_ANIMATE ]    = $_POST[PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_ANIMATE]    == '1';
			$pp_settings[ PP_SETTINGS_PANOBOX ][ PB_SETTINGS_SHADOW ]     = $_POST[PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_SHADOW]     == '1';
			$pp_settings[ PP_SETTINGS_PANOBOX ][ PB_SETTINGS_WIDTH ]      = pp_check_size( $_POST[PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_WIDTH] );
			$pp_settings[ PP_SETTINGS_PANOBOX ][ PB_SETTINGS_HEIGHT ]     = pp_check_size( $_POST[PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_HEIGHT] );
			//$pp_settings[ PP_SETTINGS_PANOBOX ][ PB_SETTINGS_RESIZE ]     = true;
			$pp_settings[ PP_SETTINGS_PANOBOX ][ PB_SETTINGS_STYLE ]      = $style;
			$pp_settings[ PP_SETTINGS_PANOBOX ][ PB_SETTINGS_GALLERIES ]  = $_POST[PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_GALLERIES]     == '1';
			/* save settings */
			if ( get_option( PP_SETTINGS ) )
				update_option( PP_SETTINGS, $pp_settings );
			else
				add_option( PP_SETTINGS, $pp_settings );
		}
	}
?>
<style type="text/css" media="screen">
.pp-advanced-settings{display:<?php echo $_POST['advanced_open'] == 'show' ? '' : 'none'?>}
th, td{white-space:nowrap}
label{padding-left:4px}
input:disabled{opacity:.5}
</style>
<div class="wrap">
<div style="float:right">Version <?php echo PP_APP_VERSION; ?></div>
<div id="icon-options-general" class="icon32"></div>
<h2><?php echo PP_APP_NAME .' ' . pp__( 'Settings' ); ?></h2>
<div id="pp_notify" style="margin-top:6px;font-weight:bold;display:none"></div>
<form method="post" id="pp-settings" name="pp-settings" action="" enctype="multipart/form-data">
<input type="hidden" id="pp_action" name="pp_action" value="update" />
<?php wp_nonce_field( 'pp-settings-action', 'pp-nonce', true ); ?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php pp_e( 'Embed Size' ); ?></th>
		<td colspan="2"> 
			<?php pp_e( 'Width' ); ?>: <input type="text" name="<?php echo PP_SETTINGS_WIDTH; ?>" value="<?php echo $pp_settings[PP_SETTINGS_WIDTH] ? $pp_settings[PP_SETTINGS_WIDTH] : PP_DEFAULT_WIDTH; ?>" size="6" />
			<?php pp_e( 'Height' ); ?>: <input type="text" name="<?php echo PP_SETTINGS_HEIGHT; ?>" value="<?php echo $pp_settings[PP_SETTINGS_HEIGHT] ? $pp_settings[PP_SETTINGS_HEIGHT] : PP_DEFAULT_HEIGHT; ?>" size="6" />
			&nbsp;<span class="description"><?php pp_e( 'you may use px, %, em, or other standard' ); ?><a href="http://www.w3schools.com/cssref/css_units.asp" target="_blank"> <?php pp_e( 'CSS units' ); ?></a>. Examples: 800px, 100%, 2.5em, etc.</span>
			<!--
			<?php if ($pp_settings[PP_SETTINGS_WIDTH]  === null): ?>
			<span class="error"><?php pp_e( 'The widht value is incorrect' ); ?></span>
			<?php endif;?>
			<?php if ($pp_settings[PP_SETTINGS_HEIGHT] === null): ?>
			<span class="error"><?php pp_e( 'The height value is incorrect' ); ?></span>
			<?php endif;?>
			-->
		</td>
	</tr>
	<tr valign="top" style="background-color:#eee">
		<th scope="row"><?php pp_e( 'Style' ); ?></th>
		<td colspan="2">
			<input id="play-button" name="<?php echo PP_SETTINGS_PLAY_BUTTON; ?>" value="1" type="checkbox"<?php if ( $pp_settings[PP_SETTINGS_PLAY_BUTTON] ) : ?> checked<?php endif; ?> /><label for="play-button"><?php pp_e( 'Show Play button' ); ?></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php pp_e( 'Panobox' ); ?>
		</th>
		<td colspan="2">
			
			<input id="panobox-active" name="<?php echo PP_SETTINGS_PANOBOX_ACTIVE; ?>" value="1" type="checkbox"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX_ACTIVE] ) : ?> checked<?php endif; ?> /><label for="panobox-active"><?php pp_e( 'Open panoramas in Panobox' ); ?></label>
			<input type="hidden" id="panobox-open" name="panobox_open" value="<?php echo ! isset($_POST['panobox_open']) || $_POST['panobox_open'] == 'hide' ? 'hide' : 'show' ?>"  />
			<br />
			<input id="panobox-galleries" name="<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_GALLERIES; ?>" value="1" type="checkbox"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_GALLERIES] ) : ?> checked<?php endif; ?> /><label for="panobox-galleries"><?php pp_e( 'Open image galleries in Panobox' ); ?></label>

			<br />
			<a id="panobox-options-label" href="javascript:toggle_panobox_options()"><?php echo ! isset($_POST['panobox_open']) || $_POST['panobox_open'] == 'hide' ? 'Customize Panobox...' : 'Customize Panobox'; ?></a>
			<br/>
			<table id="panobox-options" style="<?php if( ! isset($_POST['panobox_open']) || $_POST['panobox_open'] == 'hide') : ?>display:none<?php endif; ?>" >
				<tr>
					<td nowrap valign="top"><?php pp_e( 'Window Size' ); ?>:</td>
					<td>		
						<?php pp_e( 'Width' ); ?>: <input onchange="document.forms[0].<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_WIDTH; ?>.value = this.value" id="panobox-width"  type="text" value="<?php echo $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_WIDTH] ? $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_WIDTH] : PP_DEFAULT_WIDTH; ?>" size="6" <?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FULLSCREEN] ): ?>disabled="disabled" <?php endif; ?> />
						<?php pp_e( 'Height' ); ?>: <input onchange="document.forms[0].<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_HEIGHT; ?>.value = this.value"id="panobox-height" type="text" value="<?php echo $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_HEIGHT] ? $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_HEIGHT] : PP_DEFAULT_HEIGHT; ?>" size="6" <?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FULLSCREEN] ): ?>disabled="disabled" <?php endif; ?> />						
						<input type="hidden" name="<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_WIDTH; ?>" value="<?php echo $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_WIDTH] ? $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_WIDTH] : PP_DEFAULT_WIDTH; ?>" />
						<input type="hidden" name="<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_HEIGHT; ?>" value="<?php echo $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_HEIGHT] ? $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_HEIGHT] : PP_DEFAULT_HEIGHT; ?>" />						
						&nbsp;<span class="description"><?php pp_e( 'in CSS units' );?></span>
						<br />
						<input id="panobox-fullscreen" onchange="toggle_panobox_fulscreen(this.checked)" name="<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_FULLSCREEN; ?>" value="1" type="checkbox"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FULLSCREEN] ) : ?> checked<?php endif; ?> /><label for="panobox-fullscreen"><?php pp_e( 'Use Fullscreen' ); ?></label> 
					</td>
				</tr>
				<tr>
					<td><?php pp_e( 'Style' ); ?>:</td>
					<td>
						<select name="<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_STYLE; ?>">
						<option value="<?php echo PB_SETTINGS_STYLE_BOX; ?>:pb-light,<?php echo PB_SETTINGS_STYLE_OVERLAY; ?>:pb-light-overlay"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_STYLE][PB_SETTINGS_STYLE_BOX] == 'pb-light' ) : ?> selected<?php endif; ?> />&nbsp;Light&nbsp;</option>
						<option value="<?php echo PB_SETTINGS_STYLE_BOX; ?>:pb-dark,<?php echo PB_SETTINGS_STYLE_OVERLAY; ?>:pb-dark-overlay"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_STYLE][PB_SETTINGS_STYLE_BOX] == 'pb-dark' ) : ?> selected<?php endif; ?> />&nbsp;Dark&nbsp;</option>
						<option value="<?php echo PB_SETTINGS_STYLE_BOX; ?>:pb-adaptive,<?php echo PB_SETTINGS_STYLE_OVERLAY; ?>:pb-adaptive-overlay"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_STYLE][PB_SETTINGS_STYLE_BOX] == 'pb-adaptive' ) : ?> selected<?php endif; ?> />&nbsp;Adaptive&nbsp;</option>
						</select>
					</td>
				</tr>
				<tr>	
					<td><?php pp_e( 'Effects' ); ?>:</td>	
					<td>					
						<input id="panobox-shadow" name="<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_SHADOW; ?>" value="1" type="checkbox"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_SHADOW] ) : ?> checked<?php endif; ?> /><label for="panobox-shadow"><?php pp_e( 'Drop-shadow' ); ?></label>
						&nbsp;&nbsp;
						<input id="panobox-fade" name="<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_FADE; ?>" value="1" type="checkbox"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FADE] ) : ?> checked<?php endif; ?> /><label for="panobox-fade"><?php pp_e( 'Fade-in/out' ); ?></label>
						&nbsp;&nbsp;
						<input id="panobox-animate" name="<?php echo PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_ANIMATE; ?>" value="1" type="checkbox"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_ANIMATE] ) : ?> checked<?php endif; ?> /><label for="panobox-animate"><?php pp_e( 'Animated window resize' ); ?></label>
					</td>
				</tr>
				<tr>
					<td><?php pp_e( 'Mobile' ); ?>:</td>
					<td>
						<input id="panobox-mobile" name="<?php echo PP_SETTINGS_PANOBOX_MOBILE; ?>" value="1" type="checkbox"<?php if ( !$pp_settings[PP_SETTINGS_PANOBOX_MOBILE] ) : ?> checked<?php endif; ?> /><label for="panobox-mobile"><?php pp_e( 'Don\'t use Panobox for mobile devices' ); ?></label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings" style="background-color:#eee">
		<th scope="row"><?php pp_e( 'Upload Folder' ); ?></th>
		<td colspan="2">
			<input id="upload-sys" onchange="toggle_wp_ul(this.checked, '<?php echo $pp_wp_upload_dir; ?>' )" type="checkbox" name="<?php echo PP_SETTINGS_UPLOAD_WP; ?>" value="true"<?php if ( $pp_settings[PP_SETTINGS_UPLOAD_WP] ) : ?> checked<?php endif; ?> /><label for="upload-sys"><?php pp_e( 'Use WordPress upload folder' ); ?></label> (<?php echo $pp_wp_upload_dir; ?>)
			<br />
			Folder Path:&nbsp;<input  style="width:320px"  id="upload-dir" <?php if ( $pp_settings[PP_SETTINGS_UPLOAD_WP] ): ?>disabled="disabled" <?php endif; ?>type="text" name="<?php echo PP_SETTINGS_UPLOAD_DIR; ?>" value="<?php echo $pp_settings[PP_SETTINGS_UPLOAD_WP] ? $pp_wp_upload_dir : $pp_settings[PP_SETTINGS_UPLOAD_DIR]; ?>" size="36" /><?php if ( ! is_dir( pp_wp_root( $pp_settings[PP_SETTINGS_UPLOAD_DIR] ) ) ) : ?><span class="error"><?php pp_e( 'Folder does not exist' ); ?></span><?php endif;?>
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings">
		<th scope="row"><?php pp_e( 'Global Viewer' ); ?></th>
		<td colspan="2">
			<input id="use-viewer-dir" onchange="toggle_viewer_folder(this.checked)" type="checkbox" name="<?php echo PP_SETTINGS_USE_VIEWER_DIR; ?>" value="true"<?php if ( $pp_settings[PP_SETTINGS_USE_VIEWER_DIR] ) : ?> checked<?php endif; ?> /><label for="use-viewer-dir"><?php pp_e( 'Use Global Viewer' ); ?></label>&nbsp;<span class="description">(<?php pp_e( 'KRPano & FPP only' ); ?> <a target="_blank" href="http://www.panopress.org/krpano-global-swf/"><?php pp_e( 'learn more' ); ?></a>)</span>
			<br />
			Folder Path:&nbsp;<input style="width:320px" id="viewer-dir" <?php if ( ! $pp_settings[PP_SETTINGS_USE_VIEWER_DIR] ): ?>disabled="disabled" <?php endif; ?>type="text" value="<?php echo $pp_settings[PP_SETTINGS_VIEWER_DIR]; ?>" /><?php if ( ! is_dir( pp_wp_root( $pp_settings[PP_SETTINGS_VIEWER_DIR] ) ) && $pp_settings[PP_SETTINGS_USE_VIEWER_DIR] ) : ?><span class="error"><?php pp_e( 'Folder does not exist' ); ?></span><?php endif;?>
			<input type="hidden" id="viewer-dir-hidden" name="<?php echo PP_SETTINGS_VIEWER_DIR; ?>" value="<?php echo $pp_settings[PP_SETTINGS_VIEWER_DIR]; ?>" />
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings" style="background-color:#eee">
		<th scope="row"><?php pp_e( 'Performance' ); ?></th>
		<td colspan="2">
		<?php pp_e( 'Only one active panorama per page for' ); ?>:
		<select name="<?php echo PP_SETTINGS_OPPP; ?>">
			<option value="<?php echo PP_OPPP_DISABLED; ?>"<?php if ( $pp_settings[PP_SETTINGS_OPPP] == PP_OPPP_DISABLED ) : ?> selected<?php endif; ?> /><?php pp_e( 'None' ); ?></option>
			<option value="<?php echo PP_OPPP_MOBILE; ?>"<?php if ( $pp_settings[PP_SETTINGS_OPPP] == PP_OPPP_MOBILE ) : ?> selected<?php endif; ?> /><?php pp_e( 'Mobile devices' ); ?>&nbsp;</option>
			<option value="<?php echo PP_OPPP_ALL; ?>"<?php if ( $pp_settings[PP_SETTINGS_OPPP] == PP_OPPP_ALL ) : ?> selected<?php endif; ?> /><?php pp_e( 'All devices' ); ?></option>
		</select>
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings">
		<th scope="row"><?php pp_e( 'Flash window mode' ); ?><br/>('wmode')</th>
		<td colspan="2">
		<?php pp_e( 'Embedded panoramas' ); ?>:&nbsp;
		<select name="<?php echo PP_SETTINGS_WMODE; ?>">
			<option value="auto"<?php if ( $pp_settings[PP_SETTINGS_WMODE] == 'auto' ) : ?> selected<?php endif; ?> />Auto</option>
			<option value="window"<?php if ( $pp_settings[PP_SETTINGS_WMODE] == 'window' ) : ?> selected<?php endif; ?> />Window</option>
			<option value="opaque"<?php if ( $pp_settings[PP_SETTINGS_WMODE] == 'opaque' ) : ?> selected<?php endif; ?> />Opaque</option>
			<option value="transparent"<?php if ( $pp_settings[PP_SETTINGS_WMODE] == 'transparent' ) : ?> selected<?php endif; ?> />Transparent&nbsp;</option>
		</select>
		&nbsp;&nbsp;
		<?php pp_e( 'Panobox' ); ?>:&nbsp;
		<select name="<?php echo PP_SETTINGS_PANOBOX_WMODE; ?>">
			<option value="auto"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX_WMODE] == 'auto' ) : ?> selected<?php endif; ?> />Auto</option>
			<option value="window"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX_WMODE] == 'window' ) : ?> selected<?php endif; ?> />Window</option>
			<option value="opaque"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX_WMODE] == 'opaque' ) : ?> selected<?php endif; ?> />Opaque</option>
			<option value="transparent"<?php if ( $pp_settings[PP_SETTINGS_PANOBOX_WMODE] == 'transparent' ) : ?> selected<?php endif; ?> />Transparent&nbsp;</option>
		</select>
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings" style="background-color:#eee">
		<th scope="row" style="padding-top:20px">
			<?php pp_e( 'CSS' ); ?>
		</th>
		<td>
			<textarea name="<?php echo PP_SETTINGS_CSS; ?>" style="margin-top:10px;width:400px; height:80px"><?php echo $pp_settings[PP_SETTINGS_CSS]; ?></textarea>
			<a href="http://www.panopress.org/css/" target="_blank"><?php pp_e( 'Class reference' ); ?></a>
		</td>
	</tr>
	<tr>
		<td><input type="button" onclick="toggle_advanced()" id="toggle-advanced" class="button-secondary" value="<?php echo $_POST['advanced_open'] == 'show' ? 'Hide' : 'Show'?> advanced options" /></td>
		<td colspan="2">
			<input type="submit" onclick="return submit_form()" class="button-primary" value="<?php pp_e( 'Save Changes' ); ?>" />
			&nbsp;&nbsp;
			<input type="button" onclick="reset_form()" class="button-secondary" value="<?php pp_e( 'Reset to defaults' ); ?>" />
		</td>
	</tr>
	<tr>
		<td colspan="3"><a href="http://www.panopress.org/instructions/" target="_blank"><?php echo PP_APP_NAME; ?> <?php pp_e( 'Instructions' ); ?></a></td>
	</tr>
<input type="hidden" id="advanced-open" name="advanced_open" value="<?php echo ! isset($_POST['advanced_open']) || $_POST['advanced_open'] == 'hide' ? 'hide' : 'show' ?>"  />
</form>
</div>
<script type="text/javascript">
//<![CDATA[
$pp2 = jQuery.noConflict();
$pp2(function(){
	if(typeof pp_loaded == 'undefined'){
		$pp2.ajax({
		url: '<?php echo plugins_url( '/js/admin.js', dirname( __FILE__ ) ); ?>',
			error: function(XMLHttpRequest, textStatus, errorThrown){
				var msg = '', n = $pp2('#pp_notify');
				switch (XMLHttpRequest.status){
					case 403: msg = '<?php pp_e( 'Error: 403, The access to some of ' . PP_APP_NAME . ' files was forbidden by the server.<br/>you may need to change the ' . PP_APP_NAME . ' folder permissions.' ); ?>'; break;
					case 404: msg = '<?php pp_e( 'Error: 404, Some of ' . PP_APP_NAME . ' files was not found.' ); ?>'; break;
					default:  msg = 'Error: ' + XMLHttpRequest.status + ', ' + XMLHttpRequest.statusText + '.';
				}
				n.html(msg);
				n.addClass('error');
				n.slideDown();
			}
		});
	}
});
//]]>
</script>
<!-- <?php echo '/' . PP_APP_NAME . ' settings'; ?> -->
<?php
}
?>
