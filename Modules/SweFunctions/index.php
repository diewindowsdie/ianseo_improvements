<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/config.php');

CheckTourSession(true);

require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Lib/Obj_RankFactory.php');
include('Common/Templates/head.php');

$swe_v2 = ((int)$swe_api_version >= 2);

if (!$swe_configured) {
    echo '<div align="center" style="padding:2em;">' . htmlspecialchars($swelang_notconfigured) . '</div>';
    include('Common/Templates/tail.php');
    exit;
}
?>
<div align="center">
<style>
#sweDiv1 {
}
.SweTabel {
    width: 100%;
    padding: 0;
    margin: 0;
}
.SweTabel > thead > tr {
    background-color:#004488; 
    text-align:center;  
    font-weight:bold; 
    color: #F2F9ff; 
    font-size:120%;
}
.SweTabel > thead > tr > th {
    padding:5px; 
}

.SweTableRight {
    text-align: right;
    font-weight:bold; 
}
.SweInformation {
    background-color: rgba(255,255,255,0.5);
    border: 1px solid black;
    padding-top: 5px;
    padding-bottom: 5px;
}
.SweCenter {
    text-align: center;
}
.swewarning {
    background-color: rgba(255,0,0,0.25);
}
.sweerror {
    color: #a00000;
    font-weight: bold;
}
</style>
<div id="sweDiv1">
    
    <div style="font-size: 1.2em; width: 50%; text-align: left;" >
        <h1 style="text-align: center; background-color:#004488; text-align:center;  padding:5px; padding-left:0px; padding-right: 0px; font-weight:bold; color: #F2F9ff; font-size:120%;"><?= $swelang_supportinformation; ?></h1>
        <?= $swelang_supportinformationtext; ?>
    </div>
    <br /><hr style="width: 50%;"/><br />
    <div style="font-size: 1.2em; width: 50%; text-align: left;"><?= $swelang_moduleversion; ?>: <?= $swe_module_version; ?> </div>
    <div style="font-size: 1.2em; width: 50%; text-align: left;"><?= $swelang_apiversion; ?>: <span id="swe_api_version_text">v<?= (int)$swe_api_version; ?></span> </div>
    <div style="font-size: 1.2em; width: 50%; text-align: left;"><?= $swelang_sendto; ?>: <?= $swe_host; ?> </div>
    <br /><hr style="width: 50%;"/><br />
    <div style="font-size: 1.2em; width: 50%; text-align: left;">
        <?= $swelang_moduleinformation; ?>
        <table>
            <thead><tr><th width="20%"><?= $swelang_field; ?></th><th><?= $swelang_description; ?></th></tr></thead>
            <tbody>
                <tr><td><?= $swelang_eventnumber; ?></td><td><?= $swelang_eventnumber_desc; ?></td></tr>
                <tr><td><?= $swelang_password; ?></td><td><?= $swelang_password_desc; ?></td></tr>
                <tr><td><?= $swelang_sendresult; ?></td><td><?= $swelang_sendall_desc; ?></td></tr>
            </tbody>
        </table>
        <br />
    </div>
</div>

<br />
<div style="width: 50%; padding: 0;">
    <table id="ConfigurationSettings" class="SweTabel">
        <thead>
            <tr><th colspan="2"><?= $swelang_settings; ?></th></tr>
        </thead>
        <tbody>
            <tr><td class="SweTableRight" width="30%"><?= $swelang_eventnumber; ?></td><td><input type="text" name="s_competition_code" id="s_competition_code"></input></td></tr>
            <tr><td class="SweTableRight" width="30%"><?= $swelang_password; ?></td><td><input type="password" name="s_competition_password" id="s_competition_password"></input></td></tr>
            <tr><td colspan="2" class="SweCenter"><input type="submit" onClick="test_settings();" style="padding: 5px; margin: 5px;" value="<?= $swelang_testsettings; ?>" /></td></tr>
            <tr><td colspan="2" class="SweCenter">&nbsp;</td></tr>
            <tr><td colspan="2" class="SweInformation" >&nbsp;<span id="status_information"></span></td></tr>
        </tbody>
    </table>
    <br /><br />
    <table id="CompetitonSelector" class="SweTabel">
        <thead>
            <tr><th colspan="2"><?= $swelang_sendresult; ?></th></tr>
        </thead>
        <tbody>
            <tr><td colspan="2" class="SweCenter"><input type="submit" onClick="send_result();" style="padding: 5px; margin: 5px;" value="<?= $swelang_send; ?>" id="sendbutton" /></td></tr>
            <tr><td colspan="2" class="SweCenter">&nbsp;</td></tr>
            <tr><td colspan="2" class="SweInformation">&nbsp;<span id="status_result"></span></td></tr>
        </tbody>
    </table>
</div>
</div>
<script>
    var SWE_API_VERSION = <?= (int)$swe_api_version; ?>;
    var SWE_TXT = {
        exported: <?= json_encode($swelang_exported); ?>,
        notexported: <?= json_encode($swelang_notexported); ?>,
        warnings: <?= json_encode($swelang_warnings); ?>,
        connectionerror: <?= json_encode($swelang_connectionerror); ?>,
        individ: <?= json_encode($swelang_individ); ?>,
        team: <?= json_encode($swelang_team); ?>,
        qualification: <?= json_encode($swelang_qualification); ?>,
        final: <?= json_encode($swelang_final); ?>,
        run: <?= json_encode($swelang_run); ?>
    };

    window.onload = disableButton;
    function disableButton() {
        document.getElementById("sendbutton").disabled = true;
    };
    function authData() {
        return {"auth": {"competition": document.getElementById("s_competition_code").value, "password": document.getElementById("s_competition_password").value}};
    };
    function test_settings() {
        var xhr = new XMLHttpRequest();
        xhr.open("POST","testsetting.php",true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.onreadystatechange = handler;
        xhr.send(JSON.stringify(authData()));
    };
    function send_result() {
        // Everything the competition has is sent; IANSEO decides what that is.
        var data = authData();
        document.getElementById("sendbutton").disabled = true;
        document.getElementById("status_result").innerHTML = "...";
        var xhr = new XMLHttpRequest();
        xhr.open("POST","send.php",true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.onreadystatechange = resulthandler;
        xhr.send(JSON.stringify(data));
    };
    function parseJson(text) {
        try { return JSON.parse(text); } catch (e) { return null; }
    };
    function serverText(data, httpStatus) {
        // Never print the word "undefined" at the operator. A reply with no message is still a reply,
        // and the HTTP code plus whatever detail came with it is what makes it reportable.
        var msg = (data && typeof data['message'] === 'string') ? data['message'] : '';
        if (msg === '') {
            msg = SWE_TXT.connectionerror + ' (HTTP ' + httpStatus + ')';
        }
        return msg;
    };
    function sectionLabel(section) {
        // v2 responses may tag each class row with the block it came from.
        switch (section) {
            case 'ind_kval':   return SWE_TXT.individ + ' - ' + SWE_TXT.qualification + ': ';
            case 'ind_final':  return SWE_TXT.individ + ' - ' + SWE_TXT.final + ': ';
            case 'team_kval':  return SWE_TXT.team + ' - ' + SWE_TXT.qualification + ': ';
            case 'team_final': return SWE_TXT.team + ' - ' + SWE_TXT.final + ': ';
            case 'run':        return SWE_TXT.run + ': ';
            default:           return '';
        }
    };
    function handler() {
        if (this.readyState == 4) {
            var resp = this.status == 200 ? parseJson(this.responseText) : null;
            if (resp && resp['status'] == 'Ready') {
                document.getElementById("sendbutton").disabled = false;
            } else {
                document.getElementById("sendbutton").disabled = true;
            }
            // The test re-detects the server's API version; show it, so an organizer whose federation
            // has just upgraded sees the module follow without reinstalling anything.
            if (resp && resp['apiVersion']) {
                SWE_API_VERSION = parseInt(resp['apiVersion'], 10);
                var el = document.getElementById("swe_api_version_text");
                if (el) { el.textContent = 'v' + SWE_API_VERSION; }
            }
            if (resp) {
                document.getElementById("status_information").innerHTML = serverText(resp, this.status)
                    + (resp['detail'] ? '<br /><span class="sweerror">' + resp['detail'] + '</span>' : '');
            } else {
                document.getElementById("status_information").innerHTML = '<span class="sweerror">' + SWE_TXT.connectionerror + ' (HTTP ' + this.status + ')</span>';
            }
        }
    };
    function resulthandler() {
        if (this.readyState == 4) {
            var out = document.getElementById("status_result");
            document.getElementById("sendbutton").disabled = false;
            var data = this.status == 200 ? parseJson(this.response) : null;
            if (!data) {
                out.innerHTML = '<span class="sweerror">' + SWE_TXT.connectionerror + ' (HTTP ' + this.status + ')</span>';
                console.log(this.response);
                return;
            }
            if (data['status'] == 'Ready') {
                var html = "";
                if (data['message']) {
                    html += '<br /><b>' + data['message'] + '</b><br />';
                }
                var xc;
                for (xc in data['results']) {
                    var r = data['results'][xc];
                    html += '<br />' + sectionLabel(r['section']) + r['className'] + '<br />' + SWE_TXT.exported + ': ' + r['imported'] + ' ' + SWE_TXT.notexported + ': ' + r['failed'];
                    if (r['warningMessage'] != null && r['warningMessage'] != '') {
                        html += '<br />' + SWE_TXT.warnings + ': (' + r['warnings'] + ')<br /><span class="swewarning"> ' + r['warningMessage'] + '</span>';
                    }
                    html += '<br />';
                }
                out.innerHTML = html;
                console.log(data['results']);
            } else {
                out.innerHTML = '<span class="sweerror">' + serverText(data, this.status) + '</span>' + (data['detail'] ? '<br />' + data['detail'] : '');
                console.log(data);
            }
        }
    };
</script>
<?php
	include('Common/Templates/tail.php');
?>
