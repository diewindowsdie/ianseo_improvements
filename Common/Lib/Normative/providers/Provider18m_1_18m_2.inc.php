<?php

class Provider18m_1_18m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if ($division == 'R' || $division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 586)
                    return Normative::International;
                if ($score >= 570)
                    return Normative::Master;
                if ($score >= 530)
                    return Normative::Candidate;
                if ($score >= 500)
                    return Normative::First;
                if ($score >= 455)
                    return Normative::Second;
                if ($score >= 415)
                    return Normative::Third;
                if ($score >= 375)
                    return Normative::FirstJunior;
                if ($score >= 335)
                    return Normative::SecondJunior;
                if ($score >= 295)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 582)
                    return Normative::International;
                if ($score >= 566)
                    return Normative::Master;
                if ($score >= 525)
                    return Normative::Candidate;
                if ($score >= 490)
                    return Normative::First;
                if ($score >= 450)
                    return Normative::Second;
                if ($score >= 410)
                    return Normative::Third;
                if ($score >= 370)
                    return Normative::FirstJunior;
                if ($score >= 330)
                    return Normative::SecondJunior;
                if ($score >= 290)
                    return Normative::ThirdJunior;
            }
        } else if ($division == 'B') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 553)
                    return Normative::International;
                if ($score >= 538)
                    return Normative::Master;
                if ($score >= 502)
                    return Normative::Candidate;
                if ($score >= 467)
                    return Normative::First;
                if ($score >= 429)
                    return Normative::Second;
                if ($score >= 392)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 549)
                    return Normative::International;
                if ($score >= 534)
                    return Normative::Master;
                if ($score >= 497)
                    return Normative::Candidate;
                if ($score >= 462)
                    return Normative::First;
                if ($score >= 425)
                    return Normative::Second;
                if ($score >= 387)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }

    public function calcByOneDistance($class, $division, $score): array
    {
        if ($division == 'R' || $division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 260)
                    return Normative::First;
                if ($score >= 240)
                    return Normative::Second;
                if ($score >= 215)
                    return Normative::Third;
                if ($score >= 195)
                    return Normative::FirstJunior;
                if ($score >= 175)
                    return Normative::SecondJunior;
                if ($score >= 155)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 250)
                    return Normative::First;
                if ($score >= 235)
                    return Normative::Second;
                if ($score >= 210)
                    return Normative::Third;
                if ($score >= 190)
                    return Normative::FirstJunior;
                if ($score >= 170)
                    return Normative::SecondJunior;
                if ($score >= 150)
                    return Normative::ThirdJunior;
            }
        } else if ($division == 'B') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 243)
                    return Normative::First;
                if ($score >= 225)
                    return Normative::Second;
                if ($score >= 206)
                    return Normative::Third;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 240)
                    return Normative::First;
                if ($score >= 222)
                    return Normative::Second;
                if ($score >= 2206)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }
}

?>