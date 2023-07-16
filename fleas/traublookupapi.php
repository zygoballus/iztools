<?php
// This script is dual licensed under the MIT License and the CC0 License.
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

/* Connect to the database. */
include 'db.conf.php';
$link = mysqli_connect($host, $user, $pass, $dbname);

$strErrorDesc = '';

function sendOutput( $data, $httpHeaders=array() ) {
	header_remove('Set-Cookie');
	if ( is_array($httpHeaders) && count($httpHeaders) ) {
		foreach ( $httpHeaders as $httpHeader ) {
			header( $httpHeader );
		}
	}
	echo $data;
	exit;
}

function getFlea( $abbrev ) {
	global $link, $strErrorDesc;
	$flea = NULL;
	$result = mysqli_query( $link, "SELECT * FROM `traubabbrv` WHERE `abbrev` LIKE '".$abbrev."' LIMIT 1;" );
	if ( $result ) {
		$row = mysqli_fetch_array( $result );
		if ( $row ) {
			$flea['abbrev'] = $row['abbrev'];
			$flea['name'] = $row['name'];
			$flea['authority'] = $row['authority'];
			$flea['notes'] = $row['notes'];
			$flea['status'] = $row['status'];
		}
	} else {
		$strErrorDesc = "Database query failed.";
	}
	return $flea;
}

parse_str( $_SERVER['QUERY_STRING'], $query );

if ( isset( $query['abbrev'] ) ) {
	// Collapse all whitespace into single spaces
	$query['abbrev'] = preg_replace( '/\s+/', ' ', $query['abbrev'] );
	// Replace period-space with period
	$query['abbrev'] = str_replace( '. ', '.', $query['abbrev'] );
	// Replace any remaining spaces with periods
	$query['abbrev'] = str_replace( ' ', '.', $query['abbrev'] );
	// Make sure abbreviation ends in a period
	if ( substr( $query['abbrev'], -1 ) !== '.' ) {
		$query['abbrev'] .= '.';
	}
	$responseData = json_encode( getFlea( $query['abbrev'] ) );
} else {
	$strErrorDesc = "No abbreviation specified.";
}

// Send output
if ( !$strErrorDesc ) {
	sendOutput(
		$responseData,
		array( 'Content-Type: application/json', 'HTTP/1.1 200 OK' )
	);
} else {
	sendOutput( 
		json_encode( array( 'error' => $strErrorDesc ) ),
		array( 'Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error' )
	);
}
