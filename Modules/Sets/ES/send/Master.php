<?php

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$tourAbierto = (isset($_SESSION['TourId']) and intval($_SESSION['TourId']) > 0) ? intval($_SESSION['TourId']) : 0;
if($tourAbierto) {
	checkFullACL(AclInternetPublish, 'ipSend', AclReadWrite);
}

$store = rfsLoadStore();
$url = $store['settings']['url'];

$torneos = array();
$q = safe_r_sql("SELECT ToId, ToCode, ToName, ToWhere, ToWhenFrom, ToWhenTo, ToLocRule,
		(SELECT COUNT(*) FROM Entries WHERE EnTournament=ToId) AS Inscritos
	FROM Tournament ORDER BY ToWhenFrom DESC, ToId DESC");
while($r = safe_fetch($q)) {
	$torneos[] = array(
		'id' => intval($r->ToId),
		'code' => strval($r->ToCode),
		'name' => strval($r->ToName),
		'where' => strval($r->ToWhere),
		'from' => substr(strval($r->ToWhenFrom), 0, 10),
		'to' => substr(strval($r->ToWhenTo), 0, 10),
		'rule' => strval($r->ToLocRule),
		'entries' => intval($r->Inscritos),
	);
}

require_once('Common/Templates/head.php');
?>
<style>
	.rfsm-barra { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin: 10px 0; }
	.rfsm-barra input[type=text], .rfsm-barra input[type=password], .rfsm-barra select { padding: 4px 6px; }
	.rfsm-tabla { width: 100%; border-collapse: collapse; font-size: 13px; }
	.rfsm-tabla th, .rfsm-tabla td { padding: 5px 8px; border-bottom: 1px solid #ddd; text-align: left; }
	.rfsm-tabla th { background: #f2f2f2; position: sticky; top: 0; }
	.rfsm-scroll { max-height: 60vh; overflow: auto; border: 1px solid #ddd; }
	.rfsm-estado { font-size: 12px; }
	.rfsm-ok { color: #196939; font-weight: bold; }
	.rfsm-err { color: #a11; font-weight: bold; }
	.rfsm-pend { color: #8a6d00; }
	.rfsm-log { max-height: 220px; overflow: auto; background: #fbfbfa; border: 1px solid #ddd; padding: 8px; font-family: monospace; font-size: 12px; }
</style>

<table class="Tabella">
<tr><th class="Title" colspan="2">Subida maestra a la RFETA</th></tr>
<tr><td colspan="2" class="Center">
	Publica varias competiciones de golpe. Las que se suban desde aquí quedan
	<b>publicadas directamente</b>, sin solicitud ni aprobación.
</td></tr>
<tr><th class="Head" style="width:180px">Servidor</th><td><?php echo htmlspecialchars($url); ?></td></tr>
<tr><th class="Head">Clave maestra</th><td>
	<input type="password" id="rfsmKey" size="30" autocomplete="off" placeholder="Contrase&ntilde;a maestra del servidor">
	<input type="button" id="rfsmComprobar" value="Comprobar cu&aacute;les est&aacute;n publicadas">
	<span id="rfsmKeyMsg" class="rfsm-estado"></span>
</td></tr>
<tr><th class="Head">Clasificaci&oacute;n</th><td>
	<div class="rfsm-barra">
		<select id="rfsmFed"><option value="">Federaci&oacute;n…</option></select>
		<select id="rfsmTipo"><option value="">Tipo o jornada…</option></select>
		<select id="rfsmDisc"><option value="">Disciplina…</option></select>
		<select id="rfsmRound"><option value="">Round…</option></select>
		<label><input type="checkbox" id="rfsmRecords"> Puede batir records</label>
		<label><input type="checkbox" id="rfsmVisible" checked> Visible</label>
	</div>
	<small>Se aplica a todas las seleccionadas. Puedes dejarlo vac&iacute;o y clasificarlas luego desde el panel.
	El <b>round</b> es lo que decide qu&eacute; r&eacute;cords se pueden batir en cada una.</small>
</td></tr>
</table>

<br>

<table class="Tabella">
<tr><th class="Title" colspan="2">Competiciones de este IANSEO (<?php echo count($torneos); ?>)</th></tr>
<tr><td colspan="2">
	<div class="rfsm-barra">
		<input type="text" id="rfsmBuscar" size="34" placeholder="Filtrar por nombre, c&oacute;digo o lugar…">
		<input type="button" id="rfsmTodas" value="Seleccionar visibles">
		<input type="button" id="rfsmNinguna" value="Quitar selecci&oacute;n">
		<label><input type="checkbox" id="rfsmSoloPend"> Solo las no publicadas</label>
		<span id="rfsmCuenta" class="rfsm-estado"></span>
	</div>
	<div class="rfsm-scroll">
		<table class="rfsm-tabla" id="rfsmTabla">
			<tr><th style="width:28px"></th><th>C&oacute;digo</th><th>Competici&oacute;n</th><th>Lugar</th><th>Fechas</th><th class="Center">Inscritos</th><th>Estado</th></tr>
			<?php foreach($torneos as $t) { ?>
			<tr data-id="<?php echo $t['id']; ?>" data-code="<?php echo htmlspecialchars($t['code']); ?>"
				data-buscar="<?php echo htmlspecialchars(mb_strtolower($t['code'] . ' ' . $t['name'] . ' ' . $t['where'])); ?>">
				<td><input type="checkbox" class="rfsmSel"></td>
				<td><?php echo htmlspecialchars($t['code']); ?></td>
				<td><?php echo htmlspecialchars($t['name']); ?></td>
				<td><?php echo htmlspecialchars($t['where']); ?></td>
				<td class="Nowrap"><?php echo htmlspecialchars($t['from'] . ($t['to'] != $t['from'] ? ' – ' . $t['to'] : '')); ?></td>
				<td class="Center"><?php echo $t['entries']; ?></td>
				<td class="rfsmEstado rfsm-estado"></td>
			</tr>
			<?php } ?>
		</table>
	</div>
	<div class="rfsm-barra">
		<input type="button" id="rfsmSubir" value="Subir seleccionadas" class="Button">
		<span id="rfsmProgreso" class="rfsm-estado"></span>
	</div>
	<div class="rfsm-log" id="rfsmLog"></div>
</td></tr>
</table>

<script>
(function() {
	var filas = Array.prototype.slice.call(document.querySelectorAll('#rfsmTabla tr[data-id]'));
	var el = function(id) { return document.getElementById(id); };
	var subiendo = false;

	function estadoDe(fila) { return fila.querySelector('.rfsmEstado'); }

	function log(msg, clase) {
		var line = document.createElement('div');
		line.textContent = new Date().toLocaleTimeString() + '  ' + msg;
		if(clase) line.className = clase;
		el('rfsmLog').insertBefore(line, el('rfsmLog').firstChild);
	}

	function seleccionadas() {
		return filas.filter(function(f) { return f.querySelector('.rfsmSel').checked && f.style.display !== 'none'; });
	}

	function actualizarCuenta() {
		var vis = filas.filter(function(f) { return f.style.display !== 'none'; });
		el('rfsmCuenta').textContent = vis.length + ' visibles · ' + seleccionadas().length + ' seleccionadas';
	}

	function plano(v) {
		return String(v == null ? '' : v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
	}

	function filtrar() {
		var q = plano(el('rfsmBuscar').value.trim());
		var soloPend = el('rfsmSoloPend').checked;
		filas.forEach(function(f) {
			var okQ = !q || plano(f.getAttribute('data-buscar')).indexOf(q) !== -1;
			var okP = !soloPend || f.getAttribute('data-publicada') !== '1';
			f.style.display = (okQ && okP) ? '' : 'none';
		});
		actualizarCuenta();
	}

	el('rfsmBuscar').addEventListener('input', filtrar);
	el('rfsmSoloPend').addEventListener('change', filtrar);
	el('rfsmTodas').onclick = function() {
		filas.forEach(function(f) { if(f.style.display !== 'none') f.querySelector('.rfsmSel').checked = true; });
		actualizarCuenta();
	};
	el('rfsmNinguna').onclick = function() {
		filas.forEach(function(f) { f.querySelector('.rfsmSel').checked = false; });
		actualizarCuenta();
	};
	document.querySelectorAll('.rfsmSel').forEach(function(c) { c.addEventListener('change', actualizarCuenta); });

	function llamar(accion, params, done) {
		var body = new URLSearchParams(params || {});
		body.set('key', el('rfsmKey').value);
		fetch('MasterRun.php?action=' + accion, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function(r) { return r.json(); })
			.then(done)
			.catch(function(err) { done({ ok: false, message: 'Error de red: ' + err }); });
	}

	llamar('config', {}, function(data) {
		if(!data || !data.ok || !data.config) return;
		var cfg = data.config;
		(cfg.federations || []).forEach(function(f) {
			el('rfsmFed').add(new Option(f.name, f.code));
		});
		(cfg.disciplines || []).forEach(function(d) {
			el('rfsmDisc').add(new Option(d.name, d.code));
		});
		(cfg.rounds || []).forEach(function(r) {
			el('rfsmRound').add(new Option(r.name, r.id));
		});
		(cfg.categories || []).forEach(function(c) {
			el('rfsmTipo').add(new Option(c.name, 'cat:' + c.id));
		});
		(cfg.leagues || []).forEach(function(l) {
			(l.rounds || []).forEach(function(r) {
				el('rfsmTipo').add(new Option(l.name + ' · ' + (r.name || 'Jornada ' + r.number), 'round:' + r.id));
			});
		});
	});

	el('rfsmComprobar').onclick = function() {
		var codes = filas.map(function(f) { return f.getAttribute('data-code'); });
		el('rfsmKeyMsg').textContent = 'Comprobando…';
		llamar('exists', { codes: codes.join(',') }, function(data) {
			if(!data || !data.ok) {
				el('rfsmKeyMsg').textContent = (data && data.message) || 'No se pudo comprobar';
				el('rfsmKeyMsg').className = 'rfsm-estado rfsm-err';
				return;
			}
			var porCodigo = {};
			(data.competitions || []).forEach(function(c) { porCodigo[c.code] = c; });
			var publicadas = 0;
			filas.forEach(function(f) {
				var c = porCodigo[f.getAttribute('data-code')];
				if(c) {
					publicadas++;
					f.setAttribute('data-publicada', '1');
					estadoDe(f).textContent = 'Publicada (' + c.status + ')';
					estadoDe(f).className = 'rfsmEstado rfsm-estado rfsm-ok';
				} else {
					f.setAttribute('data-publicada', '0');
					estadoDe(f).textContent = 'Sin publicar';
					estadoDe(f).className = 'rfsmEstado rfsm-estado rfsm-pend';
				}
			});
			el('rfsmKeyMsg').textContent = publicadas + ' de ' + filas.length + ' ya publicadas';
			el('rfsmKeyMsg').className = 'rfsm-estado';
			filtrar();
		});
	};

	el('rfsmSubir').onclick = function() {
		if(subiendo) return;
		var pendientes = seleccionadas();
		if(!pendientes.length) { alert('No hay ninguna competición seleccionada'); return; }
		if(!el('rfsmKey').value) { alert('Introduce la contraseña maestra del servidor'); return; }
		if(!confirm('Se subirán ' + pendientes.length + ' competiciones y quedarán publicadas directamente. ¿Continuar?')) return;

		subiendo = true;
		el('rfsmSubir').disabled = true;
		var tipo = el('rfsmTipo').value;
		var i = 0;

		function siguiente() {
			if(i >= pendientes.length) {
				subiendo = false;
				el('rfsmSubir').disabled = false;
				el('rfsmProgreso').textContent = 'Terminado: ' + pendientes.length + ' competiciones procesadas';
				return;
			}
			var fila = pendientes[i];
			var code = fila.getAttribute('data-code');
			el('rfsmProgreso').textContent = 'Subiendo ' + (i + 1) + ' de ' + pendientes.length + ' · ' + code;
			estadoDe(fila).textContent = 'Subiendo…';
			estadoDe(fila).className = 'rfsmEstado rfsm-estado';

			llamar('upload', {
				tour: fila.getAttribute('data-id'),
				federation: el('rfsmFed').value,
				discipline: el('rfsmDisc').value,
				categoryId: tipo.indexOf('cat:') === 0 ? tipo.slice(4) : '',
				leagueRoundId: tipo.indexOf('round:') === 0 ? tipo.slice(6) : '',
				recordModalityId: el('rfsmRound').value,
				records: el('rfsmRecords').checked ? '1' : '0',
				visible: el('rfsmVisible').checked ? '1' : '0'
			}, function(data) {
				if(data && data.ok) {
					estadoDe(fila).textContent = data.message || 'Subida';
					estadoDe(fila).className = 'rfsmEstado rfsm-estado rfsm-ok';
					fila.setAttribute('data-publicada', '1');
					log(code + ': ' + (data.message || 'subida'));
				} else {
					estadoDe(fila).textContent = 'Error';
					estadoDe(fila).className = 'rfsmEstado rfsm-estado rfsm-err';
					log(code + ': ' + ((data && data.message) || 'error desconocido'), 'rfsm-err');
				}
				i++;
				siguiente();
			});
		}
		siguiente();
	};

	actualizarCuenta();
})();
</script>

<?php
require_once('Common/Templates/tail.php');
