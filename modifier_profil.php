<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$idJoueur = $_SESSION['user']['IdJoueur'];
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alias = $_POST['alias'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $newMdp = $_POST['new_mdp'] ?? '';
    $confirmMdp = $_POST['confirm_mdp'] ?? '';


    $sql = "UPDATE Joueurs SET Alias = ?, Nom = ?, Prenom = ? WHERE IdJoueur = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("sssi", $alias, $nom, $prenom, $idJoueur);
    
    if ($stmt->execute()) {
        $message = "Profil mis à jour !";
    }

    if (!empty($newMdp)) {
        if ($newMdp === $confirmMdp) {
            $hashedMdp = password_hash($newMdp, PASSWORD_BCRYPT);
            $sqlMdp = "UPDATE Joueurs SET MDP = ? WHERE IdJoueur = ?";
            $stmtMdp = $connexion->prepare($sqlMdp);
            $stmtMdp->bind_param("si", $hashedMdp, $idJoueur);
            $stmtMdp->execute();
            $message .= " Et le mot de passe a été changé.";
        } else {
            $error = "Les mots de passe ne correspondent pas !";
            $message = ""; 
        }
    }
}

// Récupération des infos actuelles
$sql = "SELECT * FROM Joueurs WHERE IdJoueur = ?";
$stmt = $connexion->prepare($sql);
$stmt->bind_param("i", $idJoueur);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$alias_val  = $user['Alias']  ?? $user['alias']  ?? '';
$prenom_val = $user['Prenom'] ?? $user['prenom'] ?? '';
$nom_val    = $user['Nom']    ?? $user['nom']    ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AVERSÉ - Paramètres</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --gold: #d4af37;
            --error: #ff4757;
            --success: #2ed573;
            --glass: rgba(255, 255, 255, 0.03);
        }

        body {
            background: radial-gradient(circle at center, #111 0%, #050507 100%);
            color: white; font-family: 'Poppins', sans-serif; margin: 0;
        }

        .settings-card {
            max-width: 600px; margin: 60px auto; padding: 40px;
            background: var(--glass); backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.08); border-radius: 25px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: rgba(46, 213, 115, 0.1); border: 1px solid var(--success); color: var(--success); }
        .alert-error { background: rgba(255, 71, 87, 0.1); border: 1px solid var(--error); color: var(--error); }

        .form-group { margin-bottom: 20px; position: relative; }
        label { display: block; font-size: 0.75rem; color: #888; margin-bottom: 8px; text-transform: uppercase; }
        
        input {
            width: 100%; padding: 12px 15px; background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; color: white;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
        }

        input:focus { border-color: var(--gold); background: rgba(255, 255, 255, 0.1); outline: none; }

        input.match { border-color: var(--success) !important; }
        input.no-match { border-color: var(--error) !important; }

        .btn-save {
            width: 100%; padding: 15px; background: var(--gold); border: none;
            border-radius: 12px; color: black; font-weight: bold; cursor: pointer;
            text-transform: uppercase; transition: 0.3s;
        }

        .btn-save:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3); }
        .divider { height: 1px; background: rgba(255,255,255,0.05); margin: 30px 0; }
    </style>
</head>
<body>

<?php include_once 'template/header.php' ?>

<div class="settings-card">
    <h2 style="font-family: 'Cinzel', serif; color: var(--gold); text-align: center;">Paramètres</h2>

    <?php if ($message): ?> <div class="alert alert-success"><?= $message ?></div> <?php endif; ?>
    <?php if ($error): ?> <div class="alert alert-error"><?= $error ?></div> <?php endif; ?>

    <form action="" method="POST" id="settingsForm">
        <div class="form-group">
            <label>Alias</label>
            <input type="text" name="alias" value="<?= htmlspecialchars($alias_val) ?>" required>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Prénom</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($prenom_val) ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Nom</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($nom_val) ?>">
            </div>
        </div>

        <div class="divider"></div>

        <div class="form-group">
            <label>Nouveau Mot de Passe</label>
            <input type="password" name="new_mdp" id="new_mdp" placeholder="Laisser vide pour ne pas changer">
        </div>

        <div class="form-group">
            <label>Confirmer le Mot de Passe</label>
            <input type="password" name="confirm_mdp" id="confirm_mdp" placeholder="••••••••">
            <small id="matchText" style="display:none; font-size: 0.7rem; margin-top: 5px;"></small>
        </div>

        <button type="submit" class="btn-save">Mettre à jour</button>
    </form>
    
    <a href="profil.php" style="display:block; text-align:center; margin-top:20px; color:#666; text-decoration:none; font-size:0.8rem;">Retour au profil</a>
</div>

<script>
    const newMdp = document.getElementById('new_mdp');
    const confirmMdp = document.getElementById('confirm_mdp');
    const matchText = document.getElementById('matchText');

    function checkMatch() {
        if (confirmMdp.value.length > 0) {
            matchText.style.display = "block";
            if (newMdp.value === confirmMdp.value) {
                confirmMdp.classList.add('match');
                confirmMdp.classList.remove('no-match');
                matchText.innerText = "✓ Les mots de passe correspondent";
                matchText.style.color = "var(--success)";
            } else {
                confirmMdp.classList.add('no-match');
                confirmMdp.classList.remove('match');
                matchText.innerText = "✗ Les mots de passe diffèrent";
                matchText.style.color = "var(--error)";
            }
        } else {
            matchText.style.display = "none";
            confirmMdp.classList.remove('match', 'no-match');
        }
    }

    newMdp.addEventListener('input', checkMatch);
    confirmMdp.addEventListener('input', checkMatch);
</script>

</body>
</html>
