$(function() {
    let Status=localStorage.getItem('ScheduleStatus');
    if(Status==null) {
        Status={
            detach:0,
            view:0,
            edit:0,
            print:1,
            exports:0,
            printComplete:1,
            printC58:0,
            printFOP:0
        };
        localStorage.setItem('ScheduleStatus', JSON.stringify(Status));
    } else {
        Status=JSON.parse(Status);
    }

    DoDetachSchedule(Status);
    if(Status.detach==0){
        DoToggleSchedule(Status);
    }
    DoToggleViewSchedule(Status);
    DoTogglePrintSchedule(Status);
    DoTogglePrintScheduleDetails(Status, 'printComplete');
    DoTogglePrintScheduleDetails(Status, 'export');
    DoTogglePrintScheduleDetails(Status, 'printC58');
    DoTogglePrintScheduleDetails(Status, 'printFOP');
    FindScheduleDays();
})

function FindScheduleDays() {
    let options = [];
    let optionFop = [];
    $('th.SchDay[ref]').each((i, item) => {
        options.push('<a onclick="gotoDate(\''+$(item).attr('ref')+'\')">'+$(item).attr('ref')+'</a>');
        optionFop.push('<li><input type="checkbox" fopDays="'+i+'" class="mr-2" value="'+$(item).attr('ref')+'">'+$(item).attr('ref')+'</li>');
    });
    $('#dayLinks').html(options.join(''));
    $('#opt_printFOP').html(optionFop.join('')+$('#opt_printFOP').html());


}

function gotoDate(dt) {
    $('#viewSchedule .SchDay[ref="' + dt + '"]')[0].scrollIntoView();
}

function DiUpdate(obj) {
//		if(obj.value=='') return;
	if((obj.type=='date' || obj.type=='time')) {
		if(obj.defaultValue==obj.value) {
			return;
		} else {
			obj.defaultValue=obj.value;
		}
	}
	var field=encodeURIComponent(obj.name)+'='+encodeURIComponent(obj.value);

	$.getJSON("AjaxUpdate.php?"+field, function(data) {
		if (data.error==0) {
			var Old=data.old;
			var New=data.new;
			var oldTimName = data.oldTimName;
			var oldDurName = data.oldDurName;
			var oldOptName = data.oldOptName;
			var newTimName = data.newTimName;
			var newDurName = data.newDurName;
			var newOptName = data.newOptName;
			if(oldTimName && oldTimName.length>0) {
				$(obj).closest('tr').find('input').each(function() {
					if(this.name==oldTimName) {
						this.name=newTimName;
						if(data.warmtime!=this.value) {
							this.value=data.warmtime;
							this.style.color='green';
						} else {
							this.style.color='blue';
						}
					}
					if(this.name==oldDurName) {
						this.name=newDurName;
						if(data.warmduration!=this.value) {
							this.value=data.warmduration;
							this.style.color='green';
						} else {
							this.style.color='blue';
						}
					}
					if(this.name==oldOptName) {
						this.name=newOptName;
						if(data.options!=this.value) {
							this.value=data.options;
							this.style.color='green';
						} else {
							this.style.color='blue';
						}
                        $(this).closest('div').find('i').attr('id', data.newId);
					}
				});
				obj.style.color='green';
			} else {
				$(obj).closest('tr').find('input').each(function() {
					let tmp=this.name.split(/[\[\]]+/);
					var FldName=(tmp.length>2 ? tmp[2].toLowerCase() : '');
					if(FldName=='') return;
					var Data = data[FldName];
					if(Data && Data!=this.value) {
						this.value=Data;
						this.style.color='green';
					} else {
						this.style.color='blue';
					}
					if(Old && Old.length>0 && New && New.length>0) {
						this.name=this.name.replace(Old, New);
					}

				});
				obj.style.color='green';
			}
			if(data.sch) {
				$('#TrueScheduler').html(data.sch);
			}
			if(data.txt) {
				$('#ScheduleTexts').html(data.txt);
			}
		} else {
			obj.style.backgroundColor='red';
			obj.value=obj.defaultValue;
			// SetStyle(Which,'error');
		}
	});
}

function editAdvanced(obj) {
	let row=$(obj).closest('tr');
	$.confirm({
		title:titAdvanced,
		content:'<div class="mt-3">'+labelTargets+'</div>' +
			'<div><input class="w-100" type="text" value="'+row.find('.advTarget').val()+'" id="advancedTarget"></div>' +
			'<div class="mt-3">'+labelLocation+'</div>' +
			'<div><input class="w-100" type="text" value="'+row.find('.advLocation').val()+'" id="advancedLocation"></div>' +
			'',
		boxWidth:'50%',
		useBootstrap: false,
		buttons:{
			ok:{
				text:btnSubmit,
				action:function() {
					row.find('.advTarget').val($('#advancedTarget').val()).trigger('change');
					row.find('.advLocation').val($('#advancedLocation').val()).trigger('change');
				}
			},
			cancel:{
				text:btnCancel,
			},
		},
	});
}

function DiDelete(obj) {
    if(obj.id=='newOption') {
        var classToDelete=$(obj).closest('div').attr('class');
        $(obj).closest('tr').find('.'+classToDelete).remove();
        return;
    }
    $.confirm({
        title:'',
        content:msgAreYouSure,
        backgroundDismiss:true,
        boxWidth: '33%',
        useBootstrap: false,
        type:'red',
        escapeKey: true,
        buttons:{
            cancel:{
                text:btnCancel
            },
            ok:{
                text:btnOk,
                action:function() {
                    var form={
                        id:$(obj).attr('ref'),
                        val:obj.id,
                    }
                    $.getJSON("AjaxDelete.php",form, function(data) {
                        if(data.error==0) {
                            if(form.id=='Fld') {
                                $(obj).closest('tr').remove();
                            } else {
                                var classToDelete=$(obj).closest('div').attr('class');
                                $(obj).closest('tr').find('.'+classToDelete).remove();
                            }
                            if(data.sch) {
                                $('#TrueScheduler').html(data.sch);
                            }
                            if(data.txt) {
                                $('#ScheduleTexts').html(data.txt);
                            }
                        } else {
                            showAlert(data.msg);
                        }
                    })
                }
            }
        },
    })

    return;
}

function DiInsert(obj) {
    var form={};
    $(obj).closest('tr').find('input').each(function() {
        form[this.name]=this.value;
    });
    $.getJSON("AjaxInsert.php", form, function(data) {
        if(data.error==0) {
            if(data.sch) {
                $('#TrueScheduler').html(data.sch);
            }
            if(data.txt) {
                $('#ScheduleTexts').html(data.txt);
            }
        } else {
            showAlert(data.msg);
        }
    })
    return;
}

function DiAddSubRow(obj) {
	var row=$(obj).closest('tr');
	var cella=row.find('.WTime input');
	// if no warmup already scheduled stops here
	if(cella.last().val()=='') {
        return;
    }
    var CellClass='item-'+cella.length;
    row.find('.WTime').append('<div class="'+CellClass+'"><input size="5"  type="text" name="'+cella.first().attr('name').replace(/\[[^\]]+\]$/, '[]')+'" value="" onchange="DiUpdate(this)"></div>');

    // change cell
    cella=row.find('.WDuration input');
    row.find('.WDuration').append('<div class="'+CellClass+'">' +
        '<input max="999" min="0" type="number" name="'+cella.first().attr('name').replace(/\[[^\]]+\]$/, '[]')+'" value="" onchange="DiUpdate(this)">' +
        '</div>');

    // change cell
    cella=row.find('.WOptions input');
    row.find('.WOptions').append('<div class="'+CellClass+'">' +
        '<input type="text" name="'+cella.first().attr('name').replace(/\[[^\]]+\]$/, '[]')+'" value="" onchange="DiUpdate(this)">' +
        '<i class="fa fa-2x fa-trash-alt text-danger ml-1" ref="WarmDelete" id="newOption" onclick="DiDelete(this)"></i>' +
        '</div>');
}

function DoDetachSchedule(Status) {
    if(Status.detach==1) {
        $('#TrueScheduler').addClass('detached');
        $('#cmdDetachSchedule').removeClass('fa-square-arrow-up-right').addClass('fa-square-xmark');
        $('#cmdToggleSchedule').removeClass('fa-caret-down').addClass('fa-caret-right');
        $('#mainSchedulerTable').removeClass('w-100').addClass('w-75');
        $('#viewSchedule').show();
        const Today = new Date();
        const SchedulerView = $('#viewSchedule .SchDay[ref="' + Today.toISOString().substring(0, 10) + '"]');
        if(SchedulerView.length!=0){
            SchedulerView[0].scrollIntoView();
        }
    } else {
        $('#TrueScheduler').removeClass('detached');
        $('#cmdDetachSchedule').removeClass('fa-square-xmark').addClass('fa-square-arrow-up-right');
        $('#mainSchedulerTable').removeClass('w-75').addClass('w-100');
        if(Status.view!=1){
            $('#viewSchedule').hide();
        } else {
            $('#cmdToggleSchedule').removeClass('fa-caret-right').addClass('fa-caret-down');
        }
    }
}
function detachSchedule() {
    var Status=JSON.parse(localStorage.getItem('ScheduleStatus'));
    Status.detach=1-Status.detach;
    //Status.view=1-Status.detach;
    localStorage.setItem('ScheduleStatus', JSON.stringify(Status));
    DoDetachSchedule(Status);
}

function DoToggleSchedule(Status) {
    if(Status.detach==1 || Status.view==0) {
        $('#cmdToggleSchedule').removeClass('fa-caret-down').addClass('fa-caret-right');
        $('#viewSchedule').hide();
    } else {
        $('#TrueScheduler').removeClass('detached');
        $('#cmdToggleSchedule').removeClass('fa-caret-right').addClass('fa-caret-down');
        $('#viewSchedule').show();
    }
    $('#mainSchedulerTable').removeClass('w-75').addClass('w-100');
}

function toggleSchedule() {
    let Status=JSON.parse(localStorage.getItem('ScheduleStatus'));
    Status.view=1-Status.view;
    if(Status.view==1) {
        Status.detach=0;
    }
    localStorage.setItem('ScheduleStatus', JSON.stringify(Status));
    $('#cmdDetachSchedule').removeClass('fa-square-xmark').addClass('fa-square-arrow-up-right');
    DoToggleSchedule(Status);
}

function DoToggleViewSchedule(Status) {
    if(Status.edit==1) {
        $('#cmdToggleViewSchedule').removeClass('fa-caret-right').addClass('fa-caret-down');
        $('#viewEditSchedule').show();
    } else {
        $('#cmdToggleViewSchedule').removeClass('fa-caret-down').addClass('fa-caret-right');
        $('#viewEditSchedule').hide();
    }
}

function toggleViewSchedule() {
    let Status=JSON.parse(localStorage.getItem('ScheduleStatus'));
    Status.edit=1-Status.edit;
    localStorage.setItem('ScheduleStatus', JSON.stringify(Status));
    DoToggleViewSchedule(Status);
}

function DoTogglePrintSchedule(Status) {
    if(Status.print==1) {
        $('#cmdTogglePrintSchedule').removeClass('fa-caret-right').addClass('fa-caret-down');
        $('#viewPrintSchedule').show();
    } else {
        $('#cmdTogglePrintSchedule').removeClass('fa-caret-down').addClass('fa-caret-right');
        $('#viewPrintSchedule').hide();
    }
}

function togglePrintSchedule() {
    let Status=JSON.parse(localStorage.getItem('ScheduleStatus'));
    Status.print=1-Status.print;
    localStorage.setItem('ScheduleStatus', JSON.stringify(Status));
    DoTogglePrintSchedule(Status);
}

function togglePrintScheduleDetails(what) {
    let Status=JSON.parse(localStorage.getItem('ScheduleStatus'));
    Status[what]=1-Status[what];
    localStorage.setItem('ScheduleStatus', JSON.stringify(Status));
    DoTogglePrintScheduleDetails(Status, what);
}

function DoTogglePrintScheduleDetails(Status, what) {
    if(Status[what]==1) {
        $('#cmd_'+what).removeClass('fa-caret-right').addClass('fa-caret-down');
        $('#opt_'+what).show();
    } else {
        $('#cmd_'+what).removeClass('fa-caret-down').addClass('fa-caret-right');
        $('#opt_'+what).hide();
    }
}

function printSchedule(checkOpt=0) {
    let options= [];
    options.push('PageBreaks='+$('#PageBreaks').val());
    if($('#Finalists').is(':checked')) {
        options.push('Finalists=1');
    }
    if($('#Ranking').is(':checked')) {
        options.push('Ranking=1');
    }
    if($('#Daily').is(':checked')) {
        options.push('Daily=1');
    }
    if($('#NoLocations').is(':checked')) {
        options.push('NoLocations=1');
    }
    if($('#Today').is(':checked')) {
        options.push('Today=1');
        options.push('FromDayDay='+$('#singleDaySchedule').val());
    }
    if($('#FromDay').is(':checked')) {
        options.push('FromDay=1');
        options.push('FromDayDay='+$('#fromDaySchedule').val());
    }
    window.open('./PrnScheduler.php?'+options.join('&'),'SchedulePDF');
}

function chkOptions(obj) {
    if($(obj).is(':checked') && obj.id=='Today') {
        $('#FromDay').prop('checked',false);
    }
    if($(obj).is(':checked') && obj.id=='FromDay') {
        $('#Today').prop('checked',false);
    }

}

function printC58() {
    let options= [];
    if($('#TeamComponents').is(':checked')) {
        options.push('TeamComponents=1');
    }
    $("[sesValue]").each((i, item) => {
        if($(item).is(':checked')) {
            options.push('ses[]='+$(item).attr('sesValue'));
        }
    });
    $("[locValue]").each((i, item) => {
        if($(item).is(':checked')) {
            options.push('loc='+$(item).attr('locValue'));
        }
    });
    window.open('./OrisSchedule.php?'+options.join('&'),'SchedulePDF');
}

function printFOP() {
    var options= {
        fop:1,
    };
    if($('#fopIncludUnscheduled:checked').length==1) {
        options.includeUnscheduled=1;
    }
    $("[fopDays]:checked").each((i, item) => {
        if(!options.Days) {
            options.Days={};
        }
        options.Days[$(item).attr('fopDays')]=$(item).val();
    });
    $('[name="fopLoc"]:checked').each((i, item) => {
        switch($(item).attr('ref')) {
            case 'tgt':
                if(!options.Locations) {
                    options.Locations={};
                }
                options.Locations[item.value]=1;
                break;
            case 'loc':
                if(!options.SesLocations) {
                    options.SesLocations=[];
                }
                options.SesLocations.push(item.value);
                break;
        }
    });
    console.log(options);
    window.open('./index.php?'+$.param(options),'fopPDF');
}

function exportODS() {
    location.href='?ods=1';
}
function exportICS() {
    location.href='?ics=1';
}

function activateSchedule(obj) {
    var form={
        Activate:$(obj).attr('ref'),
    }
    $.getJSON('AjaxActivate.php', form, function (data) {
        $(obj).closest('tr').toggleClass('active', data.active);
    })
}

function calculateMatchNo() {
    $.getJSON('CalculateMatchNo.php', (data) => {
        if(data.error==0){
            $('#btnSetMatchNo').prop('disabled', true);
        }
    });
}