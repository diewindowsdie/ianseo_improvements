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
        $query = "SELECT EnClass, EnDivision, Td1, Td2, Td3, Td4, Td5, Td6, Td7, Td8, QuD1Score, QuD2Score, QuD3Score, QuD4Score, QuD5Score, QuD6Score, QuD7Score, QuD8Score FROM Entries
                    INNER JOIN Qualifications on EnId = QuId
                    LEFT JOIN Classes ON EnClass = ClId AND ClTournament = EnTournament
                    left join TournamentDistances on TdTournament = EnTournament and TdTournament and CONCAT(TRIM(EnDivision),TRIM(EnClass)) LIKE TdClasses
                    WHERE EnTournament=" . StrSafe_DB($_SESSION['TourId']) . "
                    ORDER BY ClViewOrder";
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