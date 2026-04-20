DROP DATABASE mydb;
CREATE DATABASE mydb;
USE mydb;

CREATE TABLE Joueurs (
  IdJoueur INT NOT NULL AUTO_INCREMENT,
  Alias VARCHAR(45) UNIQUE NOT NULL,
  Nom VARCHAR(45) NOT NULL,
  Prenom VARCHAR(45) NOT NULL,
  MDP VARBINARY(128) NOT NULL,
  PieceBronze INT NOT NULL DEFAULT 100,
  PieceArgent INT NOT NULL DEFAULT 100,
  PieceOr INT NOT NULL DEFAULT 100,
  EstAdmin TINYINT(1) NOT NULL DEFAULT 0,
  EstMage TINYINT(1) NOT NULL DEFAULT 0,
  NbDemandeArgent INT NOT NULL DEFAULT 0,
  PV INT NOT NULL DEFAULT 100,
  StreakMagie INT NOT NULL DEFAULT 0,
  PRIMARY KEY (IdJoueur)
);


CREATE TABLE Items (
  IdItem INT NOT NULL AUTO_INCREMENT,
  Nom VARCHAR(45) NOT NULL,
  Type VARCHAR(1) NOT NULL,
  Prix INT NOT NULL,
  Description VARCHAR(300) NOT NULL,
  image VARCHAR(300),
  CHECK (Type in ('A', 'R', 'P', 'S')),
  PRIMARY KEY (IdItem)
);

CREATE TABLE Armes (
  IdItem INT NOT NULL,
  Efficacite INT NOT NULL,
  Genre VARCHAR(45) NOT NULL,
  PRIMARY KEY (IdItem),
  CONSTRAINT fk_IdItem_Armes
    FOREIGN KEY (IdItem)
    REFERENCES Items (IdItem)
);

CREATE TABLE Armures (
  IdItem INT NOT NULL,
  Taille VARCHAR(45) NOT NULL,
  Matiere VARCHAR(45) NOT NULL,
  PRIMARY KEY (IdItem),
  CONSTRAINT fk_IdItem_IdArmures
    FOREIGN KEY (IdItem)
    REFERENCES Items (IdItem)
);

CREATE TABLE Potions (
  IdItem INT NOT NULL,
  Effet VARCHAR(45) NOT NULL,
  Duree INT NOT NULL,
  Soins INT NOT NULL DEFAULT 0,
  CHECK (Soins < 5),
  PRIMARY KEY (IdItem),
  CONSTRAINT Fk_IdItem
    FOREIGN KEY (IdItem)
    REFERENCES Items (IdItem)
);

CREATE TABLE Sorts (
  IdItem INT NOT NULL,
  Instantane TINYINT(1) NOT NULL,
  PointDeDegat INT NOT NULL,
  Soins INT NOT NULL DEFAULT 0,
  PRIMARY KEY (IdItem),
  CONSTRAINT fk_IdItem_Sorts
    FOREIGN KEY (IdItem)
    REFERENCES Items (IdItem)
);

CREATE TABLE Inventaires (
  IdJoueur INT NOT NULL,
  IdItem INT NOT NULL,
  Quantite INT NOT NULL,
  PRIMARY KEY (IdJoueur, IdItem),
  CONSTRAINT fk_IdItem_Inventaires
    FOREIGN KEY (IdItem)
    REFERENCES Items (IdItem),
  CONSTRAINT fk_IdJoueur_Inventaires
    FOREIGN KEY (IdJoueur)
    REFERENCES Joueurs (IdJoueur)
);

CREATE TABLE Marche (
  IdJoueur INT NOT NULL,
  IdItem INT NOT NULL,
  Quantite INT NOT NULL,
  PRIMARY KEY (IdJoueur, IdItem),
  CONSTRAINT fk_IdItem_Marche
    FOREIGN KEY (IdItem)
    REFERENCES Items (IdItem),
  CONSTRAINT fk_IdJoueur_Marche
    FOREIGN KEY (IdJoueur)
    REFERENCES Joueurs (IdJoueur)
);

CREATE TABLE Evaluations (
  IdJoueur INT NOT NULL,
  IdItem INT NOT NULL,
  Etoiles INT UNSIGNED NOT NULL,
  Commentaire VARCHAR(1000) NOT NULL,
  PRIMARY KEY (IdJoueur, IdItem),
  CONSTRAINT fk_IdItem_Evaluations
    FOREIGN KEY (IdItem)
    REFERENCES Items (IdItem),
  CONSTRAINT fk_IdJoueur_Evaluations
    FOREIGN KEY (IdJoueur)
    REFERENCES Joueurs (IdJoueur)
);

CREATE TABLE CategorieEnigme (
  IdCategorie int NOT NULL AUTO_INCREMENT,
  Categorie varchar(45) NOT NULL,
  EstMagie tinyint(1) NOT NULL,
  PRIMARY KEY (IdCategorie)
);

CREATE TABLE Enigme (
  IdEnigme int NOT NULL AUTO_INCREMENT,
  IdCategorie int NOT NULL,
  Difficulte int NOT NULL,
  Question varchar(100) NOT NULL,
  Reponse1 varchar(255) NOT NULL,
  Reponse2 varchar(255) NOT NULL,
  Reponse3 varchar(255) NOT NULL,
  Reponse4 varchar(255) NOT NULL,
  BonneReponse tinyint NOT NULL,
  PRIMARY KEY (IdEnigme),
  CONSTRAINT fk_IdCategorie
    FOREIGN KEY (IdCategorie)
    REFERENCES CategorieEnigme (IdCategorie)
);

CREATE TABLE EssaieEnigmes (
  IdEssaie int NOT NULL AUTO_INCREMENT,
  IdJoueur INT NOT NULL,
  IdEnigme INT NOT NULL,
  Reussi TINYINT(1) NOT NULL,
  PRIMARY KEY (IdEssaie),
  INDEX fk_IdEnigme_idx (IdEnigme ASC) VISIBLE,
  CONSTRAINT fk_IdEnigme_EssaieEnigmes
    FOREIGN KEY (IdEnigme)
    REFERENCES Enigme (IdEnigme),
  CONSTRAINT fk_IdJoueur_EssaieEnigmes
    FOREIGN KEY (IdJoueur)
    REFERENCES Joueurs (IdJoueur)
);

CREATE TABLE Ticket (
  IdTicket INT NOT NULL AUTO_INCREMENT,
  Demande VARCHAR(300) NOT NULL,
  IdJoueur INT NOT NULL,
  EstDemandeArgent TINYINT(1) NOT NULL,
  PRIMARY KEY (IdTicket),
  CONSTRAINT fk_Ticket_Joueur
    FOREIGN KEY (IdJoueur)
    REFERENCES Joueurs (IdJoueur)
);

CREATE TABLE Achats (
  IdJoueur INT NOT NULL,
  IdItem INT NOT NULL,
  PRIMARY KEY (IdJoueur, IdItem),
  CONSTRAINT fk_IdJoueur_Achat
    FOREIGN KEY (IdJoueur)
    REFERENCES Joueurs (IdJoueur),
  CONSTRAINT fk_IdItem_Achat
    FOREIGN KEY (IdItem)
    REFERENCES Items (IdItem)
);

delimiter //

CREATE FUNCTION GetItemTypeName(Internal VARCHAR(1))
RETURNS VARCHAR(8) DETERMINISTIC
BEGIN
  return (
    CASE Internal
      WHEN 'A' THEN 'Arme'
      WHEN 'R' THEN 'Armure'
      WHEN 'P' THEN 'Potion'
      WHEN 'S' THEN 'Sort'
    END
    );
END;
//

CREATE PROCEDURE GetMarketItems(IN p_limit INT, IN search VARCHAR(100), IN sort CHAR(1))
BEGIN
    DECLARE fuzz VARCHAR(100);
    SET fuzz = CONCAT('%', search, '%');

    SET @sql = CONCAT(
      'SELECT i.IdItem, i.Nom, GetItemTypeName(i.Type) as NomType, SUM(m.Quantite) AS Quantite, i.Prix, i.Description, i.image ',
      'FROM Items i ',
      'INNER JOIN Marche m ON m.IdItem = i.IdItem ',
      'WHERE Nom LIKE ? OR Description LIKE ? OR GetItemTypeName(i.Type) LIKE ? ',
      'GROUP BY i.IdItem, i.Nom, i.Type, i.Prix, i.Description, i.image ',
      'ORDER BY ',
      CASE sort
        WHEN 'P' THEN 'i.Prix '
        ELSE 'i.Nom '
      END,
      'LIMIT ? '
    );

    PREPARE stmt FROM @sql;
    EXECUTE stmt USING fuzz, fuzz, fuzz, p_limit;
    DEALLOCATE PREPARE stmt;
END
//

CREATE PROCEDURE GetItemById(Id INT)
BEGIN 
    SELECT i.IdItem, i.Nom, SUM(m.Quantite) AS Quantite, i.Prix, i.Description, i.image, GetItemTypeName(i.Type) as Type
    FROM Items i
    INNER JOIN Marche m ON m.IdItem = i.IdItem
    WHERE i.IdItem = Id
    GROUP BY i.IdItem, i.Nom, i.Type, i.Prix, i.Description, i.image;
END;
//

CREATE FUNCTION IsItemIsOnMarket(v_IdItem int) 
RETURNS TINYINT 
DETERMINISTIC
BEGIN 
  Declare returnval TINYINT(1) DEFAULT 0;
  SELECT EXISTS (
    select 1 from 
    Marche as ma 
    where ma.IdItem = v_IdItem
  ) INTO returnval;
  return returnval;
END
//

CREATE PROCEDURE EnigmaUserStats(IdJoueur INT)
BEGIN
  SELECT
  SUM(Difficulte = 1) AS FacileTotal,
  SUM(Difficulte = 1 AND Reussi = 1) AS FacileSuccess,
  SUM(Difficulte = 2) AS MoyenTotal,
  SUM(Difficulte = 2 AND Reussi = 1) AS MoyenSuccess,
  SUM(Difficulte = 3) AS DifficileTotal,
  SUM(Difficulte = 3 AND Reussi = 1) AS DifficileSuccess,
  EstMage,
  StreakMagie
  FROM EssaieEnigmes es
  INNER JOIN Joueurs j ON es.IdJoueur = j.IdJoueur
  INNER JOIN Enigme en ON es.IdEnigme = en.IdEnigme
  INNER JOIN CategorieEnigme c ON en.IdCategorie = c.IdCategorie
  WHERE es.IdJoueur = IdJoueur;
END
//

delimiter ;

INSERT INTO CategorieEnigme (Categorie, EstMagie) VALUES
  ('Culture Générale', 0),
  ('Magie', 1);

INSERT INTO Enigme (IdCategorie, Difficulte, Question, Reponse1, Reponse2, Reponse3, Reponse4, BonneReponse) VALUES
  (1, 1, 'Quelle est la capitale de la France ?', 'Lyon', 'Paris', 'Marseille', 'Bordeaux', 2),
  (1, 2, 'Moyen', 'Lyon', 'Paris', 'Marseille', 'Bordeaux', 2),
  (2, 3, 'Enigme magie', '1', '2', '3', '4', 2);

INSERT INTO Items (Nom, Type, Prix, Description, image) VALUES
  ('Épée Magique', 'A', 300, 'Une épee magique', 'epee.png'),
  ('Armure En Fer', 'R', 150, 'Une grosse armure capable de vous protéger contre les attaques!', 'amure1.png'),
  ('Potion Magique de Soin', 'P', 34, 'Une potion de soin très utiles en combat!', 'soin1.png'),
  ('Potion Magique De Glace', 'P', 26, 'Gèle les ennemies de toute tailles!', 'potion2.png'),
  ('Potion Magique De Feu', 'P', 39, 'Attention sa brule!', 'potion3.png');

INSERT INTO Joueurs (Alias, Nom, Prenom, MDP) VALUES
  ('Trosfros', 'Guichard', 'Maxime', 0x243279243130246a6a667838374f3069666748465251715932685a4f65724f55754b6c6f43644c4f506f38645079317576506a34714b633277304943),
  ('Frou_Frou', 'Perron', 'Gabriel', 0x243279243130246b446647385a32445654434f6c5054616959416331652e6e4d555a7a526376684a616641795837504f58724b51454b63624e4c394f),
  ('Lebon', 'Lebon', 'Pascal', 0x24327924313024416d6d4968546832456f47736469437575754e72397569474f6f444f6866444c417259365a4e6b424a334d674f7a6a305a725a6453),
  ('Orisa', 'Orisa', 'Orisa', 0x243279243130244536595a6b454a735873636a566a2e4b5649322e794f6b506955386c4b756e51524c4649326a554a622e5168575062564c7371766d),
  ('fdwefewf', 'bonjour', 'salut', 0x2432792431302461764f446c396e38444c4a3444776d457864546245654a6e746c543043366b47753965724a5a2f444462795857314e652e5043414b),
  ('tamere', 'tamere', 'tamere', 0x243279243130242e4d6c30486b4533617a52746c4d6a6d45572f304b4f397a4439502e366a687956436545597041385a4c732e4c47496c7546526447);

INSERT INTO Marche (IdJoueur, IdItem, Quantite) VALUES
  (1, 1, 15),
  (2, 2, 5),
  (3, 3, 10),
  (4, 4, 13),
  (5, 5, 3);

INSERT INTO EssaieEnigmes(IdJoueur, IdEnigme, Reussi) VALUES
  (1, 1, 1),
  (1, 1, 1),
  (1, 2, 0),
  (1, 2, 0),
  (1, 3, 1),
  (1, 3, 1),
  (1, 4, 0),
  (1, 4, 1),
  (1, 4, 1),
  (1, 4, 0);
