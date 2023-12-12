<?php
// This script is dual licensed under the MIT License and the CC0 License.
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

/* Connect to the database. */
include 'db.conf.php';
$link = mysqli_connect($host, $user, $pass, $dbname);

$errors = [];
$accession = null;
$id = null;
$whereClause = '`processed` = 0 and exclude = 0';
$row = null;

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
	$host = null;
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
	margin: 2em auto;
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
	font-size: 15pt;
	line-height: 20pt;
	color: black;
	padding: 0 2em;
}
.notes, .status {
	font-size: 12pt;
	line-height: 18pt;
	color: #475DC1;
}
.traublog {
	padding: 1em;
	background-image: url("fleaassets/paper-texture.jpg");
	background-color: #F1E0C2;
	font-family: monospace;
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
if ( $row ) {
	print( '<div class="traublog">' );
	print( '<div style="text-align: left; color: black;">Record ' . $row['id'] . ' of 17885&nbsp;&nbsp;&nbsp;&nbsp;Accession: ' . $row['accession'] . '</div><br>' );
	print( '<table class="logtable" border="0" cellpadding="5" cellspacing="10">' );
	print( '<tr><th class="rightborder">Host</th><th class="rightborder">Date</th><th class="rightborder">Locality</th><th>Fleas</th></tr>' );
	print( '<tr><td class="rightborder">'.$row['host'].'</td><td class="rightborder">'.$row['date'].'</td><td class="rightborder">'.$row['locality'].'</td><td>'.$row['fleas'].'</td></tr>' );
	print( '</table>' );
	print( '</div>' );
}
?>
<table id="lookups" border="0" cellpadding="5" cellspacing="10">
<tr>
<td valign="top">
<form action="fleadatagame.php" method="post" autocomplete="off">
<p>
Flea Abbreviation: <input type="text" name="abbrev" id="abbrev" size="15" autocomplete="off"/> <input type="submit" value="Lookup" onclick="abbrevlookup(document.getElementById('abbrev').value);return false;"/>
</p>
</form>
<form action="fleadatagame.php" method="post" autocomplete="off">
<p>
Flea Name: <input type="text" name="name" id="name" size="25" autocomplete="off"/> <input type="submit" value="Lookup" onclick="namelookup(document.getElementById('name').value);return false;"/>
</p>
</form>
</td>
<td valign="top">
<p id="fleaname"></p>
</td>
</tr>
</table>
<?php

?>
<table id="output" border="0" cellpadding="5" cellspacing="10" width="100%">
<tr><td>Host</td><td>Date</td><td>Country</td><td>Locality</td><td>Flea taxon (without authority)</td><td>Quant.</td><td>&nbsp</td></tr>
<tr id="output1">
	<td><input type="text" name="fleadata[0]['host']" value="<?=$host?>"/></td>
	<td><input type="text" name="fleadata[0]['date']" size="12" value="<?=$row['date']?>"/></td>
	<td><input type="text" class="country" name="fleadata[0]['country']"/></td>
	<td><input type="text" name="fleadata[0]['locality']" size="50" value="<?=$row['locality']?>"/></td>
	<td><input type="text" name="fleadata[0]['flea']" size="50"/></td>
	<td><input type="text" name="fleadata[0]['quant']" size="3"/></td>
</tr>
<tr>
	<td><input type="text" name="fleadata[0]['host']" value="<?=$host?>"/></td>
	<td><input type="text" name="fleadata[0]['date']" size="12" value="<?=$row['date']?>"/></td>
	<td><input type="text" class="country" name="fleadata[0]['country']"/></td>
	<td><input type="text" name="fleadata[0]['locality']" size="50" value="<?=$row['locality']?>"/></td>
	<td><input type="text" name="fleadata[0]['flea']" size="50"/></td>
	<td><input type="text" name="fleadata[0]['quant']" size="3"/></td>
</tr>
</table>
&nbsp;<br>
<input type="submit" value="+" onclick="addrow();return false;"/>
<script src="fleadatagame.js"></script>
</div>
</body>
</html>
