<?php
require_once 'config.php';

$id_produit = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "CALL GetItemById(?)";
$stmt = $connexion->prepare($sql);
$stmt->bind_param("i", $id_produit);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$produit = $result->fetch_assoc();
$isOutOfStock = ($produit['Quantite'] <= 0);
$stockMax = intval($produit['Quantite']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVERSE - <?= htmlspecialchars($produit['Nom']) ?></title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #ffffff; color: #333; font-family: 'Poppins', sans-serif; margin: 0; }
        
        .product-detail-container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 20px;
            display: flex;
            gap: 40px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        /* --- IMAGE --- */
        .product-image-box {
            flex: 1;
            min-width: 350px;
            background: #f9f9f9;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid #eee;
            min-height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-image-box.out-of-stock { filter: grayscale(1); opacity: 0.6; }
        .product-image-box img { max-width: 90%; max-height: 400px; object-fit: contain; }

        /* --- INFOS --- */
        .product-info { flex: 1; min-width: 350px; }
        
        .back-link { 
            text-decoration: none; color: #888; font-size: 0.9rem; 
            display: inline-block; margin-bottom: 20px; transition: 0.3s;
        }
        .back-link:hover { color: #d4af37; transform: translateX(-5px); }

        .badges-container { margin-bottom: 15px; display: flex; gap: 10px; }
        .category-badge, .stock-badge {
            padding: 5px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;
        }
        .category-badge { background: #f0f0f0; color: #555; }
        .stock-badge { background: #f0fff0; color: #27ae60; }
        .stock-badge.empty { background: #fff0f0; color: #e74c3c; border: 1px solid #e74c3c; }

        .product-info h1 { font-family: 'Cinzel', serif; font-size: 2.8rem; margin: 10px 0; color: #111; }
        .description { color: #666; line-height: 1.7; margin-bottom: 30px; }

        .price-tag { font-size: 2.5rem; font-weight: bold; color: #d4af37; margin-bottom: 25px; }
        .price-tag span { font-size: 0.9rem; color: #aaa; letter-spacing: 1px; }

        /* --- ZONE ACHAT --- */
        .purchase-zone { 
            display: flex; gap: 15px; padding: 25px;
            background: #fcfcfc; border: 1px solid #eee; border-radius: 12px;
        }
        
        .qty-input {
            display: flex; align-items: center; border: 2px solid #eee;
            border-radius: 10px; background: white;
        }
        .qty-input.disabled { opacity: 0.4; pointer-events: none; }
        
        .qty-input button { 
            background: none; border: none; padding: 10px 15px; 
            cursor: pointer; font-size: 1.2rem; color: #d4af37; font-weight: bold;
            transition: 0.2s;
        }

        /* Classe pour griser le bouton quand le max est atteint */
        .qty-input button.max-reached {
            color: #ccc !important;
            cursor: not-allowed;
        }

        .qty-input input { width: 45px; text-align: center; border: none; font-weight: bold; font-size: 1.1rem; }

        .add-to-cart-btn {
            background: #111; color: #d4af37; border: none;
            padding: 15px 35px; border-radius: 10px; font-weight: bold;
            flex-grow: 1; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            text-transform: uppercase;
        }
        .add-to-cart-btn:hover { background: #d4af37; color: #111; }
        .add-to-cart-btn.disabled { background: #ccc !important; color: #777 !important; cursor: not-allowed; }

        @media (max-width: 768px) { .product-detail-container { flex-direction: column; } }
    </style>
</head>
<body>

<?php include 'template/header.php'; ?>

<main class="product-detail-container">
    <div class="product-image-box <?= $isOutOfStock ? 'out-of-stock' : '' ?>">
        <?php if (!empty($produit['image'])): ?>
            <img src="img/<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['Nom']) ?>">
        <?php else: ?>
            <div style="color: #ccc; text-align: center;">🖼️ Aucun visuel</div>
        <?php endif; ?>
    </div>

    <div class="product-info">
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        
        <div class="badges-container">
            <span class="category-badge">🏷️ <?= htmlspecialchars($produit['Type']) ?></span>
            <span class="stock-badge <?= $isOutOfStock ? 'empty' : '' ?>">
                <?= $isOutOfStock ? '❌ Rupture' : '📦 ' . $produit['Quantite'] . ' en stock' ?>
            </span>
        </div>

        <h1><?= htmlspecialchars($produit['Nom']) ?></h1>
        <p class="description"><?= nl2br(htmlspecialchars($produit['Description'])) ?></p>
        <p class="description">Vendeur: <?= htmlspecialchars($produit['Vendeur']) ?></p>

        <div class="price-tag"><?= number_format($produit['Prix'], 0, '.', ' ') ?> 🟡 <span>OR</span></div>

        <div class="purchase-zone">
            <div class="qty-input <?= $isOutOfStock ? 'disabled' : '' ?>">
                <button onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
                <input type="text" id="quantity" value="<?= $isOutOfStock ? 0 : 1 ?>" readonly>
                <button id="btn-plus" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
            </div>

            <?php if ($isOutOfStock): ?>
                <button class="add-to-cart-btn disabled" disabled>Épuisé</button>
            <?php else: ?>
                <button class="add-to-cart-btn" onclick="addToCart(<?= $id_produit ?>)">
                    <i class="fa-solid fa-cart-shopping"></i> Ajouter
                </button>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
const stockMax = <?= $stockMax ?>;

function updatePlusButton(currentQty) {
    const btnPlus = document.getElementById('btn-plus');
    if (currentQty >= stockMax) {
        btnPlus.classList.add('max-reached');
    } else {
        btnPlus.classList.remove('max-reached');
    }
}

function changeQty(val) {
    const input = document.getElementById('quantity');
    let current = parseInt(input.value);
    
    if (stockMax <= 0) return;

    const newVal = current + val;
    if (newVal >= 1 && newVal <= stockMax) {
        input.value = newVal;
        updatePlusButton(newVal);
    }
}

function addToCart(id) {
    const qty = document.getElementById('quantity').value;
    if (qty <= 0) return;

    let formData = new FormData();
    formData.append('id', id);
    formData.append('qty', qty);
    
    fetch('add_to_cart.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let count = document.getElementById('cart-count');
            if (count) {
                count.innerText = data.total;
                count.parentElement.style.transform = "scale(1.3)";
                setTimeout(() => count.parentElement.style.transform = "scale(1)", 200);
            }
        } 
    });
}

// Initialisation au chargement
window.onload = () => {
    updatePlusButton(parseInt(document.getElementById('quantity').value));
};
</script>

</body>
</html>
