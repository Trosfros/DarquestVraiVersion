<?php
require_once 'config.php';
require 'include/user.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alias = $_POST['alias'];
    $mdp = $_POST['mdp'];

    $stmt = $connexion->prepare("SELECT MDP FROM Joueurs WHERE Alias = ?");
    $stmt->bind_param("s", $alias);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (password_verify($mdp, $row['MDP'])) {
        LogUser($alias);
        header("Location: index.php");
        exit();
    }
}
