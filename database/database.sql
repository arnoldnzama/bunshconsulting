-- ================================================
-- Configuration de la base de données MySQL
-- Bureau d'Études - Kinshasa
-- ================================================

-- Créer la base de données (si elle n'existe pas)
CREATE DATABASE IF NOT EXISTS bureau_etudes 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE bureau_etudes;

-- ================================================
-- Table pour stocker les messages de contact
-- ================================================
CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    organisation VARCHAR(100) DEFAULT NULL,
    sujet VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    statut ENUM('nouveau', 'lu', 'en_cours', 'traite') DEFAULT 'nouveau',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Index pour améliorer les performances
    INDEX idx_date_envoi (date_envoi),
    INDEX idx_email (email),
    INDEX idx_statut (statut),
    INDEX idx_sujet (sujet)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Table pour stocker les abonnés à la newsletter (optionnel)
-- ================================================
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    nom VARCHAR(100) DEFAULT NULL,
    date_inscription DATETIME NOT NULL,
    statut ENUM('actif', 'inactif', 'desabonne') DEFAULT 'actif',
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Table pour les projets/réalisations (optionnel)
-- ================================================
CREATE TABLE IF NOT EXISTS projets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    categorie ENUM('education', 'sante', 'agriculture', 'gouvernance', 'environnement') NOT NULL,
    type_evaluation ENUM('baseline', 'midline', 'endline', 'autre') NOT NULL,
    description TEXT NOT NULL,
    client VARCHAR(150) NOT NULL,
    annee YEAR NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    visible BOOLEAN DEFAULT TRUE,
    ordre INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_categorie (categorie),
    INDEX idx_annee (annee),
    INDEX idx_visible (visible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Table pour les articles de blog (optionnel)
-- ================================================
CREATE TABLE IF NOT EXISTS articles_blog (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    extrait TEXT NOT NULL,
    contenu LONGTEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    auteur VARCHAR(100) DEFAULT NULL,
    date_publication DATE NOT NULL,
    categorie VARCHAR(50) DEFAULT NULL,
    tags VARCHAR(255) DEFAULT NULL,
    visible BOOLEAN DEFAULT TRUE,
    vues INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_date_publication (date_publication),
    INDEX idx_slug (slug),
    INDEX idx_visible (visible),
    FULLTEXT KEY idx_recherche (titre, extrait, contenu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Vue pour les statistiques des messages de contact
-- ================================================
CREATE OR REPLACE VIEW stats_contacts AS
SELECT 
    DATE(date_envoi) as date,
    COUNT(*) as nombre_messages,
    sujet,
    COUNT(CASE WHEN statut = 'nouveau' THEN 1 END) as nouveaux,
    COUNT(CASE WHEN statut = 'traite' THEN 1 END) as traites
FROM contacts
GROUP BY DATE(date_envoi), sujet;

-- ================================================
-- Insertion de données d'exemple pour les projets
-- ================================================
INSERT INTO projets (titre, categorie, type_evaluation, description, client, annee, slug, ordre) VALUES
('Évaluation Endline - Programme d\'accès à l\'éducation primaire', 'education', 'endline', 
 'Évaluation finale d\'un programme visant à améliorer l\'accès et la qualité de l\'éducation primaire dans 120 écoles de Kinshasa.',
 'ONG Internationale - Éducation pour Tous', 2024, 'evaluation-education-primaire-2024', 1),

('Étude Baseline - Santé maternelle et infantile', 'sante', 'baseline',
 'Établissement de la situation de référence pour un programme de réduction de la mortalité maternelle et infantile dans 5 zones de santé.',
 'Ministère de la Santé Publique', 2024, 'baseline-sante-maternelle-2024', 2),

('Évaluation Midline - Sécurité alimentaire et nutrition', 'agriculture', 'midline',
 'Évaluation intermédiaire d\'un programme d\'amélioration de la sécurité alimentaire touchant 3000 ménages agricoles.',
 'PAM (Programme Alimentaire Mondial)', 2023, 'midline-securite-alimentaire-2023', 3),

('Évaluation Midline - Renforcement de la gouvernance locale', 'gouvernance', 'midline',
 'Évaluation à mi-parcours d\'un programme de renforcement des capacités de 50 administrations locales.',
 'PNUD Congo', 2024, 'midline-gouvernance-locale-2024', 4),

('Évaluation Baseline - Gestion durable des forêts', 'environnement', 'baseline',
 'Étude de référence pour un projet de gestion communautaire des forêts.',
 'WWF RDC', 2024, 'baseline-forets-2024', 5);

-- ================================================
-- Procédure stockée pour nettoyer les anciens messages
-- (Optionnel - pour automatiser la suppression des messages traités de plus de 6 mois)
-- ================================================
DELIMITER //

CREATE PROCEDURE nettoyer_anciens_messages()
BEGIN
    DELETE FROM contacts 
    WHERE statut = 'traite' 
    AND date_envoi < DATE_SUB(NOW(), INTERVAL 6 MONTH);
END //

DELIMITER ;

-- Pour exécuter manuellement : CALL nettoyer_anciens_messages();

-- ================================================
-- Créer un utilisateur dédié (Remplacez par un mot de passe fort)
-- ================================================
-- CREATE USER IF NOT EXISTS 'bureau_user'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE_FORT';
-- GRANT SELECT, INSERT, UPDATE ON bureau_etudes.* TO 'bureau_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ================================================
-- Instructions de sauvegarde
-- ================================================
-- Pour sauvegarder la base de données :
-- mysqldump -u root -p bureau_etudes > bureau_etudes_backup.sql

-- Pour restaurer :
-- mysql -u root -p bureau_etudes < bureau_etudes_backup.sql

-- ================================================
-- Notes importantes
-- ================================================
-- 1. Modifiez les mots de passe par défaut
-- 2. Utilisez des mots de passe forts
-- 3. Limitez les privilèges de l'utilisateur aux besoins réels
-- 4. Planifiez des sauvegardes régulières (quotidiennes recommandées)
-- 5. En production, utilisez SSL pour les connexions MySQL
-- 6. Configurez les paramètres dans contact.php après création de la base

