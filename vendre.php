<?php
require_once 'config.php';
header('Content-Type: application/json');

$idJoueur = $_SESSION['user']['IdJoueur'];
$soldItem = $_SESSION["SoldItem"];
$qty = 1; //change this for later pls 
try 
{
    if (empty($_Session["SoldItem"]))
        throw new InvalidArgumentException("No item in sale ");
    $soldItem = $_Session["SoldItem"];
    $connexion->begin_transaction();
    $sql = "CALL SellItem('$idJoueur',?,?)";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("ii",$soldItem, $qty);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => false]);
}catch(Exception $e) {
    $connexion->rollback();
     echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>