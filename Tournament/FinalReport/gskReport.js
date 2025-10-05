function representativesOnChange(input) {
    if (!isNaN(input.value)) {
        let coachesAndRepresentatives = parseInt(input.value);
        $("#totalParticipants").html(parseInt($("#totalAthletes").html()) + coachesAndRepresentatives);
        updateField("coachesAndRepresentativesCount", coachesAndRepresentatives);
    }
}

function judgesHomeRegionChanged(select) {
    $("#nonLocalJudges").html(parseInt($("#judgesTotal").html()) - judgesPerRegion[select.value]);
    updateField("localCountryIdForJudges", select.value);
}

function updateField(field, value) {
    $.post('gskReport-Action.php', {"fieldName": field, "value": value});
}

function toggleBasicSport(checkbox) {
    updateField($(checkbox).attr("name"), $(checkbox).is(":checked") ? "1" : "0");
}

function coachesChanged(input) {
    let newValue = input.value;
    if (isNaN(input.value)) {
        newValue = "0";
    }
    let wrappedInput = $(input);
    $("#regionTotal_" + wrappedInput.attr("id")).html(parseInt($("#athletesTotal_" + wrappedInput.attr("id")).html()) + parseInt(newValue));
    updateField(input.name, newValue);
}