function toggle(trianglePrefix, targetPrefix, id, toggleDivider) {
    let triangle = $('#' + trianglePrefix + id);
    let target = $('#' + targetPrefix + id);
    let divider = $('#divider_' + id);

    if (target.is(":hidden")) {
        triangle.removeClass('fa-caret-right').addClass('fa-caret-down');
        target.show();
        if (toggleDivider) {
            divider.hide();
        }
    } else {
        triangle.removeClass('fa-caret-down').addClass('fa-caret-right');
        target.hide();
        if (toggleDivider) {
            divider.show();
        }
    }
}

function toggleResultsCheckbox(field, invert = false) {
    $.post("results-action.php", {"action": "set-basic", "param": $(field).attr('id'), "value": ($(field).is(":checked") ? "1" : "0"), "invert": invert});
}

function toggleProtocolRelatedCheckbox(field) {
    $.post("results-action.php", {"action": "set-protocol", "param": $(field).attr('id'), "value": ($(field).is(":checked") ? "1" : "0")});
}

function saveProtocolRegionFieldTitle(field) {
    $.post("results-action.php", {"action": "set-protocol", "param": $(field).attr('id'), "value": ($(field).val())});
}

function setDisplayedSessions(field) {
    $.post("results-action.php", {"action": "set-displayed-session", "param": $(field).attr('id'), "value": ($(field).is(":checked") ? "1" : "0")});
}

function saveProtocolUrl(field) {
    $.post("results-action.php", {"action": "set-basic", "param": $(field).attr('id'), "value": ($(field).val())});
}