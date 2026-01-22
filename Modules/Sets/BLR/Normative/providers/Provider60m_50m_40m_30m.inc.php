<?php

class Provider60m_50m_40m_30m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R') && str_starts_with($class, 'U18W')) {
            if ($score >= 1290)
                return Normative::Master;
            if ($score >= 1175)
                return Normative::Candidate;
            if ($score >= 1090)
                return Normative::First;
            if ($score >= 990)
                return Normative::Second;
            if ($score >= 920)
                return Normative::Third;
            if ($score >= 860)
                return Normative::FirstJunior;
        }

        return Normative::None;
    }
}

?>