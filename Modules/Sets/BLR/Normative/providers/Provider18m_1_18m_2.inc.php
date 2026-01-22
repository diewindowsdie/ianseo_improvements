<?php

class Provider18m_1_18m_2 extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 584)
                    return Normative::International;
                if ($score >= 564)
                    return Normative::Master;
                if ($score >= 543)
                    return Normative::Candidate;
                if ($score >= 520)
                    return Normative::First;
                if ($score >= 480)
                    return Normative::Second;
                if ($score >= 445)
                    return Normative::Third;
                if ($score >= 385)
                    return Normative::FirstJunior;
                if ($score >= 345)
                    return Normative::SecondJunior;
                if ($score >= 300)
                    return Normative::ThirdJunior;
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 580)
                    return Normative::International;
                if ($score >= 560)
                    return Normative::Master;
                if ($score >= 540)
                    return Normative::Candidate;
                if ($score >= 520)
                    return Normative::First;
                if ($score >= 480)
                    return Normative::Second;
                if ($score >= 445)
                    return Normative::Third;
                if ($score >= 385)
                    return Normative::FirstJunior;
                if ($score >= 345)
                    return Normative::SecondJunior;
                if ($score >= 300)
                    return Normative::ThirdJunior;
            }
        } else if (str_starts_with($division, 'C')) {
            if ($score >= 580)
                return Normative::Master;
            if ($score >= 560)
                return Normative::Candidate;
            if ($score >= 540)
                return Normative::First;
            if ($score >= 520)
                return Normative::Second;
            if ($score >= 500)
                return Normative::Third;
            if ($score >= 470)
                return Normative::FirstJunior;
            if ($score >= 430)
                return Normative::SecondJunior;
            if ($score >= 400)
                return Normative::ThirdJunior;
        }

        return Normative::None;
    }
}

?>