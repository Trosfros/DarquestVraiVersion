<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AVERSE - Inscription</title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        .register-box { max-width: 400px; margin: 80px auto; padding: 30px; background: #111; border: 1px solid #d4af37; border-radius: 10px; color: white; text-align: center; }
        input { width: 90%; padding: 12px; margin: 10px 0; border-radius: 5px; border: none; background: #222; color: #fff; }
        .btn-reg { background: #d4af37; color: #000; font-weight: bold; border: none; padding: 12px; cursor: pointer; border-radius: 25px; width: 95%; transition: 0.3s; }
        .btn-reg:hover { background: #fff; }
        
        /* Style pour le message d'erreur */
        .error-msg { 
            background: rgba(231, 76, 60, 0.2); 
            border: 1px solid #e74c3c; 
            color: #e74c3c; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
            font-size: 0.9rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include_once 'template/header.php' ?>

    <div class="register-box">
        <h2 style="font-family: 'Cinzel', serif; color: #d4af37;">Nouveau Guerrier</h2>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'alias_taken'): ?>
            <div class="error-msg">
                ⚠️ Cet alias est déjà possédé par un autre guerrier !
            </div>
        <?php endif; ?>

        <form action="process_register.php" method="POST">
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="alias" placeholder="Alias (Pseudo)" required>
            <input type="password" name="mdp" placeholder="Mot de passe" required>
            <button type="submit" class="btn-reg">Rejoindre AVERSE</button>
        </form>
        
        <p><a href="login.php" style="color: #999; font-size: 0.8rem;">Déjà inscrit ? Connectez-vous</a></p>
    </div>

    <?php include_once 'template/footer.php' ?>

</body>
</html>