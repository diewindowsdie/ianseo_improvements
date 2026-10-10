<?php
/**
 * SweFunctions - which federation's result server a tournament reports to.
 *
 * THE LOCAL RULE SET DECIDES, AND NOTHING ELSE. $_SESSION['TourLocRule'] is 'SE', 'NO' or 'IS' only
 * when the organizer created the competition under that federation's rule set, and that choice is the
 * one thing that says "this competition is run under our rules, and its results belong in our database".
 *
 * It was briefly also activated on Tournament.ToCountry, so that a competition held in Sweden, Norway
 * or Iceland under international rules got the export menu too (2026-09-06, commit fb2e0de). That is
 * not allowed: a federation's result database is for its own rounds, and the country a competition
 * happens to be held in does not make it one. An international competition hosted by a Swedish club is
 * still an international competition. So the country fallback is gone and the gate is back where it was.
 *
 * Known consequence, accepted: Run Archery is tournament type 48 and only the international rule set
 * (Modules/Sets/FITA, rule code 'default') offers it, so a Run Archery competition cannot be created
 * under a national rule set and therefore cannot be exported. The runArchery block in the v2 payload
 * stays in place for the day a national set gains the type; until then it is always empty. Reporting a
 * round the federation's own rules do not define was the worse of the two problems.
 */

/**
 * @return string 'SE', 'NO', 'IS', or '' when this tournament reports nowhere.
 */
function swe_federation_code() {
    $rule = isset($_SESSION['TourLocRule']) ? (string)$_SESSION['TourLocRule'] : '';

    return in_array($rule, array('SE', 'NO', 'IS'), true) ? $rule : '';
}
