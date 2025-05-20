$(function() {
    loadElements();
})

function loadElements() {
    let form={
        act:'getElements',
        CardType:CardType,
        CardNumber:CardNumber,
        CardPage:CardPage,
    }
    $.getJSON('IdCards-Actions.php', form, function(data) {
        if(data.error==0) {
            $('#IceElements').html(data.table);
            reloadImage('#IdCardImage')
        }
    });
}

function uploadBackground() {
    var obj=$('#UploadedBgImage')
    var f=$('<form>');
    var org=$('#UploadedBgImage').parent();
    f.append(obj);
    let form=new FormData(f[0]);
    form.append('act', 'uploadBackground');
    form.append('CardType', CardType);
    form.append('CardNumber', CardNumber);
    form.append('CardPage', CardPage);
    $.ajax({
        url: 'IdCards-Actions.php',
        type: 'POST',
        data: form,
        success:function(data){
            org.prepend(obj);
            obj.val(null);
            $('#BgImageLoaderDiv').addClass('d-none');
            $('#BgDetails').removeClass('d-none');
            $('#IdCardBackground').attr('src', data.src);
            $('#IdCardsSettings-IdBgX').val(data.settings.IdBgX);
            $('#IdCardsSettings-IdBgY').val(data.settings.IdBgY);
            $('#IdCardsSettings-IdBgW').val(data.settings.IdBgW);
            $('#IdCardsSettings-IdBgH').val(data.settings.IdBgH);
            reloadImage('#IdCardBackground')
            reloadImage('#IdCardImage');
        },
        cache: false,
        contentType: false,
        processData: false
    });
}

function deleteBackground(obj) {
    $.confirm({
        title:'',
        content:'Are You Sure?',
        boxWidth: '50%',
        useBootstrap: false,
        type:'red',
        escapeKey: true,
        backgroundDismiss: true,
        buttons:{
            ok:{
                text:btnConfirm,
                action:function() {
                    let form={
                        act:'deleteBackground',
                        CardType:CardType,
                        CardNumber:CardNumber,
                        CardPage:CardPage,
                    }
                    $(obj).closest('td').removeClass('updated animatedError');
                    $.getJSON('IdCards-Actions.php', form, function(data) {
                        if(data.error==0) {
                            $('#BgImageLoaderDiv').removeClass('d-none');
                            $('#BgDetails').addClass('d-none');
                            $('#IdCardBackground').attr('src', '');
                            reloadImage('#IdCardImage');
                        } else {
                            $(obj).closest('td').addClass('animatedError');
                        }
                    });
                }
            },
            cancel:{
                text:btnCancel,
            },
        }
    });
}
function deleteItem(obj) {
    $.confirm({
        title:'',
        content:'Are You Sure?',
        boxWidth: '50%',
        useBootstrap: false,
        type:'red',
        escapeKey: true,
        backgroundDismiss: true,
        buttons:{
            ok:{
                text:btnConfirm,
                action:function() {
                    let row=$(obj).closest('tr');
                    let form={
                        act:'deleteElement',
                        CardType:CardType,
                        CardNumber:CardNumber,
                        CardPage:CardPage,
                        type:row.attr('icetype'),
                        order:row.attr('iceorder'),
                    }
                    $(obj).closest('td').removeClass('updated animatedError');
                    $.getJSON('IdCards-Actions.php', form, function(data) {
                        if(data.error==0) {
                            row.remove();
                            reloadImage('#IdCardImage');
                        } else {
                            $(obj).closest('tr').addClass('animatedError');
                        }
                    });
                }
            },
            cancel:{
                text:btnCancel,
            },
        }
    });
}

function reloadImage(id) {
    var url= $(id).attr('src');
    $(id).attr('src', url);
    // fetch(url, { cache: 'reload', mode: 'no-cors' })
    //     // .then((response) => {
    //     //     if (!response.ok) {
    //     //         throw new Error(`HTTP error! Status: ${response.status}`);
    //     //     }
    //     //
    //     //     return response.blob();
    //     // })
    //     .then((response) => {
    //         $(id).attr('src', response.src);
    //     });
}

function getElementData(obj) {
    let row=$(obj).closest('tr');
    let form={
        act:'getElementData',
        CardType:CardType,
        CardNumber:CardNumber,
        CardPage:CardPage,
        type:obj.value,
        order:row.attr('iceorder'),
    }
    $(obj).closest('td').removeClass('updated animatedError');
    $.getJSON('IdCards-Actions.php', form, function(data) {
        if(data.error==0) {
            $.confirm({
                title:data.title,
                content:data.content,
                boxWidth: '50%',
                useBootstrap: false,
                type:'red',
                escapeKey: true,
                backgroundDismiss: true,
                buttons:{
                    ok:{
                        text:btnSave,
                        action:function() {
                            var toSave=new FormData($('#NewElementDetails')[0]);
                            toSave.append('act','saveNewElement');
                            toSave.append('CardType',CardType);
                            toSave.append('CardNumber',CardNumber);
                            toSave.append('CardPage',CardPage);
                            $.ajax({
                                url: 'IdCards-Actions.php',
                                type: 'POST',
                                data: toSave,
                                success:function(data){
                                    if(data.error==0) {
                                        $('#IceElements').append(data.NewRow)
                                    }
                                    $(obj).val('');
                                    row.find('input').val(data.NewOrder);
                                    row.attr('iceorder',data.NewOrder)

                                    var $tbody = $('#IceElements');

                                    $tbody.find('tr').sort(function(a, b) {
                                        var tda = parseInt($(a).attr('iceorder')); // target order attribute
                                        var tdb = parseInt($(b).attr('iceorder')); // target order attribute
                                        // if a < b return 1
                                        return tda > tdb ? 1
                                            // else if a > b return -1
                                            : tda < tdb ? -1
                                                // else they are equal - return 0
                                                : 0;
                                    }).appendTo($tbody);

                                    reloadImage('#IdCardImage');
                                },
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }
                    },
                    cancel:{
                        text:btnCancel
                    }
                }
            });
            // loadElements();
        } else {
            $(obj).closest('tr').addClass('animatedError');
        }
    });
}

function toggleDiv(div) {
    var toggle;
    $('.DivSelect'+div).each(function(idx) {
        if(idx==0) {
            toggle=!this.checked;
        }
        this.checked=toggle;
    });
    toggleCategory(this);
}

function toggleClass(cl) {
    var toggle;
    $('.ClSelect'+cl).each(function(idx) {
        if(idx==0) {
            toggle=!this.checked;
        }
        this.checked=toggle;
    });
    toggleCategory(this);
}

function toggleCategory() {
    var queryString='CardType='+CardType+'&CardNumber='+CardNumber;
    $('.CategorySelects:checked').each(function() {
        queryString+='&match[]='+encodeURIComponent(this.value);
    });

    $.getJSON('IdCardEdit-toggleCat.php?'+queryString, function (data) {
        if(data.error!=0) {
            // reset all selectors
            $('.CategorySelects').each(function() {
                this.checked=($(this).attr('checked') ? true : false);
            });
        }
    });
}

function UpdateCardSettings(obj) {
    let form={
        act:'update',
        fld:obj.id,
        val:obj.value,
        CardType:CardType,
        CardNumber:CardNumber,
    }
    $(obj).closest('td').removeClass('updated animatedError');
    $.getJSON('IdCards-Actions.php', form, function(data) {
        if(data.error==0) {
            $(obj).closest('td').addClass('updated');
            if(data.reloadPictures) {
                reloadImage('#IdCardImage');
            }
        } else {
            $(obj).closest('td').addClass('animatedError');
        }
    })

}

function UpdateRowContent(obj) {
    if($('#NewElementDetails').length>0) {
        return;
    }
    var row=$(obj).closest('tr');
    var order=$(obj).closest('tr').attr('iceorder');
    var type=$(obj).closest('tr').attr('icetype');
    var checkbox=false;
    if(obj.type=='checkbox') {
        checkbox=obj.checked;
        obj.value=obj.checked ? '1' : '0';
        obj.checked=true;
    }
    var f=$('<form>');
    var org=$(obj).parent();
    f.append(obj);
    let form=new FormData(f[0]);
    form.append('act', 'updateElement');
    form.append('CardType', CardType);
    form.append('CardNumber', CardNumber);
    form.append('CardPage', CardPage);
    form.append('type', type);
    form.append('order', order);
    form.append('fldname', obj.name);
    $.ajax({
        url: 'IdCards-Actions.php',
        type: 'POST',
        data: form,
        success:function(data){
            org.prepend(obj);
            if(obj.type=='checkbox') {
                obj.checked=checkbox;
            }
            if(data.reload) {
                loadElements();
                return;
            }
            if(data.error==0) {
                // update picture
                reloadImage('#IdCardImage');
            }
            if(data.reloadItem) {
                row.find('img').each(function() {
                    reloadImage(this);
                })
            }
        },
        cache: false,
        contentType: false,
        processData: false
    });
}
