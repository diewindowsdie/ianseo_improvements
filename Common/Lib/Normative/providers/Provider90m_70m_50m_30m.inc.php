<?php
class Provider90m_70m_50m_30m extends NormativeProvider {
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_ends_with('M', $class)) {
            if ($division == 'C') {
                if ($score >= 1370)
                    return Normative::International;
                if ($score >= 1300)
                    return Normative::Master;
                if ($score >= 1230)
                    return Normative::Candidate;
                if ($score >= 1160)
                    return Normative::First;
                if ($score >= 1020)
                    return Normative::Second;
                if ($score >= 950)
                    return Normative::Third;
            } else if ($division == 'R') {
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
        }

        return Normative::None;
    }

    public function calcByOneDistance($class, $division, $score): array
    {
        return Normative::None;
    }
}
?>