<?php

class Provider60m_1_60m_2 implements NormativeProvider
{
    function calculateNormativeInternal($class, $division, $score)
    {
        if (str_contains($class, "U18")) {
            if ($division == 'C') {
                if (str_ends_with($class, 'M')) {
                    if ($score >= 650)
                        return 'КМС';
                    if ($score >= 620)
                        return '1';
                    if ($score >= 570)
                        return '2';
                    if ($score >= 530)
                        return '3';
                } else if (str_ends_with($class, 'W')) {
                    if ($score >= 640)
                        return 'КМС';
                    if ($score >= 610)
                        return '1';
                    if ($score >= 560)
                        return '2';
                    if ($score >= 510)
                        return '3';
                }
            } else if ($division == 'R') {
                if (str_ends_with($class, 'M')) {
                    if ($score >= 625)
                        return 'КМС';
                    if ($score >= 595)
                        return '1';
                    if ($score >= 545)
                        return '2';
                    if ($score >= 510)
                        return '3';
                } else if (str_ends_with($class, 'W')) {
                    if ($score >= 605)
                        return 'КМС';
                    if ($score >= 580)
                        return '1';
                    if ($score >= 525)
                        return '2';
                    if ($score >= 490)
                        return '3';
                }
            }
        }
        return '';
    }
}

?>