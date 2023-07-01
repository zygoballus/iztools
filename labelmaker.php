<?php
// This script is dual licensed under the MIT License and the CC0 License.
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
ini_set( 'max_execution_time', 600 );

$useragent = 'Label Maker Script CMNH/1.0';
$errors = [];
$lines = [];
$lineLengths = [];
$rows = 1;
$pageWidth = 225;
$label = '';

/**
 * Make curl request using the passed URL
 *
 * @param string $url The URL to request
 * @return array|null
 */
function make_curl_request( $url = null ) {
	global $useragent;
	$curl = curl_init();
    if ( $curl && $url ) {
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_USERAGENT, $useragent );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        $out = curl_exec( $curl );
        $object = json_decode( $out );
        return json_decode( json_encode( $object ), true );
    } else {
        return null;
    }
}

// See if form was submitted.
if ( $_POST ) {
	// If a label was posted, make some labels
	if ( $_POST['label'] ?? null ) {
		$label = $_POST['label'];
		$lines = explode( "\n", $_POST['label'] );
		foreach ( $lines as $line ) {
			$line = rtrim( $line );
			$lineLengths[] = strlen( $line );
		}
		$maxLength = max( $lineLengths );
		$pageWidth = intval( $_POST['pagewidth'] ) ?? 225;
		$labelNumber = intdiv( $pageWidth, $maxLength );
		if ( $_POST['rows'] ?? null ) {
			$rows = $_POST['rows'];
		}
	}
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Language" content="en-us">
	<title>Label Maker</title>

<style type="text/css">
body {
	font-family: "Trebuchet MS", Verdana, sans-serif;
	color:#777777;
	background: #FFFFFF;
	}
#content {
	margin: 2em;
	text-align: center;
	}
#errors {
	margin: 1em;
	color: #FF6666;
	font-weight: bold;
	}
#labelinput {
	font-family: monospace;
	font-size: 10pt;
	color:#000000;
}
#labels {
	white-space: nowrap;
	font-family: monospace;
	font-size: 7pt;
	color:#000000;
	text-align: left;
}
</style>
<script src="./jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    $("#lookupform").submit(function () {
        $(".submitbtn").attr("disabled", true);
        return true;
    });
});
</script>
</head>
<body>
<div id="content">
<form id="lookupform" action="labelmaker.php" method="post">
<p>
	Label content:<br/><textarea rows="6" cols="40" name="label" id="labelinput"><?=$label?></textarea>
</p>
<p>
	Page width (in characters): <input type="text" name="pagewidth" value="<?=$pageWidth?>"/>
</p>
<p>
	Rows of labels: <input type="text" name="rows" value="<?=$rows?>"/>
</p>
<input class="submitbtn" type="submit" />
</form>

<?php
if ( $errors ) {
	print( '<p id="errors">' );
	print( 'Errors:<br/>' );
	foreach ( $errors as $error ) {
		print( $error . '<br/>' );
	}
	print( '</p>' );
}
if ( $lines ) {
	print( '<div id="labels">' );
	for ( $r = 1; $r <= $rows; $r++ ) {
		print( '<div style="margin: 2px 0;">' );
		foreach ( $lines as $line ) {
			$line = rtrim( $line );
			$numSpaces = $maxLength - strlen( $line ) + 1;
			for ( $x = 1; $x <= $labelNumber; $x++ ) {
				$line = str_replace( ' ', '&nbsp;', $line );
				print( $line );
				for ( $y = 1; $y <= $numSpaces; $y++ ) {
					print( '&nbsp;' );
				}
			}
			print( '<br/>' );
		}
		print( '</div>' );
	}
	print( '</div>' );
}
?>

</div>
</body>
</html>
