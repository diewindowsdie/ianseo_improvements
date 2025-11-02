function judgesHomeRegionChanged(fieldName, select) {
    $("#nonLocalJudges").html(parseInt($("#judgesTotal").html()) - judgesPerRegion[select.value]);
    updateField(fieldName, select.value);
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
    let id = wrappedInput.attr("id");
    coachesPerRegion[id] = parseInt(newValue);
    $("#regionTotal_" + id).html(parseInt($("#athletesTotal_" + wrappedInput.attr("id")).html()) + coachesPerRegion[id]);
    updateField(input.name, newValue);

    let totalCoaches = calcTotalCoaches();
    $("#totalParticipants").html(parseInt($("#totalAthletes").html()) + totalCoaches);
    $("#totalCoaches").html(totalCoaches);
}

function calcTotalCoaches() {
    let result = 0;
    Object.keys(coachesPerRegion).forEach(key => result += coachesPerRegion[key]);

    return result;
}

function resetInput(target, value) {
    let input = document.getElementsByName(target)[0];
    input.value = value;
    updateField(input.name, input.value);
}