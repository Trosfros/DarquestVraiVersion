<?php
require_once 'config.php';
header('Content-Type: application/json');

$idJoueur = $_SESSION['user']['IdJoueur'];
$soldItem = $_POST['id'];
$qty = 1; //change this for later pls 

try 
{
    if (empty($soldItem)){
                throw new InvalidArgumentException("No item in sale ");
    }
    $connexion->begin_transaction();
    $sql = "CALL SellItem('$idJoueur',?,?)";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("ii",$soldItem, $qty);
    $stmt->execute();
    $stmt->close();
    $connexion->commit();
    echo json_encode(['success' => true]);
}catch(Exception $e) {
    $connexion->rollback();
     echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
 UpdateUserSessionInfo();
?>