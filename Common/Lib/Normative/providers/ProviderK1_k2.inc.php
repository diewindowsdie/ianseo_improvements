<?php

class ProviderK1_k2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if ($division == 'C') {
            if (str_ends_with("M", $class)) {
                if ($score >= 980)
                    return Normative::International;
                if ($score >= 950)
                    return Normative::Master;
                if ($score >= 900)
                    return Normative::Candidate;
                if ($score >= 840)
                    return Normative::First;
                if ($score >= 780)
                    return Normative::Second;
                if ($score >= 720)
                    return Normative::Third;
            } else if (str_ends_with("W", $class)) {
                if ($score >= 920)
                    return Normative::International;
                if ($score >= 880)
                    return Normative::Master;
                if ($score >= 820)
                    return Normative::Candidate;
                if ($score >= 750)
                    return Normative::First;
                if ($score >= 680)
                    return Normative::Second;
                if ($score >= 610)
                    return Normative::Third;
            }
        } else if ($division == 'B') {
            if (str_ends_with("M", $class)) {
                if ($score >= 880)
                    return Normative::International;
                if ($score >= 830)
                    return Normative::Master;
                if ($score >= 770)
                    return Normative::Candidate;
                if ($score >= 700)
                    return Normative::First;
                if ($score >= 630)
                    return Normative::Second;
                if ($score >= 560)
                    return Normative::Third;
            } else if (str_ends_with("W", $class)) {
                if ($score >= 820)
                    return Normative::International;
                if ($score >= 770)
                    return Normative::Master;
                if ($score >= 720)
                    return Normative::Candidate;
                if ($score >= 660)
                    return Normative::First;
                if ($score >= 590)
                    return Normative::Second;
                if ($score >= 520)
                    return Normative::Third;
            }
        } else if ($division == 'T') {
            if (str_ends_with("M", $class)) {
                if ($score >= 810)
                    return Normative::International;
                if ($score >= 760)
                    return Normative::Master;
                if ($score >= 710)
                    return Normative::Candidate;
                if ($score >= 650)
                    return Normative::First;
                if ($score >= 590)
                    return Normative::Second;
                if ($score >= 520)
                    return Normative::Third;
            } else if (str_ends_with("W", $class)) {
                if ($score >= 710)
                    return Normative::International;
                if ($score >= 660)
                    return Normative::Master;
                if ($score >= 610)
                    return Normative::Candidate;
                if ($score >= 550)
                    return Normative::First;
                if ($score >= 490)
                    return Normative::Second;
                if ($score >= 420)
                    return Normative::Third;
            }
        } else if ($division == 'L') {
            if (str_ends_with("M", $class)) {
                if ($score >= 740)
                    return Normative::International;
                if ($score >= 690)
                    return Normative::Master;
                if ($score >= 630)
                    return Normative::Candidate;
                if ($score >= 570)
                    return Normative::First;
                if ($score >= 510)
                    return Normative::Second;
                if ($score >= 450)
                    return Normative::Third;
            } else if (str_ends_with("W", $class)) {
                if ($score >= 640)
                    return Normative::International;
                if ($score >= 590)
                    return Normative::Master;
                if ($score >= 530)
                    return Normative::Candidate;
                if ($score >= 470)
                    return Normative::First;
                if ($score >= 410)
                    return Normative::Second;
                if ($score >= 350)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }
}

?>