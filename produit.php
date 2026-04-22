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
    <link rel="stylesheet" href="styles/produit.css">
   
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
