<?php
class Provider50m_1_50m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'C')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 690)
                    return Normative::Master;
                if ($score >= 660)
                    return Normative::Candidate;
                if ($score >= 630)
                    return Normative::First;
                if ($score >= 610)
                    return Normative::Second;
                if ($score >= 590)
                    return Normative::Third;
                if ($score >= 520)
                    return Normative::FirstJunior;
                if ($score >= 500)
                    return Normative::SecondJunior;
                if ($score >= 480)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 675)
                    return Normative::Master;
                if ($score >= 650)
                    return Normative::Candidate;
                if ($score >= 620)
                    return Normative::First;
                if ($score >= 600)
                    return Normative::Second;
                if ($score >= 580)
                    return Normative::Third;
                if ($score >= 515)
                    return Normative::FirstJunior;
                if ($score >= 495)
                    return Normative::SecondJunior;
                if ($score >= 475)
                    return Normative::ThirdJunior;
            }
        }

        return Normative::None;
    }
}

?>