<?php
class Provider70m_60m_50m_30m extends NormativeProvider {
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_ends_with('W', $class)) {
            if (str_starts_with($division, 'C')) {
                if ($score >= 1380)
                    return Normative::International;
                if ($score >= 1300)
                    return Normative::Master;
                if ($score >= 1230)
                    return Normative::Candidate;
                if ($score >= 1140)
                    return Normative::First;
                if ($score >= 1020)
                    return Normative::Second;
                if ($score >= 950)
                    return Normative::Third;
            } else if (str_starts_with($division, 'R')) {
                if ($score >= 1320)
                    return Normative::International;
                if ($score >= 1250)
                    return Normative::Master;
                if ($score >= 1180)
                    return Normative::Candidate;
                if ($score >= 1120)
                    return Normative::First;
                if ($score >= 980)
                    return Normative::Second;
                if ($score >= 900)
                    return Normative::Third;
            }
        } else if (str_starts_with($class, 'U18M')) {
            if (str_starts_with($division, 'R')) {
                if ($score >= 1220)
                    return Normative::Candidate;
                if ($score >= 1155)
                    return Normative::First;
                if ($score >= 1090)
                    return Normative::Second;
                if ($score >= 1040)
                    return Normative::Third;
                if ($score >= 990)
                    return Normative::FirstJunior;
                if ($score >= 940)
                    return Normative::SecondJunior;
                if ($score >= 890)
                    return Normative::ThirdJunior;
            } else if (str_starts_with($division, 'C')) {
                if ($score >= 1280)
                    return Normative::Candidate;
                if ($score >= 1210)
                    return Normative::First;
                if ($score >= 1080)
                    return Normative::Second;
                if ($score >= 1000)
                    return Normative::Third;
                if ($score >= 920)
                    return Normative::FirstJunior;
                if ($score >= 850)
                    return Normative::SecondJunior;
                if ($score >= 800)
                    return Normative::ThirdJunior;
            }
        }

        return Normative::None;
    }
}
?>