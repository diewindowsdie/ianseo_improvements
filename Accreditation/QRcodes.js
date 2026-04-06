var SocketIP;
var SocketPort;
var isLive=0;
var socketInitFunction;
$(function() {
	showType();
})

function changeType(obj) {
	var form={
		act:'changeType',
		type:obj.value,
	}
	$.getJSON('QRcodes-action.php', form, function(data) {
		if(data.error==1) {
			$.alert(data.msg);
			return;
		}
		showType();
	})
}

function showType() {
	var conType=$('#type').val();
	switch(conType) {
		case 'socket':
			$('.showHttp').toggleClass('d-none', true);
			$('.showSocket').toggleClass('d-none', false);
			$('.SocketConnection').toggleClass('d-none', false);
			SocketIP=$('#socketAddress').val();
			SocketPort=$('#socketPort').val();
			isLive=1;

			if($('#socketScript').length==0) {
				const script = document.createElement('script');
				script.src = "../Api/ISK-NG/socket.js";
				script.id = "socketScript";

				script.onload = function() {
					// The script is loaded and executed; the function is now available in the global scope
					socketInitFunction=socket
				};

				script.onerror = function() {
					console.error('Error loading script');
				};

				document.head.appendChild(script);
			} else {
				initSocket();
			}
			break;
		default:
			isLive=0;
			$('.showHttp').toggleClass('d-none', false);
			$('.showSocket').toggleClass('d-none', true);
			$('.SocketConnection').toggleClass('d-none', true);
	}
}

function updateSettings(obj) {
	var form={
		act:'setSetting',
		id:obj.id,
		val:obj.value,
	}
	$.getJSON('QRcodes-action.php', form, function(data) {
		if(data.error==1) {
			$.alert(data.msg);
			return;
		}
		showType();
	})
}

function print() {
	window.open('./QRcodesPDF.php', 'QrCode');
}

function updateGlobal(obj) {
	var form={
		act:'updateGlobal',
		fld:$(obj).attr('ref'),
		value:obj.localName=='i'?($(obj).hasClass('fa-toggle-on')?0:1):obj.value,
	}
	$.getJSON('./QRcodes-action.php', form, function(data) {
		if(data.error==1) {
			alert(data.msg);
			return;
		}
		if(obj.localName=='i') {
			$(obj).toggleClass('fa-toggle-on fa-toggle-off text-success text-secondary');
		}
	});
}

function updateArea(obj) {
	var form={
		act:'updateArea',
		fld:$(obj).attr('ref'),
		value:$(obj).hasClass('fa-toggle-on')?0:1,
	}
	$.getJSON('./QRcodes-action.php', form, function(data) {
		if(data.error==1) {
			alert(data.msg);
			return;
		}
		$(obj).toggleClass('fa-toggle-on fa-toggle-off text-success text-secondary');
		$('.Zones').each(function() {
			if(data.values.includes(parseInt($(this).attr('ref')))===false) {
				$(this).addClass('fa-toggle-off text-secondary');
				$(this).removeClass('fa-toggle-on text-success');
			} else {
				$(this).removeClass('fa-toggle-off text-secondary');
				$(this).addClass('fa-toggle-on text-success');
			}
		});
	});
}
function updateAddon(obj) {
	var form={
		act:'updateAddon',
		fld:$(obj).attr('ref'),
		comp:$(obj).attr('comp'),
		value:$(obj).hasClass('fa-toggle-on')?0:1,
	}
	$.getJSON('./QRcodes-action.php', form, function(data) {
		if(data.error==1) {
			alert(data.msg);
			return;
		}
		$(obj).toggleClass('fa-toggle-on fa-toggle-off text-success text-secondary');
	});
}