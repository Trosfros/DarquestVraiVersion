<style>
    /* --- CONFIGURATION GLOBALE --- */
    .top-nav, .sub-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 20px;
        flex-wrap: wrap;
        background-color:black;
    }

    /* --- RECHERCHE --- */
    .search-bar input {
        padding: 8px 15px;
        border-radius: 20px;
        border: 1px solid #444;
        background: #222;
        color: white;
        width: 220px;
        transition: width 0.3s;
        outline: none;
    }

    /* --- NAVIGATION SECONDAIRE (LIENS ALIGNÉS) --- */
    .sub-nav .links {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 25px;
    }

    .sub-nav a, .sub-nav span {
        text-decoration: none;
        font-weight: bold;
        white-space: nowrap;
    }

    /* --- SYSTÈME DE DROPDOWNS --- */
    .dropdown { position: relative; display: inline-block; }
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #1a1a1a;
        min-width: 170px;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.6);
        z-index: 1000;
        border: 1px solid #d4af37;
        border-radius: 4px;
        top: 100%;
        left: 0;
    }
    .dropdown-content a {
        color: white !important;
        padding: 12px 16px;
        display: block;
        font-size: 0.9rem;
        border-bottom: 1px solid #222;
    }
    .show { display: block !important; }

    .user-menu { display: flex; align-items: center; gap: 15px; }

    /* --- BARRE DE VIE RPG --- */
    .hp-container {
        width: 120px;
        background: #333;
        height: 14px;
        border-radius: 7px;
        border: 1px solid #555;
        position: relative;
        overflow: hidden;
        box-shadow: 0 0 5px rgba(0,0,0,0.5);
    }

    .hp-bar {
        height: 100%;
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        border-radius: 6px;
        transition: width 0.5s ease-in-out;
    }

    .hp-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 9px;
        width: 100%;
        text-align: center;
        color: white;
        font-weight: bold;
        text-shadow: 1px 1px 2px black;
        z-index: 2;
        pointer-events: none;
    }

    /* --- MONNAIE RPG --- */
    .rpg-currency-bar {
        display: flex;
        gap: 15px;
        align-items: center;
        font-size: 0.9em;
        font-weight: bold;
        color: #fff;
        font-family: 'Cinzel', serif; 
    }

    .coin-item {
        display: flex;
        align-items: center;
        gap: 6px; 
    }

    .coin-item img {
        width: 22px; 
        height: 22px;
        object-fit: contain;
        filter: drop-shadow(0 2px 3px rgba(0,0,0,0.4));
    }

    /* Conteneur des stats */
    .player-stats {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-right: 20px;
    }

    /* Style spécifique pour les liens de connexion/inscription */
    .user-link {
        transition: 0.3s;
    }
    .user-link:hover {
        color: #d4af37 !important;
        opacity: 0.8;
    }

    /* --- RESPONSIVE DESIGN --- */
    @media (max-width: 768px) {
        .top-nav { flex-direction: column; gap: 15px; padding: 15px; }
        .search-bar { order: 3; width: 100%; }
        .search-bar input { width: 100%; }
        .player-stats { margin-right: 0 !important; gap: 10px !important; }
        .hp-container { width: 80px !important; }
        .sub-nav { justify-content: center; }
        .sub-nav .links { gap: 15px; font-size: 0.9rem; }
        .service-client, .hide-mobile { display: none; }
    }

    @media (max-width: 480px) {
        .user-menu { flex-wrap: wrap; justify-content: center; }
        .rpg-currency-bar { font-size: 0.75em !important; gap: 8px; }
    } 

    /* =========================================
       NOUVEAU STYLE POUR L'ANIMATION SMOOTH
       ========================================= */
    
    #cart-container {
        text-decoration: none;
        color: white;
        margin-left: 15px;
        display: inline-block;
        /* La transition est ici, elle doit être smooth */
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.2s ease;
        /* Important pour que transform-origin fonctionne bien */
        transform-origin: center center; 
        /* Aide au rendu sur certains navigateurs */
        will-change: transform; 
    }

    /* Classe appliquée temporairement par JS pour le grossissement */
    .cart-grow {
        transform: scale(1.35); /* Grossit de 35% */
        color: #d4af37 !important; /* Devient doré */
    }
</style>

<header>
    <nav class="top-nav">
        <div class="logo" style="font-family: 'Cinzel', serif; font-size: 1.5rem; color: #d4af37;">AVERSE</div> 
        
        <form action="catalogue.php" method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Q Rechercher..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit" style="display: none;">Rechercher</button>
        </form>
        
        <div class="user-menu">
                <?php
                $user = @ $_SESSION['user'];
                if (isset($user)):
                ?>
                <div class="player-stats">
                    <div class="hp-container" title="Points de Vie">
                        <div class="hp-bar" style="width: <?= $_SESSION['user']['PV'] ?>%;"></div>
                        <span class="hp-text"><?= $user['PV'] ?> HP</span>
                    </div>

                    <div class="rpg-currency-bar">
                        <div class="coin-item" title="Or">
                            <img src="./img/gold.png" alt="Or">
                            <span><?= $user['PieceOr'] ?></span>
                        </div>
                        <div class="coin-item" title="Argent">
                            <img src="./img/silver.png" alt="Argent">
                            <span><?= $user['PieceArgent'] ?></span>
                        </div>
                        <div class="coin-item" title="Bronze">
                            <img src="./img/bronze.png" alt="Bronze">
                            <span><?= $user['PieceBronze'] ?></span>
                        </div>
                    </div>
                </div>

                <a href="inventaire.php" style="text-decoration:none; color: white; margin-right: 10px;">🛍️ <span class="hide-mobile">Inventaire</span></a> 

                <div class="dropdown">
                    <strong style="cursor:pointer; color: white;" onclick="toggleHeaderMenu(event, 'userDrop')">
                        👤 <?= htmlspecialchars($user['Alias']) ?> ▾
                    </strong>
                    <div id="userDrop" class="dropdown-content">
                    <a href="profil.php">👤 Mon Profil</a>
    
                    <?php if ($user['EstAdmin']): ?>
                        <a href="admin_enigmes.php" style="color: #d4af37 !important; border-top: 1px solid #333;">🛡️ Admin Panel</a>
                    <?php endif; ?>
    
              <a href="logout.php">🚪 Déconnexion</a>
                </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="user-link" style="color: white; text-decoration: none; margin-right: 10px;">🔑 Connexion</a>
                <a href="register.php" class="user-link" style="color: #d4af37; text-decoration: none; border: 1px solid #d4af37; padding: 5px 12px; border-radius: 15px;">📜 S'inscrire</a>
            <?php endif; ?>

            <a href="panier.php" id="cart-container">
                🛒 <span class="hide-mobile">Panier</span> (<span id="cart-count"><?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?></span>)
            </a> 
        </div>
    </nav>

    <nav class="sub-nav" style="background: #111; border-top: 1px solid #222;">
        <div class="links">
            <a href="index.php" style="color: white;">🏠 <span class="hide-mobile">Accueil</span></a> 
            
            <div class="dropdown">
                <span style="cursor:pointer; color: white;" onclick="toggleHeaderMenu(event, 'catDrop')">
                    ⚔️ <span class="hide-mobile">Catalogue</span> ▾
                </span>
                <div id="catDrop" class="dropdown-content">
                    <a href="catalogue.php">📋 Tout voir</a>
                    <a href="catalogue.php?search=Armure">🛡️ Armures</a>
                    <a href="catalogue.php?search=Arme">⚔️ Armes</a>
                    <a href="catalogue.php?search=Potion">🧪 Potions</a>
                    <a href="catalogue.php?search=Sort">𑽎 Sorts</a>
                </div>
            </div>

            <a href="enigma.php" style="color: #f3b412;">🧩 <span class="hide-mobile">Enigma</span></a> 
        </div>

        <div class="service-client">
            <a href="service_client.php" style="color: #777; font-size: 0.9rem;">Service client</a> 
        </div>
    </nav>
</header>


<script>
    // Fonction de gestion des menus déroulants
    function toggleHeaderMenu(event, menuId) {
        event.stopPropagation();
        const userMenu = document.getElementById('userDrop');
        const catMenu = document.getElementById('catDrop');

        if (menuId === 'userDrop') {
            userMenu?.classList.toggle('show');
            catMenu?.classList.remove('show');
        } else {
            catMenu?.classList.toggle('show');
            userMenu?.classList.remove('show');
        }
    }

    // Fermeture automatique au clic extérieur
    window.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.getElementById('userDrop')?.classList.remove('show');
            document.getElementById('catDrop')?.classList.remove('show');
        }
    });

    /* =========================================
       NOUVEAU JS POUR L'AJOUT AJAX SMOOTH
       ========================================= */
    function addToCartAsync(productId) {
        // Envoi de la requête AJAX (Fetch) au serveur sans recharger
        // Pour le test, on va simuler la réponse du serveur
        
        // --- DEBUT SIMULATION SERVEUR (À remplacer par le vrai fetch) ---
        // fetch('add_to_cart_ajax.php?id=' + productId)
        // .then(response => response.json())
        // .then(data => { if (data.success) { ... mise à jour ... } });

        // Simulation : on récupère la valeur actuelle et on fait +1
        const countElem = document.getElementById('cart-count');
        const currentCount = parseInt(countElem.innerText);
        const newCount = currentCount + 1;
        
        // Appelle la fonction qui gère l'animation et la mise à jour
        updateCartVisual(newCount);
        // --- FIN SIMULATION SERVEUR ---
    }

    function updateCartVisual(newCount) {
        const cartContainer = document.getElementById('cart-container');
        const cartCount = document.getElementById('cart-count');

        // 1. Appliquer la classe de grossissement (déclenche la transition CSS)
        cartContainer.classList.add('cart-grow');

        // 2. Mettre à jour le chiffre (peut être fait pendant le grossissement)
        cartCount.innerText = newCount;

        // 3. Planifier le retour à la normale après la transition
        // On attend la durée de la transition (0.3s = 300ms)
        setTimeout(() => {
            cartContainer.classList.remove('cart-grow');
        }, 300); // Même durée que la transition CSS 'transform'
    }
</script>
