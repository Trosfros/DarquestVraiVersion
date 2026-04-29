<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$idJoueur = $_SESSION['user']['IdJoueur']; // known safe
$result = $connexion->query(
    "SELECT i.IdItem ,i.Quantite, it.Nom, it.image
        FROM Inventaires i 
        JOIN Items it ON i.IdItem = it.IdItem
        WHERE i.IdJoueur = $idJoueur");
$items_possedes = $result->fetch_all(MYSQLI_ASSOC);

$result = $connexion->query(
    "SELECT i.IdItem ,i.Quantite, it.Nom, it.image
        FROM Marche i 
        JOIN Items it ON i.IdItem = it.IdItem
        WHERE i.IdJoueur = $idJoueur");
$items_vente = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AVERSE - Inventaire</title>
    <link rel="stylesheet" href="CSS/style.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/inventaire.css">
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
                    
                    <button class="btn btn-sell" onclick="openModal(this, '<?= $item['Nom'] ?>', <?= $item['IdItem']?>, 'vendre.php')">Vendre</button>
                    <button class="btn btn-use" onclick="openModal(this, '<?= $item['Nom'] ?>', <?= $item['IdItem']?>, 'useItem.php')">Utiliser</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h1 class="inventory-title">Mes Items en vente</h1>

    <?php if (empty($items_vente)): ?>
        <?php else: ?>
        <div class="inventory-grid">
            <?php foreach ($items_vente as $item): ?>
                <div class="inventory-item">
                    <div class="item-img" style="height: 100px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                        <img src="img/<?= htmlspecialchars($item['image'] ?: 'default.png') ?>" alt="" style="max-height: 100%;">
                    </div>
                    <h3 style="margin: 5px 0;"><?= htmlspecialchars($item['Nom']) ?></h3>
                    <p style="color: #d4af37; font-weight: bold;">Quantité : <?= $item['Quantite'] ?></p>
                    
                    <button class="btn btn-sell" onclick="openModal(this, '<?= $item['Nom'] ?>', <?= $item['IdItem']?>, 'retirer_vente.php')">Retirer</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<div id="confirmModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Confirmation</h2>
        <p>Voulez-vous vraiment <span id="actionNameModal"></span><br>
            <strong id="itemNameModal">l'objet</strong> ?</p>
        <div class="modal-buttons">
            <button class="btn" style="background: #ccc;" onclick="closeModal()">Annuler</button>
            <button id="btn-confirm"/>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>   
<script>
    function openModal(btn, nom, id, fichier) {
        const confirmBtn = document.getElementById('btn-confirm');
        confirmBtn.textContent = btn.innerText;
        confirmBtn.className = btn.className;
        confirmBtn.onclick = () => onConfirm(id, fichier)

        document.getElementById('itemNameModal').innerText = nom;
        document.getElementById('actionNameModal').innerText = btn.innerText;
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    function onConfirm(id, fichier) {
        $.post(fichier, {id:id})
            .done(function(data) {
                location.reload()
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
