<?php

class Provider60m_1_60m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R') && str_contains($class, "U18")) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 650)
                    return Normative::Master;
                if ($score >= 585)
                    return Normative::Candidate;
                if ($score >= 510)
                    return Normative::First;
                if ($score >= 450)
                    return Normative::Second;
                if ($score >= 390)
                    return Normative::Third;
                if ($score >= 370)
                    return Normative::FirstJunior;
                if ($score >= 330)
                    return Normative::SecondJunior;
                if ($score >= 310)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 640)
                    return Normative::Master;
                if ($score >= 575)
                    return Normative::Candidate;
                if ($score >= 500)
                    return Normative::First;
                if ($score >= 440)
                    return Normative::Second;
                if ($score >= 370)
                    return Normative::Third;
                if ($score >= 350)
                    return Normative::FirstJunior;
                if ($score >= 320)
                    return Normative::SecondJunior;
                if ($score >= 300)
                    return Normative::ThirdJunior;
            }
        }

        return Normative::None;
    }
}

?>