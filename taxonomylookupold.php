<?php
// This script is dual licensed under the MIT License and the CC0 License.
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
ini_set( 'max_execution_time', 900 );

$useragent = 'Taxonomy Lookup Script CMNH/1.0';
$inatapi = 'https://api.inaturalist.org/v2/taxa';
$errors = [];
$specieslist = [];
$verifiedspecieslist = [];
$species = null;
$speciesid = null;
$taxonomydata = [];
$ancestorids = [];
$animalsonly = true;
$ranks = ['order', 'family', 'subfamily', 'tribe', 'subtribe'];

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

function clean_taxon_name( $taxonname ) {
	$taxonname = str_replace( " sp.", "", $taxonname );
	$taxonname = str_replace( "?", "", $taxonname );
	$taxonname = str_replace( " cf ", " ", $taxonname );
	$taxonname = str_replace( " cf. ", " ", $taxonname );
	$taxonname = trim( preg_replace("/[^a-zA-Z-\s]/", "", $taxonname ) );
	return $taxonname;
}

/**
 * Get taxon ID from the iNataralist API based on the taxon name
 *
 * @param string $taxonname The name of the taxon to search for. Note that the iNat API
 *     uses a prefix search.
 * @param string $rank The rank of the taxon, e.g. 'genus'
 * @param boolean Set to true to restrict query to only animals
 * @return integer|null The ID of the taxon
 */
function get_taxon_id( $taxonname, $rank = null, $animalsonly = false ) {
	global $inatapi, $errors;
	// Replace raw spaces with URL-encoded spaces
    $encodedtaxonname = str_replace( ' ', '%20', $taxonname );
    $url = $inatapi . '?q=' . $encodedtaxonname;
    // Set the rank if provided
    if ( $rank ) {
    	$url = $url . '&rank=' . $rank;
    }
    // Restrict to animals or not
    if ( $animalsonly ) {
    	$url = $url . '&taxon_id=1';
    }
    $inatdata = make_curl_request( $url );
    
    if ( $inatdata && $inatdata['results'] ) {
    	// If there is only 1 match, return it.
		if ( count( $inatdata['results'] ) === 1 ) {
			return $inatdata['results'][0]['id'];
		} else {
			return find_exact_match( $taxonname, $inatdata['results'] );
		}
	} else {
		$errors[] = 'No taxon beginning with the string \''.$taxonname.'\'.';
		return null;
	}
}

/**
 * Get the name for a given taxon ID
 * @param integer $taxonid ID of taxon to find name for
 * @return string|null Name of the taxon
 */
function get_taxon_name( $taxonid ) {
	global $inatapi, $errors;
	$url = $inatapi . '/' . $taxonid . '?fields=name';
	$inatdata = make_curl_request( $url );
	if ( $inatdata && $inatdata['results'][0]['name'] ) {
		return $inatdata['results'][0]['name'];
	} else {
		$errors[] = 'No name found for taxon '.$taxonid.'.';
		return null;
	}
}

/**
 * Given an array of taxon IDs, find the one that exactly matches a certain taxon name
 * @param string $taxonname Name of the taxon to find
 * @param array $taxonids Array of taxons to search
 * @return integer|null Taxon ID of the exact match
 */
function find_exact_match( $taxonname, $taxonids ) {
	global $inatapi, $errors;
	foreach ( $taxonids as $taxonid ) {
		$testtaxonname = get_taxon_name( $taxonid['id'] );
		if ( strtolower( $testtaxonname ) === strtolower( $taxonname ) ) {
			return $taxonid['id'];
			break;
		}
	}
	$errors[] = 'Name matching failed for \''.$taxonname.'\'.';
	return null;
}

/**
 * Get the ancestors for a given taxon ID
 * @param integer $taxonid ID of taxon to find ancestors for
 * @return array|null Array of taxon IDs
 */
function get_ancestors( $taxonid ) {
	global $inatapi, $errors;
	$url = $inatapi . '/' . $taxonid . '?fields=ancestor_ids';
    $inatdata = make_curl_request( $url );

	if ( $inatdata && $inatdata['results'] ) {
		if ( $inatdata['results'][0]['ancestor_ids'] ) {
			return $inatdata['results'][0]['ancestor_ids'];
		} else {
			$errors[] = 'No ancestors found for taxon '.$taxonid.'.';
			return null;
		}
	} else {
		$errors[] = 'No ancestor results for taxon '.$taxonid.'.';
		return null;
	}
}

/**
 * Get the name and rank for a given taxon ID
 * @param integer $taxonid ID of taxon to get info for
 * @return array|null Associative array of 'name' and 'rank'
 */
function get_name_and_rank( $taxonid ) {
	global $inatapi, $errors;
	$url = $inatapi . '/' . $taxonid . '?fields=rank,name';
	$inatdata = make_curl_request( $url );
	if ( $inatdata && $inatdata['results'] ) {
		return $inatdata['results'][0];
	} else {
		$errors[] = 'Could not retrieve name and rank for taxon ' . $taxonid . '.';
		return null;
	}
	
}

// See if form was submitted.
if ( $_POST ) {
	// If 'Search animals only' checkbox was checked, leave it checked, otherwise uncheck.
	if ( !isset( $_POST['animal'] ) ) {
		$animalsonly = false;
	}
	// If a name was posted, look up the taxonomy.
	if ( $_POST['species'] ?? null ) {
		$specieslist = explode( "\n", $_POST['species'] );
		// Limit to 50 species.
		$specieslist = array_slice( $specieslist, 0, 50 );
		foreach ( $specieslist as $species ) {
			$species = clean_taxon_name( $species );
			// Make sure the species name is valid (at least 2 characters for the genus, a
			// space, and at least 2 characters for the specific epithet.
			if ( preg_match( '/\w{2,} \w{2,}/', $species ) ) {
				$speciesid = get_taxon_id( $species, 'species', $animalsonly );
				if ( $speciesid ) {
					$ancestorids = get_ancestors( $speciesid );
					if ( $ancestorids ) {
						$verifiedspecieslist[] = $species;
						$ancestors = [];
						foreach ( $ancestorids as $taxonid ) {
							$ancestors[] = get_name_and_rank( $taxonid );
						}
						$taxonomydata[] = $ancestors;
					}
				}
			} else {
				// See if it's a genus name instead.
				if ( preg_match( '/\w{2,}/', $species ) ) {
					$genusid = get_taxon_id( $species, 'genus', $animalsonly );
					if ( $genusid ) {
						$ancestorids = get_ancestors( $genusid );
						if ( $ancestorids ) {
							$verifiedspecieslist[] = $species;
							$ancestors = [];
							foreach ( $ancestorids as $taxonid ) {
								$ancestors[] = get_name_and_rank( $taxonid );
							}
							$taxonomydata[] = $ancestors;
						}
					}
				} else {
					$errors[] = 'Invalid species name.';
				}
			}
			if ( count( $specieslist ) > 1 ) {
				sleep(12);
			}
		}
	}
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Language" content="en-us">
	<title>Taxonomy Lookup</title>

<style type="text/css">
body {
	font-family: "Trebuchet MS", Verdana, sans-serif;
	color:#777777;
	background: #FFFFFF;
	}
#content {
	margin: 4em;
	text-align: center;
	}
#errors {
	margin: 1em;
	color: #FF6666;
	font-weight: bold;
	}
.resulttable {
    background-color: #f8f9fa;
    color: #202122;
    margin: 1em 0;
    border: 1px solid #a2a9b1;
    border-collapse: collapse;
}
.resulttable > tr > th, .resulttable > * > tr > th {
    background-color: #eaecf0;
    text-align: center;
    font-weight: bold;
}
.resulttable > tr > th, .resulttable > tr > td, .resulttable > * > tr > th, .resulttable > * > tr > td {
    border: 1px solid #a2a9b1;
    padding: 0.2em 0.4em;
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
<form id="lookupform" action="taxonomylookupold.php" method="post">
<p>
	Species List (1 per line, max 50):<br/><textarea rows="5" cols="40" name="species"></textarea>
</p>
<p>
	<input type="checkbox" id="animal" name="animal" <?php if ($animalsonly) echo "checked";?> value="yes">
	<label for="animal"> Search animals only</label>
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
if ( $verifiedspecieslist ) {
	print( '<table class="resulttable" border="0" cellpadding="5" cellspacing="10">' );
	print( '<tr><th>Species</th><th>Order</th><th>Family</th><th>Subfamily</th><th>Tribe</th><th>Subtribe</th></tr>' );
	$x = 0;
	foreach ( $verifiedspecieslist as $verifiedspecies ) {
		print( '<tr>' );
		print( '<td>'.$verifiedspecies.'</td>' );
		$ancestors = $taxonomydata[$x];
		foreach ( $ranks as $rank ) {
			$rankfilled = false;
			foreach ( $ancestors as $ancestor ) {
				if ( $ancestor['rank'] == $rank ) {
					print( '<td>'.$ancestor['name'].'</td>' );
					$rankfilled = true;
					break;
				}
			}
			if ( !$rankfilled ) {
				print( '<td></td>' );
			}
		}

		print( '</tr>' );
		$x++;
	}
	print( '</table>' );
}
?>

</table>
</div>
</body>
</html>
