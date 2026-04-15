<?php
require 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['choix'], $_POST['id_enigme'])) {
    
    $id_joueur = $_SESSION['user_id'] ?? null;
    $id_enigme = (int)$_POST['id_enigme'];
    $choix_joueur = (int)$_POST['choix'];

    if (!$id_joueur) {
        header('Location: login.php');
        exit();
    }

    $stmt = $connexion->prepare("SELECT * FROM Enigme WHERE IdEnigme = ?");
    $stmt->bind_param("i", $id_enigme);
    $stmt->execute();
    $enigme = $stmt->get_result()->fetch_assoc();

    if ($enigme) {
        $estUneEnigmeMagique = ($enigme['IdCategorie'] == 3);

        if ($enigme['Difficulte'] == 1) {
            $colonnePiece = "PieceBronze";
            $colonneSuccess = "NbQuetesFacileSuccess";
            $colonneTotal = "NbQuetesFacileTotal";
            $labelPiece = "pièces de Bronze";
        } elseif ($enigme['Difficulte'] == 2) {
            $colonnePiece = "PieceArgent";
            $colonneSuccess = "NbQuetesMoyenSuccess";
            $colonneTotal = "NbQuetesMoyenTotal";
            $labelPiece = "pièces d'Argent";
        } else {
            $colonnePiece = "PieceOr";
            $colonneSuccess = "NbQuetesDifficileSuccess";
            $colonneTotal = "NbQuetesDifficileTotal";
            $labelPiece = "pièces d'Or";
        }

        if ($choix_joueur == $enigme['BonneReponse']) {
            // --- VICTOIRE ---
            $status = "success";
            $message = "Bravo ! Bonne réponse. Vous gagnez 10 $labelPiece.";
            
            
            $updGains = $connexion->prepare("UPDATE Joueurs SET $colonnePiece = $colonnePiece + 10, $colonneSuccess = $colonneSuccess + 1, $colonneTotal = $colonneTotal + 1 WHERE IdJoueur = ?");
            $updGains->bind_param("i", $id_joueur);
            $updGains->execute();

            if ($estUneEnigmeMagique) {
                $connexion->query("UPDATE joueurs SET QuetesMagieReussies = QuetesMagieReussies + 1 WHERE IdJoueur = $id_joueur");

                $resM = $connexion->query("SELECT QuetesMagieReussies FROM joueurs WHERE IdJoueur = $id_joueur")->fetch_assoc();
                $nbReussies = $resM['QuetesMagieReussies'];

                if ($nbReussies >= 5) {
                    $connexion->query("UPDATE joueurs SET EstMage = 1 WHERE IdJoueur = $id_joueur");
                    $_SESSION['EstMage'] = 1;
                }

                if ($nbReussies > 0 && $nbReussies % 3 == 0) {
                    $connexion->query("UPDATE joueurs SET PieceOr = PieceOr + 100 WHERE IdJoueur = $id_joueur");
                    $message .= "<br>💰 **SÉRIE DE 3 !** Vous recevez 100 pièces d'Or bonus !";
                }
            }
                
        } else {
            // --- ÉCHEC ---
            $status = "error";
            $perte = ($enigme['Difficulte'] == 1) ? 3 : (($enigme['Difficulte'] == 2) ? 6 : 10);
            $message = "Dommage... Mauvaise réponse. Vous perdez $perte PV.";

            $updEchec = $connexion->prepare("UPDATE joueurs SET PV = PV - ?, $colonneTotal = $colonneTotal + 1 WHERE IdJoueur = ?");
            $updEchec->bind_param("ii", $perte, $id_joueur);
            $updEchec->execute();

            if ($estUneEnigmeMagique) {
                $connexion->query("UPDATE joueurs SET QuetesMagieReussies = 0 WHERE IdJoueur = $id_joueur");
                $message .= "<br>❌ Série brisée ! Le compteur de magie retombe à zéro.";
            }
        }

        $_SESSION['feedback'] = ['message' => $message, 'status' => $status];
        header("Location: enigma.php");
        exit();
    }
}
