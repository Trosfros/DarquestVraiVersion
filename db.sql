DROP DATABASE mydb;
CREATE DATABASE mydb DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE mydb;

CREATE TABLE Joueurs (
  IdJoueur INT NOT NULL AUTO_INCREMENT,
  Alias VARCHAR(45) UNIQUE NOT NULL,
  Nom VARCHAR(45) NOT NULL,
  Prenom VARCHAR(45) NOT NULL,
  PieceBronze INT NOT NULL DEFAULT 11100,
  EstAdmin TINYINT(1) NOT NULL,
  MDP VARBINARY(128) NOT NULL,
  NbDemandeArgent INT NOT NULL,
  PV INT NOT NULL DEFAULT 100,
  Streak INT NOT NULL DEFAULT 0,
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

CREATE TABLE Inventaire (
  IdJoueur INT NOT NULL,
  IdItem INT NOT NULL,
  Quantite INT NOT NULL,
  PRIMARY KEY (IdJoueur, IdItem),
  CONSTRAINT fk_IdItem_Inventaire
    FOREIGN KEY (IdItem)
    REFERENCES Items (IdItem),
  CONSTRAINT fk_IdJoueur_Inventaire
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
  IdJoueur INT NOT NULL,
  IdEnigme INT NOT NULL,
  Reussi TINYINT(1) NOT NULL,
  PRIMARY KEY (IdJoueur, IdEnigme),
  INDEX Fk_IdEnigme_idx (IdEnigme ASC) VISIBLE,
  CONSTRAINT fk_IdEnigme_EssaieEnigmes
    FOREIGN KEY (IdEnigme)
    REFERENCES Enigme (IdEnigme),
  CONSTRAINT FK_IdJoueur_EssaieEnigmes
    FOREIGN KEY (IdJoueur)
    REFERENCES Joueurs (IdJoueur)
);

CREATE TABLE Ticket (
  IdTicket INT NOT NULL AUTO_INCREMENT,
  Demande VARCHAR(300) NOT NULL,
  IdJoueur INT NOT NULL,
  EstDemandeArgent TINYINT(1) NOT NULL,
  PRIMARY KEY (IdTicket),
  CONSTRAINT FK_Ticket_Joueur
    FOREIGN KEY (IdJoueur)
    REFERENCES Joueurs (IdJoueur)
);

CREATE TABLE Achat (
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

CREATE Function  CheckLoginCredentials
(AliasIdentity VARCHAR(100),
  PASS VARCHAR(255)
  )
RETURNS INT
DETERMINISTIC
BEGIN 
  DECLARE hashedPass VARBINARY(128);
  DECLARE returnval TINYINT(1);

  set hashedPass = SHA2(PASS, 512);

  SELECT count(*) INTO returnval FROM Joueurs 
  WHERE
  hashedPass = MDP AND 
  Alias = AliasIdentity;
return returnval;
END
//

CREATE PROCEDURE GetMarketplaceItems(IN p_limit INT, IN search VARCHAR(100))
BEGIN
    DECLARE fuzz VARCHAR(100);
    SET fuzz = CONCAT('%', search, '%');

    SELECT i.IdItems, i.Nom, i.Type, SUM(m.Quantité) AS Quantite, i.Prix, i.Description, i.image
    FROM Items i
    INNER JOIN Marcher m ON m.IdItems = i.IdItems
    WHERE Nom LIKE fuzz OR Description LIKE fuzz OR GetItemTypeName(i) LIKE fuzz
    GROUP BY i.IdItems, i.Nom, i.Type, i.Prix, i.Description, i.image
    LIMIT p_limit;
END
//

CREATE FUNCTION IsItemIsOnMarket(v_IdItem int) 
RETURNS TINYINT 
DETERMINISTIC
BEGIN 
  Declare returnval TINYINT(1) DEFAULT 0;
  SELECT EXISTS (
    select 1 from 
    Marcher as ma 
    where ma.IdItem = v_IdItem
  ) INTO returnval;
  return returnval;
END
//

CREATE PROCEDURE CreateAccount(
  Alias VARCHAR(45) ,
  Nom VARCHAR(45),
  Prenom VARCHAR(45),
  PASS VARCHAR(45),
  EstAdmin TINYINT(1)
)
BEGIN
  DECLARE ENCRYPTEDPASS VARBINARY(128);
  DECLARE ExistantAliasQuant INT;
  SELECT COUNT(*) INTO ExistantAliasQuant
  FROM Joueurs J
  WHERE J.Alias = Alias;
  if Alias IS NULL OR NOM IS NULL OR Prenom IS NULL OR PASS IS NULL OR EstAdmin IS NULL OR ExistantAliasQuant > 0 THEN 
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = ' invalid account creation';
  ELSE 
    SET ENCRYPTEDPASS = SHA2(PASS,512);
    INSERT INTO
    Joueurs (Alias,Nom,Prenom,PieceBronze,EstAdmin,MDP,NbDemandeArgent)
    VALUES (Alias,Nom,Prenom,11100,EstAdmin,ENCRYPTEDPASS,0);
  END IF ;
END
//

delimiter ;
