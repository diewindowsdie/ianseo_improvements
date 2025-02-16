<?php
require_once("Normative.php");

abstract class NormativeProvider
{
    public function calculateNormativeInternal($class, $division, $scoreDetails): array
    {
        $scoreByDistances = array();
        $score = 0;
        foreach ($scoreDetails as $distance => $data) {
            $scoreByDistances[$distance] = (int)explode('|', $data)[1];
            $score += $scoreByDistances[$distance];
        }

        $byTotalScore = $this->calcByTotalScore($class, $division, $score);
        $bySeparateDistances = $this->calcBySeparateDistances($class, $division, $scoreByDistances);

        if ($bySeparateDistances["order"] > $byTotalScore["order"]) {
            return $bySeparateDistances;
        }

        return $byTotalScore;
    }

    private function calcBySeparateDistances($class, $division, $scoreByDistances): array
    {
        $maxResult = Normative::None;
        foreach ($scoreByDistances as $score) {
            $thisDistance = $this->calcByOneDistance($class, $division, $score);
            if ($thisDistance["order"] > $maxResult["order"]) {
                $maxResult = $thisDistance;
            }
        }

        return $maxResult;
    }

    abstract public function calcByTotalScore($class, $division, $score): array;

    abstract public function calcByOneDistance($class, $division, $score): array;
}

?>