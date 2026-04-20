<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$idJoueur = $_SESSION['user']['IdJoueur']; 
$items_possedes = [];

$sql = "SELECT i.IdItem ,i.Quantite, it.Nom, it.image
        FROM Inventaires i 
        JOIN Items it ON i.IdItem = it.IdItem
        WHERE i.IdJoueur = ?";

$stmt = $connexion->prepare($sql);
$stmt->bind_param("i", $idJoueur);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items_possedes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AVERSE - Inventaire</title>
    <link rel="stylesheet" href="CSS/style.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #ffffff; color: #333; font-family: 'Roboto', sans-serif; }
        .inventory-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; text-align: center; }
        .inventory-title { font-family: 'Cinzel', serif; color: #d4af37; margin-bottom: 30px; }
        
        /* Grille d'inventaire */
        .inventory-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; }
        .inventory-item { border: 1px solid #eee; padding: 15px; border-radius: 10px; background: #f9f9f9; transition: 0.3s; }
        .inventory-item:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        /* Boutons */
        .btn { border-radius: 15px; border: none; padding: 8px 15px; cursor: pointer; font-weight: bold; transition: transform 0.2s; }
        .btn:hover { transform: scale(1.05); }
        .btn-sell { background-color: #e74c3c; color: white; }
        .btn-use { background-color: #27ae60; color: white; }

        /* STYLE DE LA MODALE (Confirmation) */
        .modal-overlay {
            display: none; /* Caché par défaut */
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000; align-items: center; justify-content: center;
        }
        .modal-content {
            background: white; padding: 30px; border-radius: 15px;
            max-width: 400px; width: 90%; text-align: center;
            border: 2px solid #d4af37; box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        .modal-content h2 { font-family: 'Cinzel', serif; color: #d4af37; margin-top: 0; }
        .modal-buttons { display: flex; justify-content: space-around; margin-top: 25px; }
    </style>
</head>
<body>

<?php include 'template/header.php'; ?>

<main class="inventory-container">
    <h1 class="inventory-title">Mon Inventaire</h1>

    <?php if (empty($items_possedes)): ?>
        <?php else: ?>
        <div class="inventory-grid">
            <?php foreach ($items_possedes as $item): ?>
                <div class="inventory-item">
                    <div class="item-img" style="height: 100px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                        <img src="img/<?= htmlspecialchars($item['image'] ?: 'default.png') ?>" alt="" style="max-height: 100%;">
                    </div>
                    <h3 style="margin: 5px 0;"><?= htmlspecialchars($item['Nom']) ?></h3>
                    <p style="color: #d4af37; font-weight: bold;">Quantité : <?= $item['Quantite'] ?></p>
                    
                    <button class="btn btn-sell" onclick="openModal('<?= $item['Nom'] ?>')">Vendre</button>
                    <button class="btn btn-use">Utiliser</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<div id="confirmModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Confirmation</h2>
        <p>Voulez-vous vraiment vendre <br><strong id="itemNameModal">l'objet</strong> ?</p>
        <div class="modal-buttons">
            <button class="btn" style="background: #ccc;" onclick="closeModal()">Annuler</button>
            <button class="btn btn-sell" onclick="processSale('<?= addslashes(htmlspecialchars($item['IdItem']))?>')">Confirmer la vente</button>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>   
<script>
    let itemToSell = "";

    function openModal(nom) {
        itemToSell = nom;
        
     
        document.getElementById('itemNameModal').innerText = nom;
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    function processSale(id) {
       
        console.log("Vente de : " + itemToSell);
         <?php $_SESSION["SoldItem"] = ""; ?>
            $.post('vendre.php', {id:id})
             .done(function(data) {
                
              console.log("Data Loaded: " + data.success);  
             })
             .fail(function() {
             alert("Error occurred.");
            });
        closeModal();
      
    }

    window.onclick = function(event) {
        let modal = document.getElementById('confirmModal');
        if (event.target == modal) closeModal();
    }
</script>
</body>
</html>
