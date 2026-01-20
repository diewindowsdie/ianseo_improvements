<?php
class Provider50m_1_50m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'C')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 670)
                    return Normative::Master;
                if ($score >= 640)
                    return Normative::Candidate;
                if ($score >= 625)
                    return Normative::First;
                if ($score >= 605)
                    return Normative::Second;
                if ($score >= 520)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 655)
                    return Normative::Master;
                if ($score >= 625)
                    return Normative::Candidate;
                if ($score >= 610)
                    return Normative::First;
                if ($score >= 590)
                    return Normative::Second;
                if ($score >= 510)
                    return Normative::Third;
            }
        } else if (str_starts_with($division, "W1")) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 620)
                    return Normative::Master;
                if ($score >= 595)
                    return Normative::Candidate;
                if ($score >= 570)
                    return Normative::First;
                if ($score >= 550)
                    return Normative::Second;
                if ($score >= 520)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 590)
                    return Normative::Master;
                if ($score >= 575)
                    return Normative::Candidate;
                if ($score >= 550)
                    return Normative::First;
                if ($score >= 530)
                    return Normative::Second;
                if ($score >= 500)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }
}

?>