<?php

class Provider18m_1_18m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 560)
                    return Normative::Master;
                if ($score >= 520)
                    return Normative::Candidate;
                if ($score >= 495)
                    return Normative::First;
                if ($score >= 455)
                    return Normative::Second;
                if ($score >= 425)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 545)
                    return Normative::Master;
                if ($score >= 515)
                    return Normative::Candidate;
                if ($score >= 485)
                    return Normative::First;
                if ($score >= 425)
                    return Normative::Second;
                if ($score >= 360)
                    return Normative::Third;
            }
        } else if (str_starts_with($division, 'C')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 575)
                    return Normative::Master;
                if ($score >= 535)
                    return Normative::Candidate;
                if ($score >= 490)
                    return Normative::First;
                if ($score >= 460)
                    return Normative::Second;
                if ($score >= 425)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 571)
                    return Normative::Master;
                if ($score >= 515)
                    return Normative::Candidate;
                if ($score >= 485)
                    return Normative::First;
                if ($score >= 430)
                    return Normative::Second;
                if ($score >= 365)
                    return Normative::Third;
            }
        } else if (str_starts_with($division, "W1")) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 547)
                    return Normative::Master;
                if ($score >= 520)
                    return Normative::Candidate;
                if ($score >= 500)
                    return Normative::First;
                if ($score >= 480)
                    return Normative::Second;
                if ($score >= 460)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 530)
                    return Normative::Master;
                if ($score >= 500)
                    return Normative::Candidate;
                if ($score >= 480)
                    return Normative::First;
                if ($score >= 460)
                    return Normative::Second;
                if ($score >= 440)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }
}

?>