<?php
// This script is dual licensed under the MIT License and the CC0 License.
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

/* Connect to the database. */
include 'db.conf.php';

$errors = [];
$accession = null;
$playerid = null;
$playerposition = null;
$previd = 0;
$nextid = 0;
$row = null;
$host = '';

parse_str( $_SERVER['QUERY_STRING'], $query );

if ( isset( $query['player'] ) && is_numeric( $query['player'] ) ) {
	$playerresult = mysqli_query( $link, "SELECT * FROM `players` WHERE `id` = " . $query['player'] . " LIMIT 1;" );
	if ( $playerresult ) {
		$row = mysqli_fetch_array($playerresult);
		setcookie( 'playerid', $row['id'], 0, '/' );
		setcookie( 'playername', $row['name'], 0, '/' );
		setcookie( 'playerscore', $row['score1'], 0, '/' );
		$playerid = $row['id'];
		$playername = $row['name'];
		$playerscore = $row['score1'];
		$playerposition = $row['position'];
	} else {
		header( "Location: index.php" );
	}
} else {
	if ( isset( $_COOKIE["playerid"] ) && isset( $_COOKIE["playername"] ) && isset( $_COOKIE["playerscore"] ) ) {
		$playerid = $_COOKIE["playerid"];
		$playername = $_COOKIE["playername"];
		$playerscore = $_COOKIE["playerscore"];
	} else {
		header( "Location: index.php" );
	}
}

$whereClause = '`processed` = 0 AND `exclude` = 0 AND `bad locality` = 0';
if ( $playerposition ) {
	$whereClause = "`id` = " . $playerposition;
}
if ( isset( $query['id'] ) && is_numeric( $query['id'] ) && $query['id'] > 0 ) {
	$whereClause = "`id` = " . $query['id'];
}
if ( isset( $query['accession'] ) && !( preg_match( '/[^\w\-\d]/', $query['accession'] ) ) ) {
	$accession = $query['accession'];
	$whereClause = "`accession` LIKE '" . $accession . "'";
}

$result = mysqli_query( $link, "SELECT * FROM `traubdata` WHERE " . $whereClause . " LIMIT 1;" );
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
	$prevresult = mysqli_query( $link, "SELECT MAX(`id`) AS id FROM `traubdata` WHERE `id` < {$row['id']} AND `processed`=0 AND `exclude`=0 AND `bad locality`=0;" );
	if ( $prevresult ) {
		$prevrow = mysqli_fetch_array( $prevresult );
		$previd = $prevrow['id'];
	}
	$nextresult = mysqli_query( $link, "SELECT MIN(`id`) AS id FROM `traubdata` WHERE `id` > {$row['id']} AND `processed`=0 AND `exclude`=0 AND `bad locality`=0;" );
	if ( $nextresult ) {
		$nextrow = mysqli_fetch_array( $nextresult );
		$nextid = $nextrow['id'];
	}
} else {
	$errors[] = "No records found.";
}

if ( isset( $query['action'] ) && $query['action'] == 'prev' && $previd ) {
	header( "Location: fleadatagame.php?id=" . $previd );
}
if ( isset( $query['action'] ) && $query['action'] == 'skip' && $nextid ) {
	header( "Location: fleadatagame.php?id=" . $nextid );
}

if ( $row['id'] ) {
	// See if form was submitted.
	if ( $_POST ) {
		$updatesuccess = true;
		if ( $_POST['submitbutton'] == 'Insufficient Data' ) {
			$query = "UPDATE `traubdata` SET `processed`=1,`exclude`=1, `player`={$playerid} WHERE `id`={$row['id']} LIMIT 1;";
			$result2 = mysqli_query( $link, $query );
			if ( !$result2 ) {
				$updatesuccess = false;
				$errors[] = "Updating original record failed.";
				$errors[] = mysqli_error( $link );
			}
		} else {
			$insertsuccess = true;
			// Process POST data.
			foreach ( $_POST['fleadata'] as $flea ) {
				$flea['host'] = mysqli_real_escape_string( $link, $flea['host'] );
				if ( $flea['date'] == '' ) {
					$date = 'NULL';
				} else {
					$date = "'" . $flea['date'] . "'";
				}
				$flea['sciname'] = mysqli_real_escape_string( $link, $flea['sciname'] );
				$flea['scientificnameauthorship'] = mysqli_real_escape_string( $link, $flea['scientificnameauthorship'] );
				$flea['country'] = mysqli_real_escape_string( $link, $flea['country'] );
				$flea['stateprovince'] = mysqli_real_escape_string( $link, $flea['stateprovince'] );
				$flea['locality'] = mysqli_real_escape_string( $link, $flea['locality'] );
				$query = "INSERT INTO `traubdataprocessed` (`originalid`, `accession`, `host`, `date`, `locality`, `country`, `stateprovince`, `elevation`, `associatedcollectors`, `sciname`, `scientificnameauthorship`, `sex`, `individualcount`, `player`) VALUES ('{$row['id']}', '{$row['accession']}', '{$flea['host']}', {$date}, '{$flea['locality']}', '{$flea['country']}', '{$flea['stateprovince']}', '{$flea['elevation']}', '{$flea['associatedcollectors']}', '{$flea['sciname']}', '{$flea['scientificnameauthorship']}', '{$flea['sex']}', '{$flea['individualcount']}', {$playerid});";
				$result2 = mysqli_query( $link, $query );
				if ( !$result2 ) {
					$insertsuccess = false;
					$updatesuccess = false;
					$errors[] = "Inserting processed record failed.";
					$errors[] = mysqli_error( $link );
				}
			}
			if ( $insertsuccess ) {
				$query = "UPDATE `traubdata` SET `processed`=1, `player`={$playerid} WHERE `id`={$row['id']} LIMIT 1;";
				$result3 = mysqli_query( $link, $query );
				if ( !$result3 ) {
					$updatesuccess = false;
					$errors[] = "Updating original record failed.";
					$errors[] = mysqli_error( $link );
				}
			}
		}
		if ( $updatesuccess ) {
			$query = "UPDATE `players` SET `score1`=" . ++$playerscore . " WHERE `id`=" . $playerid . " LIMIT 1;";
			$result4 = mysqli_query( $link, $query );
			setcookie( 'playerscore', $playerscore, 0, '/' );
			if ( $result4 ) {
				if ( $nextid ) {
					header( "Location: fleadatagame.php?id=" . $nextid );
				} elseif ( $previd ) {
					header( "Location: fleadatagame.php?id=" . $previd );
				} else {
					header( "Location: done.php" );
				}
			} else {
				$errors[] = "Updating player record failed.";
				$errors[] = mysqli_error( $link );
			}
		}
	}
	if ( $playerid ) {
		$query = "UPDATE `players` SET `position`=" . $row['id'] . " WHERE `id`=" . $playerid . " LIMIT 1;";
		mysqli_query( $link, $query );
	}
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
<table id="playerinfo" border="0" cellspacing="5" width="100%">
	<tr>
		<td style="text-align:left; padding: 5px 8px;">Player: <?=$playername?>; Score: <?=$playerscore?></td>
		<td style="text-align:right; padding: 5px 8px"><a href="index.php">Change Player</a></td>
	</tr>
</table>
<table border="0" cellpadding="5" cellspacing="10" width="100%">
<tr>
<td style="text-align:left;" width="80">
<?php
if ( $previd ) print( '<a href="fleadatagame.php?id=' . $row['id'] . '&action=prev"><< Prev</a>' );
?>
</td>
<td><div class="title">Flea Data Game</div></td>
<td style="text-align:right;" width="80">
<?php
if ( $nextid ) print( '<a href="fleadatagame.php?id=' . $row['id'] . '&action=skip">Skip >></a>' );
?>
</td>
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
<div class="lookup">
Flea Abbreviation: <input type="text" name="abbrev" id="abbrev" size="15" autocomplete="off"/> <input type="submit" value="Lookup" onclick="abbrevlookup(document.getElementById('abbrev').value);return false;"/>
</div>
<div class="lookup">
Flea Name: <input type="text" name="name" id="name" size="25" autocomplete="off"/> <input type="submit" value="Lookup" onclick="namelookup(document.getElementById('name').value);return false;"/>
</div>
</td>
<td valign="top">
<p id="fleaname" style="margin: 12px 0;"></p>
</td>
<td valign="top">
<p id="fillbuttons" style="margin: 12px 0; display: none;"><input type="submit" value="Fill 1" onclick="filldata(0);return false;" class="fill" id="fill1"/></p>
</td>
</tr>
</table>
<form action="fleadatagame.php?id=<?=$row['id']?>" method="post" autocomplete="off">
<div id="records">
<table class="record" id="record0">
	<tr>
		<td rowspan="3" class="recordlabel">1</td>
		<td>
			<table class="output" border="0" cellspacing="0">
				<tr>
					<td><label>Host</label><br/><input type="text" name="fleadata[0][host]" size="25" value="<?=$host?>"/></td>
					<td><label>Flea taxon (only 1)</label><br/><input type="text" name="fleadata[0][sciname]" size="34"/></td>
					<td><label>Taxon author <a href="#" tabindex="-1" onclick="dwcDoc('scientificNameAuthorship')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[0][scientificnameauthorship]" size="25"/></td>
					<td><label>Sex</label><br/><select name="fleadata[0][sex]"><option value="">&nbsp;</option><option value="male">male</option><option value="female">female</option><option value="male | female">male | female</option></select></td>
					<td><label>Quant.</label><br/><input type="number" name="fleadata[0][individualcount]" style="width: 44px;"/></td>
				</tr>
			</table>
			<table class="output" border="0" cellspacing="0">
				<tr>
					<td><label>Date</label><br/><input type="text" name="fleadata[0][date]" size="11" value="<?=$row['date']?>"/></td>
					<td><label>Country <a href="#" tabindex="-1" onclick="dwcDoc('country')" class="info">&#9432;</a></label><br/><input type="text" class="country" name="fleadata[0][country]" size="25" value="<?=$row['country']?>"/></td>
					<td><label>State/Province <a href="#" tabindex="-1" onclick="dwcDoc('stateProvince')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[0][stateprovince]" size="25" value="<?=$row['stateprovince']?>"/></td>
					<td><label>Elevation <a href="#" tabindex="-1" onclick="dwcDoc('minimumElevationInMeters')" class="info">&#9432;</a></label><br/><input type="number" name="fleadata[0][elevation]" style="width: 72px;"/></td>
					<td><label>Associated Collectors</label><br/><input type="text" name="fleadata[0][associatedcollectors]" size="32"/></td>
				</tr>
			</table>
			<table class="output" border="0" cellspacing="0">
				<tr>
					<td><label>Locality <a href="#" tabindex="-1" onclick="dwcDoc('locality')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[0][locality]" size="116" value="<?=$row['locality']?>"/></td>
				</tr>
			</table>
		</td>
		<td rowspan="3" class="recordclose">&nbsp;</td>
	</tr>
</table>
</div>
<?php
if ( !$row['processed'] ) {
	print( '<input type="submit" class="bottom" value="Add Record" onclick="addRecord();return false;"/> <input name="submitbutton" type="submit" class="bottom" value="Insufficient Data"/> <input name="submitbutton" type="submit" class="bottom" value="Save Records" disabled/><br>' );
} else {
	print( 'Already processed.<br>');
}
?>
</form>
<script src="fleadatagame.js"></script>
</div>
</body>
</html>
