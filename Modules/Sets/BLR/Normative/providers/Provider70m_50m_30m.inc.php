<?php
class Provider70m_50m_30m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if (str_ends_with($class, 'M')) {
                if ($score >= 954)
                    return Normative::Master;
                if ($score >= 904)
                    return Normative::Candidate;
                if ($score >= 845)
                    return Normative::First;
                if ($score >= 785)
                    return Normative::Second;
                if ($score >= 725)
                    return Normative::Third;
            }
        }

        return Normative::None;
    }
}

?>