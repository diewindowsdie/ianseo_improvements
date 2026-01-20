<?php
class Provider12m_1_12m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 420)
                    return Normative::FirstJunior;
                if ($score >= 410)
                    return Normative::SecondJunior;
                if ($score >= 400)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 410)
                    return Normative::FirstJunior;
                if ($score >= 405)
                    return Normative::SecondJunior;
                if ($score >= 400)
                    return Normative::ThirdJunior;
            }
        } else if (str_starts_with($division, 'C')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 440)
                    return Normative::FirstJunior;
                if ($score >= 430)
                    return Normative::SecondJunior;
                if ($score >= 420)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 430)
                    return Normative::FirstJunior;
                if ($score >= 420)
                    return Normative::SecondJunior;
                if ($score >= 410)
                    return Normative::ThirdJunior;
            }
        }

        return Normative::None;
    }

    public function calcByOneDistance($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 220)
                    return Normative::FirstJunior;
                if ($score >= 210)
                    return Normative::SecondJunior;
                if ($score >= 200)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 210)
                    return Normative::FirstJunior;
                if ($score >= 205)
                    return Normative::SecondJunior;
                if ($score >= 200)
                    return Normative::ThirdJunior;
            }
        } else if (str_starts_with($division, 'C')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 230)
                    return Normative::FirstJunior;
                if ($score >= 225)
                    return Normative::SecondJunior;
                if ($score >= 220)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 220)
                    return Normative::FirstJunior;
                if ($score >= 215)
                    return Normative::SecondJunior;
                if ($score >= 210)
                    return Normative::ThirdJunior;
            }
        }

        return Normative::None;
    }
}

?>