<?php
// This script is dual licensed under the MIT License and the CC0 License.
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

/* Connect to the database. */
include 'db.conf.php';

$errors = [];

if ( $_POST ) {
	// Process POST data.
	$playernames = [];
	$result = mysqli_query( $link, "SELECT * FROM `players`;" );
	while ( $row = mysqli_fetch_array( $result ) ) {
		$playernames[] = $row['name'];
	}
	if ( !in_array( $_POST['playername'], $playernames ) ) {
		$query = "INSERT INTO `players` (`name`) VALUES ('" . $_POST['playername'] . "');";
		$insertresult = mysqli_query( $link, $query );
		if ( !$insertresult ) {
			$errors[] = "Inserting new player into database failed.";
			$errors[] = mysqli_error( $link );
		}
	} else {
		$errors[] = "Name is already in use.";
	}
}
$result = mysqli_query( $link, "SELECT * FROM `players`;" );
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="en-us">
<title>Flea Data Game</title>
<link rel="icon" href="fleaassets/favicon-32.png" sizes="32x32">
<link rel="icon" href="fleaassets/favicon-180.png" sizes="180x180">
<link rel="icon" href="fleaassets/favicon-192.png" sizes="192x192">
<link rel="stylesheet" href="fleadatagame.css">
<body>
<div id="content" style="text-align: center; margin-top: 1em;">
<img src="fleaassets/flea.jpg" width="240" height="180" alt="illustration of a flea"/><br/>
<?php
if ( $errors ) {
	print( '<p id="errors">' );
	print( 'Errors:<br/>' );
	foreach ( $errors as $error ) {
		print( $error . '<br/>' );
	}
	print( '</p>' );
}
?>
<table class="players" border="0" cellpadding="5" cellspacing="10">
<tr>
	<td colspan="2"><div class="title">Flea Data Game</div></td>
</tr>
<tr>
	<td colspan="2">
		Click on your player name to begin.
	</td>
</tr>
<tr>
	<th width="50%">Name</th>
	<th width="50%">Records Processed</th>
</tr>
<?php
if ( $result ) {
	while ( $row = mysqli_fetch_array( $result ) ) {
		print( '<tr>' );
		print( '<td><a href="fleadatagame.php?player=' . $row['id'] . '">' . $row['name'] . '</a></td>' );
		print( '<td>' . $row['score1'] . '</td>' );
		print( '</tr>' );
	}
}
?>
</table>
<form id="addplayer" action="index.php" method="post">
Name: <input type="text" name="playername" size="22"/> <input type="submit" class="bottom" value="Add Player"/>
</form>
</div>
</body>
</html>
