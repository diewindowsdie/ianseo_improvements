<?php

// Lo que devuelve este archivo lo lee el navegador como JSON: un aviso de PHP
// en pantalla lo rompería. Los avisos siguen yendo al log.
@ini_set('display_errors', '0');

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// Un error grave también contesta JSON, con lo que ha pasado de verdad
register_shutdown_function(function() {
	$e = error_get_last();
	if(!$e or !empty($GLOBALS['rfsRespondido'])) return;
	if(!in_array($e['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR))) return;
	$texto = trim(strtok(strval($e['message']), "\n"));
	rfsJsonOut(array('ok' => false, 'message' => 'Error interno de IANSEO: ' . substr($texto, 0, 300) . ' (' . basename($e['file']) . ':' . $e['line'] . ')'));
});

if(!CheckTourSession()) {
	JsonOut(array('ok' => false, 'needsReload' => true, 'message' => 'La sesión de IANSEO ha caducado: recarga la página y vuelve a abrir la competición'));
}
if(!hasFullACL(AclInternetPublish, 'ipSend', AclReadWrite)) {
	JsonOut(array('ok' => false, 'message' => 'Este usuario no tiene permiso para enviar a la RFETA'));
}
if(empty($_SESSION['TourLocRule']) or $_SESSION['TourLocRule'] != 'ES') {
	JsonOut(array('ok' => false, 'message' => 'Este módulo solo está disponible con las reglas españolas (RFETA)'));
}

// A partir de aquí no se escribe nada en la sesión de IANSEO. Soltarla deja
// usar el resto de IANSEO en el mismo navegador mientras se envía.
session_write_close();

$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'sync';

// A first full upload of a big competition can take a while, and on Windows
// the time spent waiting for the network also counts
@set_time_limit(600);
rfsKeepAliveStart();
$store = rfsLoadStore();
$url = $store['settings']['url'];

if($url === '' and $action != 'saveSettings') {
	rfsJsonOut(array('ok' => false, 'message' => 'Falta configurar la URL del servidor RFETA'));
}

switch($action) {
	case 'exists': {
		// Check if this competition code is already published
		$result = rfsRemoteRequest($url, 'GET', '/api/sync/competitions/' . rawurlencode($_SESSION['TourCode']) . '/exists');
		if($result['ok']) {
			$data = rfsVal($result['response'], 'data', array());
			if(rfsVal($data, 'registered')) {
				rfsUpdateStore(array('auth' => array('status' => strval(rfsVal($data, 'status')))));
			}
			rfsJsonOut(array('ok' => true, 'registered' => (bool)rfsVal($data, 'registered'), 'status' => rfsVal($data, 'status'), 'email' => rfsVal($data, 'email')));
		}
		rfsJsonOut(array('ok' => false, 'message' => rfsRemoteErrorMessage($result)));
		break;
	}

	case 'config': {
		$result = rfsRemoteRequest($url, 'GET', '/api/sync/config');
		if($result['ok']) {
			rfsJsonOut(array('ok' => true, 'config' => rfsVal($result['response'], 'data')));
		}
		rfsJsonOut(array('ok' => false, 'message' => rfsRemoteErrorMessage($result)));
		break;
	}

	case 'register': {
		$email = trim(strval($_POST['email'] ?? ''));
		$password = strval($_POST['password'] ?? '');
		$federation = strval($_POST['federation'] ?? '');
		$categoryId = intval($_POST['categoryId'] ?? 0);
		$discipline = strval($_POST['discipline'] ?? '');
		$leagueRoundId = intval($_POST['leagueRoundId'] ?? 0);
		$recordModalityId = intval($_POST['recordModalityId'] ?? 0);

		// The type is either a plain category or a league round
		if($email === '' or strlen($password) < 4 or $federation === '' or (!$categoryId and !$leagueRoundId) or $discipline === '') {
			rfsJsonOut(array('ok' => false, 'message' => 'Completa todos los campos (la contraseña necesita al menos 4 caracteres)'));
		}

		$payload = array_merge(rfsTournamentInfo(), array(
			'code' => $_SESSION['TourCode'],
			'email' => $email,
			'password' => $password,
			'federation' => $federation,
			'discipline' => $discipline,
		));
		if($recordModalityId) {
			$payload['recordModalityId'] = $recordModalityId;
		}
		if($leagueRoundId) {
			$payload['leagueRoundId'] = $leagueRoundId;
		} else {
			$payload['categoryId'] = $categoryId;
		}
		$result = rfsRemoteRequest($url, 'POST', '/api/sync/register', $payload);
		if($result['ok']) {
			rfsUpdateStore(array(
				'request' => array('email' => $email, 'federation' => $federation, 'categoryId' => $categoryId, 'discipline' => $discipline),
				'auth' => array('status' => 'pending', 'token' => '', 'blockedReason' => '', 'privileged' => in_array(strtolower($email), rfsPrivilegedEmails())),
			));

			$login = rfsRemoteRequest($url, 'POST', '/api/sync/login', array('code' => $_SESSION['TourCode'], 'password' => $password));
			if($login['ok']) {
				$data = rfsVal($login['response'], 'data', array());
				rfsUpdateStore(array('auth' => array_merge(array(
					'token' => strval(rfsVal($data, 'token')),
					'status' => strval(rfsVal($data, 'status')),
					'blockedReason' => strval(rfsVal($data, 'blockedReason')),
				), rfsServerIntervals($data))));
			}
			$guardado = rfsLoadStore();
			if($guardado['auth']['token'] === '' and $guardado['request']['email'] === '') {
				rfsJsonOut(array('ok' => false, 'message' => 'Solicitud enviada, pero ' . lcfirst(rfsStoreError())));
			}
			rfsJsonOut(array('ok' => true, 'message' => 'Solicitud enviada. Recibirás un correo cuando la RFETA la autorice', 'status' => 'pending'));
		}
		rfsJsonOut(array('ok' => false, 'message' => rfsRemoteErrorMessage($result, 'No se pudo enviar la solicitud')));
		break;
	}

	case 'login': {
		$password = strval($_POST['password'] ?? '');
		$email = trim(strval($_POST['email'] ?? ''));
		// El correo es opcional: si no se pone, no se manda. Mandarlo vacío es
		// mandar un correo que no existe, y el servidor lo rechaza.
		$payload = array('code' => $_SESSION['TourCode'], 'password' => $password);
		if($email !== '') {
			$payload['email'] = $email;
		}
		$result = rfsRemoteRequest($url, 'POST', '/api/sync/login', $payload);
		if($result['ok']) {
			$data = rfsVal($result['response'], 'data', array());
			$cambios = array('auth' => array_merge(array(
				'token' => strval(rfsVal($data, 'token')),
				'status' => strval(rfsVal($data, 'status')),
				'blockedReason' => strval(rfsVal($data, 'blockedReason')),
			), rfsServerIntervals($data)));
			if(trim($email) !== '') {
				$cambios['request'] = array('email' => trim($email));
				$cambios['auth']['privileged'] = in_array(strtolower(trim($email)), rfsPrivilegedEmails());
			}
			rfsUpdateStore($cambios);
			$guardado = rfsLoadStore();
			if($guardado['auth']['token'] === '') {
				rfsJsonOut(array('ok' => false, 'message' => rfsStoreError()));
			}
			rfsJsonOut(array('ok' => true, 'message' => 'Conexión restablecida', 'status' => rfsVal($data, 'status'), 'blockedReason' => rfsVal($data, 'blockedReason')));
		}
		rfsJsonOut(array('ok' => false, 'message' => rfsRemoteErrorMessage($result, 'No se pudo conectar')));
		break;
	}

	case 'status': {
		$token = $store['auth']['token'];
		if($token === '') {
			rfsJsonOut(array('ok' => false, 'needsLogin' => true, 'message' => 'No hay conexión activa'));
		}
		$result = rfsRemoteRequest($url, 'GET', '/api/sync/competitions/' . rawurlencode($_SESSION['TourCode']) . '/state', null, $token);
		if($result['ok']) {
			$data = rfsVal($result['response'], 'data', array());
			rfsUpdateStore(array('auth' => array_merge(array(
				'status' => strval(rfsVal($data, 'status')),
				'blockedReason' => strval(rfsVal($data, 'blockedReason')),
			), rfsServerIntervals($data))));
			rfsJsonOut(array(
				'ok' => true,
				'status' => rfsVal($data, 'status'),
				'blockedReason' => rfsVal($data, 'blockedReason'),
				'serverBlocks' => count((array)rfsVal($data, 'blocks', array())),
				'message' => 'Estado actual: ' . rfsVal($data, 'status'),
			));
		}
		if($result['status'] == 404) {
			// If the server does not know this competition reset the cached state
			rfsUpdateStore(array('auth' => array('token' => '', 'status' => '', 'blockedReason' => '')));
			rfsJsonOut(array('ok' => false, 'needsRegister' => true, 'message' => 'Esta competición no está registrada en el servidor: envía la solicitud de publicación'));
		}
		rfsJsonOut(array('ok' => false, 'needsLogin' => ($result['status'] == 401), 'message' => rfsRemoteErrorMessage($result, 'No se pudo comprobar el estado')));
		break;
	}

	case 'categories': {
		$token = $store['auth']['token'];
		if($token === '') {
			rfsJsonOut(array('ok' => false, 'needsLogin' => true, 'message' => 'No hay conexión activa'));
		}
		$result = rfsRemoteRequest($url, 'POST', '/api/sync/competitions/' . rawurlencode($_SESSION['TourCode']) . '/categories/refresh', null, $token);
		if($result['ok']) {
			$data = rfsVal($result['response'], 'data', array());
			$asked = intval(rfsVal($data, 'asked'));
			$updated = intval(rfsVal($data, 'updated'));
			$missing = (array)rfsVal($data, 'missing', array());
			$message = $asked
				? 'Categorías reales actualizadas: ' . $updated . ' de ' . $asked . ' licencias'
				: 'No hay inscritos con licencia en el servidor todavía';
			if(count($missing)) {
				$message .= ' (sin encontrar: ' . implode(', ', array_slice($missing, 0, 10)) . (count($missing) > 10 ? '…' : '') . ')';
			}
			rfsJsonOut(array('ok' => true, 'message' => $message));
		}
		rfsJsonOut(array('ok' => false, 'needsLogin' => ($result['status'] == 401), 'message' => rfsRemoteErrorMessage($result, 'No se pudieron actualizar las categorías')));
		break;
	}

	case 'resetRequest': {
		// El correo escrito en el formulario manda; si no hay, el de la solicitud
		$email = trim(strval($_POST['email'] ?? ''));
		if($email === '') $email = trim(strval($store['request']['email']));
		if($email === '') {
			rfsJsonOut(array('ok' => false, 'message' => 'Escribe en «Correo electrónico» el correo con el que se publicó la competición y vuelve a pulsar «Enviar código al correo»'));
		}
		$result = rfsRemoteRequest($url, 'POST', '/api/sync/reset/request', array('code' => $_SESSION['TourCode'], 'email' => $email));
		rfsJsonOut(array('ok' => $result['ok'], 'message' => $result['ok']
			? 'Si los datos son correctos, recibirás un correo con el código de restablecimiento'
			: rfsRemoteErrorMessage($result)));
		break;
	}

	case 'resetConfirm': {
		$token = trim(strval($_POST['token'] ?? ''));
		$password = strval($_POST['password'] ?? '');
		$result = rfsRemoteRequest($url, 'POST', '/api/sync/reset/confirm', array('code' => $_SESSION['TourCode'], 'token' => $token, 'password' => $password));
		if($result['ok']) {
			rfsUpdateStore(array('auth' => array('token' => '')));
			rfsJsonOut(array('ok' => true, 'message' => 'Contraseña actualizada: conecta de nuevo con la nueva contraseña'));
		}
		rfsJsonOut(array('ok' => false, 'message' => rfsRemoteErrorMessage($result, 'No se pudo restablecer la contraseña')));
		break;
	}

	case 'full':
		rfsJsonOut(rfsSyncPass(true));
		break;

	case 'sync':
	default:
		rfsJsonOut(rfsSyncPass(false));
		break;
}
