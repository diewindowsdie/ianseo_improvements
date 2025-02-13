<?php
class Provider50m_1_50m_2 implements NormativeProvider
{
    function calculateNormativeInternal($class, $division, $score)
    {
        if ($division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 700)
                    return 'МСМК';
                if ($score >= 680)
                    return 'МС';
                if ($score >= 660)
                    return 'КМС';
                if ($score >= 640)
                    return '1';
                if ($score >= 620)
                    return '2';
                if ($score >= 600)
                    return '3';
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 690)
                    return 'МСМК';
                if ($score >= 670)
                    return 'МС';
                if ($score >= 650)
                    return 'КМС';
                if ($score >= 630)
                    return '1';
                if ($score >= 610)
                    return '2';
                if ($score >= 590)
                    return '3';
            }
        }

        return '';
    }
}

?>