<?php

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$IncludeJquery = true;

CheckTourSession(true);
checkFullACL(AclInternetPublish, 'ipSend', AclReadWrite);

$isSpanishRules = (!empty($_SESSION['TourLocRule']) and $_SESSION['TourLocRule'] == 'ES');

// Save the server settings.
if($isSpanishRules and !empty($_POST['SaveSettings'])) {
	$current = rfsLoadStore();
	$url = rtrim(trim(strval($_POST['rfsUrl'] ?? '')), '/');
	if($url === '') {
		$url = RFS_DEFAULT_URL;
	}
	rfsUpdateStore(array('settings' => array(
		'url' => $url,
		'interval' => max(rfsMinInterval($current), intval($_POST['rfsInterval'] ?? rfsDefaultInterval($current))),
	)));
	// A different server means a different registration: drop the cached
	// session, status and hashes so the whole flow starts clean
	if($url !== $current['settings']['url']) {
		rfsUpdateStore(array(
			'auth' => array('token' => '', 'status' => '', 'blockedReason' => ''),
			'hashes' => array(),
			'mediaHashes' => array(),
			'lastSummary' => array(),
		));
	}
}

$store = rfsLoadStore();
$settings = $store['settings'];
$auth = $store['auth'];
$request = $store['request'];
$lastSummary = $store['lastSummary'];
$transfer = $store['transfer'];
// Clamp also at use time, in case the store predates a registration change.
// An interval nobody has chosen starts at what this account is allowed to use.
$intervaloGuardado = intval($settings['interval']);
if($intervaloGuardado <= 0 or $intervaloGuardado === RFS_DEFAULT_INTERVAL) {
	$intervaloGuardado = rfsDefaultInterval($store);
}
$liveInterval = max(rfsMinInterval($store), $intervaloGuardado);

function rfsKb($bytes) {
	return number_format(intval($bytes) / 1024, 2, ',', '.') . ' kB';
}

$JS_SCRIPT = array('<style>
.rfsLoader { display:inline-block; width:11px; height:11px; border:2px solid #999; border-top-color:transparent; border-radius:50%; vertical-align:middle; animation: rfsSpin .7s linear infinite; }
@keyframes rfsSpin { to { transform: rotate(360deg); } }
input[type=button]:disabled, input[type=submit]:disabled { opacity:.5; }
#rfsLog div { line-height:1.5; }
</style>');

$PAGE_TITLE = 'Enviar a RFETA';

include('Common/Templates/head.php');

if(!$isSpanishRules) {
	echo '<table class="Tabella"><tr><th class="Title">Enviar a RFETA</th></tr>';
	echo '<tr><td class="Center" style="padding:1.5em">Este módulo solo está disponible cuando la competición usa las <b>Reglas de las Competiciones Españolas - RFETA</b> (Competición &gt; Información de la competición &gt; Regla local).</td></tr></table>';
	include('Common/Templates/tail.php');
	exit;
}
?>

<table class="Tabella">
<tr><th class="Title" colspan="2">Enviar a RFETA</th></tr>
<tr class="Divider"><td colspan="2"></td></tr>

<form method="post" action="index.php">
<tr><th class="Head" width="30%">URL del servidor</th>
	<td width="70%"><input type="text" name="rfsUrl" size="42" value="<?php echo htmlspecialchars($settings['url']); ?>"></td></tr>
<tr><th class="Head">Intervalo del directo</th>
	<td><select name="rfsInterval">
<?php foreach(rfsIntervalChoices($store) as $seg): ?>
		<option value="<?php echo $seg; ?>"<?php echo $seg == $liveInterval ? ' selected' : ''; ?>>
			<?php echo $seg; ?> seg<?php echo $seg == RFS_MIN_INTERVAL ? ' (finales)' : ($seg == RFS_FAST_INTERVAL ? ' (recomendado)' : ''); ?>
		</option>
<?php endforeach; ?>
	</select>
	&nbsp;<input type="submit" name="SaveSettings" value="Guardar">
	<div style="font-size:.9em;color:#555">
<?php if(rfsIsPrivileged($store)): ?>
		Cuenta de la RFETA: puedes bajar hasta <?php echo RFS_MIN_INTERVAL; ?> seg para las finales.
<?php endif; ?>
	</div></td></tr>
</form>
<?php
	$rfsDirEstado = rfsStoreDir();
	$rfsDirPropio = dirname(__FILE__) . '/data';
	if($rfsDirEstado === '' or $rfsDirEstado !== $rfsDirPropio):
?>
<tr><th class="Head" style="color:#a11">Sesión del módulo</th>
	<td style="color:#a11">
		<?php if($rfsDirEstado === ''): ?>
			No se puede guardar la sesión en ninguna carpeta: hay que dar permisos de escritura a
			<code><?php echo htmlspecialchars($rfsDirPropio); ?></code> (usuario del servidor web).
			Mientras tanto habrá que volver a conectar en cada recarga.
		<?php else: ?>
			Guardada en <code><?php echo htmlspecialchars($rfsDirEstado); ?></code> porque
			<code><?php echo htmlspecialchars($rfsDirPropio); ?></code> no admite escritura.
			Funciona, pero se perderá si se limpian los temporales: da permisos a esa carpeta cuando puedas.
		<?php endif; ?>
	</td></tr>
<?php endif; ?>

<tbody id="rfsRegister" style="display:none">
	<tr class="Divider"><td colspan="2"></td></tr>
	<tr><td colspan="2">Esta competición no está dada de alta en el servidor de la RFETA. Solicita la publicación:</td></tr>
	<tr><th class="Head">Correo electrónico</th><td><input type="text" id="regEmail" size="35"></td></tr>
	<tr><th class="Head">Contraseña (mín. 4)</th><td><input type="password" id="regPassword" size="35"></td></tr>
	<tr><th class="Head">Federación autonómica</th><td><select id="regFederation" style="min-width:22em"></select></td></tr>
	<tr><th class="Head">Tipo de competición</th><td><select id="regCategory" style="min-width:22em" onchange="fillRounds()"></select></td></tr>
	<tr id="regRoundRow" style="display:none"><th class="Head">Jornada</th><td><select id="regRound" style="min-width:22em"></select></td></tr>
	<tr><th class="Head">Disciplina</th><td><select id="regDiscipline" style="min-width:22em"></select></td></tr>
	<tr><th class="Head">Round</th><td>
		<select id="regRound72" style="min-width:22em"></select>
		<div><small>El round que se tira (72 flechas, 60 flechas, 1440…). Es lo que decide qué récords se pueden batir aquí.</small></div>
	</td></tr>
	<tr><td colspan="2" class="Center"><input type="button" id="rfsDoRegister" value="Solicitar publicación"></td></tr>
	<tr><td colspan="2" class="Center" style="padding-top:.4em">
		<small>¿Ya se publicó desde otro ordenador? <a href="#" id="rfsAlready">Reconectar con correo y contraseña</a></small>
	</td></tr>
</tbody>

<tbody id="rfsStatusBox" style="display:none">
	<tr class="Divider"><td colspan="2"></td></tr>
	<tr><th class="Head">Estado de publicación</th>
		<td><span id="rfsStatusBadge" class="Bold"></span> <span id="rfsStatusDetail"></span>
		&nbsp;<input type="button" id="rfsCheckStatus" value="Comprobar estado">
		<input type="button" id="rfsCategories" value="Actualizar categorías reales">
		<span id="rfsAuthBtns">
			<input type="button" id="rfsReconnect" value="Reconectar con contraseña">
			<input type="button" id="rfsForgot" value="He olvidado la contraseña">
		</span></td></tr>
</tbody>

<tbody id="rfsLoginForm" style="display:none">
	<tr><td colspan="2" id="rfsLoginInfo" style="padding-bottom:.4em"></td></tr>
	<tr><th class="Head">Correo electrónico</th>
		<td><input type="text" id="loginEmail" size="35"></td></tr>
	<tr><th class="Head">Contraseña</th>
		<td><input type="password" id="loginPassword" size="25">
		<input type="button" id="rfsDoLogin" value="Conectar"></td></tr>
	<tr><td colspan="2"><div id="rfsLoginErr" style="color:#a11;font-weight:bold;display:none"></div></td></tr>
</tbody>

<tbody id="rfsApiError" style="display:none">
	<tr class="Divider"><td colspan="2"></td></tr>
	<tr><th class="Head" style="color:#a11">Sin conexión con RFETA</th>
		<td><span id="rfsApiErrorMsg" style="color:#a11"></span>
		&nbsp;<input type="button" id="rfsRetry" value="Reintentar">
		&nbsp;<input type="button" id="rfsErrLogin" value="Reconectar con correo y contraseña"></td></tr>
</tbody>

<tbody id="rfsResetForm" style="display:none">
	<tr><th class="Head">Restablecer contraseña</th>
		<td><input type="button" id="rfsDoResetRequest" value="Enviar código al correo"></td></tr>
	<tr><th class="Head">Código y nueva contraseña</th>
		<td><input type="text" id="resetToken" size="10" placeholder="Código">
		<input type="password" id="resetPassword" size="18" placeholder="Nueva contraseña">
		<input type="button" id="rfsDoResetConfirm" value="Cambiar"></td></tr>
</tbody>

<tbody id="rfsSendBox" style="display:none">
	<tr class="Divider"><td colspan="2"></td></tr>
	<tr><td colspan="2" class="Center">
		<input type="button" id="rfsSync" value="Enviar cambios ahora">
		<input type="button" id="rfsFull" value="Reenviar todo">
		<input type="button" id="rfsAuto" value="Iniciar envío en directo">
		&nbsp;<span id="rfsSpinner" style="display:none"><span class="rfsLoader"></span> <span id="rfsSpinnerText"></span></span>
		<span id="rfsAutoState" class="Bold"></span>
	</td></tr>
	<tr><th class="Head">Transferencia</th>
		<td><table width="55%">
			<tr><th></th><th>Subida</th><th>Bajada</th></tr>
			<tr><th>&Uacute;ltimo envío</th>
				<td class="Center" id="rfsUpLast"><?php echo rfsKb($lastSummary['upBytes'] ?? 0); ?></td>
				<td class="Center" id="rfsDownLast"><?php echo rfsKb($lastSummary['downBytes'] ?? 0); ?></td></tr>
			<tr><th>Total competición</th>
				<td class="Center" id="rfsUpTotal"><?php echo rfsKb($transfer['totalUp']); ?></td>
				<td class="Center" id="rfsDownTotal"><?php echo rfsKb($transfer['totalDown']); ?></td></tr>
		</table></td></tr>
</tbody>
</table>
<br>
<table class="Tabella" id="rfsLogsBox" style="display:none">
<tr><th class="SubTitle">Logs</th></tr>
<tr><td id="rfsStatusLine">
	<?php if(!empty($lastSummary)) { ?>
	&Uacute;ltimo envío: <b><?php echo htmlspecialchars($lastSummary['when'] ?? ''); ?></b>
	&mdash; <?php echo htmlspecialchars($lastSummary['message'] ?? ''); ?>
	(<?php echo intval($lastSummary['sent'] ?? 0); ?> enviados,
	<?php echo intval($lastSummary['unchanged'] ?? 0); ?> sin cambios de
	<?php echo intval($lastSummary['total'] ?? 0); ?> bloques,
	<?php echo htmlspecialchars(strval($lastSummary['elapsed'] ?? '')); ?> s)
	<?php } else { ?>
	Todavía no se ha realizado ning&uacute;n envío.
	<?php } ?>
</td></tr>
<tr><td id="rfsLog" style="max-height:18em;overflow-y:auto;font-family:monospace;font-size:8pt"></td></tr>
</table>

<div id="rfsState" data-status="<?php echo htmlspecialchars($auth['status']); ?>" data-hastoken="<?php echo $auth['token'] !== '' ? '1' : '0'; ?>" data-email="<?php echo htmlspecialchars($request['email']); ?>"></div>

<script>
(function() {
	var autoTimer = null;
	var busy = false;
	var interval = <?php echo $liveInterval; ?> * 1000;
	var stateEl = document.getElementById('rfsState');
	var status = stateEl.dataset.status;     // '', pending, approved, rejected, blocked
	var hasToken = stateEl.dataset.hastoken === '1';
	var leagues = [];

	function el(id) { return document.getElementById(id); }

	function fmtKb(bytes) {
		return (Math.round((bytes || 0) / 1024 * 100) / 100).toLocaleString('es-ES', {minimumFractionDigits: 2}) + ' kB';
	}

	function log(message, isError) {
		var box = el('rfsLog');
		var line = document.createElement('div');
		line.textContent = new Date().toLocaleTimeString() + '  ' + message;
		if(isError) line.style.color = '#c00';
		box.insertBefore(line, box.firstChild);
		while(box.childNodes.length > 200) box.removeChild(box.lastChild);
	}

	var spinnerTexts = { sync: 'Enviando…', full: 'Reenviando todo…', categories: 'Consultando licencias…', register: 'Enviando solicitud…', login: 'Conectando…', status: 'Comprobando…', config: 'Cargando…', exists: 'Comprobando…', resetRequest: 'Enviando código…', resetConfirm: 'Cambiando contraseña…' };

	// Block button while a request is running
	function setBusy(on, action) {
		busy = on;
		document.querySelectorAll('input[type=button], input[type=submit]').forEach(function(b) {
			if(b.id !== 'rfsAuto') b.disabled = on; // check if live mode is stopped
		});
		el('rfsSpinner').style.display = on ? '' : 'none';
		if(on) el('rfsSpinnerText').textContent = spinnerTexts[action] || 'Procesando…';
	}

	function call(action, params, done) {
		if(busy) return;
		setBusy(true, action);
		var body = new URLSearchParams(params || {});
		fetch('SyncNow.php?action=' + action, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function(r) { return r.text(); })
			.then(function(txt) {
				// Lo que no es JSON lo ha contestado el servidor web o IANSEO con un
				// error: se enseña un trozo para saber qué ha pasado
				try { return JSON.parse(txt); }
				catch(e) {
					var trozo = txt.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 200);
					return { ok: false, message: 'Respuesta inesperada de IANSEO' + (trozo ? ': ' + trozo : ' (vacía)') + '. Recarga la página y vuelve a intentarlo.' };
				}
			})
			.then(function(data) {
				setBusy(false);
				if(data.message) log(data.message, !data.ok);
				if(done) done(data);
			})
			.catch(function(err) {
				setBusy(false);
				log('Error de red: ' + err, true);
				if(done) done(null);
			});
	}

	function updateStatusLine(summary) {
		if(!summary || typeof summary.total === 'undefined') return;
		el('rfsStatusLine').innerHTML =
			'Último envío: <b>' + (summary.when || '') + '</b> &mdash; ' + (summary.message || '') +
			' (' + (summary.sent || 0) + ' enviados, ' + (summary.unchanged || 0) +
			' sin cambios de ' + (summary.total || 0) + ' bloques, ' + (summary.elapsed || 0) + ' s)';
		if(typeof summary.upBytes !== 'undefined') {
			el('rfsUpLast').textContent = fmtKb(summary.upBytes);
			el('rfsDownLast').textContent = fmtKb(summary.downBytes);
			el('rfsUpTotal').textContent = fmtKb(summary.totalUp);
			el('rfsDownTotal').textContent = fmtKb(summary.totalDown);
		}
	}

	var statusNames = {
		'pending': 'PENDIENTE DE APROBACIÓN',
		'approved': 'PUBLICACIÓN AUTORIZADA',
		'rejected': 'SOLICITUD RECHAZADA',
		'blocked': 'SUBIDA BLOQUEADA POR LA RFETA'
	};

	var apiError = false;
	var checking = false;
	var configLoaded = false;

	function render() {
		el('rfsApiError').style.display = apiError ? '' : 'none';
		// Error or still-checking: hide every action until we know the real state.
		if(apiError || checking) {
			el('rfsRegister').style.display = 'none';
			el('rfsStatusBox').style.display = 'none';
			el('rfsLoginForm').style.display = 'none';
			el('rfsSendBox').style.display = 'none';
			return;
		}
		var registered = status !== '';
		el('rfsRegister').style.display = registered ? 'none' : '';
		el('rfsStatusBox').style.display = registered ? '' : 'none';
		// The send actions, transfer stats and logs stay hidden until the competition is actually connected (email + password entered).
		el('rfsSendBox').style.display = (status === 'approved' && hasToken) ? '' : 'none';
		el('rfsCategories').style.display = (status === 'approved' && hasToken) ? '' : 'none';
		el('rfsAuthBtns').style.display = hasToken ? 'none' : '';
		el('rfsLogsBox').style.display = hasToken ? '' : 'none';
		if(registered) {
			el('rfsStatusBadge').textContent = statusNames[status] || status;
			el('rfsStatusBadge').style.color = (status === 'approved' ? '#196939' : (status === 'pending' ? '#8a6d00' : '#a11'));
			el('rfsStatusDetail').textContent = hasToken ? '' : '(sin conexión activa)';
			// Sin token lo único que se puede hacer es reconectar: el formulario,
			// delante, en lugar de un aviso que manda buscar un botón.
			if(!hasToken) {
				if(!el('rfsLoginInfo').textContent.trim()) {
					el('rfsLoginInfo').textContent = 'Introduce la contraseña con la que se publicó esta competición.';
				}
				el('rfsLoginForm').style.display = '';
			}
		}
		// Load the config if not loaded yet and not busy
		if(!registered && !configLoaded && !busy) loadConfig();
	}

	// Always ask the server before deciding what to show:
	//   registered -> only ask for email + password (reconnect)
	//   not found  -> show the publish request form
	//   API failed -> show a blocking error
	function checkExisting() {
		apiError = false;
		checking = true;
		render();
		call('exists', {}, function(data) {
			checking = false;
			if(!data || !data.ok) {
				apiError = true;
				el('rfsApiErrorMsg').textContent = (data && data.message)
					? data.message
					: 'No se pudo comprobar si la competición está publicada. Revisa la URL del servidor y la conexión, y reintenta.';
				render();
				return;
			}
			if(data.registered) {
				status = data.status || 'pending';
				hasToken = false;
				el('rfsLoginInfo').innerHTML = 'Esta competición <b>ya está publicada</b>' +
					(data.email ? ' (correo ' + data.email + ')' : '') +
					'. Introduce el correo y la contraseña con los que se publicó para reconectar este ordenador.';
				el('rfsLoginErr').style.display = 'none';
				render();
				el('rfsLoginForm').style.display = '';
			} else {
				render();
			}
		});
	}

	function loadConfig() {
		if(configLoaded) return;
		call('config', {}, function(data) {
			if(!data || !data.ok || !data.config) return;
			configLoaded = true;
			el('regFederation').innerHTML = data.config.federations.map(function(f) {
				return '<option value="' + f.code + '">' + f.code + ' · ' + f.name + '</option>';
			}).join('');
			el('regDiscipline').innerHTML = data.config.disciplines.map(function(d) {
				return '<option value="' + d.code + '">' + d.name + '</option>';
			}).join('');
			el('regRound72').innerHTML = '<option value="">Sin declarar</option>' +
				(data.config.rounds || []).map(function(r) {
					return '<option value="' + r.id + '">' + r.name + '</option>';
				}).join('');
			leagues = data.config.leagues || [];
			el('regCategory').innerHTML =
				data.config.categories.map(function(c) {
					return '<option value="cat:' + c.id + '">' + c.name + '</option>';
				}).join('') +
				leagues.map(function(l) {
					return '<option value="league:' + l.id + '">' + l.name + '</option>';
				}).join('');
			fillRounds();
		});
	}

	window.fillRounds = function() {
		var type = el('regCategory').value;
		var league = type.indexOf('league:') === 0
			? leagues.find(function(l) { return l.id === Number(type.slice(7)); })
			: null;
		el('regRoundRow').style.display = league ? '' : 'none';
		if(league) {
			el('regRound').innerHTML = league.rounds.map(function(r) {
				return '<option value="' + r.id + '">' + r.name + '</option>';
			}).join('');
		}
	};

	el('rfsDoRegister').onclick = function() {
		var type = el('regCategory').value;
		var params = {
			email: el('regEmail').value,
			password: el('regPassword').value,
			federation: el('regFederation').value,
			discipline: el('regDiscipline').value,
			recordModalityId: el('regRound72').value
		};
		if(type.indexOf('league:') === 0) {
			params.leagueRoundId = el('regRound').value;
		} else {
			params.categoryId = type.slice(4);
		}
		call('register', params, function(data) {
			if(data && data.ok) { status = 'pending'; hasToken = true; render(); }
		});
	};

	el('rfsCheckStatus').onclick = function() {
		call('status', {}, function(data) {
			if(data && data.ok) {
				status = data.status;
				if(data.blockedReason) log('Motivo: ' + data.blockedReason, true);
				render();
			} else if(data && data.needsRegister) {
				status = ''; hasToken = false;
				render();
			} else if(data && data.needsLogin) {
				el('rfsLoginForm').style.display = '';
			}
		});
	};

	el('rfsReconnect').onclick = function() {
		el('rfsLoginForm').style.display = '';
		el('loginPassword').focus();
	};
	el('rfsDoLogin').onclick = function() {
		var err = el('rfsLoginErr');
		err.style.display = 'none';
		call('login', { email: el('loginEmail').value, password: el('loginPassword').value }, function(data) {
			if(data && data.ok) {
				status = data.status; hasToken = true;
				el('loginPassword').value = '';
				err.style.display = 'none';
				el('rfsLoginForm').style.display = 'none';
				render();
			} else {
				// Show the error reason 
				err.textContent = (data && data.message) ? data.message : 'No se pudo conectar. Revisa el correo y la contraseña.';
				err.style.display = '';
			}
		});
	};

	el('rfsRetry').onclick = function() { checkExisting(); };

	// Manual reconnect: the competition was published from another machine
	function showManualLogin() {
		apiError = false;
		el('rfsApiError').style.display = 'none';
		el('rfsRegister').style.display = 'none';
		el('rfsLoginInfo').innerHTML = 'Introduce el correo y la contraseña con los que se publicó esta competición desde el otro ordenador.';
		el('rfsLoginErr').style.display = 'none';
		el('rfsLoginForm').style.display = '';
		el('loginEmail').focus();
	}
	el('rfsAlready').onclick = function(e) { e.preventDefault(); showManualLogin(); };
	el('rfsErrLogin').onclick = showManualLogin;

	el('rfsForgot').onclick = function() {
		el('rfsResetForm').style.display = (el('rfsResetForm').style.display === 'none' ? '' : 'none');
	};
	el('rfsDoResetRequest').onclick = function() { call('resetRequest', { email: el('loginEmail').value }); };
	el('rfsDoResetConfirm').onclick = function() {
		call('resetConfirm', { token: el('resetToken').value, password: el('resetPassword').value }, function(data) {
			if(data && data.ok) { el('rfsResetForm').style.display = 'none'; el('rfsLoginForm').style.display = ''; }
		});
	};

	function doSync(action, done) {
		call(action, {}, function(data) {
			if(data) {
				updateStatusLine(data);
				if(data.needsReload) stopLive();
				if(data.needsLogin) { hasToken = false; render(); el('rfsLoginForm').style.display = ''; }
				if(data.needsRegister) { status = ''; hasToken = false; render(); }
			}
			if(done) done(data);
		});
	}

	el('rfsSync').onclick = function() { doSync('sync'); };

	// RFETA Records
	el('rfsCategories').onclick = function() {
		if(confirm('Se consultar\u00e1n en la base de datos de licencias las categor\u00edas reales de todos los inscritos. \u00bfContinuar?')) {
			doSync('categories');
		}
	};
	el('rfsFull').onclick = function() {
		if(confirm('Se reenviarán todos los datos e imágenes de la competición al servidor RFETA. ¿Continuar?')) {
			doSync('full');
		}
	};

	var countdownTimer = null;
	var nextSendAt = 0;

	function updateCountdown() {
		if(!autoTimer) return;
		var remaining = Math.max(0, Math.ceil((nextSendAt - Date.now()) / 1000));
		el('rfsAutoState').textContent = busy
			? 'EN DIRECTO — enviando…'
			: 'EN DIRECTO — próximo envío en ' + remaining + ' s';
	}

	function livePass() {
		nextSendAt = Date.now() + interval;
		doSync('sync', function() { updateCountdown(); });
	}

	function stopLive() {
		if(!autoTimer) return;
		clearInterval(autoTimer);
		clearInterval(countdownTimer);
		autoTimer = null;
		countdownTimer = null;
		el('rfsAuto').value = 'Iniciar envío en directo';
		el('rfsAutoState').textContent = '';
		log('Envío en directo detenido');
	}

	el('rfsAuto').onclick = function() {
		if(autoTimer) {
			stopLive();
		} else {
			this.value = 'Detener envío en directo';
			log('Envío en directo iniciado (cada ' + (interval / 1000) + ' s)');
			livePass();
			autoTimer = setInterval(livePass, interval);
			countdownTimer = setInterval(updateCountdown, 1000);
			updateCountdown();
		}
	};

	if(status === '') {
		checkExisting();
	} else {
		render();
		if(hasToken) {
			// Refresh the real status from the server every time the page opens
			call('status', {}, function(data) {
				if(data && data.ok && data.status !== status) {
					status = data.status;
					render();
				} else if(data && data.needsRegister) {
					status = ''; hasToken = false;
					checkExisting();
				} else if(data && data.needsLogin) {
					hasToken = false;
					render();
					el('rfsLoginForm').style.display = '';
				}
			});
		}
	}
})();
</script>

<?php
include('Common/Templates/tail.php');
