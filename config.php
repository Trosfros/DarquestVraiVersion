<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$serveur = "localhost";
$utilisateur = "root";
$motdepasse = "";
$nomBaseDonnees = "mydb";

$connexion = new mysqli($serveur, $utilisateur, $motdepasse, $nomBaseDonnees);

if ($connexion->connect_error) {
    die("Erreur de connexion: " . $connexion->connect_error);
}

$connexion->set_charset("utf8");
?>
