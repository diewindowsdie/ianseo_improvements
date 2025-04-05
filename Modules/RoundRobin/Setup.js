$(function() {
    $('#EvCopyFrom').hide();
    getEventDetail();
});

let showAlertDialog=false;

function getEventDetail() {
    history.pushState(null, '', '?Team='+$('#EvTeam').val()+'&Event='+$('#EvCode').val());
    let form={
        Team:$('#EvTeam').val(),
        Event:$('#EvCode').val(),
        Level:$('#EvLevel :checked').val(),
        act:'getDetails',
    };
    $.getJSON('Setup-data.php', form, function(data) {
        if(data.error==0) {
            showAlertDialog=data.showAlert;
            if(data.reloadEvents) {
                $('#EvCode').empty();
                $.each(data.events, function() {
                    $('#EvCode').append('<option value="'+this.EvCode+'">'+this.EvCode+'-'+this.EvEventName+'</option>');
                });
            }
            $('#EvCode').val(data.event);

            $('#EvLevel').empty();
            $.each(data.levels, function() {
                $('#EvLevel').append('<span class="mx-2 text-dark Button"><input style="margin-left: 0" type="radio" name="EvLevel" value="'+this.val+'" onclick="getEventDetail()" '+(this.val==data.level ? 'checked="checked"' : '')+'>'+this.text+'</span>');
            });

            if(data.copyfrom.length>0) {
                $('#EvCopyFrom').empty();
                $.each(data.copyfrom, function() {
                    $('#EvCopyFrom').append('<option value="'+this.val+'">'+this.text+'</option>');
                });

                $('#EvCopyFrom').show();
            } else {
                $('#EvCopyFrom').hide();
            }

            // finally sets the details of this level for this event!
            var fldDisabled=(data.disabled=='0' ? '' : 'disabled="disabled"');
            $('#LevDetails').empty();
            $('#LevDetails').append('<tr><th>'+data.details.name.text+'</th><td><input '+fldDisabled+' type="text" class="details non0" id="Name" value="'+data.details.name.val+'" onchange="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.groups.text+'</th><td><input '+fldDisabled+' type="number" class="details non0" id="Groups" value="'+data.details.groups.val+'" onchange="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.groupArchers.text+'</th><td><input '+fldDisabled+' type="number" class="details non0" id="GroupArchers" value="'+data.details.groupArchers.val+'" onchange="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.mode.text+'</th><td><select '+fldDisabled+' class="details" id="mode" onchange="checkValue()"></select></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.bestRanked.text+'</th><td><select '+fldDisabled+' class="details" id="bestRanked" onchange="checkValue()"></select></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.ends.text+'</th><td><input '+fldDisabled+' type="number" class="details non0" id="Ends" value="'+data.details.ends.val+'" onchange="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.arrows.text+'</th><td><input '+fldDisabled+' type="number" class="details non0" id="Arrows" value="'+data.details.arrows.val+'" onchange="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.so.text+'</th><td><input '+fldDisabled+' type="number" class="details non0" id="SO" value="'+data.details.so.val+'" onchange="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.tieAllowed.text+'</th><td><input '+fldDisabled+' type="checkbox" id="TieAllowed"'+(data.details.tieAllowed.val==1 ? ' checked="checked"' : '')+'" onclick="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.winPoints.text+'</th><td><input '+fldDisabled+' type="number" class="details" id="WinPoints" value="'+data.details.winPoints.val+'" onchange="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.tiePoints.text+'</th><td><input '+fldDisabled+' type="number" class="details" id="TiePoints" value="'+data.details.tiePoints.val+'" onchange="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.tieBreakSystem.text+'</th><td><select '+fldDisabled+' class="details non0" id="TieBreakSystem" onchange="checkValue()"></select></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.tieBreakSystem2.text+'</th><td><select '+fldDisabled+' class="details" id="TieBreakSystem2" onchange="checkValue()"><option value="0">---</option></select></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.checkGolds.text+'</th><td><input '+fldDisabled+' type="checkbox" id="CheckGolds"'+(data.details.checkGolds.val==1 ? ' checked="checked"' : '')+'" onclick="checkValue()"></td></tr>');
            $('#LevDetails').append('<tr><th>'+data.details.checkXNines.text+'</th><td><input '+fldDisabled+' type="checkbox" id="CheckXNines"'+(data.details.checkXNines.val==1 ? ' checked="checked"' : '')+'" onclick="checkValue()"></td></tr>');
            if(data.disabled!='0') {
                $('#LevDetails').append('<tr><td colspan="2"><div class="alert alert-info p-2">'+data.disabledMessage+'</div></td></tr>');
            }
            $('#LevDetails').append('<tr><td colspan="2" class="Center"><input id="CmdSubmit" '+fldDisabled+' type="button" onclick="setValue()" disabled value="'+data.cmdSave+'"></td></tr>');
            $.each(data.tieBreakOptions, function() {
                $('#TieBreakSystem').append('<option value="'+this.val+'">'+this.text+'</option>');
                $('#TieBreakSystem2').append('<option value="'+this.val+'">'+this.text+'</option>');
            });
            $.each(data.modeOptions, function() {
                $('#mode').append('<option value="'+this.val+'">'+this.text+'</option>');
            });
            $.each(data.bestOptions, function() {
                $('#bestRanked').append('<option value="'+this.val+'">'+this.text+'</option>');
            });
            $('#TieBreakSystem').val(data.details.tieBreakSystem.val);
            $('#TieBreakSystem2').val(data.details.tieBreakSystem2.val);
            $('#mode').val(data.details.mode.val);
            $('#bestRanked').val(data.details.bestRanked.val);
            $('#QualifiedArchers').html($('#Groups').val()*$('#GroupArchers').val());
            $('#CmdSubmit').prop('disabled', data.showAlert);
            var NumArchers=$('#GroupArchers').val();
            var isEven=(NumArchers%2 == 0);

            $('#RoundsPerGroup').html(Math.ceil(NumArchers/2)*2 - 1);
            $('#MatchesPerRound').html(Math.floor(NumArchers/2) + ' ('+(isEven ? '-' : '1')+')');
            $('#MatchesPerPerson').html(NumArchers - 1 + ' ('+(isEven ? '-' : '1')+')');
        } else {
            $.alert({
                title:'',
                content:data.msg,
                useBootstrap:false,
                boxWidth:'33%',
                backgroundDismiss:true,
            });
        }
    });
}

function setValue() {
    if(showAlertDialog==true) {
        $.confirm({
            title:strWarningTitle,
            content:strSetupChangeWarning,
            useBootstrap:false,
            boxWidth:'33%',
            backgroundDismiss:true,
            escapeKey:'cancel',
            buttons:{
                ok:{
                    text:cmdConfirm,
                    btnClass:'btn-blue',
                    action:function() {
                        doSetValues();
                    },
                },
                cancel:{
                    text:cmdCancel,
                    btnClass:'btn-red',
                },
            }
        });
    } else {
        doSetValues();
    }
}

function checkValue() {
    $('#QualifiedArchers').html($('#Groups').val()*$('#GroupArchers').val());
    $('#RoundsPerGroup').html(Math.max(0,$('#GroupArchers').val()-1));
    let disabled=false;
    $('.non0').each(function() {
        if(this.value==0 || this.value=='') {
            disabled=true;
        }
    });
    $('#CmdSubmit').prop('disabled', disabled);
}

function doSetValues() {
    let form={
        Team:$('#EvTeam').val(),
        Event:$('#EvCode').val(),
        Level:$('#EvLevel :checked').val(),
        act:'setDetails',
        TieAllowed:($('#TieAllowed').prop('checked') ? 1 : 0),
    };
    $('.details').each(function() {
        form[this.id]=$(this).val();
    });
    $.getJSON('Setup-data.php', form, function(data) {
        if(data.error==0) {
            if(data.levels) {
                $('#EvLevel').empty();
                $.each(data.levels, function() {
                    $('#EvLevel').append('<option value="'+this.val+'">'+this.text+'</option>');
                });
                $('#EvLevel').val(form.Level);
            }
            $('#CmdSubmit').prop('disabled', true);
            showAlertDialog=true;
        }
    });
}

function copyFromEvent(obj) {
    $.confirm({
        title:strWarningTitle,
        content:strSetupCopyWarning,
        useBootstrap:false,
        boxWidth:'33%',
        backgroundDismiss:true,
        escapeKey:'cancel',
        buttons:{
            ok:{
                text:cmdConfirm,
                btnClass:'btn-blue',
                action:function() {
                    let form={
                        Team:$('#EvTeam').val(),
                        Event:$('#EvCode').val(),
                        Level:$('#EvLevel :checked').val(),
                        act:'copyFrom',
                        from:obj.value,
                    };
                    $.getJSON('Setup-data.php', form, function(data) {
                        if(data.error==0) {
                            getEventDetail();
                        } else {
                            $(obj).val('');
                            $.alert({
                                title:'',
                                content:data.msg,
                                useBootstrap:false,
                                boxWidth:'33%',
                                backgroundDismiss:true,
                            });
                        }
                    });
                },
            },
            cancel:{
                text:cmdCancel,
                btnClass:'btn-red',
                action:function() {
                    $(obj).val('');
                }
            },
        }
    });
}