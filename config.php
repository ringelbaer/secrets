<?php
/* Verschluesselungsformular vor Zufallsfunden schaetzen? 
* Wenn true muss an die URL ein ?neu (oder anderes Query-String) angehaengt werden, um das Formular zu sehen */
$protect = true;
$protectstring = 'neu';

/* Speicherdauer */
$s = 7;

/* Maximale Laenge der Zeichenkette */
$max = 200;

/* Mehrfachen Abruf des Passworts als konfigurierbare Option erlauben */
$multiple = false;

/* Base-URL des Tools */
	/* Base-URL automatisch bestimmen */
	$baseurl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . explode("?",$_SERVER['REQUEST_URI'])[0];

	/* Manuell */
	// $baseurl = 'https://your-url-to-tool.de/secrets/'; 



/* Vorschaumodus aktivieren? (empfohlen nur fuer Test + Debugging)
* Ermoeglicht bei Kenntnis des geheimen Links und des Vorschau-Passworts den Aufruf eines Einmal-Abruf-Links, ohne dass das Geheimnis geloescht wird.
* ?id=id&key=key&vorschau=vorschaupass */
$vorschaumodus = false;
$vorschaupass = 'wysiwyg';