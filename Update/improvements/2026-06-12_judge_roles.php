<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>2026-06-12_judge_roles.php</i> update script...<br/><br/>\n");

//сначала установим для секретарей фиксированные айди должности
safe_w_SQL("update InvolvedType set ItId = 200 where ItDescription = 'ChiefSecretary';");
safe_w_SQL("update InvolvedType set ItId = 201 where ItDescription = 'ChiefSecretaryDeputy';");
safe_w_SQL("update InvolvedType set ItId = 202 where ItDescription = 'Secretary';");
safe_w_SQL("update InvolvedType set ItId = 203 where ItDescription = 'FieldJudge';");
safe_w_SQL("update InvolvedType set ItId = 204 where ItDescription = 'LineJudge';");
safe_w_SQL("update InvolvedType set ItId = 205 where ItDescription = 'TargetJudge';");

//исправим записи в таблице с данными по всем стартам
safe_w_SQL("update TournamentInvolved set TiType = 200 where TiType = 23");
safe_w_SQL("update TournamentInvolved set TiType = 201 where TiType = 24");
safe_w_SQL("update TournamentInvolved set TiType = 202 where TiType = 25");
safe_w_SQL("update TournamentInvolved set TiType = 203 where TiType = 26");
safe_w_SQL("update TournamentInvolved set TiType = 204 where TiType = 27");
safe_w_SQL("update TournamentInvolved set TiType = 205 where TiType = 28");

if ($log) fwrite($log, "<i>2026-06-12_judge_roles.php</i> script finished successfully.<br/><br/>\n");
