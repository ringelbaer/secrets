<?php
// Funktionen zur Verschluesselung und Entschluesselung mit sicherem Modus
function verschluessele($text, $schluessel) {
    $cipher = 'AES-256-CBC';
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext = openssl_encrypt($text, $cipher, $schluessel, 0, $iv);
    return base64_encode($iv . $ciphertext);
}

function entschluessele($text, $schluessel) {
    $cipher = 'AES-256-CBC';
    $data = base64_decode($text);
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($data, 0, $ivlen);
    $ciphertext = substr($data, $ivlen);
    return openssl_decrypt($ciphertext, $cipher, $schluessel, 0, $iv);
}

// Funktion zur Generierung einer zufaelligen ID
function generateRandomId() {
    return bin2hex(random_bytes(16)); // 32 Zeichen lange ID
}

// CSRF-Token generieren und ueberpruefen
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        unset($_SESSION['csrf_token']); // Token nach ueberpruefung entfernen
        return true;
    }
    return false;
}