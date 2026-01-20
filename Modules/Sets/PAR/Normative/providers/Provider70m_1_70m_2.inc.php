<?php
class Provider70m_1_70m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 615)
                    return Normative::Master;
                if ($score >= 580)
                    return Normative::Candidate;
                if ($score >= 545)
                    return Normative::First;
                if ($score >= 505)
                    return Normative::Second;
                if ($score >= 470)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 605)
                    return Normative::Master;
                if ($score >= 570)
                    return Normative::Candidate;
                if ($score >= 535)
                    return Normative::First;
                if ($score >= 495)
                    return Normative::Second;
                if ($score >= 460)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }
}

?>