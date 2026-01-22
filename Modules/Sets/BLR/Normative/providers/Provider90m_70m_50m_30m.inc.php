<?php

class Provider90m_70m_50m_30m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_ends_with($class, 'M') && str_starts_with($division, 'R')) {
            if ($score >= 1320)
                return Normative::International;
            if ($score >= 1224)
                return Normative::Master;
            if ($score >= 1145)
                return Normative::Candidate;
            if ($score >= 1055)
                return Normative::First;
            if ($score >= 970)
                return Normative::Second;
            if ($score >= 900)
                return Normative::Third;
        }

        return Normative::None;
    }
}

?>