$(function() {
    inputFocus();
});

function inputFocus() {
    $('#bib').focus();
}

function refreshForm(resetBcode=false) {
    if(resetBcode) {
        $('input[name=B]').val('');
    }
    $('#Frm').submit();
}
function sendTarget(tgt) {
    console.log(tgt);
    $('#bib').val('@'+tgt);
    $('#Frm').submit();
}