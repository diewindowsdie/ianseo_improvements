<?php
class Provider70m_1_70m_2_70m_3_70m_4 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 1320)
                    return Normative::International;
                if ($score >= 1245)
                    return Normative::Master;
                if ($score >= 1150)
                    return Normative::Candidate;
                if ($score >= 1050)
                    return Normative::First;
                if ($score >= 890)
                    return Normative::Second;
                if ($score >= 730)
                    return Normative::Third;
                if ($score >= 690)
                    return Normative::FirstJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 1290)
                    return Normative::International;
                if ($score >= 1225)
                    return Normative::Master;
                if ($score >= 1110)
                    return Normative::Candidate;
                if ($score >= 1010)
                    return Normative::First;
                if ($score >= 870)
                    return Normative::Second;
                if ($score >= 770)
                    return Normative::Third;
                if ($score >= 730)
                    return Normative::FirstJunior;
            }
        }

        return Normative::None;
    }
}

?>