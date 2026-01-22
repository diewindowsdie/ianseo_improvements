<?php
class Provider50m_30m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if ($class !== 'U18W') {
                if ($score >= 624)
                    return Normative::Candidate;
                if ($score >= 580)
                    return Normative::First;
                if ($score >= 530)
                    return Normative::Second;
                if ($score >= 480)
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