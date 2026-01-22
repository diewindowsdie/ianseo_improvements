<?php

class Provider18m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if ($score >= 275)
                return Normative::Candidate;
            if ($score >= 265)
                return Normative::First;
            if ($score >= 245)
                return Normative::Second;
            if ($score >= 225)
                return Normative::Third;
            if ($score >= 200)
                return Normative::FirstJunior;
            if ($score >= 180)
                return Normative::SecondJunior;
            if ($score >= 160)
                return Normative::ThirdJunior;
        }

        return Normative::None;
    }
}

?>