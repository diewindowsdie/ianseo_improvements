<?php
$lang['AclNotSuperuser']='Sans accès "SUPERUSER", vous serez bloqué hors de cette section et ne pourrez donc pas activer les Politiques d\'Accès.';
$lang['AuthTourCode']='Liste séparée par des virgules des codes de compétitions qui peuvent être gérés par l\'utilisateur.<br> Charactère générique autorisé : « % »';
$lang['AutoCheckinAlreadyDone']='Vous êtes déjà enregistré pour la compétition! Si vous devez modifier des informations, retournez au greffe.';
$lang['AutoCheckinConfirm']='Si tous les détails sont corrects, appuyez sur CONFIRMER L\'ENREGISTREMENT. Sinon, appuyez sur ANNULER et rendez-vous au Greffe.';
$lang['AutoCheckinSearch']='Scannez le QR Code que vous avez reçu ou saisissez votre nom!';
$lang['AutoCHK-CanEdit']='Autoriser les utilisateurs à modifier les noms, les e-mails, le pays/club, etc.';
$lang['AutoCHK-Code']='Liste des concours, un par ligne<br> Le premier code de concours sera utilisé comme en-tête dans les bornes d\'enregistrement automatique.';
$lang['AutoCHK-IP']='Liste des IP des appareils d\'enregistrement automatique. Une adresse IP par ligne, correspondant à l\'un des formats suivants :<br> IP de l\'appareil<br> IP de l\'appareil | File d\'attente d\'imprimante d\'accréditation <br> IP du périphérique | File d\'attente d\'impression d\'accréditation | Étiquette de nom de file d\'attente d\'imprimante<br> File d\'attente d\'imprimante sous la forme : Nom de la file d\'attente [ @ Serveur d\'impression ]<br> Si aucune file d\'attente d\'imprimante n\'est définie, la boîte de dialogue d\'impression standard s\'affiche';
$lang['AutoCHK-IPinfo']='Liste des IP des Bornes d\'auto-information. (Une adresse IP par ligne)';
$lang['AutoCHK-IPnoMgm']='List of IP of self check-in kiosks. One IP Address per line, matching one of the following formats:<br> Device IP<br> Device IP | Accreditation Printer Queue <br> Device IP | Accreditation Printer Queue | Name Tag Printer Queue<br> Printer Queue in form: Queue Name [ @ Printer Server ]';
$lang['AutoCHK-Print']='Impressions automatique';
$lang['AutoImportSettings']='<b>Uniquement pour les utilisateurs experts</b><br>La modification du comportement par défaut peut <b>entrainer des résultats inexacts.</b> <br> Il est important de recalculer tous les rangs qui ont été configurés comme "manuellement" AVANT d’envoyer à ianseo.net ou d’imprimer des résultats et en général avant toute distribution de quelque nature que ce soit.';
$lang['ChangeComponents']='<p>Pour procéder à un changement, supprimez d\'abord les athlètes qui ne font plus partie de l\'équipe afin d\'activer les options possibles.</p> ▶ Score inclus dans le total du tour de qualification par équipe <br> ▶ Score non inclus dans le total du tour de qualification par équipe';
$lang['CombiCompList']='Liste des codes de compétitions! Séparateur","';
$lang['DeleteChannel']='Supprimer le canal. Cela va supprimer toutes les séparation et le canal';
$lang['ExportAdvanced']='Exporte également les données d\'Entrée et de Pays pour les créer si elles sont manquantes lors du réimport de la compétition.';
$lang['ExportCategories']='Choisissez les catégories/épreuves à exporter (aucune sélection signifie tout)';
$lang['ExportDistances']='Sélectionnez la ou les distances à exporter (aucune sélection signifie toutes les distances)';
$lang['ExportSchedule']='Sélectionnez dans le planning le départ à exporter.';
$lang['ExportScheduleToday']='Affiche uniquement le(s) départ(s) programmé(s) pour aujourd\'hui';
$lang['FontFamily']='Nom de la police utilisé dans le ce CSS';
$lang['FontFile']='Chemin d\'accès de la police';
$lang['FontName']='Nom de la police actuelle';
$lang['FontStyle']='Style dans le CSS';
$lang['FontWeight']='Poids de la police en CSS';
$lang['GetBarcodeSeparator']='Aprèsl\'mpression du tableau de référence des codes barres, lire le "SÉPARATEUR" du code barre pour activer la lecture correcte des élément.';
$lang['HomePage']='Voici la page où vous pouvez sélectionner ou créer un tournoi.';
$lang['ISK-LockedSessionHelp']='Les icônes {$a} indiquent si l\'application autorise la saisie ou non dans ce départ.';
$lang['ISK-ServerUrlPin']='<b>Ne pas partager ce code</b><br>Utiliser un code Pin à 4 chiffres pour sécuriser votre compétition.<br>Les tablettes peuvent se connecter à la compétition via le QR-Code.<br> En cas de saisie manuelle dans l\'application, utiliser ce code <b>{$a}</b>';
$lang['QrCodeLandscape']='Un simple ou double "<" fera une flèche vers la gauche, un simple ou double ">" fera une flèche vers la droite, n simple ou double "^" fera une flèche vers le haut et un simple ou double "v" fera une flèche vers la bas';
$lang['QrCodePortrait']='Le champ est compatible HTML. Si vous insérez quelque chose entouré de &lt;qrcode&gt;...&lt;/qrcode&gt;, le contenu sera transformé en un QRCode.';
$lang['RecordGrayLine']='Un record pour cette catégorie est déjà présent avec un score plus élevé.<br>Vérifiez les règles et les dates d\'attribution des records.';
$lang['RecordPinkCell']='Un record pour la même catégorie est déjà présent.<bt>N\'écrivez dans le PDF que le plus important.</bt>';
$lang['ScoreBarCodeShortcuts']='Lire le code-barre de la feuille de marque.<br/> Insertion Manuelle d\'un # suivi par le nom de l\'Athlète recherche dans la base de données pour le trouver<br/> Insérer un @ suivit d\'un N° de cible recherche cette cible. La distance doit être indiquée; la session doit être spécifiée (un chiffre) et la cible 000 (3 chiffres)';
$lang['ScoreboardFreetext-Texts']='Insérez {{date}} pour insérer la date au format anglais (par exemple : 4 janvier 2024).
<br>
Insérez {{date-lang}} pour insérer une date dans la langue locale (par exemple : {{date-fr}} pour avoir 4 janvier 2024).
<br>
Insérez {{time}} pour avoir l\'heure en cours au format ISO (par exemple : 15:03:23).
<br>
Insérez {{time-am}} pour avoir l\'heure en cours au format AM/PM (par exemple : 3:03:23 PM).
<br>
Insérez {{counter-datetime}} pour avoir un compte à rebours complet (par exemple : [[[jours:]heures:]minutes:]secondes, où datetime est l\'heure à atteindre au format ISO 2024-12-04T09:00:00 pour l\'heure locale).
<br>
Il ne peut y avoir qu\'un seul de ces champs dans un texte.';
$lang['TargetRequests-Printout']='Imprime les QRCodes des cibles demandées pour permettre aux périphériques d\'être rapidement réaffectés à la bonne cible.<br> Sélectionnez le(s) groupe(s) d\'appareils que vous souhaitez imprimer et la plage de cibles :
<br> 1-10 => Imprime le QRCode des cibles 1 à 10
<br> 1,7,12-15 => Imprime le QRCode des cibles 1, 7 et de 12 à 15';
$lang['TargetScoring-Printout']='Imprime les QRCodes des cibles demandées pour permettre aux périphériques d\'être rapidement réaffectés à la bonne cible. Il s\'agit du meme QR-Code que celui présent sur les feuilles de marques.
<br> Sélectionnez le(s) groupe(s) d\'appareils que vous souhaitez imprimer et la plage de cibles :
<br> 1-10 => Imprime le QRCode des cibles 1 à 10
<br> 1,7,12-15 => Imprime le QRCode des cibles 1, 7 et de 12 à 15';
$lang['TV-ChannelSetup']='= Configuration des Canaux =
<br> Après avoir configuré vos canaux comme souhaité, connectez le navigateur de l\'appareil que vous souhaitez lier à un canal à <code>http://IP.DU.SERVEUR.IANSEO/tv.php?id=CANAL</code> où \'\'\'IP.DU.SERVEUR.IANSEO\'\'\' est l\'IP où ianseo est en cours d\'exécution (y compris le répertoire si applicable) et \'\'\'CANAL\'\'\' est l\'ID du canal.';
$lang['TV-RotEdit']='<div>Une page de présentation est composée d\'au moins une page de contenu.</div>
<div>Les pages de contenu seront ensuite affichées les unes après les autres et recommenceront.</div>
<div><b>NOTE :</b> dans les versions régulières et légères du moteur, le premier contenu est à nouveau affiché en dernier, il est donc judicieux d\'insérer en premier contenu une image (logo de la compétition par exemple).</div>
<div>Les contenus peuvent être basés sur la compétition (liste de départ, qualification, matchs...) ou "multimédia" (images, messages HTML,...).</div>';
$lang['TV-RotEdit-DB']='<h2>Unités de Longueur CSS</h2>
<ul>
  <li><b>rem :</b> Basé sur la hauteur du texte de l\'élément racine.</li>
  <li><b>em :</b> Basé sur la hauteur du texte de l\'élément actuel.</li>
  <li><b>ex :</b> Hauteur de la lettre "x" en minuscule.</li>
  <li><b>ch :</b> Largeur du chiffre "0".</li>
  <li><b>vh :</b> 1% de la hauteur totale de l\'écran.</li>
  <li><b>vw :</b> 1% de la largeur totale de l\'écran.</li>
  <li><b>vmin :</b> 1% de la plus petite dimension de l\'écran.</li>
  <li><b>vmax :</b> 1% de la plus grande dimension de l\'écran.</li>
</ul>

<h2>Boîtes Flexibles (Flexbox)</h2>
<ul>
  <li><b>flex A B C :</b>
    <ul>
      <li><b>A :</b> Capacité à s\'élargir (0 = pas d\'expansion ; >1 = expansion).</li>
      <li><b>B :</b> Capacité à rétrécir (0 = pas de rétrécissement ; 1 = rétrécissement possible).</li>
      <li><b>C :</b> Taille initiale de la boîte.</li>
    </ul>
  </li>
</ul>

<h2>Référence CSS</h2>
<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Reference">Référence CSS sur MDN</a>';
$lang['TV-RotList']='<div>Voici la liste des pages de présentation disponibles à envoyer vers le mur vidéo, les moniteurs ou la diffusion.</div>
<div>3 moteurs différents sont fournis, cliquez sur le lien pour activer :</div>
<ul>
    <li>un moteur régulier compatible avec la plupart des navigateurs</li>
    <li>un moteur en version légère compatible avec la plupart des navigateurs mais utilisant moins de ressources</li>
    <li>une version avancée qui utilise les capacités HTML5 des navigateurs modernes</li>
</ul>
<div>Pour créer un nouveau contenu, entrez un nom pour celui-ci et appuyez sur le bouton.</div>';
$lang['UserName']='Identifant unique dans le systeme. Minimum de 6 caractères';
$lang['UserPassword']='Laisser vide pour garder le mot de passe actuel';
$lang['ZeroBased']='Mumérotation à partir de zéro';
?>