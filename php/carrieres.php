<?php
/**
 * Bureau d'Études - Kinshasa
 * Traitement du formulaire de candidature
 */

// Définir le header pour JSON
header('Content-Type: application/json; charset=utf-8');

// Fonction pour nettoyer les données
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour valider l'email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Vérifier que la requête est en POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode de requête invalide.'
    ]);
    exit;
}

// Récupérer et nettoyer les données du formulaire
$poste = isset($_POST['poste']) ? cleanInput($_POST['poste']) : '';
$nom = isset($_POST['nom']) ? cleanInput($_POST['nom']) : '';
$email = isset($_POST['email']) ? cleanInput($_POST['email']) : '';
$telephone = isset($_POST['telephone']) ? cleanInput($_POST['telephone']) : '';
$message = isset($_POST['message']) ? cleanInput($_POST['message']) : '';

// Validation des champs obligatoires
$errors = [];

if (empty($poste)) {
    $errors[] = "Le poste est requis.";
}

if (empty($nom)) {
    $errors[] = "Le nom est requis.";
}

if (empty($email)) {
    $errors[] = "L'email est requis.";
} elseif (!isValidEmail($email)) {
    $errors[] = "L'email n'est pas valide.";
}

if (empty($telephone)) {
    $errors[] = "Le téléphone est requis.";
}

// Validation des fichiers
if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Le CV est requis.";
}

if (!isset($_FILES['lettre_motivation']) || $_FILES['lettre_motivation']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "La lettre de motivation est requise.";
}

// Vérification du type MIME pour PDF
$allowed_mime = ['application/pdf'];
$cv_mime = '';
$lettre_mime = '';

if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $cv_mime = finfo_file($finfo, $_FILES['cv']['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($cv_mime, $allowed_mime)) {
        $errors[] = "Le CV doit être au format PDF.";
    }
    
    // Vérifier la taille (max 5 MB)
    if ($_FILES['cv']['size'] > 5 * 1024 * 1024) {
        $errors[] = "Le CV ne doit pas dépasser 5 MB.";
    }
}

if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $lettre_mime = finfo_file($finfo, $_FILES['lettre_motivation']['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($lettre_mime, $allowed_mime)) {
        $errors[] = "La lettre de motivation doit être au format PDF.";
    }
    
    // Vérifier la taille (max 5 MB)
    if ($_FILES['lettre_motivation']['size'] > 5 * 1024 * 1024) {
        $errors[] = "La lettre de motivation ne doit pas dépasser 5 MB.";
    }
}

// Si des erreurs existent, les retourner
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// Créer le dossier pour stocker les candidatures s'il n'existe pas
$upload_dir = '../candidatures/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Créer un sous-dossier pour cette candidature avec timestamp
$candidate_folder = $upload_dir . date('Y-m-d_His') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nom) . '/';
if (!file_exists($candidate_folder)) {
    mkdir($candidate_folder, 0755, true);
}

// Déplacer les fichiers uploadés
$cv_filename = 'CV_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nom) . '.pdf';
$lettre_filename = 'Lettre_Motivation_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nom) . '.pdf';

$cv_path = $candidate_folder . $cv_filename;
$lettre_path = $candidate_folder . $lettre_filename;

$cv_uploaded = move_uploaded_file($_FILES['cv']['tmp_name'], $cv_path);
$lettre_uploaded = move_uploaded_file($_FILES['lettre_motivation']['tmp_name'], $lettre_path);

if (!$cv_uploaded || !$lettre_uploaded) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'enregistrement des fichiers.'
    ]);
    exit;
}

// Configuration de l'email
$to = "rh@bunshconsulting.com"; // Remplacez par votre email RH
$subject = "Nouvelle candidature : " . $poste . " - " . $nom;

// Construire le corps de l'email
$email_body = "Nouvelle candidature reçue via le formulaire de carrières\n\n";
$email_body .= "===================================\n\n";
$email_body .= "POSTE: " . $poste . "\n\n";
$email_body .= "INFORMATIONS DU CANDIDAT:\n";
$email_body .= "Nom: " . $nom . "\n";
$email_body .= "Email: " . $email . "\n";
$email_body .= "Téléphone: " . $telephone . "\n\n";
$email_body .= "MESSAGE:\n" . ($message ?: 'Aucun message') . "\n\n";
$email_body .= "FICHIERS JOINTS:\n";
$email_body .= "CV: " . $cv_filename . "\n";
$email_body .= "Lettre de motivation: " . $lettre_filename . "\n";
$email_body .= "Dossier de candidature: " . $candidate_folder . "\n\n";
$email_body .= "===================================\n";
$email_body .= "Date: " . date('d/m/Y H:i:s') . "\n";
$email_body .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

// Headers de l'email
$headers = "From: " . $email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

// Envoyer l'email
$mail_sent = @mail($to, $subject, $email_body, $headers);

// Enregistrer dans un fichier texte (solution de secours)
$log_file = '../candidatures/candidatures_log.txt';
$log_entry = "\n\n========== " . date('d/m/Y H:i:s') . " ==========\n";
$log_entry .= "Poste: " . $poste . "\n";
$log_entry .= "Nom: " . $nom . "\n";
$log_entry .= "Email: " . $email . "\n";
$log_entry .= "Téléphone: " . $telephone . "\n";
$log_entry .= "Message: " . $message . "\n";
$log_entry .= "CV: " . $cv_path . "\n";
$log_entry .= "Lettre: " . $lettre_path . "\n";
$log_entry .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

$file_saved = @file_put_contents($log_file, $log_entry, FILE_APPEND);

// Option : Enregistrer dans une base de données
/*
try {
    $db_host = 'localhost';
    $db_name = 'bureau_etudes';
    $db_user = 'votre_utilisateur';
    $db_pass = 'votre_mot_de_passe';
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("
        INSERT INTO candidatures (poste, nom, email, telephone, message, cv_path, lettre_path, date_candidature, ip_address)
        VALUES (:poste, :nom, :email, :telephone, :message, :cv_path, :lettre_path, NOW(), :ip)
    ");
    
    $stmt->execute([
        ':poste' => $poste,
        ':nom' => $nom,
        ':email' => $email,
        ':telephone' => $telephone,
        ':message' => $message,
        ':cv_path' => $cv_path,
        ':lettre_path' => $lettre_path,
        ':ip' => $_SERVER['REMOTE_ADDR']
    ]);
} catch (PDOException $e) {
    error_log("Erreur BD: " . $e->getMessage());
}
*/

// Réponse selon le résultat
if ($cv_uploaded && $lettre_uploaded && ($mail_sent || $file_saved)) {
    // Email de confirmation automatique au candidat
    $confirmation_subject = "Confirmation de candidature - Bureau d'Études Kinshasa";
    $confirmation_body = "Bonjour " . $nom . ",\n\n";
    $confirmation_body .= "Nous avons bien reçu votre candidature pour le poste de : " . $poste . "\n\n";
    $confirmation_body .= "Votre dossier comprend :\n";
    $confirmation_body .= "- CV\n";
    $confirmation_body .= "- Lettre de motivation\n\n";
    $confirmation_body .= "Notre équipe RH examinera votre candidature et vous contactera dans les plus brefs délais si votre profil correspond à nos besoins.\n\n";
    $confirmation_body .= "Nous vous remercions de l'intérêt que vous portez à notre organisation.\n\n";
    $confirmation_body .= "Cordialement,\n";
    $confirmation_body .= "L'équipe Ressources Humaines\n\n";
    $confirmation_body .= "---\n";
    $confirmation_body .= "Bureau d'Études - Kinshasa\n";
    $confirmation_body .= "Mesurer aujourd'hui pour transformer demain\n";
    $confirmation_body .= "Email: rh@bunshconsulting.com\n";
    $confirmation_body .= "Tél: +243 0979 286 949\n";
    
    $confirmation_headers = "From: rh@bunshconsulting.com\r\n";
    $confirmation_headers .= "Reply-To: rh@bunshconsulting.com\r\n";
    $confirmation_headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    
    @mail($email, $confirmation_subject, $confirmation_body, $confirmation_headers);
    
    echo json_encode([
        'success' => true,
        'message' => 'Votre candidature a été envoyée avec succès ! Nous examinerons votre dossier et vous contacterons prochainement.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'envoi. Veuillez nous contacter directement à rh@bunshconsulting.com'
    ]);
}

exit;
?>

