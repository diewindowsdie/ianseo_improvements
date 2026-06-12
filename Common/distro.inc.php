<?php
define('ProgramRelease', 'STABLE');
define('ProgramBuild', 'rev 204');
define('MinTimeOut', '120');
//Add the Timezone Check if not setup by system
if(strlen(ini_get('date.timezone')))
	date_default_timezone_set(ini_get('date.timezone'));
else
	date_default_timezone_set('UTC');


// Main Versions of Ianseo...
// 2026-03-01 => By failing to prepare, you are preparing to fail. (Benjamin Franklin)
// 2025-09-03 => Congratulations to Greg Easton, 10th President of World Archery - Change is the essential process of all existence. (Spock)
// 2025-05-29 => Truly wonderful the mind of a child is! Welcome Giorgio a.k.a. Gaba 2.0
// 2025-02-10 => The greatest enemy of knowledge is not ignorance, it is the illusion of knowledge. (Stephen Hawking)
// 2024-12-08 => Experience is the teacher of all things (Gaius Iulius Caesar)
// 2023-01-10 => Remembering Er Salustro. Ciao Giggi 💔
// 2023-01-09 => in loving memory of Er Salustro. Ciao Giggi 💔
// 2022-09-20 => in memory of Francesco Gnecchi-Ruscone, a true gentleman
// 2022-07-01 =>  Things are only impossible until they’re not. – Captain Jean-Luc Picard
// 2022-01-01 => Improve a mechanical device and you may double productivity. But improve man, you gain a thousandfold. – Khan Noonien Singh
// 2021-08-08 => Domo Arigato Gozaimasu #Tokyo2020
// 2020-11-10 => Team Work
// 2020-01-01 => Faber est suae quisque fortunae
// 2019-05-04 => potlhbe’chugh yay qatlh pe’’eghlu’ [Lieutenant Worf]
// 2018-09-21 => Sei un testone, ma ci piaci lo stesso
// 2018-03-31 => Otherwise you were thinking it was an April's fool
// 2017.12.13 => Saint Lucy, the former shortest day!
// 2017.11.20 => Because any day can bring new features!
// 2017.06.10 => I hope one day you will realize who is bad and who is not
// 2017.01.01 => Don't just wish for a great 2017, MAKE IT SO!
// 2016.09.21 => Age isn't how old you are but how old you feel
