<?php
class Provider50m_30m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if ($division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 575)
                    return Normative::First;
                if ($score >= 550)
                    return Normative::Second;
                if ($score >= 500)
                    return Normative::Third;
                if ($score >= 450)
                    return Normative::FirstJunior;
                if ($score >= 420)
                    return Normative::SecondJunior;
                if ($score >= 390)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 565)
                    return Normative::First;
                if ($score >= 540)
                    return Normative::Second;
                if ($score >= 490)
                    return Normative::Third;
                if ($score >= 440)
                    return Normative::FirstJunior;
                if ($score >= 410)
                    return Normative::SecondJunior;
                if ($score >= 380)
                    return Normative::ThirdJunior;
            }
        } else if ($division == 'R') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 550)
                    return Normative::First;
                if ($score >= 530)
                    return Normative::Second;
                if ($score >= 480)
                    return Normative::Third;
                if ($score >= 430)
                    return Normative::FirstJunior;
                if ($score >= 400)
                    return Normative::SecondJunior;
                if ($score >= 370)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 540)
                    return Normative::First;
                if ($score >= 520)
                    return Normative::Second;
                if ($score >= 470)
                    return Normative::Third;
                if ($score >= 420)
                    return Normative::FirstJunior;
                if ($score >= 390)
                    return Normative::SecondJunior;
                if ($score >= 360)
                    return Normative::ThirdJunior;
            }
        }

        return Normative::None;
    }

    public function calcByOneDistance($class, $division, $score): array
    {
        return Normative::None;
    }
}

?>