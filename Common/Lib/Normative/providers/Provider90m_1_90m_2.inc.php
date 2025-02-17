<?php

class Provider90m_1_90m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if ($division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 640)
                    return Normative::Master;
                if ($score >= 600)
                    return Normative::Candidate;
                if ($score >= 530)
                    return Normative::First;
                if ($score >= 500)
                    return Normative::Second;
                if ($score >= 450)
                    return Normative::Third;
            }
        } else if ($division == 'R') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 580)
                    return Normative::Master;
                if ($score >= 540)
                    return Normative::Candidate;
                if ($score >= 500)
                    return Normative::First;
                if ($score >= 460)
                    return Normative::Second;
                if ($score >= 420)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }
}

?>