<?php

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

if(isset($_SESSION['TourId']) and intval($_SESSION['TourId']) > 0) {
	checkFullACL(AclInternetPublish, 'ipSend', AclReadWrite);
}

@set_time_limit(600);

$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$key = strval($_POST['key'] ?? '');
$store = rfsLoadStore();
$url = $store['settings']['url'];

if($url === '') {
	JsonOut(array('ok' => false, 'message' => 'Falta configurar la URL del servidor RFETA'));
}

function rfsMasterRequest($url, $method, $path, $payload, $key) {
	list($ch, $upBytes) = rfsCurlHandle($url, $method, $path, $payload, '', 0, array('X-Master-Key: ' . $key));
	$body = curl_exec($ch);
	$result = rfsCurlResult($ch, $body, $upBytes);
	curl_close($ch);
	return $result;
}

switch($action) {
	case 'config': {
		$result = rfsRemoteRequest($url, 'GET', '/api/sync/config');
		if($result['ok']) {
			JsonOut(array('ok' => true, 'config' => rfsVal($result['response'], 'data')));
		}
		JsonOut(array('ok' => false, 'message' => rfsRemoteErrorMessage($result)));
		break;
	}

	case 'exists': {
		$codes = array_values(array_filter(array_map('trim', explode(',', strval($_POST['codes'] ?? '')))));
		if(!count($codes)) {
			JsonOut(array('ok' => true, 'competitions' => array()));
		}
		$result = rfsMasterRequest($url, 'POST', '/api/sync/master/exists', array('codes' => $codes), $key);
		if($result['ok']) {
			JsonOut(array('ok' => true, 'competitions' => rfsVal($result['response'], 'data', array())));
		}
		JsonOut(array('ok' => false, 'message' => rfsRemoteErrorMessage($result, 'No se pudo comprobar')));
		break;
	}

	case 'upload': {
		$tourId = intval($_POST['tour'] ?? 0);
		if(!$tourId) {
			JsonOut(array('ok' => false, 'message' => 'Falta la competición'));
		}
		$original = (isset($_SESSION['TourId']) and intval($_SESSION['TourId']) > 0) ? intval($_SESSION['TourId']) : 0;

		if(!CreateTourSession($tourId)) {
			JsonOut(array('ok' => false, 'message' => 'No se pudo abrir la competición en IANSEO'));
		}

		$info = rfsTournamentInfo();
		$payload = array_merge($info, array('code' => $_SESSION['TourCode'], 'name' => $_SESSION['TourName']));
		if(!empty($_POST['federation'])) $payload['federation'] = strval($_POST['federation']);
		if(!empty($_POST['discipline'])) $payload['discipline'] = strval($_POST['discipline']);
		if(!empty($_POST['recordModalityId'])) $payload['recordModalityId'] = intval($_POST['recordModalityId']);
		if(!empty($_POST['categoryId'])) $payload['categoryId'] = intval($_POST['categoryId']);
		if(!empty($_POST['leagueRoundId'])) $payload['leagueRoundId'] = intval($_POST['leagueRoundId']);
		if(isset($_POST['records'])) $payload['canBeatRecords'] = ($_POST['records'] === '1');
		if(isset($_POST['visible'])) $payload['visible'] = ($_POST['visible'] === '1');

		$alta = rfsMasterRequest($url, 'POST', '/api/sync/master/competitions', $payload, $key);
		if(!$alta['ok']) {
			if($original) CreateTourSession($original);
			JsonOut(array('ok' => false, 'message' => rfsRemoteErrorMessage($alta, 'No se pudo dar de alta')));
		}
		$data = rfsVal($alta['response'], 'data', array());
		$token = strval(rfsVal($data, 'token'));

		rfsUpdateStore(array(
			'settings' => array('url' => $url),
			'auth' => array('token' => $token, 'status' => strval(rfsVal($data, 'status', 'approved')), 'blockedReason' => ''),
		));

		$estado = rfsRemoteRequest($url, 'GET', '/api/sync/competitions/' . rawurlencode($_SESSION['TourCode']) . '/state', null, $token);
		if($estado['ok']) {
			$mediaServidor = (array)rfsVal(rfsVal($estado['response'], 'data', array()), 'media', array());
			rfsUpdateStore(array('mediaHashes' => $mediaServidor));
		}
		$summary = rfsSyncPass(true, false);

		if($original) CreateTourSession($original);

		JsonOut(array(
			'ok' => (bool)rfsVal($summary, 'ok', false),
			'message' => (rfsVal($data, 'created') ? 'Publicada y ' : 'Actualizada y ') . strtolower(strval(rfsVal($summary, 'message', ''))),
			'summary' => $summary,
		));
		break;
	}

	default:
		JsonOut(array('ok' => false, 'message' => 'Acción desconocida'));
}
