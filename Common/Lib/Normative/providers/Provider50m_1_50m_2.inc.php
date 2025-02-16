<?php
class Provider50m_1_50m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if ($division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 700)
                    return Normative::International;
                if ($score >= 680)
                    return Normative::Master;
                if ($score >= 660)
                    return Normative::Candidate;
                if ($score >= 640)
                    return Normative::First;
                if ($score >= 620)
                    return Normative::Second;
                if ($score >= 600)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 690)
                    return Normative::International;
                if ($score >= 670)
                    return Normative::Master;
                if ($score >= 650)
                    return Normative::Candidate;
                if ($score >= 630)
                    return Normative::First;
                if ($score >= 610)
                    return Normative::Second;
                if ($score >= 590)
                    return Normative::Third;
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