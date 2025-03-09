<?php
class Provider70m_1_70m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'C')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 685)
                    return Normative::International;
                if ($score >= 660)
                    return Normative::Master;
                if ($score >= 640)
                    return Normative::Candidate;
                if ($score >= 610)
                    return Normative::First;
                if ($score >= 555)
                    return Normative::Second;
                if ($score >= 525)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 675)
                    return Normative::International;
                if ($score >= 655)
                    return Normative::Master;
                if ($score >= 620)
                    return Normative::Candidate;
                if ($score >= 585)
                    return Normative::First;
                if ($score >= 535)
                    return Normative::Second;
                if ($score >= 505)
                    return Normative::Third;
            }
        } else if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 670)
                    return Normative::International;
                if ($score >= 640)
                    return Normative::Master;
                if ($score >= 615)
                    return Normative::Candidate;
                if ($score >= 580)
                    return Normative::First;
                if ($score >= 530)
                    return Normative::Second;
                if ($score >= 500)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 664)
                    return Normative::International;
                if ($score >= 630)
                    return Normative::Master;
                if ($score >= 600)
                    return Normative::Candidate;
                if ($score >= 560)
                    return Normative::First;
                if ($score >= 510)
                    return Normative::Second;
                if ($score >= 480)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }
}

?>