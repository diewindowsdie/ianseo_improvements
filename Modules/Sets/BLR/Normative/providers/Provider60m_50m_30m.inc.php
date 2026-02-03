<?php
class Provider60m_50m_30m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'W') || $class === "U18M") {
                if ($score >= 970)
                    return Normative::Master;
                if ($score >= 920)
                    return Normative::Candidate;
                if ($score >= 865)
                    return Normative::First;
                if ($score >= 790)
                    return Normative::Second;
                if ($score >= 740)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }
}

?>