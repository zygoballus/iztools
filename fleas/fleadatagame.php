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
<link rel="stylesheet" href="fleadatagame.css">
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
<p id="fillbuttons" style="margin: 12px 0; display: none;"><input type="submit" value="Fill 1" onclick="filldata(0);return false;" class="fill" id="fill1"/></p>
</td>
</tr>
</table>
<?php

?>
<div id="records">
<table class="record" id="record0">
	<tr>
		<td rowspan="3" class="recordlabel">1</td>
		<td>
			<table class="output" border="0" cellspacing="0">
				<tr>
					<td><label>Host</label><br/><input type="text" name="fleadata[0][host]" size="25" value="<?=$host?>"/></td>
					<td><label>Flea taxon (only 1)</label><br/><input type="text" name="fleadata[0][sciname]" size="35"/></td>
					<td><label>Taxon author <a href="#" onclick="dwcDoc('scientificNameAuthorship')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[0][scientificnameauthorship]" size="25"/></td>
					<td><label>Sex <a href="#" onclick="dwcDoc('sex')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[0][sex]" size="10"/></td>
					<td><label>Quant.</label><br/><input type="text" name="fleadata[0][individualcount]" size="4"/></td>
				</tr>
			</table>
			<table class="output" border="0" cellspacing="0">
				<tr>
					<td><label>Date</label><br/><input type="date" name="fleadata[0][date]" size="10" value="<?=$row['date']?>"/></td>
					<td><label>Country <a href="#" onclick="dwcDoc('country')" class="info">&#9432;</a></label><br/><input type="text" class="country" name="fleadata[0][country]" size="21" value="<?=$row['country']?>"/></td>
					<td><label>State/Province <a href="#" onclick="dwcDoc('stateProvince')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[0][stateprovince]" size="24" value="<?=$row['stateprovince']?>"/></td>
					<td><label>Elevation <a href="#" onclick="dwcDoc('minimumElevationInMeters')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[0][elevation]" size="8"/></td>
					<td><label>Associated Collectors</label><br/><input type="text" name="fleadata[0][collectors]" size="30"/></td>
				</tr>
			</table>
			<table class="output" border="0" cellspacing="0">
				<tr>
					<td><label>Locality <a href="#" onclick="dwcDoc('locality')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[0][locality]" size="114" value="<?=$row['locality']?>"/></td>
				</tr>
			</table>
		</td>
		<td rowspan="3" class="recordclose">&nbsp;</td>
	</tr>
</table>
</div>
<input type="submit" class="bottom" value="Add Record" onclick="addRecord();return false;"/> <input type="submit" class="bottom" value="Save Records"/><br>
<script src="fleadatagame.js"></script>
</div>
</body>
</html>
