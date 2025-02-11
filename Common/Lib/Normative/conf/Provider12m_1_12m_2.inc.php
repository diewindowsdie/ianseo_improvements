<?php
class Provider12m_1_12m_2 implements NormativeProvider
{
    function calculateNormativeInternal($class, $division, $score)
    {
        if ($division == 'R') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 420)
                    return '1ю';
                if ($score >= 410)
                    return '2ю';
                if ($score >= 400)
                    return '3ю';
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 410)
                    return '1ю';
                if ($score >= 405)
                    return '2ю';
                if ($score >= 400)
                    return '3ю';
            }

            return '';
        } else if ($division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 440)
                    return '1ю';
                if ($score >= 430)
                    return '2ю';
                if ($score >= 420)
                    return '3ю';
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 430)
                    return '1ю';
                if ($score >= 420)
                    return '2ю';
                if ($score >= 410)
                    return '3ю';
            }

            return '';
        }

        return '';
    }
}

?>