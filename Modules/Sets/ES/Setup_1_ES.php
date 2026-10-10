<?php
/*
1	Round FITA (WA 1440)
*/

$TourType=1;

$tourDetTypeName		= 'Type_FITA';
$tourDetNumDist			= '4';
$tourDetNumEnds			= '12';
$tourDetMaxDistScore	= '360';
$tourDetMaxFinIndScore	= '150';
$tourDetMaxFinTeamScore	= '240';
$tourDetCategory		= '1'; // 0: otras, 1: aire libre, 2: sala, 4: campo, 8: 3D
$tourDetElabTeam		= '0'; // 0: estándar, 1: campo, 2: 3D
$tourDetElimination		= '0'; // 0: sin eliminatorias, 1: con eliminatorias
$tourDetGolds			= 'X';
$tourDetXNine			= '10';
$tourDetGoldsChars		= 'K';
$tourDetXNineChars		= 'L';
$tourDetDouble			= '0';
$DistanceInfoArray=array(array(6, 6), array(6, 6), array(6, 6), array(6, 6));

require_once('Setup_Target.php');
