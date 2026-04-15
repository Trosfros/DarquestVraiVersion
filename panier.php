<?php
session_start();
require 'config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$totalItems = 0;
$items = [];
$total = 0;
$hasStockError = false; 

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $itemId => $itemQty) {
        $sql = "SELECT IdItems, Nom, QuantiteStock, Prix, chemin_image FROM Items WHERE IdItems = ?";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stockDisponible = $row['QuantiteStock'];
            $insufficient = ($itemQty > $stockDisponible);
            if ($insufficient) $hasStockError = true;

            $items[] = [
                'id' => $row['IdItems'],
                'name' => $row['Nom'],
                'price' => $row['Prix'],
                'qty' => $itemQty,
                'stock' => $stockDisponible,
                'insufficient' => $insufficient,
                'image' => $row['chemin_image']
            ];
            $totalItems += $itemQty;
            $total += ($row['Prix'] * $itemQty);
        }
    }
} 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AVERSE - Panier</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #fcfcfc; color: #333; font-family: 'Poppins', sans-serif; margin: 0; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; min-height: 70vh; }
        
        .cart-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #d4af37; padding-bottom: 15px; margin-bottom: 30px; }
        .cart-title { color: #111; font-family: 'Cinzel', serif; margin: 0; font-size: 2rem; }
        
        .cart-layout { display: flex; gap: 40px; align-items: flex-start; }
        
        .cart-items-list { flex: 1; } 

        .error-banner {
            background: #fff5f5; border-left: 5px solid #e74c3c; color: #c0392b;
            padding: 15px 20px; border-radius: 8px; margin-bottom: 25px;
            display: flex; align-items: center; gap: 15px;
        }

        .cart-item { 
            display: flex; align-items: center; background: #fff; padding: 20px; 
            margin-bottom: 15px; border-radius: 12px; gap: 20px; 
            border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .cart-item.item-error { border: 1px solid #e74c3c; background: #fffafa; }

        .item-img-box { width: 110px; height: 110px; background: #f9f9f9; border-radius: 8px; overflow: hidden; border: 1px solid #eee; display: flex; align-items: center; justify-content: center; }
        .item-img-box img { max-width: 85%; max-height: 85%; object-fit: contain; }

        .item-details { flex: 1; }
        .stock-warning { color: #e74c3c; font-size: 0.85rem; font-weight: bold; margin-top: 5px; }

        .quantity-control { display: flex; align-items: center; background: #f0f0f0; border-radius: 25px; padding: 5px 15px; margin-top: 12px; width: fit-content; }
        .qty-btn { border: none; background: none; cursor: pointer; color: #555; padding: 0 10px; font-size: 1.2rem; transition: 0.2s; }
        .qty-btn:hover { color: #d4af37; }
        
        .cart-summary { width: 380px; position: sticky; top: 100px; } 
        .summary-box { background: #fff; padding: 30px; border-radius: 15px; border: 1px solid #d4af37; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        .btn-order { 
            background: #d4af37; color: #000; border: none; width: 100%; padding: 18px; 
            font-weight: bold; border-radius: 10px; cursor: pointer; font-size: 1.1rem; 
            transition: 0.3s; text-transform: uppercase; margin-top: 15px;
        }
        .btn-order:hover:not(:disabled) { background: #000; color: #d4af37; }
        .btn-order:disabled { background: #ccc; cursor: not-allowed; color: #777; }

        .secure-badge {
            margin-top: 20px; padding: 10px;
        }

        /* Overlay */
        .order-success-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; justify-content: center; align-items: center; }
        .success-card { background: white; padding: 40px; border-radius: 20px; text-align: center; transform: scale(0.7); transition: 0.4s; border-top: 8px solid #d4af37; max-width: 450px; }
        .success-card.show { transform: scale(1); }

        @keyframes progress { from { width: 0; } to { width: 100%; } }
        /* Style pour l'input de quantité manuel */
.qty-input {
    width: 45px;
    border: none;
    background: transparent;
    text-align: center;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    font-size: 1rem;
    color: #333;
    outline: none;
}

/* Cache les flèches par défaut du navigateur */
.qty-input::-webkit-inner-spin-button, 
.qty-input::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
    </style>
</head>
<body>

<?php include_once 'template/header.php' ?>

<main class="container">
    <div class="cart-header">
        <h1 class="cart-title">Votre Panier</h1>
        
    </div>

    <?php if (empty($items)): ?>
        <div class="empty-cart" style="text-align:center; padding:80px 0;">
            <i class="fa-solid fa-box-open" style="font-size: 5rem; color: #ddd; margin-bottom: 20px;"></i>
            <h2 style="font-family: 'Cinzel', serif;">Votre panier est vide</h2>
            <a href="index.php" class="btn-order" style="text-decoration:none; display:inline-block; width:auto; padding: 15px 40px;">Boutique</a>
        </div>
    <?php else: ?>

        <?php if ($hasStockError): ?>
            <div class="error-banner">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Rupture de stock partielle !</strong> Certains items ne sont plus disponibles en quantité demandée.
                </div>
            </div>
        <?php endif; ?>

        <div class="cart-layout" id="main-layout">
            <div class="cart-items-list">
                <?php foreach ($items as $item): ?>
                <div class="cart-item <?= $item['insufficient'] ? 'item-error' : '' ?>">
                    <div class="item-img-box">
                        <img src="img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>

                    <div class="item-details">
                        <h3 style="margin: 0 0 5px 0; font-size: 1.1rem;"><?= htmlspecialchars($item['name']) ?></h3>
                        <span style="color: #888; font-size: 0.9rem;">Prix unitaire: <?= $item['price'] ?> 🟡</span>
                        
                        <?php if($item['insufficient']): ?>
                            <div class="stock-warning">
                                <i class="fa-solid fa-circle-exclamation"></i> Stock max : <?= $item['stock'] ?>
                            </div>
                        <?php endif; ?>

                        <div class="quantity-control">
    <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, -1)">
        <?= ($item['qty'] > 1) ? '<i class="fa-solid fa-minus"></i>' : '<i class="fa-solid fa-trash-can" style="color:#e74c3c"></i>' ?>
    </button>
    
    <input type="number" 
           class="qty-input" 
           value="<?= $item['qty'] ?>" 
           min="1" 
           max="<?= $item['stock'] ?>"
           onchange="setManualQty(<?= $item['id'] ?>, this.value)">
    
    <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, 1)">
        <i class="fa-solid fa-plus"></i>
    </button>
</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.3rem; font-weight: 700; color: #d4af37;"><?= $item['price'] * $item['qty'] ?> 🟡</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary">
                <div class="summary-box">                

                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.4rem; padding-top: 15px; margin-bottom: 25px;">
                        <span>Total</span>
                        <span style="color: #d4af37;"><?= $total ?> 🟡</span>
                    </div>
                    
                    <?php if(!isset($_SESSION['alias'])): ?>
                        <a href="register.php" class="btn-order" style="text-decoration:none; display:block; text-align:center;">Se connecter</a>
                    <?php else: ?>
                        <button id="btn-valider-commande" 
                                onclick="validerCommande()" 
                                class="btn-order" 
                                <?= $hasStockError ? 'disabled' : '' ?>>
                            <?= $hasStockError ? 'Ajuster les stocks' : 'Finaliser la transaction' ?>
                        </button>
                    <?php endif; ?>

                    <div class="secure-badge">
                        <strong>💳 Paiement sécurisé 🔒</strong><br>
                    </div>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</main>

<div id="success-overlay" class="order-success-overlay">
    <div class="success-card" id="success-card">
        <div style="font-size: 60px; color: #2ecc71; margin-bottom: 20px;"><i class="fa-solid fa-circle-check"></i></div>
        <h2 style="font-family: 'Cinzel', serif; color: #111;">Achat Réussi</h2>
        <p>Vos nouveaux équipements vous attendent dans votre inventaire.</p>
        <div style="margin-top: 25px; height: 6px; background: #eee; border-radius: 10px; overflow: hidden;">
            <div style="width: 100%; height: 100%; background: #d4af37; animation: progress 2.5s linear;"></div>
        </div>
    </div>
</div>

<script>
const currentCart = {
    <?php foreach($items as $item): ?>
    "<?= $item['id'] ?>": <?= $item['qty'] ?>,
    <?php endforeach; ?>
};

function updateQty(id, delta) {
    let formData = new FormData();
    formData.append('id', id);
    formData.append('qty', delta);
    fetch('add_to_cart.php', { method: 'POST', body: formData })
    .then(() => location.reload());
}

function setManualQty(id, newVal) {
    let val = parseInt(newVal);
    let oldVal = currentCart[id];
    
    if (isNaN(val) || val < 1) {
        updateQty(id, (1 - oldVal)); 
        return;
    }

    let delta = val - oldVal;
    
    if (delta !== 0) {
        updateQty(id, delta);
    }
}

function validerCommande() {
    const btn = document.getElementById('btn-valider-commande');
    const overlay = document.getElementById('success-overlay');
    const card = document.getElementById('success-card');
    const layout = document.getElementById('main-layout');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> TRANSFERT EN COURS...';

    fetch('commander.php', { method: 'POST' })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            layout.style.filter = 'blur(8px)';
            overlay.style.display = 'flex';
            setTimeout(() => { card.classList.add('show'); }, 50);
            setTimeout(() => { window.location.href = 'inventaire.php'; }, 2600); 
        } else {
            alert("Erreur : " + data.message);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        btn.disabled = false;
        btn.innerText = "Réessayer";
    });
}
</script>
</body>
</html>