<?php
session_start();
require 'config.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Session invalide ou panier vide']);
    exit;
}

$idJoueur = $_SESSION['user_id'];

try {
    $connexion->begin_transaction();

    foreach ($_SESSION['cart'] as $itemId => $qty) {
        
        $sqlStock = "UPDATE Items SET QuantiteStock = QuantiteStock - ? 
                     WHERE IdItems = ? AND QuantiteStock >= ?";
        $stmtStock = $connexion->prepare($sqlStock);
        
        if (!$stmtStock) {
            throw new Exception("Erreur préparation stock : " . $connexion->error);
        }

        $stmtStock->bind_param("iii", $qty, $itemId, $qty);
        $stmtStock->execute();

        
        if ($stmtStock->affected_rows === 0) {
            throw new Exception("Stock insuffisant pour l'un des articles.");
        }

      
        $sqlInv = "INSERT INTO inventaire (IdJoueur, IdItems, Quantite) 
                   VALUES (?, ?, ?) 
                   ON DUPLICATE KEY UPDATE Quantite = Quantite + ?";
        
        $stmtInv = $connexion->prepare($sqlInv);
        if (!$stmtInv) {
            throw new Exception("Erreur préparation inventaire : " . $connexion->error);
        }

        $stmtInv->bind_param("iiii", $idJoueur, $itemId, $qty, $qty);
        $stmtInv->execute();
    }

   
    $_SESSION['cart'] = [];

    $connexion->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    
    $connexion->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}