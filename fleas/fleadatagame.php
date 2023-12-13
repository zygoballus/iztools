<?php
// This script is dual licensed under the MIT License and the CC0 License.
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

/* Connect to the database. */
include 'db.conf.php';

$errors = [];
$accession = null;
$id = null;
$whereClause = '`processed` = 0 and exclude = 0';
$row = null;
$host = '';

parse_str( $_SERVER['QUERY_STRING'], $query );
if ( isset( $query['id'] ) && is_numeric( $query['id'] ) ) {
	$id = $query['id'];
	$whereClause = "`id` = " . $id;
}
if ( isset( $query['accession'] ) && !( preg_match( '/[^\w\-\d]/', $query['accession'] ) ) ) {
	$accession = $query['accession'];
	$whereClause = "`accession` LIKE '" . $accession . "'";
	$id = null;
}

$result = mysqli_query( $link, "SELECT * FROM `traubdata` WHERE " . $whereClause . ";" );
if ( $result ) {
	$row = mysqli_fetch_array($result);
	// Set the host to the most granular taxon.
	if ( isset( $row['host genus'] ) && $row['host genus'] ) {
		if ( isset( $row['host species'] ) && $row['host species'] ) {
			$host = $row['host genus'] . ' ' . $row['host species'];
		} else {
			$host = $row['host genus'];
		}
	} else if( isset( $row['host family'] ) && $row['host family'] ) {
		$host = $row['host family'];
	} else if( isset( $row['host order'] ) && $row['host order'] ) {
		$host = $row['host order'];
	} else if( isset( $row['host class'] ) && $row['host class'] ) {
		$host = $row['host class'];
	}
} else {
	$errors[] = "No records found.";
}

// See if form was submitted.
if ( $_POST ) {
	// Process POST data.
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="en-us">
<title>Flea Data Game</title>
<link rel="icon" href="fleaassets/favicon-32.png" sizes="32x32">
<link rel="icon" href="fleaassets/favicon-180.png" sizes="180x180">
<link rel="icon" href="fleaassets/favicon-192.png" sizes="192x192">
<style type="text/css">
body {
	font-family: "Trebuchet MS", Verdana, sans-serif;
	color:#777777;
	background: #FFFFFF;
	}
#content {
	margin: 1em auto;
	padding: 0 2em;
	text-align: center;
	max-width: 1200px;
	}
#errors {
	margin: 1em;
	color: #FF6666;
	font-weight: bold;
	font-size: 15pt;
	}
#fleaname {
	font-size: 14pt;
	line-height: 18pt;
	color: black;
	padding: 0 2em;
}
.notes, .status {
	font-size: 11pt;
	line-height: 16pt;
	color: #475DC1;
}
.traublog {
	padding: 1em;
	background-image: url("fleaassets/paper-texture.jpg");
	background-color: #F1E0C2;
	font-family: monospace;
}
form.lookup {
	font-size: 11pt;
	margin: 16px 0;
}
input.fill {
	margin: 3px;
}
table {
	border-collapse: collapse;
	color: black;
}
table.logtable {
	width: 100%;
}
table.logtable th, table.logtable td {
	padding: 0.2em 1em;
}
table.logtable th.rightborder, table.logtable td.rightborder {
	border-right: 1px solid black;
}
label {
	font-size: 80%;
}
div.record {
	background-color: #E3E3E3;
	margin-top: 1em;
	margin-bottom: 1em;
}
</style>
<link rel="stylesheet" href="jquery-ui.min.css">
<script src="jquery.min.js"></script>
<script src="jquery-ui.min.js"></script>
<body>
<div id="content">
<table border="0" cellpadding="5" cellspacing="10" width="100%">
<tr>
<td style="text-align:left;"><a href=""><< Prev</a></td>
<td><h2 style="text-align:center;">Flea Data</h2></td>
<td style="text-align:right;"><a href="">Skip >></a></td>
</tr>
</table>
<?php
if ( $errors ) {
	print( '<p id="errors">' );
	print( 'Errors:<br/>' );
	foreach ( $errors as $error ) {
		print( $error . '<br/>' );
	}
	print( '</p>' );
}
if ( $row ) {
	print( '<div class="traublog">' );
	print( '<div style="text-align: left; color: black;">Record ' . $row['id'] . ' of 17885&nbsp;&nbsp;&nbsp;&nbsp;Accession: ' . $row['accession'] . '</div><br>' );
	print( '<table class="logtable" border="0" cellpadding="5" cellspacing="10">' );
	print( '<tr><th class="rightborder">Host</th><th class="rightborder" width="80">Date</th><th class="rightborder">Locality</th><th>Fleas</th></tr>' );
	print( '<tr><td class="rightborder">'.$row['host'].'</td><td class="rightborder">'.$row['date'].'</td><td class="rightborder">'.$row['locality'].'</td><td>'.$row['fleas'].'</td></tr>' );
	print( '</table>' );
	print( '</div>' );
}
?>
<table id="lookups" border="0" cellpadding="5" cellspacing="10">
<tr>
<td valign="top">
<form action="fleadatagame.php" method="post" autocomplete="off" class="lookup">
Flea Abbreviation: <input type="text" name="abbrev" id="abbrev" size="15" autocomplete="off"/> <input type="submit" value="Lookup" onclick="abbrevlookup(document.getElementById('abbrev').value);return false;"/>
</form>
<form action="fleadatagame.php" method="post" autocomplete="off" class="lookup">
Flea Name: <input type="text" name="name" id="name" size="25" autocomplete="off"/> <input type="submit" value="Lookup" onclick="namelookup(document.getElementById('name').value);return false;"/>
</form>
</td>
<td valign="top">
<p id="fleaname" style="margin: 12px 0;"></p>
</td>
<td valign="top">
<p id="fillbuttons" style="margin: 12px 0; display: none;">
<input type="submit" value="Fill 1" onclick="fillrow(1);return false;" class="fill"/>
</p>
</td>
</tr>
</table>
<?php

?>
<div id="records">
<div class="record">
<table class="output" border="0" cellpadding="5" cellspacing="0">
<tr>
	<td><label>Host:</label><br/><input type="text" name="fleadata[0]['host']" size="25" value="<?=$host?>"/></td>
	<td><label>Flea taxon (only 1):</label><br/><input type="text" name="fleadata[0]['sciname']" size="35"/></td>
	<td><label>Taxon authority:</label><br/><input type="text" name="fleadata[0]['scientificnameauthorship']" size="25"/></td>
	<td><label>Sex:</label><br/><input type="text" name="fleadata[0]['sex']" size="10"/></td>
	<td><label>Quant.:</label><br/><input type="text" name="fleadata[0]['individualcount']" size="3"/></td>
	<td><label>Date:</label><br/><input type="date" name="fleadata[0]['date']" size="10" value="<?=$row['date']?>"/></td>
</tr>
<table>
<table class="output" border="0" cellpadding="5" cellspacing="0">
<tr>
	<td><label>Country:</label><br/><input type="text" class="country" name="fleadata[0]['country']" value="<?=$row['country']?>" size="18"/></td>
	<td><label>State/Province:</label><br/><input type="text" class="country" name="fleadata[0]['stateprovince']" value="<?=$row['stateprovince']?>" size="20"/></td>
	<td><label>Locality:</label><br/><input type="text" name="fleadata[0]['locality']" size="90" value="<?=$row['locality']?>"/></td>
</tr>
</table>
</div>
</div>
&nbsp;<br>
<input type="submit" value="+" onclick="addrow();return false;"/>
<script src="fleadatagame.js"></script>
</div>
</body>
</html>
