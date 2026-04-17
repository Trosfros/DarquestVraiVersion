<?php
require_once 'config.php';

$result = $connexion->query("CALL GetMarketItems(12, '', '')");
$items = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVERSE - Accueil</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            background-color: #ffffff; 
            color: #333; 
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        /* --- SECTION TITRE --- */
        .nouveaute-header {
            text-align: center;
            margin: 80px 0 60px;
            animation: fadeIn 0.8s ease;
        }

    
        .title-new {
            font-family: 'Cinzel', serif;
            font-size: 3.5rem; 
            margin: 0;
            text-transform: uppercase;
            position: relative;
            display: inline-block;
            line-height: 1;
        }

        .animated-text {
            
            background: linear-gradient(90deg, 
                #a67c00 0%, 
                #d4af37 25%, 
                #ffdf00 50%, 
                #d4af37 75%, 
                #a67c00 100%
            );
            background-size: 200% auto;
            color: #d4af37;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            
            /* Balayage et Grossissement */
            animation: 
                shine-gold 4s linear infinite, 
                pulse-grow 3s ease-in-out infinite;
            
            display: inline-block;
        }

     
        .badge-new {
            color: #e74c3c;
            font-size: 1rem;
            vertical-align: top;
            border: 2px solid #e74c3c;
            padding: 3px 10px;
            border-radius: 6px;
            margin-left: 15px;
            font-family: 'Poppins', sans-serif;
            font-weight: bold;
            -webkit-text-fill-color: #e74c3c; 
            position: relative;
            top: 10px;
        }

     
        @keyframes shine-gold {
            to { background-position: 200% center; }
        }

   
        @keyframes pulse-grow {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 0 5px rgba(212, 175, 55, 0.3));
            }
            50% {
                transform: scale(1.05);
                
                filter: drop-shadow(0 0 15px rgba(212, 175, 55, 0.5)); 
            }
        }

        /* --- GRILLE DE PRODUITS --- */
        .product-grid {
            max-width: 1200px;
            margin: 0 auto 80px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            padding: 0 20px;
        }

        .product-card { 
            background: #fff;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            animation: slideUp 0.6s ease both;
        }

        .product-card:hover { 
            transform: translateY(-8px);
            border-color: #d4af37;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .img-box { 
            width: 100%; height: 200px; 
            background: #fcfcfc;
            border-radius: 8px; 
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 15px; overflow: hidden;
        }

        .img-box img {
            max-width: 85%; max-height: 85%;
            object-fit: contain; transition: transform 0.5s ease;
        }

        .product-card:hover .img-box img { transform: scale(1.1); }

        .product-card h3 {
            font-family: 'Cinzel', serif;
            font-size: 1.1rem; margin: 10px 0; color: #111;
        }

        .product-type {
            font-size: 0.7rem; color: #999;
            text-transform: uppercase; letter-spacing: 1px;
        }

        .price {
            font-size: 1.3rem; color: #d4af37;
            font-weight: 600; margin: 10px 0 20px;
        }

        .add-btn {
            background: #111; color: #d4af37; border: none;
            padding: 12px; cursor: pointer; border-radius: 8px;
            font-weight: bold; font-size: 0.8rem;
            text-transform: uppercase; letter-spacing: 1px;
            transition: 0.3s; margin-top: auto;
        }

        .add-btn:hover { background: #d4af37; color: #111; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        footer { text-align: center; padding: 40px; background: #f9f9f9; color: #bbb; font-size: 0.8rem; }
        /* Bouton hors stock */
.add-btn.out-of-stock {
    background: #ccc;
    color: #666;
    cursor: not-allowed;
    border: none;
}

.add-btn.out-of-stock:hover {
    background: #ccc;
    transform: none;
}

/* Texte rupture de stock */
.stock-status {
    font-size: 0.8rem;
    font-weight: bold;
    color: #e74c3c;
    margin-bottom: 10px;
}
    </style>
</head>
<body>

<?php include_once 'template/header.php' ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'forbidden'): ?>
    <div style="background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #e74c3c; padding: 15px; text-align: center; margin: 20px auto; max-width: 800px; border-radius: 8px; font-weight: bold; font-family: sans-serif;">
        🚫 ACCÈS REFUSÉ : Vous n'avez pas les privilèges requis pour accéder à cette zone du grimoire.
    </div>
<?php endif; ?>

<main>
    <div class="nouveaute-header">
        <h1 class="title-new">
            <span class="animated-text">Nouveautés</span> 
            <span class="badge-new">NEW</span>
        </h1>
    </div>

    <section class="product-grid">
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $index => $item): ?>
                <?php $isOutOfStock = ($item['Quantite'] <= 0); ?>
                
                <div class="product-card" style="animation-delay: <?= $index * 0.05 ?>s">
                    <a href="produit.php?id=<?= $item['IdItem'] ?>" style="text-decoration: none; color: inherit;">
                        <span class="product-type"><?= htmlspecialchars($item['NomType']) ?></span>
                        
                        <div class="img-box" style="<?= $isOutOfStock ? 'filter: grayscale(1); opacity: 0.5;' : '' ?>">
                            <?php if (!empty($item['image'])): ?>
                                <img src="img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['Nom']) ?>">
                            <?php else: ?>
                                <i class="fa-regular fa-image" style="font-size: 3rem; color: #eee;"></i>
                            <?php endif; ?>
                        </div>

                        <h3><?= htmlspecialchars($item['Nom']) ?></h3>
                        <div class="price"><?= number_format($item['Prix'], 0, '.', ' ') ?> 🟡</div>
                    </a>
                    
                    <?php if ($isOutOfStock): ?>
                        <div class="stock-status">RUPTURE DE STOCK</div>
                        <button class="add-btn out-of-stock" disabled title="Cet article n'est plus en stock">
                            <i class="fa-solid fa-ban"></i> Indisponible
                        </button>
                    <?php else: ?>
                        <button class="add-btn" onclick="addToCart(<?= $item['IdItem'] ?>)">
                            <i class="fa-solid fa-plus"></i> Ajouter au panier
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1/-1; color: #999;">Aucun trésor n'est disponible pour le moment.</p>
        <?php endif; ?>
    </section> </main>

<footer>
    <p>Powered by StackForge • AVERSE © 2026</p> 
</footer>

<script>
    function addToCart(id) {
        let formData = new FormData();
        formData.append('id', id);
        formData.append('qty', 1);
        
        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let count = document.getElementById('cart-count');
                if(count) {
                    count.innerText = data.total;
                    count.parentElement.style.transform = "scale(1.2)";
                    setTimeout(() => count.parentElement.style.transform = "scale(1)", 200);
                }
            } else {
                alert("Erreur : " + data.error);
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
</script>
</body>
</html>
