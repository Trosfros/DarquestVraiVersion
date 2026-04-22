<?php
require_once 'config.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tri = isset($_GET['tri']) ? $_GET['tri'] : '';

if (!in_array($tri, ['A', 'D']))
    $tri = '';

$stmt = $connexion->prepare("CALL GetMarketItems(12, ?, '$tri')");
$stmt->bind_param('s', $search);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVERSE - Catalogue</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/catalogue.css">
</head>
<body>

<?php include 'template/header.php'; ?>

<div class="container">
    
    <h1 class="page-title">
        <?php if (!empty($search)): ?>
            <span style="color: #888; font-size: 1rem; display: block;">Résultats pour :</span>
            "<?= htmlspecialchars($search) ?>"
        <?php else: ?>
            L'Armurerie AVERSE
        <?php endif; ?>
    </h1>

    <div class="filter-bar">
        <span><strong><?= count($items) ?></strong> objet(s) trouvé(s)</span>
        
        <form method="GET" class="sort-form">
            <?php if(!empty($search)): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <?php endif; ?>

            <label for="tri">Trier par : </label>
            <select name="tri" id="tri" onchange="this.form.submit()">
                <option value="" <?= $tri == '' ? 'selected' : '' ?>>Nom (A-Z)</option>
                <option value="A" <?= $tri == 'P' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="D" <?= $tri == 'P' ? 'selected' : '' ?>>Prix décroissant</option>
            </select>
        </form>
    </div>

    <div class="items-grid">
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <a href="produit.php?id=<?= $item['IdItem'] ?>" class="item-card">
                    <div class="item-img">
                        <?php if (!empty($item['image'])): ?>
                            <img src="img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['Nom']) ?>">
                        <?php else: ?>
                            <div style="color: #ccc;">🖼️ Aucun visuel</div>
                        <?php endif; ?>
                    </div>

                    <div class="badges">
                        <span class="category-badge">🏷️ <?= htmlspecialchars($item['NomType']) ?></span>
                        <span class="stock-badge">📦 <?= $item['Quantite'] ?></span>
                    </div>

                    <h3 class="item-name"><?= htmlspecialchars($item['Nom']) ?></h3>
                    <p class="item-desc"><?= htmlspecialchars(substr($item['Description'], 0, 85)) ?>...</p>

                    <div class="price-zone">
                        <div class="price"><?= number_format($item['Prix'], 0, '.', ' ') ?> <span>PO</span></div>
                        <div class="view-btn">Détails</div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-result">
                <i class="fas fa-search" style="font-size: 3rem; display: block; margin-bottom: 10px;"></i>
                Aucun objet ne correspond à votre recherche voyageur.
                <br><a href="catalogue.php" style="color: #d4af37; font-size: 1rem; text-decoration: none; border-bottom: 1px solid;">Voir tout le catalogue</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
