<?php
class Provider70m_1_70m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 665)
                    return Normative::International;
                if ($score >= 630)
                    return Normative::Master;
                if ($score >= 585)
                    return Normative::Candidate;
                if ($score >= 530)
                    return Normative::First;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 660)
                    return Normative::International;
                if ($score >= 620)
                    return Normative::Master;
                if ($score >= 575)
                    return Normative::Candidate;
                if ($score >= 520)
                    return Normative::First;
            }
        }

        return Normative::None;
    }
}

?>