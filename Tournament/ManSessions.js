$(function() {
    updateStatus();
})

function editRow(id) {
    $('#oldKey').val(id);
    $('#d_SesOrder').val($('#order-' + id).val());
    $('#orderInEdit').html($('#order-' + id).val());
    $('#d_SesType').val(id.split('_')[1]);
    $('#d_SesName').val($('#name-' + id).val());
    $('#d_SesLoc').val($('#location-' + id).val());
    $('#d_SesDtStart').val($('#dtstart-' + id).val());
    $('#d_SesDtEnd').val($('#dtend-' + id).val());

    $('#d_SesTar4Session').val($('#tar4session-' + id).val());
    $('#d_SesAth4Target').val($('#ath4target-' + id).val());
    $('#d_SesFirstTarget').val($('#firstTarget-' + id).val());
    $('#d_SesFollow').val($('#follow-' + id).val());
    if(isODF == 1) {
        $('#d_SesOdfCode').val($('#odfcode-' + id).val());
        $('#d_SesOdfPeriod').val($('#odftype-' + id).val());
        $('#d_SesOdfVenue').val($('#odfvenue-' + id).val());
        $('#d_SesOdfLocation').val($('#odflocation-' + id).val());
    }
    $('.EventCheck').prop('checked', false);
    $.each($('#events-' + id).val().split(','), (i, event) => {
        $('#ev_'+event).prop('checked', true);
    });
    updateStatus();
}

function updateStatus() {
    const sesType = $('#d_SesType').val();
    if (sesType=='Q' || sesType=='E') {
        $('#d_SesFollow').val(0).prop('readOnly',true).addClass('disabled');
        $('#d_SesTar4Session').prop('readOnly',false).removeClass('disabled');
        $('#d_SesAth4Target').prop('readOnly',false).removeClass('disabled');
        $('#d_SesFirstTarget').prop('readOnly',false).removeClass('disabled');
        $('.EventCheck').prop('checked', false);
        $('#limitEvents').hide();
    } else if (sesType=='F') {
        $('#d_SesFollow').prop('readOnly',false).removeClass('disabled');
        $('#d_SesTar4Session').val(0).prop('readOnly',true).addClass('disabled');
        $('#d_SesAth4Target').val(0).prop('readOnly',true).addClass('disabled');
        $('#d_SesFirstTarget').val(1).prop('readOnly',true).addClass('disabled');
        $('#limitEvents').show();
    }
}

function cancelEditRow() {
    $('#oldKey').val('');
    $('#d_SesOrder').val(0);
    $('#orderInEdit').html('');
    $('#d_SesType').val('Q');
    $('#d_SesName').val('');
    $('#d_SesLoc').val('');
    $('#d_SesDtStart').val('');
    $('#d_SesDtEnd').val('');

    $('#d_SesTar4Session').val(0);
    $('#d_SesAth4Target').val(0);
    $('#d_SesFirstTarget').val(1);
    $('#d_SesFollow').val(0);
    if(isODF == 1) {
        $('#d_SesOdfCode').val('');
        $('#d_SesOdfPeriod').val('');
        $('#d_SesOdfVenue').val('');
        $('#d_SesOdfLocation').val('');
    }
    updateStatus();
}

function deleteRow(id) {
    if (confirm(StrMsgAreYouSure)) {
        window.location='ManSessions.php?Command=DEL&id=' + id;
    }
}

function saveRow() {
    if($('#d_SesName').val() != '') {
        $('#frmSave').submit();
    }
}