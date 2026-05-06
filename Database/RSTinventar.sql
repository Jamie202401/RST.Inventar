-- ============================================================
-- Datenbank: RSTInventar
-- Beschreibung: Inventarverwaltungssystem RST-Veolia GmbH & Co. KG
-- Erstellt für: IHK-Abschlussprojekt Fachinformatiker Systemintegration
-- Auth: Keycloak (kein lokales Passwort in der DB)
-- ============================================================

CREATE DATABASE IF NOT EXISTS rst_inventar
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE rst_inventar;

-- ============================================================
-- TABELLEN
-- Reihenfolge wichtig: FK-Ziel-Tabellen zuerst!
-- 1. Benutzer → 2. Hersteller → 3. Lieferant → 4. Kategorie
-- → 5. Geraete → 6. Standorte → 7. Inventar → 8. Historie
-- ============================================================

-- ── Benutzer ─────────────────────────────────────────────────
-- Kein Passwort – Authentifizierung erfolgt ausschließlich über Keycloak.
-- B_KeycloakSub verweist auf die Subject-UUID aus dem Keycloak-JWT.
CREATE TABLE Benutzer (
  BID           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  B_Name        VARCHAR(100) NOT NULL,
  B_Email       VARCHAR(255),
  B_KeycloakSub VARCHAR(36)  UNIQUE,
  CONSTRAINT PK_Benutzer PRIMARY KEY (BID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Hersteller ───────────────────────────────────────────────
CREATE TABLE Hersteller (
  HID          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  H_Name       VARCHAR(150) NOT NULL,
  H_Land       VARCHAR(100),
  H_Webseite   VARCHAR(255),
  H_CreateDate DATETIME     DEFAULT CURRENT_TIMESTAMP,
  H_Creator    VARCHAR(100),
  H_CreatorID  INT UNSIGNED,
  H_ChangeDate DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  H_Changer    VARCHAR(100),
  H_ChangerID  INT UNSIGNED,
  CONSTRAINT PK_Hersteller   PRIMARY KEY (HID),
  CONSTRAINT FK_Her_Creator  FOREIGN KEY (H_CreatorID) REFERENCES Benutzer(BID) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT FK_Her_Changer  FOREIGN KEY (H_ChangerID) REFERENCES Benutzer(BID) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Lieferant ────────────────────────────────────────────────
CREATE TABLE Lieferant (
  LID          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  L_Name       VARCHAR(150) NOT NULL,
  L_Kontakt    VARCHAR(150),
  L_Email      VARCHAR(255),
  L_Telefon    VARCHAR(50),
  L_Land       VARCHAR(100),
  L_CreateDate DATETIME     DEFAULT CURRENT_TIMESTAMP,
  L_Creator    VARCHAR(100),
  L_CreatorID  INT UNSIGNED,
  L_ChangeDate DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  L_Changer    VARCHAR(100),
  L_ChangerID  INT UNSIGNED,
  CONSTRAINT PK_Lieferant   PRIMARY KEY (LID),
  CONSTRAINT FK_Lie_Creator FOREIGN KEY (L_CreatorID) REFERENCES Benutzer(BID) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT FK_Lie_Changer FOREIGN KEY (L_ChangerID) REFERENCES Benutzer(BID) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Kategorie ────────────────────────────────────────────────
CREATE TABLE Kategorie (
  KID          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  K_Name       VARCHAR(100) NOT NULL,
  K_CreateDate DATETIME     DEFAULT CURRENT_TIMESTAMP,
  K_Creator    VARCHAR(100),
  K_CreatorID  INT UNSIGNED,
  K_ChangeDate DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  K_Changer    VARCHAR(100),
  K_ChangerID  INT UNSIGNED,
  CONSTRAINT PK_Kategorie   PRIMARY KEY (KID),
  CONSTRAINT FK_Kat_Creator FOREIGN KEY (K_CreatorID) REFERENCES Benutzer(BID) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT FK_Kat_Changer FOREIGN KEY (K_ChangerID) REFERENCES Benutzer(BID) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Geraete ──────────────────────────────────────────────────
-- G_Hersteller / G_Lieferant (VARCHAR) entfernt → FK auf eigene Tabellen
CREATE TABLE Geraete (
  GID            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  G_Name         VARCHAR(255)  NOT NULL,
  HID            INT UNSIGNED,
  LID            INT UNSIGNED,
  G_Kaufdatum    DATE,
  G_Kosten       DECIMAL(10,2),
  G_Garantieende DATE,
  G_CreateDate   DATETIME      DEFAULT CURRENT_TIMESTAMP,
  G_Creator      VARCHAR(100),
  G_CreatorID    INT UNSIGNED,
  G_ChangeDate   DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  G_Changer      VARCHAR(100),
  G_ChangerID    INT UNSIGNED,
  KID            INT UNSIGNED  NOT NULL,
  CONSTRAINT PK_Geraete        PRIMARY KEY (GID),
  CONSTRAINT FK_Ger_Kategorie  FOREIGN KEY (KID)         REFERENCES Kategorie(KID)  ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT FK_Ger_Hersteller FOREIGN KEY (HID)         REFERENCES Hersteller(HID) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT FK_Ger_Lieferant  FOREIGN KEY (LID)         REFERENCES Lieferant(LID)  ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT FK_Ger_Creator    FOREIGN KEY (G_CreatorID) REFERENCES Benutzer(BID)   ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT FK_Ger_Changer    FOREIGN KEY (G_ChangerID) REFERENCES Benutzer(BID)   ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Standorte ────────────────────────────────────────────────
CREATE TABLE Standorte (
  SID          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  S_Name       VARCHAR(200) NOT NULL,
  S_Strasse    VARCHAR(255),
  S_PLZ        VARCHAR(20),
  S_Ort        VARCHAR(100),
  S_Land       VARCHAR(100),
  S_CreateDate DATETIME     DEFAULT CURRENT_TIMESTAMP,
  S_Creator    VARCHAR(100),
  S_CreatorID  INT UNSIGNED,
  S_ChangeDate DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  S_Changer    VARCHAR(100),
  S_ChangerID  INT UNSIGNED,
  CONSTRAINT PK_Standorte   PRIMARY KEY (SID),
  CONSTRAINT FK_Sta_Creator FOREIGN KEY (S_CreatorID) REFERENCES Benutzer(BID) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT FK_Sta_Changer FOREIGN KEY (S_ChangerID) REFERENCES Benutzer(BID) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Inventar ─────────────────────────────────────────────────
CREATE TABLE Inventar (
  InvID   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Barcode VARCHAR(100) NOT NULL UNIQUE,
  GID     INT UNSIGNED NOT NULL,
  SID     INT UNSIGNED NOT NULL,
  BID     INT UNSIGNED,
  CONSTRAINT PK_Inventar      PRIMARY KEY (InvID),
  CONSTRAINT FK_Inv_Geraete   FOREIGN KEY (GID) REFERENCES Geraete(GID)   ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT FK_Inv_Standorte FOREIGN KEY (SID) REFERENCES Standorte(SID) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT FK_Inv_Benutzer  FOREIGN KEY (BID) REFERENCES Benutzer(BID)  ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Historie ─────────────────────────────────────────────────
CREATE TABLE Historie (
  HID         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  H_Tabelle   VARCHAR(100) NOT NULL,
  H_Aktion    VARCHAR(50)  NOT NULL,
  H_Feld      VARCHAR(100),
  H_AlterWert TEXT,
  H_NeuerWert TEXT,
  H_Datum     DATETIME     DEFAULT CURRENT_TIMESTAMP,
  BID         INT UNSIGNED,
  GID         INT UNSIGNED,
  CONSTRAINT PK_Historie     PRIMARY KEY (HID),
  CONSTRAINT FK_His_Benutzer FOREIGN KEY (BID) REFERENCES Benutzer(BID) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT FK_His_Geraete  FOREIGN KEY (GID) REFERENCES Geraete(GID)  ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- INDIZES
-- ============================================================

CREATE INDEX idx_inventar_barcode  ON Inventar(Barcode);
CREATE INDEX idx_inventar_sid      ON Inventar(SID);
CREATE INDEX idx_geraete_name      ON Geraete(G_Name);
CREATE INDEX idx_geraete_kid       ON Geraete(KID);
CREATE INDEX idx_geraete_hid       ON Geraete(HID);
CREATE INDEX idx_geraete_lid       ON Geraete(LID);
CREATE INDEX idx_geraete_garantie  ON Geraete(G_Garantieende);
CREATE INDEX idx_historie_datum    ON Historie(H_Datum);
CREATE INDEX idx_historie_gid      ON Historie(GID);
CREATE INDEX idx_benutzer_sub      ON Benutzer(B_KeycloakSub);


-- ============================================================
-- TRIGGER – Automatische Versionierung
-- ============================================================

DELIMITER $$

-- Neues Gerät angelegt
CREATE TRIGGER trg_geraete_insert
AFTER INSERT ON Geraete
FOR EACH ROW
BEGIN
  INSERT INTO Historie (H_Tabelle, H_Aktion, H_Feld, H_AlterWert, H_NeuerWert, BID, GID)
  VALUES ('Geraete', 'INSERT', 'G_Name', NULL, NEW.G_Name, NEW.G_CreatorID, NEW.GID);
END$$

-- Gerät geändert
CREATE TRIGGER trg_geraete_update
AFTER UPDATE ON Geraete
FOR EACH ROW
BEGIN
  IF OLD.G_Name != NEW.G_Name THEN
    INSERT INTO Historie (H_Tabelle, H_Aktion, H_Feld, H_AlterWert, H_NeuerWert, BID, GID)
    VALUES ('Geraete', 'UPDATE', 'G_Name', OLD.G_Name, NEW.G_Name, NEW.G_ChangerID, NEW.GID);
  END IF;
  IF IFNULL(OLD.HID, 0) != IFNULL(NEW.HID, 0) THEN
    INSERT INTO Historie (H_Tabelle, H_Aktion, H_Feld, H_AlterWert, H_NeuerWert, BID, GID)
    VALUES ('Geraete', 'UPDATE', 'HID', OLD.HID, NEW.HID, NEW.G_ChangerID, NEW.GID);
  END IF;
  IF IFNULL(OLD.LID, 0) != IFNULL(NEW.LID, 0) THEN
    INSERT INTO Historie (H_Tabelle, H_Aktion, H_Feld, H_AlterWert, H_NeuerWert, BID, GID)
    VALUES ('Geraete', 'UPDATE', 'LID', OLD.LID, NEW.LID, NEW.G_ChangerID, NEW.GID);
  END IF;
  IF IFNULL(OLD.G_Kosten, 0) != IFNULL(NEW.G_Kosten, 0) THEN
    INSERT INTO Historie (H_Tabelle, H_Aktion, H_Feld, H_AlterWert, H_NeuerWert, BID, GID)
    VALUES ('Geraete', 'UPDATE', 'G_Kosten', OLD.G_Kosten, NEW.G_Kosten, NEW.G_ChangerID, NEW.GID);
  END IF;
  IF IFNULL(OLD.G_Garantieende, '') != IFNULL(NEW.G_Garantieende, '') THEN
    INSERT INTO Historie (H_Tabelle, H_Aktion, H_Feld, H_AlterWert, H_NeuerWert, BID, GID)
    VALUES ('Geraete', 'UPDATE', 'G_Garantieende', OLD.G_Garantieende, NEW.G_Garantieende, NEW.G_ChangerID, NEW.GID);
  END IF;
  IF OLD.KID != NEW.KID THEN
    INSERT INTO Historie (H_Tabelle, H_Aktion, H_Feld, H_AlterWert, H_NeuerWert, BID, GID)
    VALUES ('Geraete', 'UPDATE', 'KID', OLD.KID, NEW.KID, NEW.G_ChangerID, NEW.GID);
  END IF;
END$$

-- Gerät gelöscht
CREATE TRIGGER trg_geraete_delete
BEFORE DELETE ON Geraete
FOR EACH ROW
BEGIN
  INSERT INTO Historie (H_Tabelle, H_Aktion, H_Feld, H_AlterWert, H_NeuerWert, BID, GID)
  VALUES ('Geraete', 'DELETE', 'G_Name', OLD.G_Name, NULL, OLD.G_ChangerID, OLD.GID);
END$$

-- Standort im Inventar geändert
CREATE TRIGGER trg_inventar_update
AFTER UPDATE ON Inventar
FOR EACH ROW
BEGIN
  IF OLD.SID != NEW.SID THEN
    INSERT INTO Historie (H_Tabelle, H_Aktion, H_Feld, H_AlterWert, H_NeuerWert, BID, GID)
    VALUES ('Inventar', 'UPDATE', 'SID', OLD.SID, NEW.SID, NEW.BID, NEW.GID);
  END IF;
END$$

DELIMITER ;


-- ============================================================
-- STORED PROCEDURES
-- ============================================================

DELIMITER $$
-- sp_GeraetAnlegen: p_HID / p_LID sind jetzt FK-IDs statt Freitextfelder
CREATE PROCEDURE sp_GeraetAnlegen(
  IN p_Name        VARCHAR(255),
  IN p_HID         INT UNSIGNED,
  IN p_LID         INT UNSIGNED,
  IN p_Kaufdatum   DATE,
  IN p_Kosten      DECIMAL(10,2),
  IN p_Garantieende DATE,
  IN p_KID         INT UNSIGNED,
  IN p_SID         INT UNSIGNED,
  IN p_BID         INT UNSIGNED,
  IN p_Barcode     VARCHAR(100))
BEGIN
  DECLARE v_GID   INT UNSIGNED;
  DECLARE v_BName VARCHAR(100);
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN ROLLBACK; END;

  START TRANSACTION;
    SELECT B_Name INTO v_BName FROM Benutzer WHERE BID = p_BID;
    INSERT INTO Geraete (G_Name, HID, LID, G_Kaufdatum,
      G_Kosten, G_Garantieende, G_Creator, G_CreatorID, KID)
    VALUES (p_Name, p_HID, p_LID, p_Kaufdatum,
      p_Kosten, p_Garantieende, v_BName, p_BID, p_KID);
    SET v_GID = LAST_INSERT_ID();
    INSERT INTO Inventar (Barcode, GID, SID, BID)
    VALUES (p_Barcode, v_GID, p_SID, p_BID);
  COMMIT;
END$$
DELIMITER ;


-- ============================================================
-- TESTDATEN
-- ============================================================

-- ── Benutzer (kein Passwort – Login via Keycloak) ─────────────
-- B_KeycloakSub wird beim ersten Login durch die App gesetzt
INSERT INTO Benutzer (B_Name, B_Email) VALUES
  ('admin',    'admin@rst-veolia.de'),
  ('mmueller', 'mmueller@rst-veolia.de'),
  ('sschmidt', 'sschmidt@rst-veolia.de');

-- ── Hersteller ───────────────────────────────────────────────
-- HID 1–15 (Reihenfolge entspricht den CALL-Statements unten)
INSERT INTO Hersteller (H_Name, H_Land, H_Creator, H_CreatorID) VALUES
  ('Dell',     'USA',         'admin', 1),  -- HID 1
  ('LG',       'Südkorea',    'admin', 1),  -- HID 2
  ('Samsung',  'Südkorea',    'admin', 1),  -- HID 3
  ('Lenovo',   'China',       'admin', 1),  -- HID 4
  ('HP',       'USA',         'admin', 1),  -- HID 5
  ('HPE',      'USA',         'admin', 1),  -- HID 6
  ('Cisco',    'USA',         'admin', 1),  -- HID 7
  ('Ubiquiti', 'USA',         'admin', 1),  -- HID 8
  ('Sophos',   'Großbritannien','admin', 1), -- HID 9
  ('APC',      'USA',         'admin', 1),  -- HID 10
  ('Zebra',    'USA',         'admin', 1),  -- HID 11
  ('Logitech', 'Schweiz',     'admin', 1),  -- HID 12
  ('Epson',    'Japan',       'admin', 1),  -- HID 13
  ('Delock',   'Deutschland', 'admin', 1),  -- HID 14
  ('Anker',    'China',       'admin', 1);  -- HID 15

-- ── Lieferant ────────────────────────────────────────────────
-- LID 1–4
INSERT INTO Lieferant (L_Name, L_Land, L_Creator, L_CreatorID) VALUES
  ('Bechtle',      'Deutschland', 'admin', 1),  -- LID 1
  ('Mindfactory',  'Deutschland', 'admin', 1),  -- LID 2
  ('Amazon',       'USA',         'admin', 1),  -- LID 3
  ('Telekom',      'Deutschland', 'admin', 1);  -- LID 4

-- ── Kategorien ───────────────────────────────────────────────
INSERT INTO Kategorie (K_Name, K_Creator, K_CreatorID) VALUES
  ('Monitor',          'admin', 1),
  ('PC / Laptop',      'admin', 1),
  ('Server',           'admin', 1),
  ('Netzwerkgerät',    'admin', 1),
  ('Dockingstation',   'admin', 1),
  ('USV',              'admin', 1),
  ('Mobiles Gerät',    'admin', 1),
  ('Zubehör',          'admin', 1),
  ('Konferenztechnik', 'admin', 1),
  ('Kabel',            'admin', 1);

-- ── Standorte ────────────────────────────────────────────────
INSERT INTO Standorte (S_Name, S_Creator, S_CreatorID) VALUES
  ('Büro 1 – Erdgeschoss',  'admin', 1),
  ('Büro 2 – Erdgeschoss',  'admin', 1),
  ('Büro 3 – Obergeschoss', 'admin', 1),
  ('Serverraum',            'admin', 1),
  ('Besprechungsraum',      'admin', 1),
  ('Lager / Archiv',        'admin', 1),
  ('Homeoffice Müller',     'admin', 1),
  ('Partnerfirma Spanien',  'admin', 1);

-- ── Geräte + Inventar über Stored Procedure ──────────────────
-- sp_GeraetAnlegen(Name, HID, LID, Kaufdatum, Kosten, Garantieende, KID, SID, BID, Barcode)
CALL sp_GeraetAnlegen('Dell UltraSharp 27"',        1,  1, '2022-03-15',  549.00, '2025-03-15', 1, 1, 1, 'RST-0001');
CALL sp_GeraetAnlegen('LG 24MK430H',                2,  1, '2021-06-01',  189.00, '2024-06-01', 1, 2, 1, 'RST-0002');
CALL sp_GeraetAnlegen('Samsung 32 Zoll Curved',     3,  2, '2023-01-10',  399.00, '2026-01-10', 1, 5, 1, 'RST-0003');
CALL sp_GeraetAnlegen('Lenovo ThinkPad T14',         4,  1, '2022-08-20', 1299.00, '2025-08-20', 2, 7, 2, 'RST-0004');
CALL sp_GeraetAnlegen('HP EliteBook 840 G9',         5,  1, '2023-02-14', 1499.00, '2026-02-14', 2, 1, 1, 'RST-0005');
CALL sp_GeraetAnlegen('Dell OptiPlex 7090',          1,  1, '2021-11-05',  899.00, '2024-11-05', 2, 2, 1, 'RST-0006');
CALL sp_GeraetAnlegen('Lenovo ThinkCentre M70q',     4,  2, '2022-05-18',  749.00, '2025-05-18', 2, 3, 2, 'RST-0007');
CALL sp_GeraetAnlegen('HPE ProLiant DL380 Gen10',    6,  1, '2020-09-01', 4899.00, '2025-09-01', 3, 4, 1, 'RST-0008');
CALL sp_GeraetAnlegen('Dell PowerEdge T340',         1,  1, '2021-03-22', 2199.00, '2024-03-22', 3, 4, 1, 'RST-0009');
CALL sp_GeraetAnlegen('Cisco Catalyst 2960-X',       7,  1, '2020-06-15', 1299.00, '2025-06-15', 4, 4, 1, 'RST-0010');
CALL sp_GeraetAnlegen('Ubiquiti UniFi AP AC Pro',    8,  3, '2022-04-10',  179.00, '2024-04-10', 4, 4, 1, 'RST-0011');
CALL sp_GeraetAnlegen('Sophos XGS 136 Firewall',     9,  1, '2023-01-05',  899.00, '2026-01-05', 4, 4, 3, 'RST-0012');
CALL sp_GeraetAnlegen('Lenovo ThinkPad USB-C Dock',  4,  1, '2022-08-20',  189.00, '2025-08-20', 5, 1, 1, 'RST-0013');
CALL sp_GeraetAnlegen('Dell WD19S Dockingstation',   1,  1, '2023-02-14',  219.00, '2026-02-14', 5, 2, 1, 'RST-0014');
CALL sp_GeraetAnlegen('APC Smart-UPS 1500VA',       10,  1, '2020-09-01',  699.00, '2025-09-01', 6, 4, 1, 'RST-0015');
CALL sp_GeraetAnlegen('Samsung Galaxy Tab A8',       3,  4, '2022-11-01',  329.00, '2024-11-01', 7, 3, 1, 'RST-0016');
CALL sp_GeraetAnlegen('Zebra TC52 Handheld MDE',    11,  1, '2021-07-15', 1199.00, '2024-07-15', 7, 6, 2, 'RST-0017');
CALL sp_GeraetAnlegen('Logitech MX Keys Tastatur',  12,  3, '2022-01-10',  119.00, '2024-01-10', 8, 1, 1, 'RST-0018');
CALL sp_GeraetAnlegen('Logitech MX Master 3 Maus',  12,  3, '2022-01-10',   99.00, '2024-01-10', 8, 1, 1, 'RST-0019');
CALL sp_GeraetAnlegen('Logitech MeetUp Konferenz',  12,  1, '2021-05-20',  899.00, '2024-05-20', 9, 5, 1, 'RST-0020');
CALL sp_GeraetAnlegen('Epson EB-W52 Beamer',        13,  2, '2022-09-01',  549.00, '2025-09-01', 9, 5, 3, 'RST-0021');
CALL sp_GeraetAnlegen('DisplayPort Kabel 2m',       14,  3, '2022-03-15',   12.99, NULL,         10, 2, 1, 'RST-0022');
CALL sp_GeraetAnlegen('USB-C Kabel 1m',             15,  3, '2023-01-10',   15.99, NULL,         10, 1, 1, 'RST-0023');
