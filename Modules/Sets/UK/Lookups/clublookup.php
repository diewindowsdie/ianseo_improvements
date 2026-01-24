<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
Function LookupClubsGBR() //Important the function name must be this.
{
    $Tour = $_SESSION['TourId'];
// Fetch JSON from remote URL
    $url = 'https://records.agbextranet.org.uk/Public/ClubNames.php';
    $json = file_get_contents($url);
    if ($json === false) {
        die("Failed to fetch JSON");
    }

// Decode JSON
    $data = json_decode($json, true);
    if (!is_array($data)) {
        die("Invalid JSON data");
    }

    //Load Counties and Regions first so they can be linked to clubs.
    foreach ($data as $item) {
        $Id = intval($item['ClubID'] ?? 0); // will be 9000-9999 for counties, 10000-11000 for regions
        $coName = addslashes(trim($item['LongName'] ?? '')); // escape single quotes
        $coShort = addslashes(trim($item['ShortName'] ?? ''));

        if ($Id >= 9000 && $Id <= 11000) { // only counties and regions
            $query = "INSERT INTO Countries (CoCode, CoTournament, CoNameComplete, CoName, CoMaCode, CoCaCode)
                  VALUES ($Id, $Tour, '$coName', '$coShort','GBR','WAE')
                  ON DUPLICATE KEY UPDATE 
                      CoNameComplete='$coName', CoName='$coShort'";
            safe_w_SQL($query) or die(safe_w_error());
        }
    }

    //Load Clubs
    foreach ($data as $item) {
        $clubID = intval($item['ClubID'] ?? 0);
        $longName = addslashes(trim($item['LongName'] ?? ''));
        $shortName = addslashes(trim($item['ShortName'] ?? ''));
        $countyID = intval($item['CountyID'] ?? 0);
        $regionID = intval($item['RegionID'] ?? 0);

        // skip if invalid club
        if ($clubID === 0 || $countyID === 0 || $regionID === 0) continue;

//Select the County CoId from the DB
        $result = safe_r_SQL( "SELECT CoId FROM Countries WHERE CoCode=$countyID AND CoTournament=$Tour");
        if ($result) {
            $row = safe_fetch($result);
            $coParent1 = $row ? $row->CoId : 0;
        } else {
            $coParent1 = 0;
        }
//Select the Region CoId from the DB
        $result = safe_r_SQL( "SELECT CoId FROM Countries WHERE CoCode=$regionID AND CoTournament=$Tour");
        if ($result) {
            $row = safe_fetch($result);
            $coParent2 = $row ? $row->CoId : 0;
        } else {
            $coParent2 = 0;
        }

        //Insert into the DB for the club (or update as nessecary)

        $query = "INSERT INTO Countries (CoCode, CoTournament, CoNameComplete, CoName, CoParent1, CoParent2, CoMaCode, CoCaCode)
              VALUES ($clubID, $Tour, '$longName', '$shortName', $coParent1, $coParent2, 'GBR', 'WAE')
              ON DUPLICATE KEY UPDATE 
                  CoNameComplete='$longName', CoName='$shortName', CoParent1=$coParent1, CoParent2=$coParent2, CoMaCode='GBR', CoCaCode='WAE'";
        safe_w_SQL($query) or die(safe_w_error());
    }


}