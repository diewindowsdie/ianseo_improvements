<?php

class Provider30m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if ($score >= 310)
                return Normative::First;
            if ($score >= 285)
                return Normative::Second;
            if ($score >= 260)
                return Normative::Third;
            if ($score >= 240)
                return Normative::FirstJunior;
            if ($score >= 220)
                return Normative::SecondJunior;
            if ($score >= 200)
                return Normative::ThirdJunior;
        }

        return Normative::None;
    }
}

?>