<?php

require_once('Common/Lib/Obj_RankFactory.php');
require_once('Common/Lib/Fun_Scheduler.php');
require_once('Common/Lib/ArrTargets.inc.php');
require_once('Common/Lib/Fun_Phases.inc.php');

define('RFS_VERSION', '2026-09-23');
define('RFS_REMOTE_TIMEOUT', 120);
define('RFS_CONNECT_TIMEOUT', 10);
define('RFS_BATCH_SIZE', 50);
// Un texto mal codificado no puede dejar un bloque sin subir para siempre
define('RFS_JSON', JSON_UNESCAPED_UNICODE | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0));
// Primera línea de los archivos de sesión: si alguien los pide por la web, PHP corta ahí
define('RFS_STORE_GUARD', "<?php exit; ?>\n");
define('RFS_DEFAULT_URL', 'https://api-res.rfeta.es');
define('RFS_DEFAULT_INTERVAL', 120);
define('RFS_FAST_INTERVAL', 40);
define('RFS_MIN_INTERVAL', 10);

function rfsPrivilegedEmails() {
	return array(
		'competiciones@federarco.es',
		'competicion@federarco.es',
		'resultados@federarco.es',
		'secretaria.tecnica@federarco.es',
		'javier@addon-sport.com',
	);
}

function rfsIsPrivileged($store) {
	if(!empty($store['auth']['privileged'])) return true;
	$email = strtolower(trim(strval(rfsVal($store['request'], 'email', ''))));
	return $email !== '' and in_array($email, rfsPrivilegedEmails());
}

// Fastest allowed: 10 s for the federation accounts, 120 s for everyone else,
// unless the server says otherwise when connecting.
function rfsMinInterval($store) {
	$servidor = intval(rfsVal($store['auth'], 'minInterval', 0));
	if($servidor > 0) return max(5, $servidor);
	return rfsIsPrivileged($store) ? RFS_MIN_INTERVAL : RFS_DEFAULT_INTERVAL;
}

function rfsDefaultInterval($store) {
	$servidor = intval(rfsVal($store['auth'], 'defaultInterval', 0));
	$defecto = $servidor > 0 ? $servidor : (rfsIsPrivileged($store) ? RFS_FAST_INTERVAL : RFS_DEFAULT_INTERVAL);
	return max(rfsMinInterval($store), $defecto);
}

// Intervals the server may send with the login or status answer
function rfsServerIntervals($data) {
	$out = array();
	foreach(array('minInterval', 'defaultInterval') as $k) {
		if(is_numeric(rfsVal($data, $k))) $out[$k] = intval(rfsVal($data, $k));
	}
	return $out;
}

function rfsIntervalChoices($store) {
	$min = rfsMinInterval($store);
	$todos = array(10, 15, 20, 30, 40, 60, 90, 120, 180, 300);
	$out = array();
	if(!in_array($min, $todos)) $todos[] = $min;
	sort($todos);
	foreach($todos as $v) if($v >= $min) $out[] = $v;
	return $out;
}

function rfsStoreDir() {
	static $elegido = null;
	if($elegido !== null) return $elegido;

	$candidatos = array(dirname(__FILE__) . '/data');
	$tmp = sys_get_temp_dir();
	if($tmp !== '') {
		$candidatos[] = rtrim($tmp, '/\\') . '/sendtorfeta-' . substr(md5(dirname(__FILE__)), 0, 8);
	}
	foreach($candidatos as $dir) {
		if(!is_dir($dir)) @mkdir($dir, 0775, true);
		if(is_dir($dir) and is_writable($dir)) {
			$elegido = $dir;
			return $elegido;
		}
	}
	$elegido = '';
	return $elegido;
}

function rfsStoreError() {
	return 'No se puede guardar la sesión del módulo en ' . dirname(__FILE__) . '/data'
		. '. Da permisos de escritura a esa carpeta al usuario del servidor web (por ejemplo chmod 775 y el grupo del servidor) y vuelve a conectar.';
}

function rfsStoreWritable() {
	return rfsStoreDir() !== '';
}

/*
	La sesión se guarda en un .php que empieza cortando la ejecución: la
	carpeta está dentro de la web de IANSEO y, como .json, cualquiera en la red
	de la sede podía descargarla con el token de subida dentro.
*/
function rfsStorePath($ext = 'php') {
	$dir = rfsStoreDir();
	if($dir === '') return '';
	$tour = isset($_SESSION['TourId']) ? intval($_SESSION['TourId']) : 0;
	return $dir . '/tour_' . ($tour > 0 ? $tour : 'master') . '.' . $ext;
}

function rfsReadStoreFile($path) {
	$raw = strval(@file_get_contents($path));
	if(strncmp($raw, RFS_STORE_GUARD, strlen(RFS_STORE_GUARD)) === 0) {
		$raw = substr($raw, strlen(RFS_STORE_GUARD));
	}
	return json_decode($raw, true);
}

/* Sesión de este torneo en la ubicación anterior del módulo, si existe */
function rfsOldModuleStore() {
	$tour = isset($_SESSION['TourId']) ? intval($_SESSION['TourId']) : 0;
	$base = dirname(dirname(dirname(dirname(__FILE__)))) . '/Custom/SendToRfeta/data/tour_' . ($tour > 0 ? $tour : 'master');
	foreach(array('.php', '.json') as $ext) {
		if(is_file($base . $ext)) return $base . $ext;
	}
	return '';
}

function rfsLoadStore() {
	$defaults = array(
		'settings' => array('url' => RFS_DEFAULT_URL, 'interval' => RFS_DEFAULT_INTERVAL),
		'auth' => array('token' => '', 'status' => '', 'blockedReason' => '', 'privileged' => false),
		'request' => array('email' => '', 'federation' => '', 'categoryId' => 0, 'discipline' => ''),
		'hashes' => array(),
		'mediaHashes' => array(),
		'lastSummary' => array(),
		'transfer' => array('totalUp' => 0, 'totalDown' => 0),
	);
	$path = rfsStorePath();
	$stored = null;
	if($path !== '' and is_file($path)) {
		$stored = rfsReadStoreFile($path);
	} elseif($path !== '' and is_file($antiguo = rfsStorePath('json'))) {
		// Sesión de una versión anterior: se pasa al formato nuevo y se borra la vieja
		$stored = rfsReadStoreFile($antiguo);
		if(is_array($stored) and rfsSaveStore($stored)) {
			@unlink($antiguo);
		}
	} elseif($path !== '' and ($antiguo = rfsOldModuleStore()) !== '') {
		// El módulo estaba antes en Modules/Custom/SendToRfeta: se copia su sesión, sin tocar la original
		$stored = rfsReadStoreFile($antiguo);
		if(is_array($stored)) rfsSaveStore($stored);
	}
	if(is_array($stored)) {
		foreach($defaults as $k => $v) {
			if(isset($stored[$k]) and is_array($stored[$k])) {
				$defaults[$k] = array_merge($v, $stored[$k]);
			}
		}
	}
	return $defaults;
}

function rfsSaveStore($store) {
	$path = rfsStorePath();
	if($path === '') return false;
	$contenido = RFS_STORE_GUARD . json_encode($store, RFS_JSON | JSON_PRETTY_PRINT);
	// Se escribe aparte y se sustituye de golpe: un corte a mitad no deja la sesión rota
	$tmp = substr($path, 0, -4) . '.tmp' . getmypid() . '.php';
	if(@file_put_contents($tmp, $contenido) !== false) {
		if(@rename($tmp, $path)) return true;
		@unlink($tmp);
	}
	return @file_put_contents($path, $contenido, LOCK_EX) !== false;
}

function rfsUpdateStore($changes) {
	$store = rfsLoadStore();
	foreach($changes as $k => $v) {
		// An empty array is a reset; otherwise merge over the current values
		$store[$k] = (is_array($v) and count($v)) ? array_merge($store[$k], $v) : $v;
	}
	rfsSaveStore($store);
	return $store;
}

/*
	Respuestas largas. MAMP corta una petición si PHP pasa 30 s sin mandar nada
	(nginx e IIS hacen lo mismo a los 60-70 s), y un primer envío con fotos por
	una conexión lenta tarda más. Una vez empezada la respuesta, se manda un
	espacio cada pocos segundos: el JSON admite espacios delante.
*/
function rfsKeepAliveStart() {
	$GLOBALS['rfsKeepAlive'] = microtime(true);
	while(ob_get_level()) ob_end_clean();
	header('Content-Type: application/json; charset=UTF-8');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('X-Accel-Buffering: no');
}

function rfsKeepAlive() {
	if(empty($GLOBALS['rfsKeepAlive'])) return;
	if(microtime(true) - $GLOBALS['rfsKeepAlive'] < 5) return;
	$GLOBALS['rfsKeepAlive'] = microtime(true);
	echo ' ';
	flush();
}

// Final answer of SyncNow, whether or not the keep-alive already started the body
function rfsJsonOut($data) {
	$GLOBALS['rfsRespondido'] = true;
	if(!empty($GLOBALS['rfsKeepAlive']) or headers_sent()) {
		echo json_encode($data, RFS_JSON);
		exit;
	}
	JsonOut($data);
}

// Helpers

function rfsVal($arr, $key, $default = null) {
	if(is_array($arr) and array_key_exists($key, $arr)) return $arr[$key];
	return $default;
}

function rfsItem($item, $keys, $default = null) {
	foreach((array)$keys as $key) {
		if(is_array($item) and array_key_exists($key, $item)) return $item[$key];
	}
	return $default;
}

function rfsInt($value) {
	return is_numeric($value) ? intval($value) : null;
}

// Phase names are always resolved in Spanish, whatever language the IANSEO session runs.
function rfsPhaseName($phaseCode, $cntPhase) {
	return get_text(getPhaseTV($phaseCode, $cntPhase) . '_Phase', 'Tournament', null, false, false, 'es');
}

function rfsMakeBlock($kind, $key, $data, $event = null, $teamEvent = null) {
	$block = array(
		'kind' => $kind,
		'key' => $key,
		'hash' => md5(json_encode($data, RFS_JSON)),
		'data' => $data,
	);
	if($event !== null) {
		$block['event'] = $event;
		$block['teamEvent'] = (bool)$teamEvent;
	}
	return $block;
}

// Basic tournament data
function rfsTournamentInfo() {
	$q = safe_r_sql("SELECT ToCode, ToName, ToNameShort, ToCommitee, ToWhere, ToCountry,
			DATE_FORMAT(ToWhenFrom, '%Y-%m-%d') as WhenFrom, DATE_FORMAT(ToWhenTo, '%Y-%m-%d') as WhenTo,
			ToLocRule
		FROM Tournament WHERE ToId=" . intval($_SESSION['TourId']));
	$r = safe_fetch($q);
	
	$organizerName = '';
	$colq = safe_r_sql("SHOW COLUMNS FROM Tournament LIKE 'ToSubCommitee'");
	if($colq and safe_fetch($colq)) {
		$rs = safe_fetch(safe_r_sql("SELECT ToSubCommitee FROM Tournament WHERE ToId=" . intval($_SESSION['TourId'])));
		$organizerName = $rs ? strval($rs->ToSubCommitee) : '';
	}

	// Competition officials 
	$officials = array();
	$oq = safe_r_sql("SELECT ti.TiName AS familyName, ti.TiGivenName AS givenName, it.ItDescription AS role
		FROM TournamentInvolved ti
		LEFT JOIN InvolvedType it ON it.ItId = ti.TiType
		WHERE ti.TiTournament=" . intval($_SESSION['TourId']) . "
		ORDER BY ti.TiType, ti.TiName");
	while($oq and $o = safe_fetch($oq)) {
		$officials[] = array(
			'familyName' => strval($o->familyName),
			'givenName' => strval($o->givenName),
			'role' => strval($o->role),
		);
	}
	return array(
		'name' => strval($r->ToName),
		'shortName' => strval($r->ToNameShort),
		'organizer' => strval($r->ToCommitee),
		'organizerName' => $organizerName,
		'officials' => $officials,
		'place' => strval($r->ToWhere),
		'country' => strval($r->ToCountry),
		'startDate' => strval($r->WhenFrom),
		'endDate' => strval($r->WhenTo),
		'rules' => strval($r->ToLocRule),
	);
}

function rfsBuildInfoBlock() {
	return rfsMakeBlock('info', 'info', rfsTournamentInfo());
}

// Schedule
function rfsFlattenSchedule($node, $day, &$items) {
	if(!is_array($node)) {
		return;
	}
	if(array_key_exists('Day', $node) and array_key_exists('Items', $node)) {
		foreach((array)$node['Items'] as $child) {
			rfsFlattenSchedule($child, strval($node['Day']), $items);
		}
		return;
	}
	if(array_key_exists('Start', $node) and array_key_exists('Text', $node)) {
		$items[] = array(
			'day' => strval($day),
			'startTime' => rfsVal($node, 'Start'),
			'endTime' => rfsVal($node, 'End'),
			'name' => strval(rfsVal($node, 'Text')),
			'kind' => rfsVal($node, 'Type'),
			'extra' => array(
				'level' => rfsVal($node, 'Level'),
				'duration' => rfsVal($node, 'Duration'),
				'active' => (bool)rfsVal($node, 'Active'),
			),
		);
		return;
	}
	foreach($node as $child) {
		rfsFlattenSchedule($child, $day, $items);
	}
}

// Build Schedule with active sessions
function rfsBuildScheduleBlock() {
	$scheduler = new Scheduler(intval($_SESSION['TourId']));
	$scheduler->Finalists = true;
	$activeExtra = array();
	foreach((array)$scheduler->ActiveSessions as $activeKey) {
		$parts = explode('|', $activeKey);
		if(count($parts) === 5 && $parts[3] !== '1') {
			$parts[3] = '1';
			$activeExtra[] = implode('|', $parts);
		}
	}
	if($activeExtra) {
		$scheduler->ActiveSessions = array_values(array_unique(array_merge((array)$scheduler->ActiveSessions, $activeExtra)));
	}
	$items = array();
	rfsFlattenSchedule($scheduler->getScheduleByDay(), '', $items);
	$items = array_values(array_filter($items, function($item) {
		return $item['day'] !== '' && $item['name'] !== '';
	}));
	return rfsMakeBlock('schedule', 'schedule', $items);
}

// Start list
function rfsBuildEntriesBlock() {
	require_once('Common/OrisFunctions.php');
	$overTgtDesc = array(0 => '', 1 => ' bis', 2 => ' ter');

	$List = getStartListByCountries(true, false, false, array(), '');
	$tour = intval($_SESSION['TourId']);
	$divAthlete = array();
	$q = safe_r_sql("SELECT DivId, DivAthlete FROM Divisions WHERE DivTournament={$tour}");
	while($r = safe_fetch($q)) {
		$divAthlete[trim(strval($r->DivId))] = (trim(strval($r->DivAthlete)) === '1');
	}
	$clsAthlete = array();
	$q = safe_r_sql("SELECT ClId, ClAthlete FROM Classes WHERE ClTournament={$tour}");
	while($r = safe_fetch($q)) {
		$clsAthlete[trim(strval($r->ClId))] = (trim(strval($r->ClAthlete)) === '1');
	}

	$items = array();
	$seen = array();
	foreach((array)$List->Data['Items'] as $countryItems) {
		foreach((array)$countryItems as $item) {
			$bib = strval($item->Bib);
			if($bib === '' or isset($seen[$bib])) {
				continue;
			}
			$seen[$bib] = true;

			$target = intval((!empty($List->BisTarget) and (intval($item->TargetNo) > intval($List->NumEnd))) ? intval($item->TargetNo) - intval($List->NumEnd) : $item->TargetNo);
			$overTgt = (!empty($List->BisTarget) and (intval($item->TargetNo) > intval($List->NumEnd))) ? intval(intval($item->TargetNo) / intval($List->NumEnd)) : 0;
			$class = strval($item->ClassCode);

			$div = trim(strval($item->DivCode));
			$esDeportista = (!isset($divAthlete[$div]) or $divAthlete[$div])
				and (!isset($clsAthlete[trim($class)]) or $clsAthlete[trim($class)]);

			$items[] = array(
				'bib' => $bib,
				'license' => $bib,
				'athlete' => strval($item->Athlete),
				'competitor' => (bool)$esDeportista,
				'gender' => (substr(trim($class), -1) == 'W') ? 'W' : 'M',
				'clubCode' => strval($item->NationCode),
				'clubName' => strval($item->Nation),
				'division' => $div,
				'divisionName' => strval($item->DivDescription),
				'class' => trim($class),
				'className' => strval($item->ClDescription),
				'session' => intval($item->Session),
				'sessionName' => strval($item->SesName),
				'target' => $target . substr(strval($item->TargetNo), -1, 1) . $overTgtDesc[$overTgt],
			);
		}
	}
	return rfsMakeBlock('entries', 'entries', $items);
}

function rfsEventsData() {
	$items = array();
	$eventList = array();
	$tg = safe_fetch(safe_r_sql("SELECT ToGolds, ToXNine, ToGoldsChars, ToXNineChars FROM Tournament WHERE ToId=" . intval($_SESSION['TourId'])));
	$toGolds = $tg ? strval($tg->ToGolds) : '';
	$toXNine = $tg ? strval($tg->ToXNine) : '';
	$toGoldsChars = $tg ? strval($tg->ToGoldsChars) : '';
	$toXNineChars = $tg ? strval($tg->ToXNineChars) : '';
	$q = safe_r_sql("SELECT EvCode, EvEventName, EvTeamEvent, EvFinalFirstPhase, EvDistance, EvTargetSize,
			EvGolds, EvXNine, EvGoldsChars, EvXNineChars, EvMatchMode,
			EvElimEnds, EvElimArrows, EvElimSO, EvFinEnds, EvFinArrows, EvFinSO,
			EvElimType, EvElim1, EvE1Ends, EvE1Arrows, EvE1SO, EvElim2, EvE2Ends, EvE2Arrows, EvE2SO
		FROM Events WHERE EvTournament=" . intval($_SESSION['TourId']) . "
		ORDER BY EvTeamEvent, EvProgr");
	while($r = safe_fetch($q)) {
		$hasFinals = intval($r->EvFinalFirstPhase) != 0;
		$phases = array();
		$cntPhase = 1;
		if($hasFinals) {
			foreach(getPhasesId($r->EvFinalFirstPhase) as $ph) {
				$phases[] = array(
					'code' => strval(bitwisePhaseId($ph)),
					'name' => rfsPhaseName($ph, $cntPhase),
				);
				$cntPhase++;
			}
		}

		$elimRounds = array();
		if(intval($r->EvElim1) > 0) {
			$elimRounds[] = array('archers' => intval($r->EvElim1), 'ends' => intval($r->EvE1Ends), 'arrowsPerEnd' => intval($r->EvE1Arrows), 'shootOffArrows' => intval($r->EvE1SO));
		}
		if(intval($r->EvElim2) > 0) {
			$elimRounds[] = array('archers' => intval($r->EvElim2), 'ends' => intval($r->EvE2Ends), 'arrowsPerEnd' => intval($r->EvE2Arrows), 'shootOffArrows' => intval($r->EvE2SO));
		}

		$evGolds = strval($r->EvGolds) !== '' ? strval($r->EvGolds) : $toGolds;
		$evGoldsChars = strval($r->EvGoldsChars) !== '' ? strval($r->EvGoldsChars) : $toGoldsChars;
		$evXNine = strval($r->EvXNine) !== '' ? strval($r->EvXNine) : $toXNine;
		$evXNineChars = strval($r->EvXNineChars) !== '' ? strval($r->EvXNineChars) : $toXNineChars;
		$format = array(
			'golds' => array('label' => $evGolds, 'chars' => $evGoldsChars),
			'xnine' => array('label' => $evXNine, 'chars' => $evXNineChars),
			'targetSize' => intval($r->EvTargetSize),
			'qualification' => null, // merged later from the ranking meta
			'matches' => array(
				'mode' => intval($r->EvMatchMode) == 1 ? 'sets' : 'cumulative',
				'eliminations' => array('ends' => intval($r->EvElimEnds), 'arrowsPerEnd' => intval($r->EvElimArrows), 'shootOffArrows' => intval($r->EvElimSO)),
				'finals' => array('ends' => intval($r->EvFinEnds), 'arrowsPerEnd' => intval($r->EvFinArrows), 'shootOffArrows' => intval($r->EvFinSO)),
				'elimRounds' => $elimRounds,
			),
		);

		$teamEvent = intval($r->EvTeamEvent) == 1;
		$hasRobin = intval($r->EvElimType) == 5;
		$items[] = array(
			'code' => strval($r->EvCode),
			'teamEvent' => $teamEvent,
			'name' => strval($r->EvEventName),
			'distance' => intval($r->EvDistance),
			'phases' => $phases,
			'roundRobin' => $hasRobin,
			'format' => $format,
		);
		$eventList[] = array('code' => strval($r->EvCode), 'teamEvent' => $teamEvent, 'hasFinals' => $hasFinals, 'roundRobin' => $hasRobin);
	}
	return array($items, $eventList);
}

function rfsMapRankingItem($item, $teamEvent) {
	$bib = rfsItem($item, array('localbib', 'localBib', 'bib'));
	$mapped = array(
		'rank' => rfsItem($item, array('rank')),
		'bib' => $bib,
		'license' => $teamEvent ? null : $bib,
		'familyName' => rfsItem($item, array('familyname', 'familyName')),
		'givenName' => rfsItem($item, array('givenname', 'givenName')),
		'gender' => rfsItem($item, array('gender')),
		'clubCode' => rfsItem($item, array('countryCode')),
		'clubName' => rfsItem($item, array('countryName')),
		'score' => rfsInt(rfsItem($item, array('score'))),
		'gold' => rfsInt(rfsItem($item, array('gold'))),
		'xnine' => rfsInt(rfsItem($item, array('xnine'))),
		'arrowsShot' => rfsInt(rfsItem($item, array('arrowsShot'))),
		'irm' => rfsItem($item, array('irmText')),
		'tieBreak' => rfsItem($item, array('tieBreakDecoded', 'tiebreakDecoded', 'tieWeightDecoded', 'tieBreak', 'tiebreak')),
	);
	if($teamEvent) {
		$components = array();
		foreach((array)rfsItem($item, array('athletes'), array()) as $athlete) {
			$athleteBib = rfsItem($athlete, array('localbib', 'localBib', 'bib'));
			$components[] = array(
				'bib' => $athleteBib,
				'license' => $athleteBib,
				'familyName' => rfsItem($athlete, array('familyname', 'familyName')),
				'givenName' => rfsItem($athlete, array('givenname', 'givenName')),
				'gender' => rfsItem($athlete, array('gender')),
				'score' => rfsInt(rfsItem($athlete, array('score', 'quscore'))),
				'gold' => rfsInt(rfsItem($athlete, array('gold', 'qugolds', 'qugold'))),
				'xnine' => rfsInt(rfsItem($athlete, array('xnine', 'quxnine'))),
				'order' => rfsItem($athlete, array('order')),
			);
		}
		$mapped['components'] = $components;
	}
	return $mapped;
}

// Per distance partials
function rfsMapDistances($item, $numDist, $meta = array()) {
	$distances = array();
	for($i = 1; $i <= $numDist; $i++) {
		$raw = strval(rfsItem($item, array('dist_' . $i)));
		if($raw === '') continue;
		$parts = explode('|', $raw);
		$distances[] = array(
			'distance' => $i,
			'name' => rfsDistName($meta, $i),
			'rank' => rfsVal($parts, 0),
			'score' => rfsVal($parts, 1),
			'gold' => rfsVal($parts, 2),
			'xnine' => rfsVal($parts, 3),
		);
	}
	return $distances;
}

// Arrow by arrow scorecard from the encoded arrow strings
function rfsMapScorecard($item, $meta) {
	$numDist = intval(rfsVal($meta, 'numDist', 0));
	$distances = array();
	for($i = 1; $i <= $numDist; $i++) {
		$format = rfsDistFormat($meta, $i);
		$arrowsPerEnd = max(1, intval($format['arrowsPerEnd']));
		$endsNo = intval($format['ends']);

		$arrowString = strval(rfsItem($item, array('D' . $i . 'Arrowstring')));
		$arrows = array();
		for($j = 0; $j < strlen($arrowString); $j++) {
			$arrows[] = DecodeFromLetter($arrowString[$j]);
		}
		$ends = array_chunk($arrows, $arrowsPerEnd);

		$parts = explode('|', strval(rfsItem($item, array('dist_' . $i))));
		$distances[] = array(
			'distance' => $i,
			'name' => rfsDistName($meta, $i),
			'ends' => $ends,
			'endsPlanned' => $endsNo,
			'arrowsPerEnd' => $arrowsPerEnd,
			'score' => rfsVal($parts, 1),
			'gold' => rfsVal($parts, 2),
			'xnine' => rfsVal($parts, 3),
		);
	}
	return $distances;
}

// Default qualification structure of the tournament
function rfsQualDefaults() {
	static $defaults = null;
	if($defaults !== null) return $defaults;

	$distances = array();
	$q = safe_r_sql("SELECT DiDistance, DiEnds, DiArrows FROM DistanceInformation
		WHERE DiTournament=" . intval($_SESSION['TourId']) . " AND DiType='Q' AND DiSession=1
		ORDER BY DiDistance");
	while($r = safe_fetch($q)) {
		$distances[] = array(
			'distance' => intval($r->DiDistance),
			'ends' => intval($r->DiEnds),
			'arrowsPerEnd' => intval($r->DiArrows),
		);
	}
	$maxArrows = 0;
	foreach($distances as $d) {
		$maxArrows += $d['ends'] * $d['arrowsPerEnd'];
	}
	$defaults = array('distances' => $distances, 'maxArrows' => $maxArrows);
	return $defaults;
}

// Name of a distance
function rfsDistName($meta, $i) {
	$fields = (array)rfsVal($meta, 'fields', array());
	return trim(strval(rfsVal($fields, 'dist_' . $i, '')));
}

// Ends/arrows of one distance
function rfsDistFormat($meta, $i) {
	$name = rfsDistName($meta, $i);
	$info = (array)rfsVal((array)rfsVal($meta, 'distanceInfo', array()), 'dist_' . $i, array());
	$ends = intval(rfsVal($info, 'ends', 0));
	$arrows = intval(rfsVal($info, 'arr', 0));
	if($ends > 0 and $arrows > 0) {
		return array('distance' => $i, 'name' => $name, 'ends' => $ends, 'arrowsPerEnd' => $arrows);
	}
	foreach(rfsQualDefaults()['distances'] as $d) {
		if($d['distance'] == $i) {
			$d['name'] = $name;
			return $d;
		}
	}
	return array('distance' => $i, 'name' => $name, 'ends' => $ends, 'arrowsPerEnd' => max(1, $arrows));
}

// Qualification ranking and scorecards of one event
function rfsBuildQualificationBlocks($event) {
	$type = $event['teamEvent'] ? 1 : 0;
	$suffix = $event['code'] . ':' . $type;

	$options = array('tournament' => intval($_SESSION['TourId']), 'events' => $event['code'], 'dist' => 0);
	$rank = Obj_RankFactory::create($event['teamEvent'] ? 'AbsTeam' : 'Abs', $options);
	$rank->read();
	$data = $rank->getData();

	$results = array();
	$cards = array();
	$qualFormat = null;
	foreach((array)rfsVal($data, 'sections', array()) as $section) {
		$meta = (array)rfsVal($section, 'meta', array());
		$numDist = intval(rfsVal($meta, 'numDist', 0));

		if($qualFormat === null and $numDist > 0) {
			$distances = array();
			$maxArrows = 0;
			for($i = 1; $i <= $numDist; $i++) {
				$d = rfsDistFormat($meta, $i);
				$distances[] = $d;
				$maxArrows += $d['ends'] * $d['arrowsPerEnd'];
			}
			$qualFormat = array('distances' => $distances, 'maxArrows' => $maxArrows);
		}

		foreach((array)rfsVal($section, 'items', array()) as $item) {
			$mapped = rfsMapRankingItem($item, $event['teamEvent']);
			if(!$event['teamEvent']) {
				$mapped['distances'] = rfsMapDistances($item, $numDist, $meta);
			}
			$results[] = $mapped;

			if(!$event['teamEvent']) {
				$cards[] = array(
					'bib' => strval(rfsItem($item, array('localbib', 'localBib', 'bib'))),
					'athlete' => trim(rfsItem($item, array('givenname', 'givenName'), '') . ' ' . rfsItem($item, array('familyname', 'familyName'), '')),
					'clubName' => rfsItem($item, array('countryName')),
					'target' => rfsItem($item, array('target', 'targetno', 'targetNo')),
					'total' => intval(rfsItem($item, array('score'), 0)),
					'distances' => rfsMapScorecard($item, $meta),
				);
			}
		}
	}

	$blocks = array(rfsMakeBlock('qualification', 'qual:' . $suffix, $results, $event['code'], $event['teamEvent']));
	if(!$event['teamEvent']) {
		$blocks[] = rfsMakeBlock('scorecards', 'cards:' . $event['code'], $cards, $event['code'], false);
	}
	if($qualFormat === null) {
		$qualFormat = rfsQualDefaults();
	}
	return array('blocks' => $blocks, 'qualFormat' => $qualFormat);
}

function rfsLineupChanges($event) {
	if(empty($event['teamEvent'])) {
		return null;
	}
	$options = array('tournament' => intval($_SESSION['TourId']), 'events' => $event['code']);
	$rank = Obj_RankFactory::create('TeamComponents', $options);
	$rank->read();
	$data = $rank->getData();

	$name = function($athlete) {
		$bib = rfsItem($athlete, array('localbib', 'localBib', 'bib'));
		return array(
			'bib' => $bib,
			'license' => $bib,
			'familyName' => rfsItem($athlete, array('familyname', 'familyName')),
			'givenName' => rfsItem($athlete, array('givenname', 'givenName')),
		);
	};

	$changes = array();
	foreach((array)rfsVal($data, 'sections', array()) as $section) {
		foreach((array)rfsVal($section, 'items', array()) as $item) {
			$prev = (array)rfsItem($item, array('previousAthletes'), array());
			$next = (array)rfsItem($item, array('nextAthletes'), array());
			$slots = array();
			foreach($prev as $i => $out) {
				if(!isset($next[$i])) continue;
				$outId = rfsItem($out, array('id'));
				$inId = rfsItem($next[$i], array('id'));
				if(strval($outId) === strval($inId)) continue; // slot untouched
				$slots[] = array(
					'order' => $i + 1,
					'out' => $name($out),
					'in' => $name($next[$i]),
				);
			}
			if(!count($slots)) continue;
			$lineup = array();
			foreach($next as $i => $athlete) {
				$row = $name($athlete);
				$row['order'] = $i + 1;
				$lineup[] = $row;
			}
			$changes[] = array(
				'clubCode' => rfsItem($item, array('countryCode')),
				'clubName' => rfsItem($item, array('countryName')),
				'subTeam' => rfsItem($item, array('subteam')),
				'at' => rfsItem($item, array('timestamp')),
				'slots' => $slots,
				'lineup' => $lineup,
			);
		}
	}
	return count($changes) ? $changes : null;
}

function rfsBuildFinalBlock($event) {
	$type = $event['teamEvent'] ? 1 : 0;
	$options = array('tournament' => intval($_SESSION['TourId']), 'eventsR' => $event['code']);
	$rank = Obj_RankFactory::create($event['teamEvent'] ? 'FinalTeam' : 'FinalInd', $options);
	$rank->read();
	$data = $rank->getData();

	$results = array();
	foreach((array)rfsVal($data, 'sections', array()) as $section) {
		foreach((array)rfsVal($section, 'items', array()) as $item) {
			$results[] = rfsMapRankingItem($item, $event['teamEvent']);
		}
	}
	return rfsMakeBlock('final', 'final:' . $event['code'] . ':' . $type, $results, $event['code'], $event['teamEvent']);
}

function rfsTeamRoster($athletes, $teamId, $subTeam) {
	$roster = rfsVal((array)rfsVal((array)$athletes, $teamId, array()), $subTeam, null);
	if($roster === null) {
		// Sub-team keys are sometimes strings ('0') and sometimes ints.
		$roster = rfsVal((array)rfsVal((array)$athletes, strval($teamId), array()), strval($subTeam), array());
	}
	return (array)$roster;
}

// Who shot each arrow of a team match, as a list of licence numbers aligned with the arrow list (null where unknown).
function rfsArrowBy($shooting, $arrowCount, $athletes, $teamId, $subTeam) {
	if(!is_array($shooting) or !count($shooting) or $arrowCount < 1) {
		return null;
	}
	// The map holds entry ids (TfcId = EnId)
	$bibById = array();
	foreach(rfsTeamRoster($athletes, $teamId, $subTeam) as $athlete) {
		if(!is_array($athlete)) continue;
		$id = rfsItem($athlete, array('id'));
		if($id === null or $id === '') continue;
		$bibById[strval($id)] = rfsItem($athlete, array('localBib', 'localbib', 'code', 'bib'));
	}

	$out = array();
	$any = false;
	for($i = 0; $i < $arrowCount; $i++) {
		$enId = null;
		if(array_key_exists($i, $shooting)) $enId = $shooting[$i];
		elseif(array_key_exists(strval($i), $shooting)) $enId = $shooting[strval($i)];
		$bib = ($enId !== null and $enId !== '' and isset($bibById[strval($enId)])) ? $bibById[strval($enId)] : null;
		if($bib !== null) $any = true;
		$out[] = $bib;
	}
	return $any ? $out : null;
}

function rfsTeamLineup($athletes, $teamId, $subTeam) {
	$roster = rfsTeamRoster($athletes, $teamId, $subTeam);
	$components = array();
	$order = 0;
	foreach((array)$roster as $athlete) {
		if(!is_array($athlete)) continue;
		$order++;
		$bib = rfsItem($athlete, array('localBib', 'localbib', 'code', 'bib'));
		$components[] = array(
			'bib' => $bib,
			'license' => $bib,
			'familyName' => rfsItem($athlete, array('familyName', 'familyname')),
			'givenName' => rfsItem($athlete, array('givenName', 'givenname')),
			'gender' => rfsItem($athlete, array('gender')),
			'order' => $order,
			'backNo' => rfsItem($athlete, array('backNo', 'backno')),
		);
	}
	return $components;
}

function rfsFinalTarget($event) {
	$teamFlag = !empty($event['teamEvent']) ? 1 : 0;
	$e = safe_fetch(safe_r_sql("SELECT EvFinalTargetType, EvTargetSize, EvDistance FROM Events
		WHERE EvTournament=" . intval($_SESSION['TourId']) . " AND EvCode=" . StrSafe_DB($event['code']) . " AND EvTeamEvent=" . $teamFlag));
	if((!$e or !intval($e->EvFinalTargetType)) and $teamFlag) {
		$e = safe_fetch(safe_r_sql("SELECT EvFinalTargetType, EvTargetSize, EvDistance FROM Events
			WHERE EvTournament=" . intval($_SESSION['TourId']) . " AND EvCode=" . StrSafe_DB($event['code']) . " AND EvTeamEvent=0"));
	}
	if(!$e or !intval($e->EvFinalTargetType)) return null;
	$tar = safe_fetch(safe_r_sql("SELECT * FROM Targets WHERE TarId=" . intval($e->EvFinalTargetType)));
	if(!$tar) return null;
	$diameterCm = floatval($e->EvTargetSize) ?: 122;
	$fullSize = floatval($tar->TarFullSize) ?: 100;
	$rings = array();
	$outerSize = 0;
	foreach(get_object_vars($tar) as $k => $v) {
		if(preg_match('/^([A-Z]|[1-9])_size$/', $k, $m) and floatval($v) > 0) {
			$letter = $m[1];
			$size = floatval($v);
			if($size > $outerSize) $outerSize = $size;
			$rings[] = array('size' => $size, 'color' => '#' . strval($tar->{$letter . '_color'}), 'value' => DecodeFromLetter($letter));
		}
	}
	if(!$outerSize) return null;
	usort($rings, function($a, $b) { return $b['size'] <=> $a['size']; });
	$normRings = array();
	foreach($rings as $r) {
		$normRings[] = array('r' => round($r['size'] / $outerSize, 4), 'color' => $r['color'], 'value' => $r['value']);
	}
	return array(
		'targetSizeCm' => $diameterCm,
		'distance' => intval($e->EvDistance),
		'radiusMm' => round($diameterCm * $outerSize / $fullSize * 5, 2),
		'rings' => $normRings,
	);
}

function rfsFinalPositions($eventCode, $teamEvent) {
	$map = array();
	if($teamEvent) {
		$q = safe_r_sql("SELECT TfMatchNo mno, TfArrowstring astr, TfArrowPosition apos, TfTiebreak tstr, TfTiePosition tpos
			FROM TeamFinals WHERE TfTournament=" . intval($_SESSION['TourId']) . " AND TfEvent=" . StrSafe_DB($eventCode) . "
			AND TfArrowPosition IS NOT NULL AND TfArrowPosition!=''");
	} else {
		$q = safe_r_sql("SELECT FinMatchNo mno, FinArrowstring astr, FinArrowPosition apos, FinTiebreak tstr, FinTiePosition tpos
			FROM Finals WHERE FinTournament=" . intval($_SESSION['TourId']) . " AND FinEvent=" . StrSafe_DB($eventCode) . "
			AND FinArrowPosition IS NOT NULL AND FinArrowPosition!=''");
	}
	while($q and $r = safe_fetch($q)) {
		$pos = json_decode(strval($r->apos), true);
		if(!is_array($pos)) continue;
		$tie = $r->tpos ? json_decode(strval($r->tpos), true) : null;
		if(is_array($tie)) $pos = array_merge($pos, $tie);
		$scoreStr = rtrim(strval($r->astr)) . rtrim(strval($r->tstr));
		$astr = trim(strval($r->astr));
		$entry = array('positions' => $pos, 'scores' => $scoreStr);
		$map['m' . intval($r->mno) . '|' . $astr] = $entry;
		if($astr !== '' and !isset($map['a|' . $astr])) $map['a|' . $astr] = $entry;
	}
	return $map;
}

// Impacts of one side: {x,y} normalised to the outer radius (−1..1).
function rfsSideImpacts($matchNo, $arrowString, $positions, $radiusMm) {
	if($radiusMm <= 0) return array();
	$astr = trim(strval($arrowString));
	if($astr === '') return array();
	$hit = rfsVal($positions, 'm' . intval($matchNo) . '|' . $astr);
	if(!is_array($hit)) $hit = rfsVal($positions, 'a|' . $astr);
	if(!is_array($hit)) return array();
	$scores = strval(rfsVal($hit, 'scores', ''));
	$impacts = array();
	foreach((array)rfsVal($hit, 'positions', array()) as $i => $p) {
		if(!is_array($p)) continue;
		$X = floatval(rfsItem($p, array('X', 'x')));
		$Y = floatval(rfsItem($p, array('Y', 'y')));
		if($X <= -1999) continue;
		$ch = isset($scores[$i]) ? $scores[$i] : '';
		$impacts[] = array(
			'x' => round($X / $radiusMm, 4),
			'y' => round($Y / $radiusMm, 4),
			'd' => round(floatval(rfsItem($p, array('D', 'd'))), 1),
			'score' => $ch !== '' ? DecodeFromLetter($ch) : null,
		);
	}
	return $impacts;
}

function rfsMapBracketSide($item, $prefix, $arrowsPerEnd, $athletes = array(), $matchNo = 0, $positions = array(), $radiusMm = 0, $jueces = array()) {
	$key = function($name) use ($prefix) {
		return $prefix ? $prefix . ucfirst($name) : $name;
	};

	$arrowString = strval(rfsItem($item, array($key('arrowstring'), $key('arrowString'))));
	$arrows = array();
	for($i = 0; $i < strlen($arrowString); $i++) {
		$arrows[] = DecodeFromLetter($arrowString[$i]);
	}
	$ends = array_chunk($arrows, max(1, $arrowsPerEnd));
	$endScores = array();
	foreach($ends as $end) {
		$sum = 0;
		foreach($end as $arrow) {
			if($arrow === 'X') $sum += 10;
			elseif(is_numeric($arrow)) $sum += intval($arrow);
		}
		$endScores[] = $sum;
	}

	$bib = rfsItem($item, array($key('localBib'), $key('localbib')));

	$teamId = rfsItem($item, array($key('teamId')));
	$subTeam = rfsItem($item, array($key('subTeam')));
	$components = rfsTeamLineup($athletes, $teamId, $subTeam);
	$tieString = strval(rfsItem($item, array($key('tiebreak'))));
	$tieArrows = array();
	for($i = 0; $i < strlen($tieString); $i++) {
		$tieArrows[] = DecodeFromLetter($tieString[$i]);
	}

	$shooting = rfsItem($item, array($key('shootingArchers')));
	$arrowByTodo = rfsArrowBy($shooting, count($arrows) + max(1, count($tieArrows)), $athletes, $teamId, $subTeam);
	$arrowBy = $arrowByTodo ? array_slice($arrowByTodo, 0, count($arrows)) : null;
	$tieBreakBy = $arrowByTodo ? array_slice($arrowByTodo, count($arrows)) : null;
	if($tieBreakBy !== null) {
		$hay = false;
		foreach($tieBreakBy as $b) { if($b !== null) { $hay = true; break; } }
		if(!$hay) $tieBreakBy = null;
	}
	if($arrowBy !== null) {
		$hay = false;
		foreach($arrowBy as $b) { if($b !== null) { $hay = true; break; } }
		if(!$hay) $arrowBy = null;
	}

	return array(
		'bib' => $bib,
		'license' => $bib,
		'familyName' => rfsItem($item, array($key('familyName'), $key('familyname'))),
		'givenName' => rfsItem($item, array($key('givenName'), $key('givenname'))),
		'gender' => rfsItem($item, array($key('gender'))),
		'clubCode' => rfsItem($item, array($key('countryCode'))),
		'clubName' => rfsItem($item, array($key('countryName'))),
		'target' => rfsItem($item, array($key('target'))),
		'score' => rfsItem($item, array($key('score'))),
		'setScore' => rfsItem($item, array($key('setScore'))),
		'setPoints' => rfsItem($item, array($key('setPoints'))),
		'setPointsByEnd' => rfsItem($item, array($key('setPointsByEnd'))),
		'arrows' => $arrows,
		'ends' => $ends,
		'endScores' => $endScores,
		'components' => $components,
		'arrowBy' => $arrowBy,
		'impacts' => rfsSideImpacts($matchNo, $arrowString, $positions, $radiusMm),
		'tieBreak' => rfsItem($item, array($key('tiebreakDecoded'))),
		'tieBreakArrows' => count($tieArrows) ? $tieArrows : null,
		'tieBreakBy' => $tieBreakBy,
		'coach' => rfsNombre($item, $key('coach'), $key('coachFamName'), $key('coachGivName')),
		'lineJudge' => rfsVal($jueces, 'line'),
		'targetJudge' => rfsVal($jueces, 'target'),
		'winner' => (bool)rfsItem($item, array($key('winner'))),
		'irm' => rfsItem($item, array($key('irmText'))),
	);
}

function rfsNombre($item, $full, $famName = null, $givName = null) {
	$nombre = trim(strval(rfsItem($item, array($full))));
	if($nombre === '' and $famName !== null) {
		$fam = trim(strval(rfsItem($item, array($famName))));
		$giv = trim(strval(rfsItem($item, array($givName))));
		$nombre = trim($fam . ' ' . $giv);
	}
	return $nombre !== '' ? $nombre : null;
}

function rfsMapRobinSide($item, $prefix, $arrowsPerEnd) {
	$key = function($name) use ($prefix) {
		return $prefix ? $prefix . ucfirst($name) : $name;
	};

	$arrowString = strval(rfsItem($item, array($key('arrowstring'), $key('arrowString'))));
	$arrows = array();
	for($i = 0; $i < strlen($arrowString); $i++) {
		$arrows[] = DecodeFromLetter($arrowString[$i]);
	}

	$bib = rfsItem($item, array($key('localBib'), $key('localbib'), $key('bib')));
	return array(
		'bib' => $bib,
		'license' => $bib,
		'athlete' => rfsItem($item, array($key('athlete'))),
		'familyName' => rfsItem($item, array($key('familyName'), $key('familyname'))),
		'givenName' => rfsItem($item, array($key('givenName'), $key('givenname'))),
		'gender' => rfsItem($item, array($key('gender'))),
		'clubCode' => rfsItem($item, array($key('countryCode'))),
		'clubName' => rfsItem($item, array($key('countryName'))),
		'target' => rfsItem($item, array($key('target'))),
		'score' => rfsItem($item, array($key('score'))),
		'setScore' => rfsItem($item, array($key('setScore'))),
		'setPoints' => rfsItem($item, array($key('setPoints'))),
		'setPointsByEnd' => rfsItem($item, array($key('setPointsByEnd'))),
		'points' => rfsInt(rfsItem($item, array($key('points')))),
		'arrows' => $arrows,
		'ends' => array_chunk($arrows, max(1, $arrowsPerEnd)),
		'winner' => (bool)rfsItem($item, array($key('winner'))),
		'tie' => (bool)rfsItem($item, array($key('tie'))),
		'tieBreak' => rfsItem($item, array($key('tiebreakDecoded'))),
		'irm' => rfsItem($item, array($key('irmText'))),
		'itemId' => rfsItem($item, array($key('itemId'))),
	);
}

function rfsBuildRoundRobinBlock($event) {
	$type = $event['teamEvent'] ? 1 : 0;
	$options = array(
		'tournament' => intval($_SESSION['TourId']),
		'events' => array($event['code']),
		'team' => $type,
	);
	$rank = Obj_RankFactory::create('Robin', $options);
	$rank->read();
	$data = $rank->getData();

	$levels = array();
	foreach((array)rfsVal($data, 'sections', array()) as $section) {
		$meta = (array)rfsVal($section, 'meta', array());
		$matchMode = intval(rfsVal($meta, 'matchMode', 0)) == 1 ? 'sets' : 'cumulative';

		foreach((array)rfsVal($section, 'levels', array()) as $levelNo => $level) {
			$arrowsPerEnd = max(1, intval(rfsVal($level, 'arrows', 3)));
			$allMatches = (array)rfsVal($level, 'matches', array());

			$licenses = array();
			foreach($allMatches as $groupMatches) {
				foreach((array)rfsVal($groupMatches, 'rounds', array()) as $round) {
					foreach((array)rfsVal($round, 'items', array()) as $item) {
						foreach(array(array('itemId', 'localBib'), array('oppItemId', 'oppLocalBib')) as $pair) {
							$id = rfsItem($item, array($pair[0]));
							$lic = rfsItem($item, array($pair[1]));
							if($id !== null and strval($lic) !== '') $licenses[strval($id)] = $lic;
						}
					}
				}
			}

			$groups = array();
			foreach((array)rfsVal($level, 'ranks', array()) as $groupKey => $group) {
				$groupNo = intval(ltrim(strval($groupKey), 'g'));

				$played = array(); $won = array(); $tied = array();
				foreach((array)rfsVal(rfsVal($allMatches, $groupKey, array()), 'rounds', array()) as $round) {
					foreach((array)rfsVal($round, 'items', array()) as $item) {
						$lId = strval(rfsItem($item, array('itemId')));
						$rId = strval(rfsItem($item, array('oppItemId')));
						if($lId === '' or $lId === '0' or $rId === '' or $rId === '0') continue;
						$lScore = strval(rfsItem($item, array('score')));
						$rScore = strval(rfsItem($item, array('oppScore')));
						$lWin = (bool)rfsItem($item, array('winner'));
						$rWin = (bool)rfsItem($item, array('oppWinner'));
						if($lScore === '' and $rScore === '' and !$lWin and !$rWin) continue;
						$lPts = intval(rfsItem($item, array('points')));
						$rPts = intval(rfsItem($item, array('oppPoints')));
						$draw = !$lWin && !$rWin && $lPts > 0 && $lPts === $rPts;
						foreach(array(array($lId, $lWin, $lPts > $rPts), array($rId, $rWin, $rPts > $lPts)) as $side) {
							if($side[0] === '') continue;
							$played[$side[0]] = rfsVal($played, $side[0], 0) + 1;
							if($draw) $tied[$side[0]] = rfsVal($tied, $side[0], 0) + 1;
							elseif($side[1] or $side[2]) $won[$side[0]] = rfsVal($won, $side[0], 0) + 1;
						}
					}
				}

				$standings = array();
				foreach((array)rfsVal($group, 'items', array()) as $item) {
					$id = strval(rfsItem($item, array('id')));
					$p = intval(rfsVal($played, $id, 0));
					$w = intval(rfsVal($won, $id, 0));
					$t = intval(rfsVal($tied, $id, 0));
					$license = $event['teamEvent'] ? null : rfsVal($licenses, $id);
					$clubName = $event['teamEvent'] ? null : rfsItem($item, array('countryName'));
					$standings[] = array(
						'rank' => rfsInt(rfsItem($item, array('rank'))),
						'athlete' => rfsItem($item, array('athlete')),
						'bib' => $license,
						'license' => $license,
						'subTeam' => rfsInt(rfsItem($item, array('subteam'))),
						'clubName' => $clubName,
						'points' => rfsInt(rfsItem($item, array('score'))),
						'tieBreaker' => rfsInt(rfsItem($item, array('tieBreaker'))),
						'tieBreaker2' => rfsInt(rfsItem($item, array('tieBreaker2'))),
						'played' => $p,
						'won' => $w,
						'tied' => $t,
						'lost' => max(0, $p - $w - $t),
						'qualified' => (bool)rfsItem($item, array('qualified')),
						'irm' => rfsItem($item, array('irmText')),
						'tieBreak' => rfsItem($item, array('tieDecoded')),
					);
				}

				$rounds = array();
				foreach((array)rfsVal(rfsVal($allMatches, $groupKey, array()), 'rounds', array()) as $roundNo => $round) {
					$matches = array();
					foreach((array)rfsVal($round, 'items', array()) as $item) {
						$lId = strval(rfsItem($item, array('itemId')));
						$rId = strval(rfsItem($item, array('oppItemId')));
						if($lId === '' or $lId === '0' or $rId === '' or $rId === '0') continue;
						$scheduledDate = trim(strval(rfsItem($item, array('scheduledDate'))));
						$scheduledTime = trim(strval(rfsItem($item, array('scheduledTime'))));
						$scheduledAt = ($scheduledDate !== '' and $scheduledDate !== '0000-00-00')
							? trim($scheduledDate . ' ' . substr($scheduledTime, 0, 5))
							: null;
						$matches[] = array(
							'matchNo' => rfsInt(rfsItem($item, array('matchNo'))),
							'scheduledAt' => $scheduledAt,
							'left' => rfsMapRobinSide($item, '', $arrowsPerEnd),
							'right' => rfsMapRobinSide($item, 'opp', $arrowsPerEnd),
						);
					}
					$rounds[] = array(
						'round' => intval(rfsVal($round, 'id', $roundNo)),
						'name' => rfsVal($round, 'name'),
						'matches' => $matches,
					);
				}

				$groups[] = array(
					'group' => $groupNo,
					'name' => rfsVal($group, 'name'),
					'standings' => $standings,
					'rounds' => $rounds,
				);
			}

			if(!$groups) continue;
			$levels[] = array(
				'level' => intval($levelNo),
				'name' => rfsVal($level, 'name'),
				'qualifiedNo' => rfsInt(rfsVal($level, 'qualifiedNo')),
				'matchMode' => $matchMode,
				'tiebreaker' => rfsVal($level, 'tiebreaker'),
				'tiebreaker2' => rfsVal($level, 'tiebreaker2'),
				'groups' => $groups,
			);
		}
	}

	if(!$levels) return null;
	return rfsMakeBlock('roundrobin', 'robin:' . $event['code'] . ':' . $type, array('levels' => $levels), $event['code'], $event['teamEvent']);
}

function rfsBuildBracketsBlock($event, $target = null) {
	$type = $event['teamEvent'] ? 1 : 0;
	$options = array('tournament' => intval($_SESSION['TourId']), 'events' => array($event['code']));
	if($event['teamEvent']) {
		$options['extended'] = true;
	}
	$rank = Obj_RankFactory::create($event['teamEvent'] ? 'GridTeam' : 'GridInd', $options);
	$rank->read();
	$data = $rank->getData();

	// Arrow impacts (spotted finals)
	$radiusMm = is_array($target) ? floatval(rfsVal($target, 'radiusMm', 0)) : 0;
	$positions = $radiusMm > 0 ? rfsFinalPositions($event['code'], $event['teamEvent']) : array();

	$matches = array();
	foreach((array)rfsVal($data, 'sections', array()) as $section) {
		$meta = (array)rfsVal($section, 'meta', array());
		$arrowsPerEnd = max(1, intval(rfsVal($meta, 'finArrows', 3)));
		$athletes = (array)rfsVal($section, 'athletes', array());
		$cntPhase = 1;
		foreach((array)rfsVal($section, 'phases', array()) as $phaseCode => $phase) {
			$phaseName = rfsPhaseName($phaseCode, $cntPhase);
			foreach((array)rfsVal($phase, 'items', array()) as $item) {
				$scheduledDate = trim(strval(rfsItem($item, array('scheduledDate'))));
				$scheduledTime = trim(strval(rfsItem($item, array('scheduledTime'))));
				$scheduledAt = ($scheduledDate !== '' and $scheduledDate !== '0000-00-00')
					? trim($scheduledDate . ' ' . substr($scheduledTime, 0, 5))
					: null;

				$matchNo = intval(rfsItem($item, array('matchNo')));
				// Judges are assigned by the finals schedule (FinSchedule)
				$jueces = array(
					'line' => rfsNombre($item, 'lineJudge', 'lineFamName', 'lineGivName'),
					'target' => rfsNombre($item, 'targetJudge', 'targetFamName', 'targetGivName'),
				);
				$matches[] = array(
					'phaseCode' => strval($phaseCode),
					'phaseName' => $phaseName,
					'matchNo' => $matchNo,
					'scheduledAt' => $scheduledAt,
					'left' => rfsMapBracketSide($item, '', $arrowsPerEnd, $athletes, $matchNo, $positions, $radiusMm, $jueces),
					'right' => rfsMapBracketSide($item, 'opp', $arrowsPerEnd, $athletes, $matchNo, $positions, $radiusMm, $jueces),
				);
			}
			$cntPhase++;
		}
	}
	return rfsMakeBlock('brackets', 'brackets:' . $event['code'] . ':' . $type, $matches, $event['code'], $event['teamEvent']);
}

function rfsBracketStats($matches) {
	if(!is_array($matches) or !count($matches)) return null;
	$firstCode = null;
	foreach($matches as $m) {
		$c = intval(rfsVal($m, 'phaseCode'));
		if($firstCode === null or $c > $firstCode) $firstCode = $c;
	}
	$named = function($s) {
		return is_array($s) and (trim(strval(rfsVal($s, 'familyName', ''))) !== '' or trim(strval(rfsVal($s, 'clubName', ''))) !== '');
	};
	$m = 0; $byes = 0; $entries = 0;
	foreach($matches as $match) {
		if(intval(rfsVal($match, 'phaseCode')) !== $firstCode) continue;
		$l = $named(rfsVal($match, 'left'));
		$r = $named(rfsVal($match, 'right'));
		if($l) $entries++;
		if($r) $entries++;
		if($l and $r) $m++;
		elseif($l or $r) $byes++;
	}
	return array('phase' => strval($firstCode), 'matches' => $m, 'byes' => $byes, 'entries' => $entries);
}

function rfsStatFromIanseo($it, $team) {
	$firstNum = function($v) { return preg_match('/-?\d+/', strval($v), $m) ? intval($m[0]) : null; };
	$sumNums = function($v) { return preg_match_all('/\d+/', strval($v), $m) ? array_sum($m[0]) : 0; };
	$matches = strval(rfsVal($it, 'Matches', ''));
	return array(
		'entries' => intval(rfsVal($it, 'Number', 0)),
		'matches' => ($matches === '' ? null : $firstNum($matches)),
		'byes' => $sumNums(rfsVal($it, 'Byes', '')),
		'in' => $firstNum(rfsVal($it, 'ArchersIn', '')),
		'out' => $sumNums(rfsVal($it, 'ArchersOut', '')),
		'phaseLabel' => strval($team ? rfsVal($it, 'FirstPhase', '') : rfsVal($it, 'Phase', '')),
	);
}

function rfsEntryStatsMap() {
	static $map = null;
	if($map !== null) return $map;
	$map = array();
	require_once('Common/Fun_FormatText.inc.php');
	require_once('Common/OrisFunctions.php');
	if(!function_exists('getStatEntriesByEvent')) return $map;
	$Data = getStatEntriesByEvent('');
	foreach((array)rfsVal(rfsVal($Data->Data, 'IF', array()), 'Data', array()) as $code => $it) {
		$map['0|' . trim(strval($code))] = rfsStatFromIanseo($it, false);
	}
	foreach((array)rfsVal(rfsVal($Data->Data, 'TF', array()), 'Data', array()) as $code => $it) {
		$map['1|' . trim(strval($code))] = rfsStatFromIanseo($it, true);
	}
	return $map;
}

function rfsRobinStats($data) {
	$levels = (array)rfsVal($data, 'levels', array());
	if(!count($levels)) return null;
	$lvl = $levels[0];
	$entries = 0; $matches = 0;
	foreach((array)rfsVal($lvl, 'groups', array()) as $g) {
		$entries += count((array)rfsVal($g, 'standings', array()));
		foreach((array)rfsVal($g, 'rounds', array()) as $r) {
			$matches += count((array)rfsVal($r, 'matches', array()));
		}
	}
	return array('phase' => 'robin', 'matches' => $matches, 'byes' => 0, 'entries' => $entries);
}

function rfsCollectBlocks() {
	$blocks = array();
	$blocks[] = rfsBuildInfoBlock();
	$blocks[] = rfsBuildScheduleBlock();
	$blocks[] = rfsBuildEntriesBlock();
	list($eventItems, $eventList) = rfsEventsData();
	$statsMap = rfsEntryStatsMap();

	foreach($eventList as $ix => $event) {
		rfsKeepAlive();
		$qual = rfsBuildQualificationBlocks($event);
		foreach($qual['blocks'] as $block) {
			$blocks[] = $block;
		}
		if($qual['qualFormat'] !== null) {
			$eventItems[$ix]['format']['qualification'] = $qual['qualFormat'];
		}
		$sk = ($event['teamEvent'] ? '1|' : '0|') . $event['code'];
		$stats = isset($statsMap[$sk]) ? $statsMap[$sk] : null;
		if(!empty($event['roundRobin'])) {
			$robin = rfsBuildRoundRobinBlock($event);
			if($robin !== null) {
				$blocks[] = $robin;
				if($stats === null) $stats = rfsRobinStats($robin['data']);
			}
		}
		if(!empty($event['hasFinals'])) {
			$target = rfsFinalTarget($event);
			if($target !== null) $eventItems[$ix]['format']['finalTarget'] = $target;
			$blocks[] = rfsBuildFinalBlock($event);
			$bracketBlock = rfsBuildBracketsBlock($event, $target);
			$blocks[] = $bracketBlock;
			if($stats === null) $stats = rfsBracketStats($bracketBlock['data']);
		}
		if($stats !== null) {
			if(!isset($eventItems[$ix]['format']) or !is_array($eventItems[$ix]['format'])) $eventItems[$ix]['format'] = array();
			$eventItems[$ix]['format']['entryStats'] = $stats;
		}
		$lineup = rfsLineupChanges($event);
		if($lineup !== null) {
			if(!isset($eventItems[$ix]['format']) or !is_array($eventItems[$ix]['format'])) $eventItems[$ix]['format'] = array();
			$eventItems[$ix]['format']['lineupChanges'] = $lineup;
		}
	}

	$blocks[] = rfsMakeBlock('events', 'events', $eventItems);
	return $blocks;
}

function rfsMediaManifest() {
	$manifest = array();
	$q = safe_r_sql("SELECT FlCode, MD5(FlJPG) as h FROM Flags
		WHERE FlTournament=" . intval($_SESSION['TourId']) . " AND FlJPG!=''");
	while($r = safe_fetch($q)) {
		$manifest['club:' . trim($r->FlCode)] = strval($r->h);
	}
	return $manifest;
}

function rfsDecodeImage($blob) {
	$b = ltrim(strval($blob));
	if($b === '') return '';
	if(strncmp($b, 'data:', 5) === 0) {
		$comma = strpos($b, ',');
		if($comma !== false) $b = substr($b, $comma + 1);
		$d = base64_decode($b, true);
		return $d !== false ? $d : $blob;
	}
	if(preg_match('#^(/9j/|iVBORw0|R0lGOD|Qk)#', $b)) {
		$d = base64_decode($b, true);
		if($d !== false and strlen($d) > 100) return $d;
	}
	return $blob;
}

function rfsMediaBlob($kind, $refKey) {
	if($kind == 'athlete') {
		$q = safe_r_sql("SELECT PhPhoto as img FROM Photos
			INNER JOIN Entries ON PhEnId=EnId
			WHERE EnTournament=" . intval($_SESSION['TourId']) . " AND EnCode=" . StrSafe_DB($refKey));
	} else {
		$q = safe_r_sql("SELECT FlJPG as img FROM Flags
			WHERE FlTournament=" . intval($_SESSION['TourId']) . " AND FlCode=" . StrSafe_DB($refKey));
	}
	$r = safe_fetch($q);
	return $r ? rfsDecodeImage($r->img) : '';
}

// Uploads the new/changed images. Returns array(sent, unchanged, errors[])
function rfsSyncMedia($url, $token, $force = false) {
	$store = rfsLoadStore();
	$known = $force ? array() : $store['mediaHashes'];
	$manifest = rfsMediaManifest();

	$pending = array();
	$unchanged = 0;
	foreach($manifest as $key => $hash) {
		if(isset($known[$key]) and $known[$key] === $hash) {
			$unchanged++;
		} else {
			$pending[] = $key;
		}
	}

	$sent = 0;
	$errors = array();
	$upBytes = 0;
	$downBytes = 0;
	$stored = $force ? array() : $store['mediaHashes'];
	$path = '/api/sync/competitions/' . rawurlencode($_SESSION['TourCode']) . '/media';

	// Build the chunks (12 images each), reading each blob from the DB once.
	$chunks = array();
	foreach(array_chunk($pending, 12) as $chunk) {
		$items = array();
		foreach($chunk as $key) {
			list($kind, $refKey) = explode(':', $key, 2);
			$blob = rfsMediaBlob($kind, $refKey);
			if($blob === '') continue;
			$items[] = array(
				'kind' => $kind,
				'refKey' => $refKey,
				'hash' => $manifest[$key],
				'data' => base64_encode($blob),
			);
		}
		if(count($items)) $chunks[] = $items;
	}

	// Upload the chunks concurrently
	$requests = array();
	foreach($chunks as $items) {
		$requests[] = array('method' => 'POST', 'path' => $path, 'payload' => array('items' => $items));
	}
	$results = rfsRemoteMulti($url, $requests, $token);
	foreach($results as $ci => $result) {
		$upBytes += $result['upBytes'];
		$downBytes += $result['downBytes'];
		if($result['ok']) {
			// The server reports the status of each image
			$statusByKey = array();
			foreach((array)rfsVal(rfsVal($result['response'], 'data', array()), 'items', array()) as $ri) {
				$statusByKey[strval(rfsVal($ri, 'key'))] = strval(rfsVal($ri, 'status'));
			}
			foreach($chunks[$ci] as $item) {
				$k = $item['kind'] . ':' . $item['refKey'];
				$st = isset($statusByKey[$k]) ? $statusByKey[$k] : 'applied';
				if($st === 'error') {
					$errors[] = 'No se pudo guardar la imagen ' . $k;
					continue;
				}
				$stored[$k] = $item['hash'];
				if($st !== 'unchanged') $sent++;
			}
		} else {
			$errors[] = rfsRemoteErrorMessage($result, 'Error subiendo imágenes');
		}
	}
	if($sent or $force) {
		rfsUpdateStore(array('mediaHashes' => count($stored) ? $stored : array()));
	}
	return array('sent' => $sent, 'unchanged' => $unchanged, 'errors' => $errors, 'upBytes' => $upBytes, 'downBytes' => $downBytes);
}

function rfsCurlHandle($url, $method, $path, $payload = null, $token = '', $timeoutMs = 0, $extraHeaders = array()) {
	$ch = curl_init(rtrim($url, '/') . $path);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	/*
		En macOS, resolver un nombre por IPv4 e IPv6 a la vez toca Objective-C, y
		el sistema mata cualquier proceso que lo haga después de un fork. php-cgi
		se bifurca para atender peticiones, así que el envío tumbaba al trabajador
		y el navegador recibía un error 500 en lugar de la respuesta. Pidiendo
		solo IPv4 no se pasa por ahí.
	*/
	if(PHP_OS_FAMILY === 'Darwin') {
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	}
	// Sin red, mejor saberlo en 10 s que esperar dos minutos
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, RFS_CONNECT_TIMEOUT);
	if($timeoutMs > 0) {
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeoutMs);
		curl_setopt($ch, CURLOPT_NOSIGNAL, true);
	} else {
		curl_setopt($ch, CURLOPT_TIMEOUT, RFS_REMOTE_TIMEOUT);
	}
	curl_setopt($ch, CURLOPT_NOPROGRESS, false);
	curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function() {
		rfsKeepAlive();
		return 0;
	});
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($ch, CURLOPT_ENCODING, '');
	// Para que el servidor sepa con qué versión habla
	$headers = array(
		'X-Rfs-Module: ' . RFS_VERSION,
		'X-Ianseo-Version: ' . (defined('ProgramVersion') ? ProgramVersion : '') . (defined('ProgramBuild') ? ' ' . ProgramBuild : ''),
	);
	if($token !== '') {
		$headers[] = 'X-Sync-Token: ' . $token;
	}
	foreach((array)$extraHeaders as $h) {
		$headers[] = $h;
	}
	$upBytes = 0;
	if($payload !== null) {
		$body = json_encode($payload, RFS_JSON);
		$headers[] = 'Content-Type: application/json';
		if(strlen($body) > 1024) {
			$level = (strpos($path, '/media') !== false) ? 1 : 6;
			$body = gzencode($body, $level);
			$headers[] = 'Content-Encoding: gzip';
		}
		$upBytes = strlen($body);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	}
	if($headers) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	return array($ch, $upBytes);
}

function rfsCurlResult($ch, $body, $upBytes, $errno = 0) {
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$downBytes = intval(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
	$error = curl_error($ch);
	// curl_error() va vacío en las peticiones en paralelo: el motivo llega aparte
	if($error === '' and $errno) $error = curl_strerror($errno);
	$decoded = json_decode(strval($body), true);
	return array(
		'ok' => ($body !== false and $body !== null and $httpCode >= 200 and $httpCode < 300),
		'status' => $httpCode,
		'error' => $error,
		'response' => is_array($decoded) ? $decoded : null,
		'upBytes' => $upBytes,
		'downBytes' => $downBytes,
	);
}

function rfsRemoteRequest($url, $method, $path, $payload = null, $token = '') {
	list($ch, $upBytes) = rfsCurlHandle($url, $method, $path, $payload, $token);
	$body = curl_exec($ch);
	$result = rfsCurlResult($ch, $body, $upBytes);
	curl_close($ch);
	return $result;
}

function rfsRemoteBestEffort($url, $method, $path, $payload = null, $token = '', $timeoutMs = 1500) {
	list($ch, ) = rfsCurlHandle($url, $method, $path, $payload, $token, $timeoutMs);
	curl_exec($ch);
	curl_close($ch);
}

function rfsRemoteMulti($url, $requests, $token = '', $concurrency = 4) {
	$out = array();
	if(!count($requests)) return $out;
	foreach(array_chunk($requests, max(1, $concurrency), true) as $wave) {
		$mh = curl_multi_init();
		$handles = array();
		$ups = array();
		foreach($wave as $i => $req) {
			list($ch, $up) = rfsCurlHandle($url, $req['method'], $req['path'], isset($req['payload']) ? $req['payload'] : null, $token);
			$handles[$i] = $ch;
			$ups[$i] = $up;
			curl_multi_add_handle($mh, $ch);
		}
		$running = null;
		$errnos = array();
		do {
			curl_multi_exec($mh, $running);
			if($running > 0) curl_multi_select($mh, 1.0);
			while($info = curl_multi_info_read($mh)) {
				$errnos[(int)array_search($info['handle'], $handles, true)] = $info['result'];
			}
			rfsKeepAlive();
		} while($running > 0);
		foreach($handles as $i => $ch) {
			$out[$i] = rfsCurlResult($ch, curl_multi_getcontent($ch), $ups[$i], isset($errnos[$i]) ? $errnos[$i] : 0);
			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
		}
		curl_multi_close($mh);
	}
	ksort($out);
	return $out;
}

function rfsRemoteErrorMessage($result, $fallback = 'Error de conexión con el servidor RFETA') {
	if(!empty($result['response']['error']['message'])) {
		return $result['response']['error']['message'];
	}
	if(!empty($result['error'])) {
		return (intval($result['status']) === 0 ? 'Sin conexión con el servidor RFETA: ' : '') . $result['error'];
	}
	return $fallback . ' (HTTP ' . $result['status'] . ')';
}

function rfsSyncPass($force = false, $forceMedia = null) {
	if($forceMedia === null) {
		$forceMedia = $force;
	}
	$started = microtime(true);
	$store = rfsLoadStore();
	$url = $store['settings']['url'];
	$token = $store['auth']['token'];

	if($url === '') {
		return array('ok' => false, 'message' => 'Falta configurar la URL del servidor RFETA');
	}
	if($token === '') {
		return array('ok' => false, 'message' => 'No hay conexión activa: conecta con la contraseña de la competición', 'needsLogin' => true);
	}

	$blocks = rfsCollectBlocks();
	$hashes = $force ? array() : $store['hashes'];

	$changed = array();
	$unchangedCount = 0;
	foreach($blocks as $block) {
		if(isset($hashes[$block['key']]) and $hashes[$block['key']] === $block['hash']) {
			$unchangedCount++;
		} else {
			$changed[] = $block;
		}
	}

	usort($changed, function($a, $b) {
		return ($a['kind'] == 'info' ? -1 : ($b['kind'] == 'info' ? 1 : 0));
	});

	$sentKeys = array();
	$errors = array();
	$needsLogin = false;
	$needsRegister = false;
	$upBytes = 0;
	$downBytes = 0;
	if(count($changed)) {
		$storedHashes = $force ? array() : $store['hashes'];
		$batches = array_values(array_chunk($changed, RFS_BATCH_SIZE));
		$path = '/api/sync/competitions/' . rawurlencode($_SESSION['TourCode']);
		$requests = array();
		foreach($batches as $batch) {
			$requests[] = array('method' => 'PUT', 'path' => $path, 'payload' => array('blocks' => array_values($batch)));
		}
		$results = rfsRemoteMulti($url, $requests, $token);
		foreach($results as $bi => $result) {
			$upBytes += $result['upBytes'];
			$downBytes += $result['downBytes'];
			if($result['ok']) {
				foreach($batches[$bi] as $block) {
					$storedHashes[$block['key']] = $block['hash'];
					$sentKeys[] = $block['key'];
				}
			} else {
				if($result['status'] == 401) $needsLogin = true;
				if($result['status'] == 404) $needsRegister = true;
				$errors[] = rfsRemoteErrorMessage($result, 'Error subiendo datos');
			}
		}
		rfsUpdateStore(array(
			'hashes' => $storedHashes,
			'transfer' => array(
				'totalUp' => intval($store['transfer']['totalUp']) + $upBytes,
				'totalDown' => intval($store['transfer']['totalDown']) + $downBytes,
			),
		));
	}

	if($needsRegister) {
		rfsUpdateStore(array('auth' => array('token' => '', 'status' => '', 'blockedReason' => '')));
	}

	$media = array('sent' => 0, 'unchanged' => 0, 'errors' => array());
	if(!count($errors)) {
		$media = rfsSyncMedia($url, $token, $forceMedia);
		$errors = array_merge($errors, $media['errors']);
		$mUp = intval(rfsVal($media, 'upBytes', 0));
		$mDown = intval(rfsVal($media, 'downBytes', 0));
		if($mUp or $mDown) {
			rfsUpdateStore(array('transfer' => array(
				'totalUp' => intval($store['transfer']['totalUp']) + $upBytes + $mUp,
				'totalDown' => intval($store['transfer']['totalDown']) + $downBytes + $mDown,
			)));
			$upBytes += $mUp;
			$downBytes += $mDown;
		}
	}

	$summary = array(
		'ok' => count($errors) == 0,
		'message' => count($errors)
			? implode('; ', $errors)
			: ((count($sentKeys) || $media['sent'])
				? trim((count($sentKeys) ? 'Enviados ' . count($sentKeys) . ' bloques' : '') . ($media['sent'] ? ' + ' . $media['sent'] . ' imágenes' : ''), ' +')
				: 'Sin cambios'),
		'mediaSent' => $media['sent'],
		'needsLogin' => $needsLogin,
		'needsRegister' => $needsRegister,
		'total' => count($blocks),
		'sent' => count($sentKeys),
		'unchanged' => $unchangedCount,
		'sentKeys' => $sentKeys,
		'errors' => $errors,
		'upBytes' => $upBytes,
		'downBytes' => $downBytes,
		'totalUp' => intval($store['transfer']['totalUp']) + $upBytes,
		'totalDown' => intval($store['transfer']['totalDown']) + $downBytes,
		'elapsed' => round(microtime(true) - $started, 2),
		'when' => date('Y-m-d H:i:s'),
	);
	rfsUpdateStore(array('lastSummary' => $summary));

	if($token !== '' and !$needsRegister and (count($sentKeys) or $media['sent'] or count($errors))) {
		rfsRemoteBestEffort($url, 'POST', '/api/sync/competitions/' . rawurlencode($_SESSION['TourCode']) . '/log', array(
			'action' => 'sync',
			'bytesUp' => $upBytes,
			'bytesDown' => $downBytes,
			'elapsedMs' => intval(round(floatval($summary['elapsed']) * 1000)),
			'blocksSent' => count($sentKeys) + intval($media['sent']),
			'ok' => count($errors) == 0,
			'message' => count($errors) ? substr(implode('; ', $errors), 0, 500) : null,
		), $token);
	}
	return $summary;
}
