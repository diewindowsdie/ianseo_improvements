<?php
/*
1001 - Type_50_30mRound

$TourId is the ID of the tournament!
$SubRule is the eventual subrule (see sets.php for the order)
$TourType is the Tour Type (1001)

*/

$TourType=1001;

$tourDetTypeName		= 'Type_50_30mRound';
$tourDetNumDist			= '2';
$tourDetNumEnds			= '6';
$tourDetMaxDistScore	= '360';
$tourDetMaxFinIndScore	= '';
$tourDetMaxFinTeamScore	= '';
$tourDetCategory		= '1'; // 0: Other, 1: Outdoor, 2: Indoor, 4:Field, 8:3D
$tourDetElabTeam		= '0'; // 0:Standard, 1:Field, 2:3DI
$tourDetElimination		= '0'; // 0: No Eliminations, 1: Elimination Allowed
$tourDetGolds			= '10+X';
$tourDetXNine			= 'X';
$tourDetGoldsChars		= 'KL';
$tourDetXNineChars		= 'K';
$tourDetDouble			= '0';
$DistanceInfoArray=array(array(6, 6), array(6, 6));

require_once('Setup_Target.php');

