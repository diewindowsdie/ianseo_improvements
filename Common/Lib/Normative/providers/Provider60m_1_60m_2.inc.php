<?php

class Provider60m_1_60m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_contains($class, "U18")) {
            if ($division == 'C') {
                if (str_ends_with($class, 'M')) {
                    if ($score >= 650)
                        return Normative::Candidate;
                    if ($score >= 620)
                        return Normative::First;
                    if ($score >= 570)
                        return Normative::Second;
                    if ($score >= 530)
                        return Normative::Third;
                } else if (str_ends_with($class, 'W')) {
                    if ($score >= 640)
                        return Normative::Candidate;
                    if ($score >= 610)
                        return Normative::First;
                    if ($score >= 560)
                        return Normative::Second;
                    if ($score >= 510)
                        return Normative::Third;
                }
            } else if ($division == 'R') {
                if (str_ends_with($class, 'M')) {
                    if ($score >= 625)
                        return Normative::Candidate;
                    if ($score >= 595)
                        return Normative::First;
                    if ($score >= 545)
                        return Normative::Second;
                    if ($score >= 510)
                        return Normative::Third;
                } else if (str_ends_with($class, 'W')) {
                    if ($score >= 605)
                        return Normative::Candidate;
                    if ($score >= 580)
                        return Normative::First;
                    if ($score >= 525)
                        return Normative::Second;
                    if ($score >= 490)
                        return Normative::Third;
                }
            }
        }

        return Normative::None;
    }
}

?>