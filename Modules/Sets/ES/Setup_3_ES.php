<?php
/*
3	Round 70/50 m (WA 70m Round)
*/

$TourType=3;

$tourDetTypeName		= 'Type_70m Round';
$tourDetNumDist			= '2';
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
$DistanceInfoArray=array(array(6, 6), array(6, 6));

require_once('Setup_Target.php');
