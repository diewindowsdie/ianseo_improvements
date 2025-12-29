let deferredObj =  new Map();

$(function() {
    LoadSessions();
})

function ChangeNumSessions() {
    $.getJSON('ManSessions_kiss-actions.php', {act:'updateSessions', reqSession: $('#txtNumSession').val()}, function(data) {
        if(data.error==0) {
            buildSessions(data.sessions);
        } else {
            alert(data.msg);
            $('#txtNumSession').val($('#txtNumSession').attr('oldVal'));
        }
    });
}

function ChangeSessionInfo(obj) {
    if(deferredObj.has($(obj).attr('id'))) {
        let toInfo =  deferredObj.get($(obj).attr('id'));
        clearTimeout(toInfo);
    }
    let toInfo = setTimeout(() => {
        ChangeSessionInfo_deferred(obj);
    }, 500);
    deferredObj.set($(obj).attr('id'),toInfo);
}
function ChangeSessionInfo_deferred(obj) {
    deferredObj.delete($(obj).attr('id'));
    $.getJSON('ManSessions_kiss-actions.php', {act:$(obj).attr('refValue'), session:$(obj).attr('refSession'), value: $(obj).val()}, function(data) {
        if(data.error==0) {
            buildSessions(data.sessions);
        } else {
            alert(data.msg);
            $(obj).val($(obj).attr('oldVal'));
        }
    });
}

function LoadSessions() {
    $.getJSON('ManSessions_kiss-actions.php', {act:'getSessions'}, function(data) {
        if(data.error==0) {
            buildSessions(data.sessions);
        }
    });
}

function buildSessions(sessions) {
    let tmpHtml = ''
    $.each(sessions, (index, item) => {
        tmpHtml += '<div class="sesInfo">'+
            '<div class="sesInfoHeader">'+item.Order+'</div>'+
            '<div class="sesInfoBody">'+
                '<div class="sesInfoDataRow"><span class="bold">'+Tar4Session+'</span><div class="w-100 inputBox"><input type="number" id="t4s_'+item.Order+'" refSession="'+item.Order+'" refValue="t4s"  min="1" max="9999" oldVal="'+item.Tar4Session+'" value="'+item.Tar4Session+'" onchange="ChangeSessionInfo(this)"></div></div>' +
                '<hr class="w-95">'+
                '<div class="sesInfoDataRow"><span class="bold">'+Ath4Target+'</span><div class="w-100 inputBox"><input type="number" id="a4t_'+item.Order+'" refSession="'+item.Order+'" refValue="a4t" min="1" max="26" oldVal="'+item.Ath4Target+'" value="'+item.Ath4Target+'" onchange="ChangeSessionInfo(this)"></div></div>' +
            '</div>'+
        '</div>';
    });
    $('#sessionList').html(tmpHtml);
    $('#txtNumSession').val(sessions.length);
    $('#txtNumSession').attr('oldVal',sessions.length);
    LoadDistanceSessions();
}
