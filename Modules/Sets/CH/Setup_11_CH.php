<?php
/*
11 	3D 	(1 distance)

$TourId is the ID of the tournament!
$SubRule is the eventual subrule (see sets.php for the order)
$TourType is the Tour Type (11)

*/

$TourType=11;

$tourDetTypeName		= '3D';
$tourDetNumDist			= ($SubRule<4 ? '1' : '2' );
$tourDetNumEnds = ($SubRule == 1 || $SubRule == 4) ? '24' : (($SubRule == 2) ? '28' : (($SubRule == 3) ? '32' : '28')); //ML 25.05.2025
$tourDetMaxDistScore = ($SubRule == 1 || $SubRule == 4) ? '528' : (($SubRule == 2) ? '616' : (($SubRule == 3) ? '704' : '616')); //ML 25.05.2025
$tourDetMaxFinIndScore	= '44';
$tourDetMaxFinTeamScore	= '132';
$tourDetCategory		= '8'; // 0: Other, 1: Outdoor, 2: Indoor, 4:Field, 8:3D
$tourDetElabTeam		= '2'; // 0:Standard, 1:Field, 2:3DI
$tourDetElimination		= '1'; // 0: No Eliminations, 1: Elimination Allowed
$tourDetGolds			= '11';
$tourDetXNine			= '10';
$tourDetGoldsChars		= 'M';
$tourDetXNineChars		= 'L';
$tourDetDouble			= '0';
$DistanceInfoArray=array(array(24, 2));

require_once('Setup_Target.php');

?>