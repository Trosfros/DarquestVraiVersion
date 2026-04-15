<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alias = $_POST['alias'];
    $mdp = $_POST['mdp'];

    $stmt = $connexion->prepare("SELECT IdJoueur, Alias, MDP, EstAdmin, PieceBronze, PieceArgent, PieceOr, PV FROM Joueurs WHERE Alias = ?");
    $stmt->bind_param("s", $alias);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($mdp, $row['MDP'])) {
            
            $_SESSION['user_id'] = $row['IdJoueur'];
            $_SESSION['alias'] = $row['Alias'];
            $_SESSION['is_admin'] = $row['EstAdmin'];

          
            $_SESSION['piece_bronze'] = $row['PieceBronze'];
            $_SESSION['piece_argent'] = $row['PieceArgent'];
            $_SESSION['piece_or'] = $row['PieceOr'];
            $_SESSION['pv'] = $row['PV'];

            header("Location: index.php");
            exit();
        }
    }
    
    header("Location: login.php?error=1");
    exit();
}