<?php
// This script is dual licensed under the MIT License and the CC0 License.
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

/* Connect to the database. */
include 'db.conf.php';

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

function getFlea( $fleaname ) {
	global $link, $strErrorDesc;
	$flea = NULL;
	$result = mysqli_query( $link, "SELECT * FROM `fleanames` WHERE `scientificName` LIKE '".$fleaname."' LIMIT 1;" );
	if ( $result ) {
		$row = mysqli_fetch_array( $result );
		if ( $row ) {
			$flea['name'] = $row['scientificName'];
			$flea['authority'] = $row['authorship'];
			$flea['status'] = $row['status'];
			$flea['validName'] = $row['validName'];
		}
	} else {
		$strErrorDesc = "Database query failed.";
	}
	return $flea;
}

parse_str( $_SERVER['QUERY_STRING'], $query );

if ( isset( $query['name'] ) ) {
	$responseData = json_encode( getFlea( $query['name'] ) );
} else {
	$strErrorDesc = "No name specified.";
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
