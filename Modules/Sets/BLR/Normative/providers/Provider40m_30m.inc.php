<?php
class Provider40m_30m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if ($class === 'U18W') {
                if ($score >= 625)
                    return Normative::Candidate;
                if ($score >= 575)
                    return Normative::First;
                if ($score >= 525)
                    return Normative::Second;
                if ($score >= 470)
                    return Normative::Third;
                if ($score >= 460)
                    return Normative::FirstJunior;
                if ($score >= 400)
                    return Normative::SecondJunior;
                if ($score >= 360)
                    return Normative::ThirdJunior;
            }
        }

        return Normative::None;
    }
}

?>