
$(function() {
	$('.disabled input').prop('disabled', true);
});

function CheckIRM(Field) {
	if(Field.value=='man') {
		var Opener=window;
		var IrmWin=window.open(ROOT_DIR+'Partecipants/ManIrmStatus.php?team='+$(Field).attr('team')+'&event='+$(Field).attr('event')+'&phase='+$(Field).attr('phase'));
		$(IrmWin).on('unload', function() {Opener.location.reload();});
		return;
	} else if(Field.value.substr(0,4)=='irm-') {
		// move the opponent's Tie selector to "Bye" if it is set to 0
		var items=Field.id.split('_');
		items[2] = (items[2]%2==0 ? parseInt(items[2])+1 : parseInt(items[2])-1);
		var opp=items.join('_');
		if($('#'+opp).val()==0) {
			$('#'+opp).val(2);
		}
	}
}

function confirmAll2NextPhase() {
	if (confirm("Перевести в следующий этап лидеров всех матчей, даже тех, где матчи еще не закончились?")) {
		document.Frm.Command.value='SAVE';
		document.Frm.all2NextPhase.value = "1";
		document.Frm.submit();
	}
}
