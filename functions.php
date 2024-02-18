<?php
	// Funktionen zur Verschlüsselung und Entschlüsselung
	function verschluessele($text, $schluessel) {
		return openssl_encrypt($text, 'AES-128-ECB', $schluessel);
	}

	function entschluessele($text, $schluessel) {
		return openssl_decrypt($text, 'AES-128-ECB', $schluessel);
	}
	
	// Funktion zur Generierung einer zufälligen id
	function generateRandomId() {
		return random_int(1, 999999999);
	}

	// Funktion zur Überprüfung, ob die id bereits existiert
	function isIdUnique($db, $id) {
		$idqu = $db->prepare("SELECT COUNT(*) FROM geheimnisse WHERE id = :id");
		$idqu->bindValue(':id', $id, PDO::PARAM_INT);
		$idqu->execute();
		$anzahl = $idqu->fetchColumn();
		return $anzahl == 0;
	}