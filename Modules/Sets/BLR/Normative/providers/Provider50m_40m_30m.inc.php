<?php
class Provider50m_40m_30m extends NormativeProvider
{
    public function calcByTotalScore($class, $division, $score): array
    {
        if (str_starts_with($division, 'R')) {
            if ($class == 'U18W') {
                if ($score >= 990)
                    return Normative::Master;
                if ($score >= 920)
                    return Normative::Candidate;
                if ($score >= 870)
                    return Normative::First;
                if ($score >= 815)
                    return Normative::Second;
                if ($score >= 755)
                    return Normative::Third;
                if ($score >= 700)
                    return Normative::FirstJunior;
            }
        }

        return Normative::None;
    }
}

?>