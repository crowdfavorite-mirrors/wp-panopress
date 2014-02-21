<?php
$input = filter_input_array ( INPUT_GET );
if( parse_url ( $input['xml'],  PHP_URL_HOST ) ){
	exit;
}
$js = '/' . substr( $input['xml'], 0, strlen( $input['xml'] ) - 3 ) . 'js';

if ( !file_exists ( $_SERVER['DOCUMENT_ROOT'] . $js ) ) {
	$js = '../../panorama/global/krpano.js';
}
?>
<html>
<head>
<script type="text/javascript" src="<?php echo $js; ?>"></script>	
</head>
<body>
<div id="krp" style="width:100%; height:100%; display:block"></div>
<script type="text/javascript">
embedpano({'target':'krp','xml':'/<?php echo $input['xml'];?>'});
document.getElementById('krp').className = 'pp-embed-content';
</script>
</body>
</html>
