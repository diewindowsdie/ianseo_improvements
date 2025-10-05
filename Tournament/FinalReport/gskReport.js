function representativesOnChange(input) {
    if (!Number.isNaN(input.value)) {
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