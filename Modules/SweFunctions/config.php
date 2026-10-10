<?php
$swe_module_version = "20261007";

/*
 * $swe_api_version selects which ARS endpoint send.php talks to:
 *   1 = legacy  POST {host}/api/CompetitionResults     (individual qualification + finals only)
 *   2 =         POST {host}/api/v2/CompetitionResults  (adds tournament header, team qualification,
 *                                                       team finals and run archery)
 *
 * It is ASKED OF THE SERVER, not configured here — see ApiVersion.php. The three federations upgrade
 * independently, and pinning the version per federation meant every organizer needed a new module on the
 * day their federation's server changed. Now the same module follows whichever server it is pointed at,
 * and falls back to v1 whenever v2 cannot be confirmed.
 *
 * To pin a federation to one version anyway, set $swe_api_version_force = 1 (or 2) in its branch below;
 * that skips the probe entirely.
 */

require_once(dirname(__FILE__).'/Federation.php');
require_once(dirname(__FILE__).'/ApiVersion.php');

/*
 * Which federation this tournament reports to: its local rule set, and nothing else. A competition held
 * in one of the three countries but run under international rules does NOT report. See Federation.php.
 */
$swe_federation = swe_federation_code();

if ($swe_federation == 'SE') {
    // Config
    $swe_host = "https://resultat.bagskytte.se";
    // Language
    $swelang_menutext = "* Exportera svenska resultat";
    $swelang_supportinformation = "Support information!";
    $swelang_supportinformationtext = "Detta är en IANSEO modul som supportas av Fredrik Larsson (fredrik@larre.nu) <br />
            för Svenska Bågskytteförbundets räkning.<br /><br />
            Uppstår frågor eller problem med denna modul kontakta då Fredrik Larsson, <br />
            IANSEO's support-team kan inte hjälpa till med frågor gällande denna modul.";
    $swelang_moduleversion = "Modulversion";
    $swelang_apiversion = "API-version";
    $swelang_sendto = "Skickar till";
    $swelang_moduleinformation = "
            Modulen låter dig rapportera in tävlingensresultatet direkt till den svenska resultatdatabasen.<br />
            Fyll i Tävlingsnummer och lösenord, klicka sedan på Testa inställningar. <br />
            Som svar kommer du få tävlingens namn till svar och om den tillåter att resultat kan skickas in.<br />
            Klicka i omgångarna som skall skickas in och klicka på Skicka knappen<br /><br />";
    $swelang_field = "Fält";
    $swelang_description = "Beskrivning";
    $swelang_eventnumber = "Tävlingsnummer";
    $swelang_eventnumber_desc = "Är tävlingens nummer angivet i tävlingsdatabasen.";
    $swelang_password = "Lösenord";
    $swelang_password_desc = "Lösenordet som är satt på tävlingen i tävlingsdatabasen.";
    $swelang_qualification = "Grundomgång";
    $swelang_qualification_desc = "Skickar in grundomgångens resultat.";
    $swelang_final = "Final";
    $swelang_final_desc = "Skickar in finalernas resultat.";
    $swelang_team_qualification_desc = "Skickar in lagens grundomgångsresultat (inklusive lagmedlemmar).";
    $swelang_team_final_desc = "Skickar in lagfinalernas resultat.";
    $swelang_run = "Run-Archery";
    $swelang_run_desc = "Skickar in Run-Archery-resultat (kval, semifinal och final) för både individuella och lag.";
    $swelang_sendresult = "Skicka in resultat";
    $swelang_sendall_desc = "Allt som finns registrerat för tävlingen skickas in: individuella resultat, lagresultat och Run Archery. IANSEO avgör vad som finns, du behöver inte välja.";
    $swelang_testsettings = "Testa inställningar";
    $swelang_settings = "Inställningar";
    $swelang_individ = "Individuella";
    $swelang_team = "Lag";
    $swelang_send = "Skicka";
    $swelang_noinformation_sent = "Ingen data att skicka.";
    $swelang_notexported = "Ej exporterade";
    $swelang_exported = "Exporterade";
    $swelang_warnings = "Varningar";
    $swelang_connectionerror = "Kunde inte nå servern";
}
elseif ($swe_federation == 'NO') {
    // Config
    $swe_host = "https://resultat.bueskyting.no";
    // Language
    $swelang_menutext = "* Exportere norske resultat";
    $swelang_supportinformation = "Support information!";
    $swelang_supportinformationtext = "Dette er en IANSEO modul som supporteres av Jon-Arne Storelv (jon-arne@bueskyting.no) <br />
            for Norges Bueskytterforbunds regning. <br /><br />
            Om du har spørsmål eller problemer med denne modulen, kontakt da Jon-Arne  Storelv,<br />
            IANSEO's support-team kan ikke hjelpe til med spørsmål vedrørende denne modul.";
    $swelang_moduleversion = "Modulversion";
    $swelang_apiversion = "API-versjon";
    $swelang_sendto = "Sender til";
    $swelang_moduleinformation = "
            Modulen lar deg rapportere in stevneresultat direkte til den norske resultatdatabasen.<br />
            Fyll inn stevnenummer og passord, klikk deretter på Test innstillinger.<br />
            Som svar kommer du til å få stevnets navn og om den tillatter at resultat sendes inn.<br />
            Velg om det er Kvalifisering og/eller finaler den skal sendes inn resultater og klikk på Last opp knappen<br /><br />";
    $swelang_field = "Felt";
    $swelang_description = "Beskrivelse";
    $swelang_eventnumber = "Stevnenummer";
    $swelang_eventnumber_desc = "Stevnets nummer i den norske resultat-databasen.";
    $swelang_password = "Passord";
    $swelang_password_desc = "Ett passord satt på stevnet.";
    $swelang_qualification = "Kvalifiseringsrunde";
    $swelang_qualification_desc = "Sender inn resultat for kvalifiseringsrunder til resultat-databasen.";
    $swelang_final = "Finaler";
    $swelang_final_desc = "Sender inn resultater for finalerundene..";
    $swelang_team_qualification_desc = "Sender inn lagenes kvalifiseringsresultat (inkludert lagmedlemmer).";
    $swelang_team_final_desc = "Sender inn resultater for lagfinalene.";
    $swelang_run = "Run-Archery";
    $swelang_run_desc = "Sender inn Run-Archery-resultater (kvalifisering, semifinale og finale) for både individuelle og lag.";
    $swelang_sendresult = "Sender inn resultat";
    $swelang_sendall_desc = "Alt som er registrert for stevnet sendes inn: individuelle resultater, lagresultater og Run Archery. IANSEO avgjør hva som finnes, du trenger ikke velge.";
    $swelang_testsettings = "Teste innstillinger";
    $swelang_settings = "Instillinger";
    $swelang_individ = "Individuelle";
    $swelang_team = "Lag";
    $swelang_send = "Last opp";
    $swelang_noinformation_sent = "Ikke noe data å sende.";

    $swelang_notexported = "Not exported";
    $swelang_exported = "Exported";
    $swelang_warnings = "Warnings";
    $swelang_connectionerror = "Could not reach the server";

}
elseif ($swe_federation == 'IS') {
    // Config
    $swe_host = "https://mot.bogfimi.is";
    // Language
    $swelang_menutext = "* Senda íslensk úrslit";
    $swelang_supportinformation = "Support information!";
    $swelang_supportinformationtext = "Þetta er ianseo viðbót sem hægt er að fá stuðning við á bogfimi@bogfimi.is <br />
            fyrir hönd Bogfimisambands Íslands.<br /><br />
            Ef þú hefur spurningar eða lendir í vandamálum með þessa viðbót hafðu samband við Bogfimisamband Íslands, <br />
            IANSEO's support-team getur ekki aðstoðað þig með spurningar tengt þessari viðbót.";
    $swelang_moduleversion = "Module version";
    $swelang_apiversion = "API version";
    $swelang_sendto = "Senda til";
    $swelang_moduleinformation = "
            Viðbótin gerir þér kleift að senda úrslit beint í íslenska úrslita gagnagrunninn.<br />
            Fylltu inn viðburðarnúmer og lykilorð og veldu svo prufa stillingar.<br />
            Sem svar færðu nafn viðburðarins og hvort hann leyfir innsendingu úrslita.<br />
            Veldu hvort senda á inn niðurstöður um undankeppni og/eða útsláttarkeppni og smelltu á senda hnappinn<br /><br />";
    $swelang_field = "Field";
    $swelang_description = "Lýsing";
    $swelang_eventnumber = "Viðburðarnúmer";
    $swelang_eventnumber_desc = "Númer viðburðar í íslenska úrslita gagnagrunninum";
    $swelang_password = "Lykilorð";
    $swelang_password_desc = "Lykilorð fyrir viðkomandi viðburð";
    $swelang_qualification = "Undankeppni";
    $swelang_qualification_desc = "Senda inn upplýsingar um undankeppni í úrslita gagnagrunn.";
    $swelang_final = "Úrslit";
    $swelang_final_desc = "Senda inn upplýsingar um útsláttarkeppni";
    $swelang_team_qualification_desc = "Senda inn úrslit liða í undankeppni.";
    $swelang_team_final_desc = "Senda inn úrslit liða í útsláttarkeppni.";
    $swelang_run = "Run-Archery";
    $swelang_run_desc = "Senda inn Run-Archery úrslit (undankeppni, undanúrslit og úrslit) fyrir einstaklinga og lið.";
    $swelang_sendresult = "Senda inn úrslit";
    $swelang_sendall_desc = "Allt sem skráð er fyrir mótið er sent inn: einstaklingsúrslit, liðaúrslit og Run Archery. IANSEO ákveður hvað er til, þú þarft ekki að velja.";
    $swelang_testsettings = "Prufa stillingar";
    $swelang_settings = "Stillingar";
    $swelang_individ = "Einstaklinga";
    $swelang_team = "Lið";
    $swelang_send = "Senda";
    $swelang_noinformation_sent = "Engin gögn til þess að senda.";

    $swelang_notexported = "Not exported";
    $swelang_exported = "Exported";
    $swelang_warnings = "Warnings";
    $swelang_connectionerror = "Could not reach the server";

}

// Ask the server which version it speaks. Cached per host in the session, so this is one short HTTP
// round trip now and then, not one per page load. v1 whenever v2 cannot be confirmed.
$swe_api_version = isset($swe_host)
    ? swe_detect_api_version($swe_host, isset($swe_api_version_force) ? $swe_api_version_force : null)
    : 1;

// This competition is not run under any of the three federations' rule sets. The menu hides the module
// then; this keeps a direct URL from rendering a page of undefined values.
$swe_configured = ($swe_federation !== '' && isset($swe_host));
if (!$swe_configured) {
    $swe_host = '';
    $swelang_notconfigured = 'This competition is not set up for result export: it was not created under the rule set of a federation this module serves.';
}

$swe_url_test      = $swe_host . "/api/CompetitionStatus";
$swe_url_result    = $swe_host . "/api/CompetitionResults";      // v1 (legacy, unversioned URL)
$swe_url_result_v2 = $swe_host . "/api/v2/CompetitionResults";   // v2

?>
