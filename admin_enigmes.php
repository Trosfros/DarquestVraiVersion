<?php
session_start();
require 'check_admin.php'; 
require 'config.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_enigme'])) {
    $idCategorie = intval($_POST['id_categorie']);
    $question = htmlspecialchars($_POST['question']); 
    $r1 = htmlspecialchars($_POST['r1']);
    $r2 = htmlspecialchars($_POST['r2']);
    $r3 = htmlspecialchars($_POST['r3']);
    $r4 = htmlspecialchars($_POST['r4']);
    $bonne_rep = intval($_POST['bonne_rep']);

    $difficulte = $idCategorie;


    $est_magie = ($idCategorie == 3) ? 1 : 0;

    $sql = "INSERT INTO enigme (IdCategorie, Difficulte, Question, Reponse1, Reponse2, Reponse3, Reponse4, BonneReponse, EstMagie) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $connexion->prepare($sql);
    
    // "iisssssii" = 9 paramètres
    $stmt->bind_param("iisssssii", 
        $idCategorie, 
        $difficulte, 
        $question, 
        $r1, 
        $r2, 
        $r3, 
        $r4, 
        $bonne_rep, 
        $est_magie
    );

    if ($stmt->execute()) {
        $msg_status = ($est_magie == 1) ? "Magique ✨" : "Standard ⚔️";
        $message = "<p style='color: #27ae60; font-weight: bold;'>📜 Quête scellée avec succès ! ($msg_status)</p>";
    } else {
        $message = "<p style='color: #e74c3c;'>❌ Erreur SQL : " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Grimoire</title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        .admin-box { max-width: 800px; margin: 40px auto; padding: 30px; background: #1a1a1a; color: white; border-radius: 15px; border: 2px solid #d4af37; box-shadow: 0 0 25px rgba(0,0,0,0.7); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
        label { color: #d4af37; font-weight: bold; margin-top: 15px; display: block; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        input, select { width: 100%; padding: 12px; background: #262626; border: 1px solid #444; color: white; border-radius: 5px; box-sizing: border-box; }
        input:focus, select:focus { border-color: #d4af37; outline: none; background: #333; }
        .btn-submit { background: #d4af37; color: black; border: none; padding: 18px; width: 100%; margin-top: 30px; border-radius: 35px; font-weight: bold; cursor: pointer; transition: 0.3s; text-transform: uppercase; }
        .btn-submit:hover { background: white; transform: translateY(-2px); }
        h1 { text-align: center; font-family: 'Cinzel', serif; color: #d4af37; text-shadow: 2px 2px 4px black; }
    </style>
</head>
<body>

<?php include 'template/header.php'; ?>

<main class="admin-box">
    <h1>🛠️ Forgeron de Quêtes</h1>
    <?= $message ?>

    <form method="POST">
        <label>📍 Origine de la Quête :</label>
        <select name="id_categorie">
            <option value="1">🔨 Forgeron (Facile)</option>
            <option value="2">🛡️ Armurier (Moyen)</option>
            <option value="3">✨ Grand Mage (Difficile + Magie)</option>
        </select>

        <label>❓ Énigme :</label>
        <input type="text" name="question" maxlength="100" placeholder="Le texte de ton énigme..." required>

        <div class="form-grid">
            <div><label>Réponse 1 :</label><input type="text" name="r1" required></div>
            <div><label>Réponse 2 :</label><input type="text" name="r2" required></div>
            <div><label>Réponse 3 :</label><input type="text" name="r3" required></div>
            <div><label>Réponse 4 :</label><input type="text" name="r4" required></div>
        </div>

        <label>✅ Index de la Vérité :</label>
        <select name="bonne_rep">
            <option value="1">Réponse 1</option>
            <option value="2">Réponse 2</option>
            <option value="3">Réponse 3</option>
            <option value="4">Réponse 4</option>
        </select>

        <button type="submit" name="add_enigme" class="btn-submit">INSCRIRE DANS LE GRIMOIRE 📜</button>
    </form>
</main>

</body>
</html>