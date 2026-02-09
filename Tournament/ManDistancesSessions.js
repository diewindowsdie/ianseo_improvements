$(function() {
	LoadDistanceSessions();
})

function LoadDistanceSessions() {
	$.getJSON('ManDistancesSessions-Action.php', {act:'getDistanceSessions'}, function(data) {
		if(data.error==0) {
			buildDistanceSessions(data.value);
		}
	});
}

function buildDistanceSessions(sessions) {
	let tmpHtml = ''
	$.each(sessions, (index, item) => {
		tmpHtml += '<tr><th class="Title" colspan="11">' + (item.Name ? item.Order + ': ' + item.Name : Session + ': ' +item.Order) +'</th></tr>'+
			headerDistanceSession;
		$.each(item.Distances, (iDist, itemDist) => {
			tmpHtml += '<tr>'+
				'<th>.' + iDist + '.</th>' +
				'<td class="Center"><input size="4" maxlength="3" type="text" name="end['+item.Order+']['+iDist+']" onchange="ChangeInfo(this)" value="'+itemDist.Ends+'"></td>'+
				'<td class="Center"><input size="4" maxlength="3" type="text" name="arr['+item.Order+']['+iDist+']" onchange="ChangeInfo(this)" value="'+itemDist.Arrows+'"></td>'+
				'<td class="Center d-none advanced"><input size="4" maxlength="3" type="text" name="shoot['+item.Order+']['+iDist+']" onchange="ChangeInfo(this)" value="'+itemDist.ScoringEnds+'"></td>'+
				'<td class="Center d-none advanced"><input size="4" maxlength="3" type="text" name="offset['+item.Order+']['+iDist+']" onchange="ChangeInfo(this)" value="'+itemDist.ScoringOffset+'"></td>'+
				'<td class="Center"><input size="10" maxlength="10" type="date" name="startday['+item.Order+']['+iDist+']" onblur="ChangeInfo(this)" value="'+itemDist.Day+'"></td>'+
				'<td class="Center"><input size="6" maxlength="5" type="time" name="warmtime['+item.Order+']['+iDist+']" onchange="ChangeInfo(this)" value="'+itemDist.WarmStart+'"></td>'+
				'<td class="Center"><input size="4" maxlength="3" type="text" name="warmduration['+item.Order+']['+iDist+']" onchange="ChangeInfo(this)" value="'+itemDist.WarmDuration+'"></td>'+
				'<td class="Center"><input size="6" maxlength="5" type="time" name="starttime['+item.Order+']['+iDist+']" onblur="ChangeInfo(this)" value="'+itemDist.Start+'"></td>'+
				'<td class="Center"><input size="5" maxlength="3" type="text" name="duration['+item.Order+']['+iDist+']" onchange="ChangeInfo(this)" value="'+itemDist.Duration+'"></td>'+
				'<td class="Center"><input size="70" type="text" name="comment['+item.Order+']['+iDist+']" onchange="ChangeInfo(this)" value="'+itemDist.Options+'"></td>'+
			'</tr>';
		});
		tmpHtml += '<tr class="Divider"><td colspan="11"></td></tr>'
	});
	$('#lstDistanceSession').html(tmpHtml);

}

function ChangeInfo(obj) {
	if((obj.type=='date' || obj.type=='time')) {
		if(obj.defaultValue==obj.value) {
			return;
		} else {
			obj.defaultValue=obj.value;
		}
	}
	let form={
		act:'update',
	};

	$('.text-success').toggleClass('text-success', false);
	$.getJSON('ManDistancesSessions-Action.php?'+obj.name+'='+encodeURIComponent(obj.value), form, function(data) {
		if(data.error==0) {
			$(obj).toggleClass('text-success', true);
			$(obj).val(data.value);
		} else {
			showAlert(data.msg);
		}
	});
}

