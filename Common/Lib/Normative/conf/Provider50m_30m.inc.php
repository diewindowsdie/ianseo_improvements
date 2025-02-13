<?php
class Provider50m_30m implements NormativeProvider
{
    function calculateNormativeInternal($class, $division, $score)
    {
        if ($division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 575)
                    return '1';
                if ($score >= 550)
                    return '2';
                if ($score >= 500)
                    return '3';
                if ($score >= 450)
                    return '1ю';
                if ($score >= 420)
                    return '2ю';
                if ($score >= 390)
                    return '3ю';
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 565)
                    return '1';
                if ($score >= 540)
                    return '2';
                if ($score >= 490)
                    return '3';
                if ($score >= 440)
                    return '1ю';
                if ($score >= 410)
                    return '2ю';
                if ($score >= 380)
                    return '3ю';
            }
        } else if ($division == 'R') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 550)
                    return '1';
                if ($score >= 530)
                    return '2';
                if ($score >= 480)
                    return '3';
                if ($score >= 430)
                    return '1ю';
                if ($score >= 400)
                    return '2ю';
                if ($score >= 370)
                    return '3ю';
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 540)
                    return '1';
                if ($score >= 520)
                    return '2';
                if ($score >= 470)
                    return '3';
                if ($score >= 420)
                    return '1ю';
                if ($score >= 390)
                    return '2ю';
                if ($score >= 360)
                    return '3ю';
            }
        }

        return '';
    }
}

?>