<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?message=restricted'); 
    exit();
}

$stmtUser = $connexion->prepare("SELECT QuetesMagieReussies, EstMage, 
    NbQuetesFacileSuccess, NbQuetesFacileTotal, 
    NbQuetesMoyenSuccess, NbQuetesMoyenTotal, 
    NbQuetesDifficileSuccess, NbQuetesDifficileTotal, 
    PV FROM Joueurs WHERE IdJoueur = ?");
$stmtUser->bind_param("i", $_SESSION['user_id']);
$stmtUser->execute();
$userStats = $stmtUser->get_result()->fetch_assoc();

if ($userStats) {
    $_SESSION['quetes_magie'] = $userStats['QuetesMagieReussies'];
    $_SESSION['EstMage'] = $userStats['EstMage'];
    
    // Succès
    $_SESSION['NbQuetesFacileSuccess'] = $userStats['NbQuetesFacileSuccess'];
    $_SESSION['NbQuetesMoyenSuccess'] = $userStats['NbQuetesMoyenSuccess'];
    $_SESSION['NbQuetesDifficileSuccess'] = $userStats['NbQuetesDifficileSuccess'];
    
  
    $_SESSION['NbQuetesFacileTotal'] = $userStats['NbQuetesFacileTotal'];
    $_SESSION['NbQuetesMoyenTotal'] = $userStats['NbQuetesMoyenTotal'];
    $_SESSION['NbQuetesDifficileTotal'] = $userStats['NbQuetesDifficileTotal'];
    
    $_SESSION['pv'] = $userStats['PV'];
}

$is_playing = false;
$enigme = null;


if (isset($_GET['action']) && $_GET['action'] === 'start' && isset($_GET['diff'])) {
    $is_playing = true;

    $diff = (int)$_GET['diff'];
    $query = $connexion->prepare("
            SELECT e.*, c.Categorie as NomCat 
            FROM Enigme e 
            LEFT JOIN CategorieEnigme c ON e.IdCategorie = c.IdCategorie 
            WHERE ? = 0 or e.Difficulte = ?
            ORDER BY RAND() 
            LIMIT 1
            ");
    $query->execute([$diff, $diff]);
    $enigme = $query->get_result()->fetch_assoc();
}
if ($is_playing && !$enigme) {
    $enigme = ['IdEnigme' => 0, 'Difficulte' => 1, 'Question' => 'Aucune énigme.', 'Reponse1' => 'Vide', 'Reponse2' => 'Vide', 'Reponse3' => 'Vide', 'Reponse4' => 'Vide', 'NomCat' => 'Aucune'];
}

$estMage = (isset($_SESSION['EstMage']) && $_SESSION['EstMage'] == 1);
$classeNom = $estMage ? 'Maître des Arcanes' : 'Apprenti Guerrier';
$classeIcon = $estMage ? 'fa-hat-wizard' : 'fa-hammer';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Averse - Salle des Quêtes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        :root {
            --bg-light: #fdfdfd;
            --card-dark: #1a1a1a;
            --gold: #d4af37;
            --red: #e74c3c;
            --green: #2ecc71;
            --purple: #9b59b6;
        }

        body { background-color: var(--bg-light); font-family: 'Segoe UI', sans-serif; color: #333; }
        .dashboard { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .page-title { font-family: 'Cinzel', serif; color: var(--gold); text-align: center; margin-bottom: 40px; }

        /* --- Profil Identity --- */
        .player-identity {
            display: flex; align-items: center; gap: 20px;
            background: #111; padding: 15px 25px; border-radius: 50px;
            margin-bottom: 20px; border: 2px solid #333; transition: 0.5s;
        }
        .is-mage-profile { border-color: var(--purple); background: linear-gradient(145deg, #111, #2c003e); box-shadow: 0 0 20px rgba(155, 89, 182, 0.3); }
        .class-avatar { width: 50px; height: 50px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: var(--gold); border: 2px solid var(--gold); }
        .is-mage-profile .class-avatar { color: #dfa6ff; border-color: var(--purple); }
        .player-info h2 { margin: 0; font-size: 1rem; color: white; font-family: 'Cinzel', serif; }
        .class-tag { font-size: 0.6rem; text-transform: uppercase; letter-spacing: 2px; color: #777; font-weight: bold; }
        .is-mage-profile .class-tag { color: var(--purple); }

        /* --- Alertes --- */
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; text-align: center; font-weight: bold; animation: fadeIn 0.5s ease; }
        .alert-success { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71; }
        .alert-error { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid #e74c3c; }

        /* --- Stats Bar --- */
        .stats-bar { background: #151515; border-radius: 12px; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; color: white; margin-bottom: 30px; }
        .stat-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; }
        .stat-item i { color: var(--gold); }

        /* --- Grille et Cartes --- */
        .quest-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
    gap: 25px; 
}


        .quest-card { background: white; border-radius: 15px; padding: 30px; text-align: center; text-decoration: none; color: inherit; transition: 0.3s; border: 1px solid #eee; border-bottom: 5px solid #ccc; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .quest-card:hover { transform: translateY(-10px); background: #fffdf5; }
        .quest-card.bronze { border-bottom-color: #cd7f32; }
        .quest-card.silver { border-bottom-color: #c0c0c0; }
        .quest-card.gold { border-bottom-color: var(--gold); }
        .quest-icon { font-size: 2.5rem; margin-bottom: 15px; }
        .reward-tag { display: inline-block; padding: 5px 12px; background: #eee; border-radius: 20px; font-weight: bold; font-size: 0.75rem; }

        /* --- Section Mage --- */
        .mage-section { margin-top: 50px; padding-top: 30px; border-top: 2px dashed #ddd; }
        .section-title { font-family: 'Cinzel', serif; font-size: 1.3rem; color: #444; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .locked-container { position: relative; background: #eee; border-radius: 15px; padding: 40px; text-align: center; border: 2px solid #ccc; overflow: hidden; }
        .locked-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(4px); display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 2; }
        .locked-overlay i { font-size: 2.5rem; color: #888; margin-bottom: 10px; }
        .locked-overlay p { font-weight: bold; color: #666; text-transform: uppercase; font-size: 0.8rem; }
        .unlocked-mage-zone { background: linear-gradient(145deg, #1a1a1a, #2c003e); border: 2px solid var(--purple); box-shadow: 0 0 30px rgba(155, 89, 182, 0.4); animation: aura-glow 3s infinite alternate; }
        
        @keyframes aura-glow { from { box-shadow: 0 0 15px rgba(155, 89, 182, 0.2); } to { box-shadow: 0 0 35px rgba(155, 89, 182, 0.6); } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* --- Enigme Card --- */
        .enigma-card { background: var(--card-dark); color: white; border-radius: 15px; padding: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
        .badge-diff { padding: 6px 15px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; animation: pulse-glow 2s infinite; }
        .diff-1 { background: #2ecc71; color: #fff; }
        .diff-2 { background: #f1c40f; color: #000; }
        .diff-3 { background: #e74c3c; color: #fff; }
        .cat-badge { display: flex; align-items: center; gap: 8px; background: rgba(212, 175, 55, 0.1); color: var(--gold); padding: 5px 12px; border-radius: 8px; border: 1px solid var(--gold); font-size: 0.8rem; }
        .question-text { font-size: 1.4rem; margin: 30px 0; line-height: 1.4; text-align: center; }
        .choices-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .choice-btn { background: #252525; border: 1px solid #333; color: #ccc; padding: 18px; border-radius: 8px; cursor: pointer; transition: 0.2s; }
        .choice-btn:hover { background: #333; border-color: var(--gold); color: white; }
        .btn-abandon { display: inline-block; margin-top: 30px; padding: 10px 25px; background: #333; color: #888 !important; text-decoration: none; border-radius: 5px; font-size: 0.8rem; }
        .btn-abandon:hover { background: #c0392b; color: white !important; }
        .stats-title {
    font-family: 'Cinzel', serif;
    font-size: 1rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stats-title {
    font-family: 'Cinzel', serif;
    font-size: 1rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Style pour le lien */
.stats-link {
    text-decoration: none;
    color: inherit; /* Garde la couleur grise du titre */
    transition: 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
}

/* Effet au survol */
.stats-link:hover {
    color: var(--gold); /* Le titre devient doré au survol */
}

/* Petit texte "Voir détails" */
.stats-hint {
    font-size: 0.65rem;
    text-transform: none; /* Enlève les majuscules pour ce textee */
    letter-spacing: 0;
    color: #999;
    opacity: 0; /* Caché par défaut */
    transition: 0.3s;
    transform: translateX(-10px);
}

.stats-link:hover .stats-hint {
    opacity: 1; /* Apparaît au survol */
    transform: translateX(0);
}

.stats-title::after {
    content: "";
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, #ddd, transparent);
}
.class-avatar i {
    display: block; 
    color: var(--gold);
    font-size: 1.5rem; 
}
    </style>
</head>
<body>

<?php include_once 'template/header.php'; ?>

<div class="dashboard">
    <div class="player-identity <?= $estMage ? 'is-mage-profile' : '' ?>">
        <div class="class-avatar">
            <i class="fas <?= $classeIcon ?>"></i>
        </div>
        <div class="player-info">
            <span class="class-tag"><?= $classeNom ?></span>
            <h2><?= htmlspecialchars($_SESSION['pseudo'] ?? 'Aventurier') ?></h2>
        </div>
        <?php if($estMage): ?>
            <div style="margin-left: auto; color: var(--purple); font-size: 0.8rem; font-weight: bold;">
                <i class="fas fa-sparkles"></i> MAGE ACTIF
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['feedback'])): ?>
        <div class="alert alert-<?= $_SESSION['feedback']['status'] === 'success' ? 'success' : 'error' ?>">
            <i class="fas <?= $_SESSION['feedback']['status'] === 'success' ? 'fa-trophy' : 'fa-skull-crossbones' ?>"></i>
            <?= $_SESSION['feedback']['message'] ?>
        </div>
        <?php unset($_SESSION['feedback']); ?>
    <?php endif; ?>

<h2 class="stats-title">
    <a href="profil.php" class="stats-link">
        <i class="fas fa-chart-line"></i> 
        Voir statistiques détaillées<i class="fas fa-arrow-right"> </i>
        <span class="stats-hint">(Voir détails)</span>
    </a>
</h2>

<div class="stats-bar"> <div class="stat-item">
        <i class="fas fa-check-circle"></i> 
        <?php 
            $reussies = ($_SESSION['NbQuetesFacileSuccess'] ?? 0) + 
                        ($_SESSION['NbQuetesMoyenSuccess'] ?? 0) + 
                        ($_SESSION['NbQuetesDifficileSuccess'] ?? 0);
            echo $reussies;
        ?> résolues
    </div>

    <div class="stat-item">
        <i class="fas fa-times-circle" style="color:var(--red);"></i> 
        <?php 
            $total_essais = ($_SESSION['NbQuetesFacileTotal'] ?? 0) + 
                            ($_SESSION['NbQuetesMoyenTotal'] ?? 0) + 
                            ($_SESSION['NbQuetesDifficileTotal'] ?? 0);
            
            $total_succes = $reussies;
            echo ($total_essais - $total_succes);
        ?> échouées
    </div>

    <div class="stat-item">
        <i class="fas fa-wand-magic-sparkles"></i> 
        <?= min($_SESSION['quetes_magie'] ?? 0, 5) ?>/5 magie
    </div>
</div>

    <?php if (!$is_playing): ?>
        <h1 class="page-title"><i class="fas fa-scroll"></i> Salle des Quêtes</h1>
        <div class="quest-grid">
            <a href="enigma.php?action=start&diff=1" class="quest-card bronze">
                <div class="quest-icon" style="color:#cd7f32;"><i class="fas fa-hammer"></i></div>
                <h3>Forgeron</h3>
                <p>Énigmes sur les <strong>Armes</strong>.</p>
                <div class="reward-tag">Facile</div>
            </a>

            <a href="enigma.php?action=start&diff=2" class="quest-card silver">
                <div class="quest-icon" style="color:#c0c0c0;"><i class="fas fa-shield-alt"></i></div>
                <h3>Armurier</h3>
                <p>Énigmes sur les <strong>Armures</strong>.</p>
                <div class="reward-tag">Moyen</div>
            </a>

            <a href="enigma.php?action=start&diff=3" class="quest-card gold">
                <div class="quest-icon" style="color:var(--gold);"><i class="fas fa-wand-sparkles"></i></div>
                <h3>Grand Mage</h3>
                <p>Énigmes sur la <strong>Magie</strong>.</p>
                <div class="reward-tag">Difficile</div>
            </a>

            <a href="enigma.php?action=start&diff=0" class="quest-card" style="border-bottom-color: var(--purple); background: #fdf0ff;">
                <div class="quest-icon" style="color: var(--purple);"><i class="fas fa-dice"></i></div>
                <h3>Mélange Magouilleux</h3>
                <p>Difficulté <strong>totalement aléatoire</strong>. Oserez-vous ?</p>
                <div class="reward-tag" style="background: var(--purple); color: white;">Gain Variable</div>
            </a>
        </div>

        <div class="mage-section">
            <h2 class="section-title">
                <i class="fas fa-sparkles" style="color: var(--purple);"></i> 
                Sanctuaire des Arcanes
            </h2>

            <?php if ($estMage): ?>
                <div class="quest-card unlocked-mage-zone">
                    <div class="quest-icon" style="color: #dfa6ff;"><i class="fas fa-book-spells"></i></div>
                    <h3 style="color: white;">Grimoire Interdit</h3>
                    <p style="color: #bbb;">Accédez aux secrets les plus profonds d'Averse.</p>
                    <a href="mage_special.php" class="choice-btn" style="display:inline-block; text-decoration:none; margin-top:15px; border-color: var(--purple);">
                        Entrer dans le Sanctuaire
                    </a>
                </div>
            <?php else: ?>
    <div class="locked-container">
        <div class="locked-overlay">
            <i class="fas fa-lock"></i>
            <p>Devenez Mage pour débloquer (<?= $_SESSION['quetes_magie'] ?? 0 ?>/5)</p>
        </div>
        <div style="filter: blur(8px); opacity: 0.3;">
            <i class="fas fa-dragon" style="font-size: 3rem;"></i>
            <h3>Quête Légendaire</h3>
        </div>
    </div>
<?php endif; ?>
        </div>

    <?php else: ?>
        <div class="enigma-card">
            <div style="display:flex; justify-content:space-between; align-items: center;">
                <?php 
                    $diffClass = "diff-" . $enigme['Difficulte'];
                    $diffLabel = ($enigme['Difficulte'] == 3) ? 'Difficile' : (($enigme['Difficulte'] == 2) ? 'Moyen' : 'Facile');
                ?>
                <span class="badge-diff <?= $diffClass ?>"><?= $diffLabel ?></span>
                <div class="cat-badge">
                    <i class="fas fa-scroll"></i>
                    <span><?= htmlspecialchars($enigme['NomCat'] ?? 'Général') ?></span>
                </div>
            </div>

            <h2 class="question-text"><?= htmlspecialchars($enigme['Question']) ?></h2>

            <form action="verifier_enigme.php" method="POST" class="choices-grid">
                <input type="hidden" name="id_enigme" value="<?= $enigme['IdEnigme'] ?>">
                <button type="submit" name="choix" value="1" class="choice-btn"><?= htmlspecialchars($enigme['Reponse1']) ?></button>
                <button type="submit" name="choix" value="2" class="choice-btn"><?= htmlspecialchars($enigme['Reponse2']) ?></button>
                <button type="submit" name="choix" value="3" class="choice-btn"><?= htmlspecialchars($enigme['Reponse3']) ?></button>
                <button type="submit" name="choix" value="4" class="choice-btn"><?= htmlspecialchars($enigme['Reponse4']) ?></button>
            </form>
            
            <div style="text-align:center;">
                <a href="enigma.php" class="btn-abandon"><i class="fas fa-flag"></i> Abandonner</a>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
