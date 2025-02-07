<?php
class Provider18m_1_18m_2 implements NormativeProvider
{
    function calculateNormativeInternal($class, $division, $score)
    {
        if (str_ends_with($class, 'M')) {
            if ($score >= 586)
                return 'МСМК';
            if ($score >= 570)
                return 'МС';
            if ($score >= 530)
                return 'КМС';
            if ($score >= 500)
                return '1';
            if ($score >= 455)
                return '2';
            if ($score >= 415)
                return '3';
            if ($score >= 375)
                return '1ю';
            if ($score >= 335)
                return '2ю';
            if ($score >= 295)
                return '3ю';
        } else if (str_ends_with($class, 'W')) {
            if ($score >= 582)
                return 'МСМК';
            if ($score >= 566)
                return 'МС';
            if ($score >= 525)
                return 'КМС';
            if ($score >= 490)
                return '1';
            if ($score >= 450)
                return '2';
            if ($score >= 410)
                return '3';
            if ($score >= 370)
                return '1ю';
            if ($score >= 330)
                return '2ю';
            if ($score >= 290)
                return '3ю';
        }

        return '';
    }
}

?>