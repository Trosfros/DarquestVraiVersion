<?php
require 'config.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tri = isset($_GET['tri']) ? $_GET['tri'] : 'nom';

$sql = "SELECT IdItems, Nom, Description, Prix, QuantiteStock, Type, chemin_image FROM Items";
$params = [];
$types = "";

// Si une recherche est effectuée
if (!empty($search)) {
    $sql .= " WHERE Nom LIKE ? OR Description LIKE ? OR Type LIKE ?";
    $searchTerm = "%" . $search . "%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = "sss";
}


switch ($tri) {
    case 'prix_desc':
        $sql .= " ORDER BY Prix DESC";
        break;
    case 'prix_asc':
        $sql .= " ORDER BY Prix ASC";
        break;
    default:
        $sql .= " ORDER BY Nom ASC";
        break;
}

$stmt = $connexion->prepare($sql);
if (!empty($search)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVERSE - Catalogue</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* --- Style Global --- */
        body { background-color: #ffffff; color: #333; font-family: 'Roboto', sans-serif; margin: 0; }
        
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        .page-title {
            font-family: 'Cinzel', serif;
            text-align: center;
            font-size: 2.2rem;
            color: #000;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        /* --- Barre de filtres (Tri & Résultats) --- */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9f9f9;
            padding: 15px 25px;
            border-radius: 12px;
            border: 1px solid #eee;
            margin-bottom: 40px;
        }

        .filter-bar select {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
            outline: none;
        }

        /* --- Grille d'items --- */
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        /* --- Carte Item --- */
        .item-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .item-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border-color: #d4af37;
        }

        .item-img {
            width: 100%;
            height: 220px;
            background: #fdfdfd;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .item-img img { width: 100%; height: 100%; object-fit: contain; }

        .item-name {
            font-family: 'Cinzel', serif;
            font-size: 1.15rem;
            margin: 10px 0;
            color: #000;
            font-weight: 700;
        }

        .item-desc {
            font-size: 0.85rem;
            color: #777;
            margin-bottom: 15px;
            line-height: 1.4;
            height: 40px;
            overflow: hidden;
        }

        /* --- Badges animés --- */
        .badges { margin-bottom: 10px; display: flex; gap: 8px; }
        
        .category-badge {
            background: #fff0f0; color: #e74c3c; padding: 4px 10px;
            border-radius: 5px; font-size: 0.7rem; font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .stock-badge {
            background: #f0fff0; color: #27ae60; padding: 4px 10px;
            border-radius: 5px; font-size: 0.7rem; font-weight: bold;
        }

        .item-card:hover .category-badge {
            background: #e74c3c;
            color: #fff;
            transform: scale(1.05);
        }

        /* --- Prix et Bouton --- */
        .price-zone {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #f5f5f5;
        }

        .price { font-weight: bold; color: #d4af37; font-size: 1.3rem; }
        .price span { font-size: 0.75rem; color: #999; margin-left: 2px; }

        .view-btn {
            background: #000;
            color: #d4af37;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            transition: 0.2s;
        }

        .item-card:hover .view-btn {
            background: #d4af37;
            color: #000;
        }

        .no-result {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            color: #999;
            font-size: 1.2rem;
        }
    </style>
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
                <option value="nom" <?= $tri == 'nom' ? 'selected' : '' ?>>Nom (A-Z)</option>
                <option value="prix_asc" <?= $tri == 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="prix_desc" <?= $tri == 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
            </select>
        </form>
    </div>

    <div class="items-grid">
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <a href="produit.php?id=<?= $item['IdItems'] ?>" class="item-card">
                    <div class="item-img">
                        <?php if (!empty($item['chemin_image'])): ?>
                            <img src="img/<?= htmlspecialchars($item['chemin_image']) ?>" alt="<?= htmlspecialchars($item['Nom']) ?>">
                        <?php else: ?>
                            <div style="color: #ccc;">🖼️ Aucun visuel</div>
                        <?php endif; ?>
                    </div>

                    <div class="badges">
                        <span class="category-badge">🏷️ <?= htmlspecialchars($item['Type']) ?></span>
                        <span class="stock-badge">📦 <?= $item['QuantiteStock'] ?></span>
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
