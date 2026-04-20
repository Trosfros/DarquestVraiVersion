<?php
require_once 'config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $alias = $_POST['alias'];
    $mdp_hashe = password_hash($_POST['mdp'], PASSWORD_BCRYPT);

    try {
        $sql = "INSERT INTO Joueurs (Alias, Nom, Prenom, MDP) VALUES (?, ?, ?, ?)";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param("ssss", $alias, $nom, $prenom, $mdp_hashe);

        if ($stmt->execute()) {
            LogUser($alias);
            header("Location: index.php");
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        
        if ($e->getCode() === 1062) {
            header("Location: register.php?error=alias_taken");
            exit();
        } else {
            die("Erreur fatale du grimoire : " . $e->getMessage());
        }
    }
}
?>
