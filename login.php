<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AVERSE - Connexion</title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        .login-box { max-width: 400px; margin: 100px auto; padding: 20px; background: #111; border: 1px solid #d4af37; border-radius: 8px; color: white; text-align: center; }
        input { width: 90%; padding: 10px; margin: 10px 0; border-radius: 5px; border: none; }
        .btn-login { background: #d4af37; color: black; font-weight: bold; border: none; padding: 10px 20px; cursor: pointer; border-radius: 20px; width: 100%; }
    </style>
</head>
<body>
    
    <?php include_once 'template/header.php' ?>
    

    <p></p>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'restricted'): ?>
    <div style="color: #e74c3c; background: rgba(231, 76, 60, 0.1); border: 1px solid #e74c3c; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold;">
        <i class="fas fa-exclamation-circle"></i> 
        Vous devez être connecté pour accéder aux quêtes.
    </div>
<?php endif; ?>
    <div class="login-box">
        <h2 style="font-family: 'Cinzel', serif; color: #d4af37;">Accès au Royaume</h2>
        <form action="auth.php" method="POST">
            <input type="text" name="alias" placeholder="Alias (Pseudo)" required>
            <input type="password" name="mdp" placeholder="Mot de passe" required>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
        <?php if(isset($_GET['error'])) echo "<p style='color:red;'>Identifiants invalides</p>"; ?>
    </div>
    <?php include_once 'template/footer.php' ?>
</body>
</html>