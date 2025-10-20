<?php
/**
 * Bureau d'Études - Kinshasa
 * Traitement du formulaire de contact
 */

// Définir le header pour JSON
header('Content-Type: application/json; charset=utf-8');

// Activer l'affichage des erreurs en développement (à désactiver en production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

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

// Détecter la langue (depuis le formulaire ou l'en-tête HTTP)
$lang = isset($_POST['lang']) ? cleanInput($_POST['lang']) : 'fr';
if (!in_array($lang, ['fr', 'en'])) {
    $lang = 'fr';
}

// Dictionnaire de traductions
$translations = [
    'fr' => [
        'invalid_method' => 'Méthode de requête invalide.',
        'name_required' => 'Le nom est requis.',
        'email_required' => 'L\'email est requis.',
        'email_invalid' => 'L\'email n\'est pas valide.',
        'subject_required' => 'Le sujet est requis.',
        'message_required' => 'Le message est requis.',
        'subject_baseline' => 'Demande d\'Étude Baseline',
        'subject_midline' => 'Demande d\'Évaluation Midline',
        'subject_endline' => 'Demande d\'Évaluation Endline',
        'subject_me' => 'Demande de Conception système M&E',
        'subject_formation' => 'Demande de Renforcement de capacités',
        'subject_data' => 'Demande d\'Analyse de données',
        'subject_other' => 'Autre demande',
        'new_message' => 'Nouveau message',
        'email_intro' => 'Nouveau message reçu via le formulaire de contact',
        'name_label' => 'NOM',
        'email_label' => 'EMAIL',
        'phone_label' => 'TÉLÉPHONE',
        'org_label' => 'ORGANISATION',
        'subject_label' => 'SUJET',
        'message_label' => 'MESSAGE',
        'not_provided' => 'Non fourni',
        'not_provided_fem' => 'Non fournie',
        'date_label' => 'Date',
        'confirmation_subject' => 'Confirmation de réception - Bunsh',
        'confirmation_greeting' => 'Bonjour',
        'confirmation_body1' => 'Nous avons bien reçu votre message concernant :',
        'confirmation_body2' => 'Notre équipe vous répondra dans les plus brefs délais (généralement sous 24-48 heures).',
        'confirmation_body3' => 'En attendant, n\'hésitez pas à consulter nos ressources sur notre site web ou à nous contacter directement au +243 0979 286 949.',
        'confirmation_closing' => 'Cordialement,',
        'confirmation_team' => 'L\'équipe Bunsh',
        'confirmation_footer1' => 'Bunsh - Partenaire de Recherche et d\'Evaluation',
        'confirmation_footer2' => 'Mesurer aujourd\'hui pour transformer demain',
        'success_message' => 'Merci pour votre message ! Nous vous répondrons dans les plus brefs délais.',
        'error_message' => 'Une erreur est survenue lors de l\'envoi. Veuillez nous contacter directement à contact@bunshconsulting.com'
    ],
    'en' => [
        'invalid_method' => 'Invalid request method.',
        'name_required' => 'Name is required.',
        'email_required' => 'Email is required.',
        'email_invalid' => 'Email is not valid.',
        'subject_required' => 'Subject is required.',
        'message_required' => 'Message is required.',
        'subject_baseline' => 'Baseline Study Request',
        'subject_midline' => 'Midline Evaluation Request',
        'subject_endline' => 'Endline Evaluation Request',
        'subject_me' => 'M&E System Design Request',
        'subject_formation' => 'Capacity Building Request',
        'subject_data' => 'Data Analysis Request',
        'subject_other' => 'Other Request',
        'new_message' => 'New Message',
        'email_intro' => 'New message received via contact form',
        'name_label' => 'NAME',
        'email_label' => 'EMAIL',
        'phone_label' => 'PHONE',
        'org_label' => 'ORGANIZATION',
        'subject_label' => 'SUBJECT',
        'message_label' => 'MESSAGE',
        'not_provided' => 'Not provided',
        'not_provided_fem' => 'Not provided',
        'date_label' => 'Date',
        'confirmation_subject' => 'Receipt Confirmation - Bunsh',
        'confirmation_greeting' => 'Hello',
        'confirmation_body1' => 'We have received your message regarding:',
        'confirmation_body2' => 'Our team will respond to you as soon as possible (usually within 24-48 hours).',
        'confirmation_body3' => 'In the meantime, feel free to check our resources on our website or contact us directly at +243 0979 286 949.',
        'confirmation_closing' => 'Best regards,',
        'confirmation_team' => 'The Bunsh Team',
        'confirmation_footer1' => 'Bunsh - Research and Evaluation Partner',
        'confirmation_footer2' => 'Measure today to transform tomorrow',
        'success_message' => 'Thank you for your message! We will respond to you as soon as possible.',
        'error_message' => 'An error occurred while sending. Please contact us directly at contact@bunshconsulting.com'
    ]
];

// Fonction pour obtenir une traduction
function t($key, $lang, $translations) {
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}

// Vérifier que la requête est en POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        'success' => false,
        'message' => t('invalid_method', $lang, $translations)
    ]);
    exit;
}

// Récupérer et nettoyer les données du formulaire
$nom = isset($_POST['nom']) ? cleanInput($_POST['nom']) : '';
$email = isset($_POST['email']) ? cleanInput($_POST['email']) : '';
$telephone = isset($_POST['telephone']) ? cleanInput($_POST['telephone']) : '';
$organisation = isset($_POST['organisation']) ? cleanInput($_POST['organisation']) : '';
$sujet = isset($_POST['sujet']) ? cleanInput($_POST['sujet']) : '';
$message = isset($_POST['message']) ? cleanInput($_POST['message']) : '';

// Validation des champs obligatoires
$errors = [];

if (empty($nom)) {
    $errors[] = t('name_required', $lang, $translations);
}

if (empty($email)) {
    $errors[] = t('email_required', $lang, $translations);
} elseif (!isValidEmail($email)) {
    $errors[] = t('email_invalid', $lang, $translations);
}

if (empty($sujet)) {
    $errors[] = t('subject_required', $lang, $translations);
}

if (empty($message)) {
    $errors[] = t('message_required', $lang, $translations);
}

// Si des erreurs existent, les retourner
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// Configuration de l'email
$to = "contact@bunshconsulting.com"; // Email réel
$subject_map = [
    'baseline' => t('subject_baseline', $lang, $translations),
    'midline' => t('subject_midline', $lang, $translations),
    'endline' => t('subject_endline', $lang, $translations),
    'me' => t('subject_me', $lang, $translations),
    'formation' => t('subject_formation', $lang, $translations),
    'data' => t('subject_data', $lang, $translations),
    'autre' => t('subject_other', $lang, $translations)
];

$email_subject = isset($subject_map[$sujet]) ? $subject_map[$sujet] : t('new_message', $lang, $translations);
$email_subject .= " - " . $nom;

// Construire le corps de l'email
$email_body = t('email_intro', $lang, $translations) . "\n\n";
$email_body .= "===================================\n\n";
$email_body .= t('name_label', $lang, $translations) . ": " . $nom . "\n";
$email_body .= t('email_label', $lang, $translations) . ": " . $email . "\n";
$email_body .= t('phone_label', $lang, $translations) . ": " . ($telephone ?: t('not_provided', $lang, $translations)) . "\n";
$email_body .= t('org_label', $lang, $translations) . ": " . ($organisation ?: t('not_provided_fem', $lang, $translations)) . "\n";
$email_body .= t('subject_label', $lang, $translations) . ": " . ($subject_map[$sujet] ?? $sujet) . "\n\n";
$email_body .= t('message_label', $lang, $translations) . ":\n" . $message . "\n\n";
$email_body .= "===================================\n";
$email_body .= t('date_label', $lang, $translations) . ": " . date('d/m/Y H:i:s') . "\n";
$email_body .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

// Headers de l'email
$headers = "From: " . $email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

// Envoyer l'email
$mail_sent = @mail($to, $email_subject, $email_body, $headers);

// Option : Enregistrer dans une base de données MySQL
// Décommenter et configurer si vous souhaitez stocker les messages dans une base de données

/*
try {
    // Configuration de la base de données
    $db_host = 'localhost';
    $db_name = 'bureau_etudes';
    $db_user = 'votre_utilisateur';
    $db_pass = 'votre_mot_de_passe';
    
    // Connexion PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Préparer la requête d'insertion
    $stmt = $pdo->prepare("
        INSERT INTO contacts (nom, email, telephone, organisation, sujet, message, date_envoi, ip_address)
        VALUES (:nom, :email, :telephone, :organisation, :sujet, :message, NOW(), :ip)
    ");
    
    // Exécuter la requête
    $stmt->execute([
        ':nom' => $nom,
        ':email' => $email,
        ':telephone' => $telephone,
        ':organisation' => $organisation,
        ':sujet' => $sujet,
        ':message' => $message,
        ':ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    $db_saved = true;
} catch (PDOException $e) {
    $db_saved = false;
    // Log l'erreur (ne pas l'afficher à l'utilisateur en production)
    error_log("Erreur BD: " . $e->getMessage());
}
*/

// Option : Enregistrer dans un fichier texte (solution de secours)
// Utile si l'envoi d'email ne fonctionne pas immédiatement

$log_file = '../contact_messages.txt';
$log_entry = "\n\n========== " . date('d/m/Y H:i:s') . " ==========\n";
$log_entry .= "Nom: " . $nom . "\n";
$log_entry .= "Email: " . $email . "\n";
$log_entry .= "Téléphone: " . $telephone . "\n";
$log_entry .= "Organisation: " . $organisation . "\n";
$log_entry .= "Sujet: " . $sujet . "\n";
$log_entry .= "Message: " . $message . "\n";
$log_entry .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

$file_saved = @file_put_contents($log_file, $log_entry, FILE_APPEND);

// Réponse selon le résultat
if ($mail_sent || $file_saved) {
    // Email de confirmation automatique au client (optionnel)
    $confirmation_subject = t('confirmation_subject', $lang, $translations);
    $confirmation_body = t('confirmation_greeting', $lang, $translations) . " " . $nom . ",\n\n";
    $confirmation_body .= t('confirmation_body1', $lang, $translations) . " " . ($subject_map[$sujet] ?? $sujet) . "\n\n";
    $confirmation_body .= t('confirmation_body2', $lang, $translations) . "\n\n";
    $confirmation_body .= t('confirmation_body3', $lang, $translations) . "\n\n";
    $confirmation_body .= t('confirmation_closing', $lang, $translations) . "\n";
    $confirmation_body .= t('confirmation_team', $lang, $translations) . "\n\n";
    $confirmation_body .= "---\n";
    $confirmation_body .= t('confirmation_footer1', $lang, $translations) . "\n";
    $confirmation_body .= t('confirmation_footer2', $lang, $translations) . "\n";
    $confirmation_body .= "Email: contact@bunshconsulting.com\n";
    $confirmation_body .= "Tél: +243 0979 286 949\n";
    
    $confirmation_headers = "From: contact@bunshconsulting.com\r\n";
    $confirmation_headers .= "Reply-To: contact@bunshconsulting.com\r\n";
    $confirmation_headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    
    @mail($email, $confirmation_subject, $confirmation_body, $confirmation_headers);
    
    echo json_encode([
        'success' => true,
        'message' => t('success_message', $lang, $translations)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => t('error_message', $lang, $translations)
    ]);
}

exit;
?>

