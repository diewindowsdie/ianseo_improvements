<?php
require_once("Common/Lib/Normative/NormativeCalculator.php");

class NormativeStatistics
{
    public static function normativeDescriptions(): array
    {
        $normativeDescriptions = array();

        foreach (Normative::all() as $existingNormative) {
            $normativeDescriptions[$existingNormative["codeLetters"]] = $existingNormative["name"];
        }

        return $normativeDescriptions;
    }

    static function getNormativeStatistics()
    {
        $data = array();
        $normativeTotals = array();
        $query = "SELECT e.EnClass, e.EnDivision, td.Td1, td.Td2, td.Td3, td.Td4, td.Td5, td.Td6, td.Td7, td.Td8, q.QuD1Score, q.QuD2Score, q.QuD3Score, q.QuD4Score, q.QuD5Score, q.QuD6Score, q.QuD7Score, q.QuD8Score FROM Entries e
                    INNER JOIN Qualifications q on e.EnId = q.QuId
                    inner join Divisions d on d.DivId = e.EnDivision and d.DivTournament = e.EnTournament
                    inner join Classes cl ON e.EnClass = cl.ClId AND cl.ClTournament = e.EnTournament
                    left join TournamentDistances td on td.TdTournament = e.EnTournament and td.TdTournament and CONCAT(TRIM(e.EnDivision),TRIM(e.EnClass)) LIKE td.TdClasses
                    WHERE d.DivAthlete = 1 and cl.ClAthlete = 1 and e.EnTournament=" . StrSafe_DB($_SESSION['TourId']) . "
                    ORDER BY cl.ClViewOrder";
        $rs = safe_r_SQL($query);
        while ($row = safe_fetch($rs)) {
            if (!array_key_exists($row->EnClass, $data)) {
                $data[$row->EnClass] = array();
            }
            $thisRowDistances = array();
            $thisRowScore = array();
            for ($i = 1; $i <= 8; $i++) {
                $thisRowDistances[] = $row->{'Td' . $i};
                $thisRowScore["dist_" . $i] = "0|" . $row->{'QuD' . $i . 'Score'} . "|0|0";
            }
            $normative = calcNormative($thisRowDistances, $row->EnClass, $row->EnDivision, $thisRowScore, $_SESSION['TourLocRule']);

            if (!array_key_exists($normative["codeLetters"], $data[$row->EnClass])) {
                $data[$row->EnClass][$normative["codeLetters"]] = 0;
            }
            ++$data[$row->EnClass][$normative["codeLetters"]];

            if (!array_key_exists($normative["codeLetters"], $normativeTotals)) {
                $normativeTotals[$normative["codeLetters"]] = 0;
            }
            ++$normativeTotals[$normative["codeLetters"]];
        }

        $results = array();
        foreach (Normative::all() as $normative) {
            if ($normativeTotals[$normative["codeLetters"]]) {
                $results[$normative["name"]] = $normativeTotals[$normative["codeLetters"]];
            }
        }
        $data["normativeTotals"] = $results;
        return $data;
    }
}