<?php
require 'config.php';

// Calcul du total des items dans le panier
$totalItems = 0;
if (isset($_SESSION['cart'])) {
    $totalItems = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVERSÉ - Centre de Support des Héros</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Mode Sombre RPG pour coller à l'univers du jeu */
        body { 
            background: radial-gradient(circle at center, #1a1a2e 0%, #050507 100%);
            color: #eee; 
            font-family: 'Poppins', sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        /* --- HEADER ADAPTATION --- */
        header {
            background: rgba(0,0,0,0.9) !important;
            border-bottom: 1px solid var(--gold);
        }

        .container-support {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }

        .support-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .support-header h1 {
            font-family: 'Cinzel', serif;
            color: #d4af37;
            font-size: 3rem;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
            margin: 0;
        }

        /* --- CARTES DE CATEGORIES --- */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 60px;
        }

        .cat-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: 0.4s;
            cursor: pointer;
            backdrop-filter: blur(5px);
        }

        .cat-card:hover {
            background: rgba(212, 175, 55, 0.1);
            border-color: #d4af37;
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .cat-card i {
            font-size: 2.5rem;
            color: #d4af37;
            margin-bottom: 15px;
        }

        /* --- ACCORDEON FAQ GAMING --- */
        .faq-box {
            background: rgba(0, 0, 0, 0.4);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .faq-item {
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding: 15px 0;
        }

        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            color: #d4af37;
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s ease;
            color: #bbb;
            font-size: 0.95rem;
            padding-right: 30px;
        }

        .faq-item.active .faq-answer {
            max-height: 150px;
            margin-top: 15px;
        }

        .faq-item.active i {
            transform: rotate(180deg);
        }

        /* --- STATUS DES SERVEURS --- */
        .server-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: rgba(46, 213, 115, 0.1);
            border: 1px solid #2ed573;
            color: #2ed573;
            padding: 10px 20px;
            border-radius: 50px;
            width: fit-content;
            margin: 0 auto 40px auto;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .dot {
            height: 8px; width: 8px; background-color: #2ed573;
            border-radius: 50%; display: inline-block;
            box-shadow: 0 0 10px #2ed573;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }

        .btn-report {
            display: block;
            width: fit-content;
            margin: 40px auto;
            background: #d4af37;
            color: #000;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            transition: 0.3s;
        }

        .btn-report:hover {
            box-shadow: 0 0 25px rgba(212, 175, 55, 0.5);
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<?php include_once 'template/header.php' ?>

<main class="container-support">
    <div class="support-header">
        <h1>Centre d'Assistance</h1>
        <p>Problème technique ou perte de stuff ? Nos mages sont là.</p>
    </div>

    <div class="server-status">
        <span class="dot"></span> Serveurs Aversé : Opérationnels
    </div>

    <div class="category-grid">
        <div class="cat-card">
            <i class="fas fa-ghost"></i>
            <h3>Bugs & Glitches</h3>
            <p>Signaler un problème de collision ou un sort défectueux.</p>
        </div>
        <div class="cat-card">
            <i class="fas fa-user-shield"></i>
            <h3>Compte</h3>
            <p>Récupération de mot de passe ou changement d'Alias.</p>
        </div>
        <div class="cat-card">
            <i class="fas fa-gem"></i>
            <h3>Boutique & Or</h3>
            <p>Problème lors d'un achat d'item ou de conversion de pièces.</p>
        </div>
    </div>

    <section class="faq-box">
        <h2 style="font-family: 'Cinzel', serif; color: #fff; margin-bottom: 30px;">FAQ des Aventuriers</h2>
        
        <div class="faq-item">
            <div class="faq-question">
                J'ai perdu mon stuff après un crash, que faire ?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Pas de panique ! Notre système de sauvegarde automatique enregistre votre inventaire toutes les 5 minutes. Contactez-nous avec l'heure exacte du crash.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                Comment convertir mes pièces de Bronze en Or ?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Comme indiqué dans le Grimoire : 100 Bronze = 10 Argent, et 10 Argent = 1 Or. La conversion se fait automatiquement au Coffre.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                Est-ce que le multicompte est autorisé ?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Non aventurier ! Un seul héros par utilisateur est autorisé pour garantir l'équité lors des quêtes Enigma.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                Le boss de l'Enigma est trop dur, c'est un bug ?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Non, c'est un défi ! Assurez-vous d'avoir bien lu les parchemins d'indices disséminés dans le village avant de l'affronter.
            </div>
        </div>
    </section>

    <a href="mailto:support@averse.game" class="btn-report">Ouvrir un ticket de support</a>
</main>

<script>
    // Accordéon FAQ
    document.querySelectorAll('.faq-question').forEach(q => {
        q.addEventListener('click', () => {
            q.parentNode.classList.toggle('active');
        });
    });

    // Scripts de ton header
    function toggleDropdown(event) {
        event.stopPropagation();
        document.getElementById("userDropdown").classList.toggle("show");
    }
</script>

</body>
</html>
