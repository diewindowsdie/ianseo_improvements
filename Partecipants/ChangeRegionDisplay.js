function setRegionFieldDisplay(field) {
    $.post("ChangeRegionsDisplay.php", {"set": $(field).attr('id'), "value": ($(field).is(":checked") ? "1" : "0")});
}