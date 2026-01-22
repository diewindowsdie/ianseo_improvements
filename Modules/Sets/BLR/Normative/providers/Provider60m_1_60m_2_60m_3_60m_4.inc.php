<?php
class Provider60m_1_60m_2_60m_3_60m_4 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'U18M')) {
                if ($score >= 1290)
                    return Normative::Master;
                if ($score >= 1150)
                    return Normative::Candidate;
                if ($score >= 1050)
                    return Normative::First;
                if ($score >= 890)
                    return Normative::Second;
                if ($score >= 730)
                    return Normative::Third;
                if ($score >= 700)
                    return Normative::FirstJunior;
                if ($score >= 650)
                    return Normative::SecondJunior;
                if ($score >= 600)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'U18W')) {
                if ($score >= 1270)
                    return Normative::Master;
                if ($score >= 1130)
                    return Normative::Candidate;
                if ($score >= 1010)
                    return Normative::First;
                if ($score >= 870)
                    return Normative::Second;
                if ($score >= 710)
                    return Normative::Third;
                if ($score >= 675)
                    return Normative::FirstJunior;
                if ($score >= 640)
                    return Normative::SecondJunior;
                if ($score >= 590)
                    return Normative::ThirdJunior;
            }
        }

        return Normative::None;
    }
}

?>