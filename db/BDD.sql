DROP DATABASE IF EXISTS DungeonXplorer;
CREATE DATABASE DungeonXplorer;
USE DungeonXplorer;

-- Comptes
CREATE TABLE Account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    current_hero INT DEFAULT NULL
);

-- Classes
CREATE TABLE Class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    base_pv INT NOT NULL,
    base_mana INT NOT NULL,
    strength INT NOT NULL,
    initiative INT NOT NULL,
    max_items INT NOT NULL
);

-- Items
CREATE TABLE Items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    item_type VARCHAR(50) NOT NULL
);

-- Monstres
CREATE TABLE Monster (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    pv INT NOT NULL,
    mana INT DEFAULT 0,
    strength INT NOT NULL,
    initiative INT NOT NULL,
    attack_text TEXT NOT NULL,
    xp_reward INT NOT NULL,
    image VARCHAR(255) DEFAULT NULL
);

-- Loot
CREATE TABLE Monster_Loot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monster_id INT,
    item_id INT,
    quantity INT NOT NULL DEFAULT 1,
    drop_rate DECIMAL(5,2) DEFAULT 1.0,
    FOREIGN KEY (monster_id) REFERENCES Monster(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items(id),
    UNIQUE (monster_id, item_id)
);

-- Héros
CREATE TABLE Hero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    class_id INT,
    image VARCHAR(255),
    biography TEXT,
    pv INT NOT NULL,
    mana INT NOT NULL,
    strength INT NOT NULL,
    initiative INT NOT NULL,
    armor_item_id INT,
    primary_weapon_item_id INT,
    secondary_weapon_item_id INT,
    shield_item_id INT,
    spell_list TEXT,
    xp INT NOT NULL,
    current_level INT DEFAULT 1,
    FOREIGN KEY (account_id) REFERENCES Account(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES Class(id),
    FOREIGN KEY (armor_item_id) REFERENCES Items(id),
    FOREIGN KEY (primary_weapon_item_id) REFERENCES Items(id),
    FOREIGN KEY (secondary_weapon_item_id) REFERENCES Items(id),
    FOREIGN KEY (shield_item_id) REFERENCES Items(id)
);

-- Chapitres
CREATE TABLE Chapter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255)
);

-- Rencontres (Combats)
CREATE TABLE Encounter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT,
    monster_id INT,
    FOREIGN KEY (chapter_id) REFERENCES Chapter(id) ON DELETE CASCADE,
    FOREIGN KEY (monster_id) REFERENCES Monster(id)
);

-- Liens entre chapitres
CREATE TABLE Links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT,
    next_chapter_id INT,
    description TEXT,
    FOREIGN KEY (chapter_id) REFERENCES Chapter(id) ON DELETE CASCADE,
    FOREIGN KEY (next_chapter_id) REFERENCES Chapter(id)
);

-- Inventaire
CREATE TABLE Inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (hero_id) REFERENCES Hero(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items(id),
    UNIQUE (hero_id, item_id)
);

-- Aventures
CREATE TABLE Adventure
(
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    hero_id            INT NOT NULL,
    start_date         DATETIME    DEFAULT CURRENT_TIMESTAMP,
    end_date           DATETIME,                         -- NULL = aventure en cours
    status             VARCHAR(20) DEFAULT 'InProgress', -- InProgress / Completed / Failed

    current_chapter_id INT         DEFAULT 1,            -- position actuelle de l’aventure

    FOREIGN KEY (hero_id) REFERENCES Hero (id) ON DELETE CASCADE,
    FOREIGN KEY (current_chapter_id) REFERENCES Chapter (id)
);

DELIMITER $$

CREATE TRIGGER trg_adventure_single_active_insert
    BEFORE INSERT
    ON Adventure
    FOR EACH ROW
BEGIN
    IF NEW.end_date IS NULL THEN
        IF (SELECT COUNT(*)
            FROM Adventure
            WHERE hero_id = NEW.hero_id
              AND end_date IS NULL) > 0 THEN

            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Un héros ne peut pas avoir plusieurs aventures en cours.';
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_adventure_single_active_update
    BEFORE UPDATE
    ON Adventure
    FOR EACH ROW
BEGIN
    IF NEW.end_date IS NULL THEN
        IF (SELECT COUNT(*)
            FROM Adventure
            WHERE hero_id = NEW.hero_id
              AND end_date IS NULL
              AND id != NEW.id) > 0 THEN

            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Un héros ne peut pas avoir plusieurs aventures en cours.';
        END IF;
    END IF;
END$$

DELIMITER ;

-- Historique de progression
CREATE TABLE Adventure_Progress
(
    id           INT AUTO_INCREMENT PRIMARY KEY,
    adventure_id INT NOT NULL,
    chapter_id   INT NOT NULL,
    status       VARCHAR(20) DEFAULT 'Visited',
    visit_date   DATETIME    DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (adventure_id) REFERENCES Adventure (id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES Chapter (id)
);

-- Insertion Classes
INSERT INTO Class (name, description, base_pv, base_mana, strength, initiative, max_items)
VALUES 
('Guerrier', 'Un combattant robuste et puissant', 100, 0, 15, 5, 5),
('Voleur', 'Rapide et agile', 80, 10, 10, 15, 5),
('Magicien', 'Puissant en magie', 60, 20, 5, 10, 5);

-- Monstres
INSERT INTO Monster (name, pv, mana, strength, initiative, attack_text, xp_reward, image)
VALUES 
('Sanglier Enragé', 50, 0, 12, 5, 'Le sanglier charge violemment !', 50, 'img/monsters/boar.png'),
('Loup Noir', 40, 0, 8, 12, 'Le loup bondit et tente de vous mordre !', 60, 'img/monsters/wolf.png'),
('Orc Guerrier', 70, 0, 15, 8, 'L’orc abat sa massue !', 120, 'img/monsters/orc.png'),
('Squelette', 25, 0, 6, 3, 'Le squelette claque des os et frappe de son épée rouillée !', 30, 'img/bossSquellete.jpg'),
('Gargouille de Pierre', 60, 0, 14, 10, 'La gargouille pousse un hurlement rocailleux avant de fondre sur vous !', 150, 'img/bossGargouille.jpg'),
('Ombre Ténébreuse', 90, 20, 18, 12, 'L’ombre murmure des mots interdits et vous attaque depuis les ténèbres !', 200, 'img/bossOmbre.jpg'),
('Esprit Élémentaire', 55, 30, 16, 8, 'L’esprit tourbillonne et lance une salve d’énergie élémentaire !', 120, 'img/bossElementaire.jpg'),
('Malkor l''Ensorceleur', 120, 80, 25, 15, 'Malkor incante un sort de destruction pure !', 500, 'img/bossSorcier.jpg');





;

-- Chapitres
INSERT INTO Chapter (title, description, image)
VALUES 
('Introduction', 'Le ciel est lourd ce soir sur le village du Val Perdu, dissimulé entre les montagnes. La petite taverne, dernier refuge avant l''immense forêt, est étrangement calme quand le bourgmestre s''approche de vous. Homme d''apparence usée par les années et les soucis, il vous adresse un regard désespéré.
<<< Ma fille... elle a disparu dans la forêt. Personne n''a osé la chercher... sauf vous, peut-être? On raconte qu''un sorcier vit dans un château en ruines, caché au cœur des bois. Depuis des mois, des jeunes filles disparaissent... J''ai besoin de vous pour la retrouver. >>>
Vous sentez le poids de la mission qui s''annonce, et un frisson parcourt votre échine. Bientôt, la forêt s''ouvre devant vous, sombre et menaçante. La quête commence.
', 'img/Village01.jpg'),
('L''orée de la forêt', 'Vous franchissez la lisière des arbres, la pénombre de la forêt avalant le sentier devant vous. Un vent froid glisse entre les troncs, et le bruissement des feuilles ressemble à un murmure menaçant. Deux chemins s''offrent à vous: l''un sinueux, bordé de vieux arbres noueux; l''autre droit mais envahi par des ronces épaisses.', 'img/BrambleTrails01.jpg'),
('L''arbre aux corbeaux', 'Votre choix vous mène devant un vieux chêne aux branches tordues, grouillant de corbeaux noirs qui vous observent en silence. À vos pieds, des traces de pas légers, probablement récents, mènent plus loin dans les bois. Soudain, un bruit de pas feutrés se fait entendre. Vous ressentez la présence d''un prédateur.', 'img/Dark Forest01.jpg'),
('Le sanglier enragé', 'En progressant, le calme de la forêt est soudain brisé par un grognement. Surgissant des buissons, un énorme sanglier, au pelage épais et aux yeux injectés de sang, se dirige vers vous. Sa rage est palpable, et il semble prêt à en découdre. Le voici qui décide brutalement de vous charger!', 'img/SavageBoard01.jpg'),
('Rencontre avec le paysan', 'Tandis que vous progressez, une voix humaine s''élève, interrompant le silence de la forêt. Vous tombez sur un vieux paysan, accroupi près de champignons aux couleurs vives. Il sursaute en vous voyant, puis se détend, vous souriant tristement.
<<< Vous devriez faire attention, étranger, murmure-t-il. La nuit, des cris terrifiants retentissent depuis le cœur de la forêt... Des créatures rôdent. >>>
', 'img/OldMan01.jpg'),
('Le loup noir', 'À mesure que vous avancez, un bruissement attire votre attention. Une silhouette sombre s''élance soudainement devant vous: un loup noir aux yeux perçants. Son poil est hérissé et sa gueule laisse entrevoir des crocs acérés. Vous sentez son regard fixé sur vous, prêt à bondir. Le combat est inévitable.', 'img/Wolf01.jpg'),
('La clairière aux pierres anciennes', 'Après votre rencontre, vous atteignez une clairière étrange, entourée de pierres dressées, comme un ancien autel oublié par le temps. Une légère brume rampe au sol, et les ombres des pierres semblent danser sous la lueur de la lune.', 'img/StoneWall01.jpg'),
('Les murmures du ruisseau', 'Essoufflé mais déterminé, vous arrivez près d''un petit ruisseau qui serpente au milieu des arbres. Le chant de l''eau vous apaise quelque peu, mais des murmures étranges semblent émaner de la rive. Vous apercevez des inscriptions anciennes gravées dans une pierre moussue.','img/StoneWall01.jpg'),
('Au pied du château','La forêt se disperse enfin, et devant vous se dresse une colline escarpée. Au sommet, le château en ruines projette une ombre menaçante sous le clair de lune. Les murs effrités et les tours en partie effondrées ajoutent à la sinistre réputation du lieu. Vous sentez que la véritable aventure commence ici, et que l''influence du sorcier n''est peut-être pas qu''une légende....','img/Castle02.jpg'),
('La lumière au bout du néant', 'Le monde se dérobe sous vos pieds, et une obscurité profonde vous enveloppe, glaciale et insondable. Vous ne sentez plus le poids de votre équipement, ni la morsure de la douleur. Juste un vide infini, vous aspirant lentement dans les ténèbres. Alors que vous perdez toute notion du temps, une lueur douce apparaît au loin, vacillante comme une flamme fragile dans l''obscurité. Au fur et à mesure que vous approchez, vous entendez une voix, faible mais bienveillante, qui murmure des mots oubliés, anciens.
<< Brave âme, ton chemin n''est pas achevé... À ceux qui échouent, une seconde chance est accordée. Mais les caprices du destin exigent un sacrifice. >>>
La lumière s''intensifie, et vous sentez vos forces revenir, mais vos poches sont vides, votre sac allégé de tout trésor. Votre équipement, vos armes, tout a disparu, laissant place à une sensation de vulnérabilité. Lorsque la lumière vous enveloppe, vous ouvrez de nouveau les yeux, retrouvant la terre ferme sous vos pieds. Vous êtes de retour, sans autre possession que votre volonté de reprendre cette quête. Mais cette fois-ci, peut-être, saurez-vous éviter les pièges fatals qui vous ont mené à votre perte.
','img/Lumiere.png'),
('La curiosité tua le chat' , 'Qu''avez-vous fait, Malheureux!','img/mort.png'),
('L''escalier menaçant ', 'Au pied du château, vous trouvez une entrée dissimulée menant à un escalier sombre. L''air y est moisi et un bruit de ferraille retentit au loin.','img/escalierSinistre.png' ),
('Le Guet-apens du Squelette' , 'En haut de l''escalier, un piège se déclenche ! Un squelette armé d''une épée rouillée surgit d''une alcôve et vous attaque.','img/Skeleton.jpg'),
('La Salle des Armures','Votre prudence vous permet de détecter le piège et de l''éviter. Vous arrivez dans une grande salle dont les murs sont tapissés d''armures vides. Un coffre semble posé sur une table centrale.','img/Chest02.jpg'),
('Le Couloir des Illusions' , 'Vous arrivez dans un couloir étonnamment bien conservé, mais des murmures constants semblent vous désorienter. Vous distinguez une porte à gauche et une porte à droite.','img/couloir.jpg'),
('Le Trésor Maudit','Le coffre est lourd. Vous réussissez à le forcer. Il contient de l''Or, mais en le touchant, une malédiction vous frappe : vous perdez une partie de vos PV (points de vie) et une partie de l''Or est perdue au combat futur. ','img/tresor.jpg'),
('La Bibliothèque Interdite','La porte s''ouvre sur une bibliothèque où d''anciens parchemins se désagrègent. Au centre, un Gargouille de pierre s''anime et bloque votre chemin.','img/gargouille.jpg'),
('La Crypte des Disparus','La porte du crâne mène à une crypte humide. Vous trouvez le cadavre d''un aventurier et, à côté de lui, une Potion de Soin.','img/crypte.jpg'),
('Le Donjon Souterrain','Vous progressez vers un escalier descendant. Vous entendez de faibles pleurs et réalisez que vous êtes proche de la captive. Vous arrivez dans un donjon souterrain.','img/donjon.jpg'),
('Le Coffre Verrouillé','En longeant le mur de droite, vous trouvez un coffre solidement verrouillé par une serrure complexe.','img/coffre.jpg'),
('Le Piège à Dalles','En suivant le mur de gauche, vous marchez sur une dalle instable. Une fosse pleine de pointes s''ouvre sous vos pieds ! Vous devez faire un jet d''Agilité pour l''éviter.','img/dalleInstable.jpg'),
('La Cellule de la Fille','Vous arrivez enfin devant la cellule : la fille du bourgmestre est là, terrifiée. Elle vous dit que le sorcier s''est enfermé dans son laboratoire au niveau supérieur et qu''il est protégé par un puissant gardien.','img/cellule.jpg'),
('Le Passage Secret', 'La fille (ou le simple fait de l''avoir trouvée) vous révèle l''emplacement d''un passage secret menant à l''étage supérieur : derrière une tapisserie usée.','img/passageSecret.jpg'),
('L''Ombre Ténébreuse ','Le passage secret vous mène derrière un ennemi. Une Ombre Ténébreuse, gardien du laboratoire, vous fait face. Elle est forte, mais vous avez l''avantage de la surprise.','img/Laboratoire.jpg'),
('L''Ombre Ténébreuse ', 'L''Ombre Ténébreuse vous attend devant la porte du laboratoire, prête à vous accueillir.','img/ombreTenebreuse.jpg'),
('Le Laboratoire du Sorcier','Vous entrez dans un laboratoire rempli d''ingrédients étranges et de fioles bouillonnantes. Le sorcier, Malkor l''Ensorceleur, vous attend. Il lève une main et un champ de force magique vous empêche de passer.','img/sorcier.jpg'),
('Le Bouclier Magique','Vous frappez le bouclier magique de toutes vos forces, mais il tient bon. Le sorcier utilise ce temps pour invoquer un Esprit Elémentaire afin de vous affaiblir.', 'img/combatEsprit.jpg'),
('La Fiole de Puissance', 'Vous repérez une fiole bleue luisante sur une étagère, qui semble alimenter le bouclier. Vous la saisissez. Le sorcier vous attaque par la pensée.','img/fiole.jpg'),
('Le Face-à-Face Final','Le sorcier est maintenant affaibli et son bouclier est tombé. Il lève ses mains et se prépare au combat ultime, le sortilège de Destruction Finale prêt à être lancé.','img/final.jpg'),
('Le Retour des Ténèbres ','Le sorcier est vaincu. La magie qui enveloppait la forêt se dissipe lentement. Vous libérez la captive et retournez au Val Perdu, accueilli en héros. Le bourgmestre vous offre une grande récompense. Vous avez sauvé le village !','img/mortSorcier.jpg'),
('Fin de l''Aventure', 'Vous avez réussi à sauver la fille du bourgmestre et à vaincre le sorcier maléfique. Votre nom sera gravé dans les annales du Val Perdu comme un héros légendaire. Félicitations pour avoir mené cette aventure à son terme !','img/victoire.jpg');


-- Liens entre chapitres
INSERT INTO Links (chapter_id, next_chapter_id, description)
VALUES

(2, 3, 'Emprunter le chemin sinueux'),
(2, 4, 'Prendre le sentier couvert de ronces'),
(3, 5, 'Rester prudent'),
(3, 6, 'Ignorer les bruits'),
(4, 8, 'tuer le sanglier'),
(4, 10, 'mourir face au sanglier'),
(5, 7, 'après avoir écouté le paysan'),
(6, 7, 'Survivre au loup'),
(6 ,10, 'mourir face au loup'),
(7, 8, 'prendre le sentier couvert de mousses'),
(7, 9, 'suivre le chemin tortueux à travers les racines'),
(8, 11, 'toucher la pierre gravée'),
(8, 9, 'ignorer cette curiosité et poursuivez votre route'),
(9,12, 'l''aventure continue...'),
(10,2, 'retour au debut de l''aventure'),
(11,10, 'mourir empoisonné par la pierre gravée'),
(12,13, 'montez l''escalier à la hâte'),
(12,14, 'avancez prudemment, prêt à dégainer votre arme'),
(13,15, 'gagnez le combat contre le squelette'),
(13,10, 'mourir face au squelette'),
(14,16, 'examiner le coffre sur la table'),
(14,15, 'continuer sans toucher le coffre'),
(15,17, 'ouvrir la porte de gauche'),
(15,18, 'ouvrir la porte de droite'),
(16,15, 'vous gagnez de l''or mais perdez des pv à cause d''une malédiction'),
(17,19, 'gagner le combat contre la gargouille'),
(17,10, 'mourir face à la gargouille'),
(18,19, 'buvez la potion de soin(retrouver tous vos pv)'),
(18,19, 'ignorer la potion de soin'),
(19,20, 'suivez le mur de gauche'),
(19,21, 'suivez le mur de droite plus rapide'),
(20,22, 'si vous êtes voleur et reussissez à forcer le coffre'),
(20,22, 'si vous êtes pas voleur ou échouer à ouvrir le coffre'),
(21,22, 'réussir le jet d''agilité'),
(21,22, 'échouer le jet d''agilité vous perdez des dégats'),
(22,23, 'liberez la fille(elle vous suivra)'),
(22,23, 'demander d''attendre pour plus de sécurité'),
(23,24, 'utiliser le passage pour surprendre le sorcier'),
(23,25, 'affronter le sorcier de façon direct par la porte principale'),
(24,26, 'gagner le combat contre l''ombre ténébreuse gardien du laboratoire par surprise'),
(24,10, 'mourir face à l''ombre ténébreuse'),
(25,26, 'gagner le combat comtre l''ombre ténébreuse de face'),
(25,10, 'mourir face à l''ombre ténébreuse'),
(26,27, 'essayer de detruire le champs de force par la force'),
(26,28, 'chercher un moyen de détruire la source du champs de force'),
(27,29, 'gagner le combat contre l''esprit élémentaire'),
(27,10, 'mourir face à l''esprit élémentaire'),
(28,29, 'buvez la fiole de puissance gagnez un bonus de puissance mais subissez de faible dégats'),
(28,29, 'lancez la fiole sur le sol,le bouclier explose en blessant le sorcier'),
(29,30, 'gagner le combat final contre le sorcier'),
(29,2, 'mourir face au sorcier'),
(30,31, 'retourner au village avec la fille du bourgmestre'),
(31,32, 'fin de l''aventure');

-- Combats
INSERT INTO Encounter (chapter_id, monster_id)
VALUES
(4, 1),
(6, 2),
(7, 3),
(13, 4),
(17, 5),
(24, 6),
(27, 7),
(29, 8);
