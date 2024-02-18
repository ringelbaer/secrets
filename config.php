<?php
/* Verschlüsselungsformular vor Zufallsfunden schützen? 
* Wenn true muss an die URL ein ?neu (oder anderes Query-String) angehängt werden, um das Formular zu sehen */
$protect = true;
$protectstring = 'neu';

/* Speicherdauer */
$s = 7;

/* Maximale Länge der Zeichenkette */
$max = 200;

/* Base-URL des Tools */
	/* Base-URL automatisch bestimmen */
	$baseurl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . explode("?",$_SERVER['REQUEST_URI'])[0];

	/* Manuell */
	// $baseurl = 'https://your-url-to-tool.de/secrets/'; 



/* Vorschaumodus aktivieren? (empfohlen nur für Test + Debugging)
* Ermöglicht bei Kenntnis des geheimen Links und des Vorschau-Passworts den Aufruf eines Einmal-Abruf-Links, ohne dass das Geheimnis gelöscht wird.
* ?id=id&key=key&vorschau=vorschaupass */
$vorschaumodus = false;
$vorschaupass = 'wysiwyg';