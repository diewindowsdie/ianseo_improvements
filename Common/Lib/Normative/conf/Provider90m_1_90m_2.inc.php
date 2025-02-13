<?php
class Provider90m_1_90m_2 implements NormativeProvider
{
    function calculateNormativeInternal($class, $division, $score)
    {
        if ($division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 640)
                    return 'МС';
                if ($score >= 600)
                    return 'КМС';
                if ($score >= 530)
                    return '1';
                if ($score >= 500)
                    return '2';
                if ($score >= 450)
                    return '3';
            }
        } else if ($division == 'R') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 580)
                    return 'МС';
                if ($score >= 540)
                    return 'КМС';
                if ($score >= 500)
                    return '1';
                if ($score >= 460)
                    return '2';
                if ($score >= 420)
                    return '3';
            }
        }

        return '';
    }
}

?>