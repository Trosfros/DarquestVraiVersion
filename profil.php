<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$idJoueur = $_SESSION['user']['IdJoueur'];

$stmt = $connexion->prepare("SELECT * FROM Joueurs WHERE IdJoueur = ?");
$stmt->bind_param("i", $idJoueur);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: logout.php');
    exit();
}

// --- LOGIQUE DES DONNÉES ---
$alias = $user['Alias'] ?? $user['alias'] ?? 'Aventurier';
$id = $user['IdJoueur'] ?? $user['idjoueur'] ?? 0;
$pv = $user['PV'] ?? $user['pv'] ?? 0;
$or = $user['PieceOr'] ?? $user['piece_or'] ?? 0;
$argent = $user['PieceArgent'] ?? $user['piece_argent'] ?? 0;
$bronze = $user['PieceBronze'] ?? $user['piece_bronze'] ?? 0;

// --- CALCUL DES STATISTIQUES ---
$f_succes = $user['NbQuetesFacileSuccess'] ?? 0;
$m_succes = $user['NbQuetesMoyenSuccess'] ?? 0;
$d_succes = $user['NbQuetesDifficileSuccess'] ?? 0;

$f_total = $user['NbQuetesFacileTotal'] ?? 0;
$m_total = $user['NbQuetesMoyenTotal'] ?? 0;
$d_total = $user['NbQuetesDifficileTotal'] ?? 0;

$totalSucces = $f_succes + $m_succes + $d_succes;
$totalTentatives = $f_total + $m_total + $d_total;
$tauxReussite = ($totalTentatives > 0) ? round(($totalSucces / $totalTentatives) * 100) : 0;

UpdateUserSessionInfo();

$pvPercent = min(100, max(0, ($pv / 100) * 100));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVERSE - Profil de <?= htmlspecialchars($alias) ?></title>
    
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        /* --- ANIMATIONS --- */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- BASE --- */
        body { background: #050507; color: white; font-family: 'Poppins', sans-serif; margin: 0; overflow-x: hidden; }
        .container-profil { max-width: 850px; margin: 60px auto; padding: 0 20px; animation: slideUp 0.7s ease-out; }
        .profile-card { background: #111; border-radius: 30px; padding: 40px; border: 1px solid rgba(255, 255, 255, 0.05); box-shadow: 0 30px 60px rgba(0,0,0,0.6); position: relative; }

        /* --- HEADER --- */
        .profile-header { display: flex; align-items: center; gap: 30px; margin-bottom: 40px; }
        .avatar-frame { width: 110px; height: 110px; border-radius: 50%; border: 3px solid #d4af37; padding: 5px; background: #1a1a1a; box-shadow: 0 0 20px rgba(212, 175, 55, 0.2); }
        .avatar-frame img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .user-info h1 { font-family: 'Cinzel', serif; color: #d4af37; font-size: 2.5rem; margin: 0; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        .badge-rank { font-size: 0.7rem; letter-spacing: 2px; color: #888; text-transform: uppercase; }

        /* --- VITALITÉ --- */
        .vitalite-hud { margin-bottom: 40px; }
        .vitalite-hud .info { display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: bold; font-size: 0.85rem; color: #aaa; }
        .barre-fond { width: 100%; height: 18px; background: #1a1a1a; border-radius: 10px; border: 1px solid #333; overflow: hidden; }
        .barre-vie { height: 100%; background: linear-gradient(90deg, #ff416c, #ff4b2b); width: 0; transition: width 1.5s cubic-bezier(0.2, 0.8, 0.2, 1); box-shadow: 0 0 15px rgba(255, 65, 108, 0.4); }

        /* --- MONNAIE --- */
        .monnaie-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .coin-box { background: rgba(255,255,255,0.02); padding: 25px; border-radius: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .coin-box:hover { border-color: #d4af37; transform: translateY(-5px); background: rgba(212, 175, 55, 0.03); }
        .coin-box img { width: 55px; height: 55px; object-fit: contain; margin-bottom: 15px; }
        .coin-box h3 { margin: 5px 0; font-size: 1.6rem; color: #fff; }
        .coin-box span { color: #555; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; }

        .stats-section { margin-top: 40px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.05); }
        .stats-title { font-family: 'Cinzel', serif; color: #d4af37; font-size: 1.2rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .global-progress { background: rgba(212, 175, 55, 0.05); padding: 20px; border-radius: 15px; margin-bottom: 20px; border: 1px solid rgba(212, 175, 55, 0.1); display: flex; justify-content: space-between; align-items: center; }
        .stats-grid-detail { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat-detail-box { background: rgba(255,255,255,0.02); padding: 15px; border-radius: 15px; text-align: center; border: 1px solid rgba(255,255,255,0.05); }
        .stat-detail-box i { font-size: 1.2rem; margin-bottom: 8px; display: block; }
        .stat-detail-box .val { font-size: 1.1rem; font-weight: bold; display: block; color: white; }
        .stat-detail-box .lab { font-size: 0.6rem; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .box-f i { color: #cd7f32; } .box-m i { color: #c0c0c0; } .box-d i { color: #d4af37; }

        /* --- ACTIONS --- */
        .actions-bar { display: flex; gap: 15px; }
        .btn { flex: 1; padding: 15px; border-radius: 12px; font-weight: bold; text-decoration: none; text-align: center; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s; border: none; cursor: pointer; }
        .btn-gold { background: #d4af37; color: #000; }
        .btn-gold:hover { background: #f1c40f; box-shadow: 0 0 20px rgba(212, 175, 55, 0.3); }
        .btn-dark { background: #222; color: #fff; border: 1px solid #333; }
        .btn-logout { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid #e74c3c; flex: 0.7; }
        .btn-logout:hover { background: #e74c3c; color: #fff; }
    </style>
</head>
<body>

<?php include_once 'template/header.php'; ?>

<main class="container-profil">
    <div class="profile-card">
        
        <div class="profile-header">
            <div class="avatar-frame">
                <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=<?= urlencode($alias) ?>" alt="Avatar">
            </div>
            <div class="user-info">
                <div class="badge-rank">🛡️ Aventurier #<?= $id ?></div>
                <h1><?= htmlspecialchars($alias) ?></h1>
            </div>
        </div>

        <div class="vitalite-hud">
            <div class="info">
                <span>VITALITÉ</span>
                <span style="color: #ff416c;"><?= $pv ?> / 100 HP</span>
            </div>
            <div class="barre-fond">
                <div class="barre-vie" id="js-hp-bar"></div>
            </div>
        </div>

        <div class="monnaie-grid">
            <div class="coin-box">
                <img src="gold.png" alt="Or">
                <h3><?= number_format($or) ?></h3>
                <span>Pièces d'Or</span>
            </div>
            <div class="coin-box">
                <img src="silver.png" alt="Argent">
                <h3><?= number_format($argent) ?></h3>
                <span>Pièces d'Argent</span>
            </div>
            <div class="coin-box">
                <img src="bronze.png" alt="Bronze">
                <h3><?= number_format($bronze) ?></h3>
                <span>Pièces de Bronze</span>
            </div>
        </div>

        <div class="stats-section">
            <h2 class="stats-title"><i class="fas fa-medal"></i>Statistiques Des Quêtes</h2>

            <div class="global-progress">
                <div>
                    <span style="display:block; font-size: 0.7rem; color: #888; text-transform: uppercase;">Efficacité globale</span>
                    <span style="font-size: 1.5rem; font-weight: bold; color: #d4af37;"><?= $tauxReussite ?>%</span>
                </div>
                <div style="text-align: right;">
                    <span style="display:block; font-size: 0.7rem; color: #888; text-transform: uppercase;">Quêtes Complétées</span>
                    <span style="font-size: 1.5rem; font-weight: bold; color: white;"><?= $totalSucces ?> / <?= $totalTentatives ?></span>
                </div>
            </div>

            <div class="stats-grid-detail">
                <div class="stat-detail-box box-f">
                    <i class="fas fa-hammer"></i>
                    <span class="val"><?= $f_succes ?> / <?= $f_total ?></span>
                    <span class="lab">Facile</span>
                </div>
                <div class="stat-detail-box box-m">
                    <i class="fas fa-shield-alt"></i>
                    <span class="val"><?= $m_succes ?> / <?= $m_total ?></span>
                    <span class="lab">Moyen</span>
                </div>
                <div class="stat-detail-box box-d">
                    <i class="fas fa-wand-sparkles"></i>
                    <span class="val"><?= $d_succes ?> / <?= $d_total ?></span>
                    <span class="lab">Difficile</span>
                </div>
            </div>
        </div>

        <div class="actions-bar">
            <a href="modifier_profil.php" class="btn btn-gold">
                <i class="fas fa-user-pen"></i> MODIFIER
            </a>
            <a href="inventaire.php" class="btn btn-dark">
                <i class="fas fa-scroll"></i> INVENTAIRE
            </a>
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-power-off"></i> QUITTER
            </a>
        </div>

    </div>
</main>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const bar = document.getElementById('js-hp-bar');
            if(bar) bar.style.width = "<?= $pvPercent ?>%";
        }, 400);
    });
</script>

</body>
</html>
