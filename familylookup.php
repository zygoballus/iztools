<?php
// This script is dual licensed under the MIT License and the CC0 License.
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
ini_set( 'max_execution_time', 600 );

$useragent = 'Family Lookup Script/1.0';
$inatapi = 'https://api.inaturalist.org/v2/taxa';
$errors = [];
$genus = null;
$genusid = null;
$familydata = [];
$animalsonly = true;

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
			$errors[] = 'No ancestors found for that ID.';
			return null;
		}
	} else {
		$errors[] = 'No results for that ID.';
		return null;
	}
}

/**
 * Get the family data (name and ID) for a given taxon ID
 * @param integer $taxonid ID of taxon to find family for
 * @return array|null Array including ID and name of family
 */
function get_family_data( $taxonid ) {
	global $inatapi, $errors;
	$family = [];
	$ancestors = get_ancestors( $taxonid );
	if ( $ancestors ) {
		$ancestors = array_reverse( $ancestors );

		foreach ( $ancestors as $ancestorid ) {
			$url = $inatapi . '/' . $ancestorid . '?fields=rank,name';
			$inatdata = make_curl_request( $url );
			
			if ( $inatdata && $inatdata['results'] ) {
				if ( $inatdata['results'][0]['rank'] == 'family' ) {
					$family[] = $ancestorid;
					$family[] = $inatdata['results'][0]['name'];
					break;
				}
			}
		}

		if ( $family ) {
			return $family;
		} else {
			$errors[] = 'Family not found among ancestors.';
			return null;
		}
	} else {
		$errors[] = 'No ancestors for that ID.';
		return null;
	}
}

// See if form was submitted.
if ( $_POST ) {
	// If 'Search animals only' checkbox was checked, leave it checked, otherwise uncheck.
	if ( !isset( $_POST['animal'] ) ) {
		$animalsonly = false;
	}
	// If a genus name was posted, look up the family.
	if ( $_POST['genus'] ?? null ) {
		$genus = $_POST['genus'];
		$genusid = get_taxon_id( $genus, 'genus', $animalsonly );
		if ( $genusid ) {
			$familydata = get_family_data( $genusid );
		}
	}
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Language" content="en-us">
	<title>Family Lookup</title>

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
</style>
<body>
<div id="content">
<form action="familylookup.php" method="post">
<p>
	Genus: <input type="text" name="genus">
</p>
<p>
	<input type="checkbox" id="animal" name="animal" <?php if ($animalsonly) echo "checked";?> value="yes">
	<label for="animal"> Search animals only</label>
</p>
<input type="submit"/>
</form>

<?php
if ( $familydata ) {
	$familyid = $familydata[0];
	$family = $familydata[1];
	print( 'Genus: ' . $genus . ' (<a href="https://www.inaturalist.org/taxa/' . $genusid . '">iNaturalist taxon page</a>)<br/>');
	print( 'Family: ' . $family . ' (<a href="https://www.inaturalist.org/taxa/' . $familyid . '">iNaturalist taxon page</a>)<br/>');
}
if ( $errors ) {
	print( '<p id="errors">' );
	print( 'Errors:<br/>' );
	foreach ( $errors as $error ) {
		print( $error . '<br/>' );
	}
	print( '</p>' );
}
?>
</div>
</body>
</html>
