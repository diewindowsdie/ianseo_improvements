<?php
class Provider60m_50m_40m_30m extends NormativeProvider {
    public function calcByTotalScore($class, $division, $score): array
    {
        if ($class == 'U18W') {
            if ($division == 'C') {
                if ($score >= 1280)
                    return Normative::Candidate;
                if ($score >= 1220)
                    return Normative::First;
                if ($score >= 1160)
                    return Normative::Second;
                if ($score >= 1100)
                    return Normative::Third;
                if ($score >= 1050)
                    return Normative::FirstJunior;
                if ($score >= 1000)
                    return Normative::SecondJunior;
                if ($score >= 950)
                    return Normative::ThirdJunior;
            } else if ($division == 'R') {
                if ($score >= 1230)
                    return Normative::Candidate;
                if ($score >= 1170)
                    return Normative::First;
                if ($score >= 1100)
                    return Normative::Second;
                if ($score >= 1050)
                    return Normative::Third;
                if ($score >= 1000)
                    return Normative::FirstJunior;
                if ($score >= 950)
                    return Normative::SecondJunior;
                if ($score >= 900)
                    return Normative::ThirdJunior;
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