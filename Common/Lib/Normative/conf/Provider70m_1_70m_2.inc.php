<?php
class Provider70m_1_70m_2 implements NormativeProvider
{
    function calculateNormativeInternal($class, $division, $score)
    {
        if ($division == 'C') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 685)
                    return 'МСМК';
                if ($score >= 660)
                    return 'МС';
                if ($score >= 640)
                    return 'КМС';
                if ($score >= 610)
                    return '1';
                if ($score >= 555)
                    return '2';
                if ($score >= 525)
                    return '3';
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 675)
                    return 'МСМК';
                if ($score >= 655)
                    return 'МС';
                if ($score >= 620)
                    return 'КМС';
                if ($score >= 585)
                    return '1';
                if ($score >= 535)
                    return '2';
                if ($score >= 505)
                    return '3';
            }
        } else if ($division == 'R') {
            if (str_ends_with($class, 'M')) {
                if ($score >= 670)
                    return 'МСМК';
                if ($score >= 640)
                    return 'МС';
                if ($score >= 615)
                    return 'КМС';
                if ($score >= 580)
                    return '1';
                if ($score >= 530)
                    return '2';
                if ($score >= 500)
                    return '3';
            } else if (str_ends_with($class, 'W')) {
                if ($score >= 664)
                    return 'МСМК';
                if ($score >= 630)
                    return 'МС';
                if ($score >= 600)
                    return 'КМС';
                if ($score >= 560)
                    return '1';
                if ($score >= 510)
                    return '2';
                if ($score >= 480)
                    return '3';
            }
        }

        return '';
    }
}

?>