<?php
/* Verschl�sselungsformular vor Zufallsfunden sch�tzen? 
* Wenn true muss an die URL ein ?neu (oder anderes Query-String) angeh�ngt werden, um das Formular zu sehen */
$protect = true;
$protectstring = 'neu';

/* Speicherdauer */
$s = 7;

/* Maximale L�nge der Zeichenkette */
$max = 200;

/* Base-URL des Tools */
	/* Base-URL automatisch bestimmen */
	$baseurl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . explode("?",$_SERVER['REQUEST_URI'])[0];

	/* Manuell */
	// $baseurl = 'https://your-url-to-tool.de/secrets/'; 



/* Vorschaumodus aktivieren? (empfohlen nur f�r Test + Debugging)
* Erm�glicht bei Kenntnis des geheimen Links und des Vorschau-Passworts den Aufruf eines Einmal-Abruf-Links, ohne dass das Geheimnis gel�scht wird.
* ?id=id&key=key&vorschau=vorschaupass */
$vorschaumodus = false;
$vorschaupass = 'wysiwyg';