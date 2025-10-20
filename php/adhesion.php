<?php
/**
 * Bureau d'Études - Kinshasa
 * Traitement du formulaire d'adhésion
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
$type_adhesion = isset($_POST['type_adhesion']) ? cleanInput($_POST['type_adhesion']) : '';
$nom = isset($_POST['nom']) ? cleanInput($_POST['nom']) : '';
$prenom = isset($_POST['prenom']) ? cleanInput($_POST['prenom']) : '';
$email = isset($_POST['email']) ? cleanInput($_POST['email']) : '';
$telephone = isset($_POST['telephone']) ? cleanInput($_POST['telephone']) : '';
$adresse = isset($_POST['adresse']) ? cleanInput($_POST['adresse']) : '';
$profession = isset($_POST['profession']) ? cleanInput($_POST['profession']) : '';
$organisation = isset($_POST['organisation']) ? cleanInput($_POST['organisation']) : '';
$secteur = isset($_POST['secteur']) ? cleanInput($_POST['secteur']) : '';
$experience = isset($_POST['experience']) ? cleanInput($_POST['experience']) : '';
$motivations = isset($_POST['motivations']) ? cleanInput($_POST['motivations']) : '';
$interets = isset($_POST['interets']) ? $_POST['interets'] : [];
$accepte_conditions = isset($_POST['accepte_conditions']) ? true : false;

// Validation des champs obligatoires
$errors = [];

if (empty($type_adhesion)) {
    $errors[] = "Le type d'adhésion est requis.";
}

if (empty($nom)) {
    $errors[] = "Le nom est requis.";
}

if (empty($prenom)) {
    $errors[] = "Le prénom est requis.";
}

if (empty($email)) {
    $errors[] = "L'email est requis.";
} elseif (!isValidEmail($email)) {
    $errors[] = "L'email n'est pas valide.";
}

if (empty($telephone)) {
    $errors[] = "Le téléphone est requis.";
}

if (!$accepte_conditions) {
    $errors[] = "Vous devez accepter les conditions générales.";
}

// Si des erreurs existent, les retourner
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// Déterminer le montant selon le type d'adhésion
$montant = 0;
$type_label = '';
switch ($type_adhesion) {
    case 'individuelle':
        $montant = 50;
        $type_label = 'Individuelle';
        break;
    case 'etudiante':
        $montant = 20;
        $type_label = 'Étudiante';
        break;
    case 'organisationnelle':
        $montant = 300;
        $type_label = 'Organisationnelle';
        break;
    default:
        $montant = 0;
        $type_label = $type_adhesion;
}

// Convertir les intérêts en chaîne
$interets_text = is_array($interets) ? implode(', ', $interets) : '';

// Configuration de l'email
$to = "adhesions@bunshconsulting.com"; // Remplacez par votre email
$subject = "Nouvelle demande d'adhésion " . $type_label . " - " . $nom . " " . $prenom;

// Construire le corps de l'email
$email_body = "Nouvelle demande d'adhésion reçue\n\n";
$email_body .= "===================================\n\n";
$email_body .= "TYPE D'ADHÉSION: " . $type_label . " (" . $montant . " USD/an)\n\n";
$email_body .= "INFORMATIONS PERSONNELLES:\n";
$email_body .= "Nom: " . $nom . "\n";
$email_body .= "Prénom: " . $prenom . "\n";
$email_body .= "Email: " . $email . "\n";
$email_body .= "Téléphone: " . $telephone . "\n";
$email_body .= "Adresse: " . ($adresse ?: 'Non fournie') . "\n\n";
$email_body .= "INFORMATIONS PROFESSIONNELLES:\n";
$email_body .= "Profession: " . ($profession ?: 'Non fournie') . "\n";
$email_body .= "Organisation: " . ($organisation ?: 'Non fournie') . "\n";
$email_body .= "Secteur: " . ($secteur ?: 'Non fourni') . "\n";
$email_body .= "Expérience: " . ($experience ?: 'Non fournie') . "\n\n";
$email_body .= "DOMAINES D'INTÉRÊT:\n";
$email_body .= $interets_text ?: 'Aucun sélectionné' . "\n\n";
$email_body .= "MOTIVATIONS:\n";
$email_body .= $motivations ?: 'Aucune motivation fournie' . "\n\n";
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
$log_file = '../adhesions_log.txt';
$log_entry = "\n\n========== " . date('d/m/Y H:i:s') . " ==========\n";
$log_entry .= "Type: " . $type_label . " (" . $montant . " USD)\n";
$log_entry .= "Nom: " . $nom . " " . $prenom . "\n";
$log_entry .= "Email: " . $email . "\n";
$log_entry .= "Téléphone: " . $telephone . "\n";
$log_entry .= "Adresse: " . $adresse . "\n";
$log_entry .= "Profession: " . $profession . "\n";
$log_entry .= "Organisation: " . $organisation . "\n";
$log_entry .= "Secteur: " . $secteur . "\n";
$log_entry .= "Expérience: " . $experience . "\n";
$log_entry .= "Intérêts: " . $interets_text . "\n";
$log_entry .= "Motivations: " . $motivations . "\n";
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
        INSERT INTO adhesions (
            type_adhesion, montant, nom, prenom, email, telephone, adresse,
            profession, organisation, secteur, experience, interets, motivations,
            date_adhesion, ip_address, statut
        )
        VALUES (
            :type_adhesion, :montant, :nom, :prenom, :email, :telephone, :adresse,
            :profession, :organisation, :secteur, :experience, :interets, :motivations,
            NOW(), :ip, 'en_attente'
        )
    ");
    
    $stmt->execute([
        ':type_adhesion' => $type_adhesion,
        ':montant' => $montant,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':telephone' => $telephone,
        ':adresse' => $adresse,
        ':profession' => $profession,
        ':organisation' => $organisation,
        ':secteur' => $secteur,
        ':experience' => $experience,
        ':interets' => $interets_text,
        ':motivations' => $motivations,
        ':ip' => $_SERVER['REMOTE_ADDR']
    ]);
} catch (PDOException $e) {
    error_log("Erreur BD: " . $e->getMessage());
}
*/

// Réponse selon le résultat
if ($mail_sent || $file_saved) {
    // Email de confirmation automatique au demandeur
    $confirmation_subject = "Confirmation de votre demande d'adhésion - Bureau d'Études Kinshasa";
    $confirmation_body = "Bonjour " . $prenom . " " . $nom . ",\n\n";
    $confirmation_body .= "Nous avons bien reçu votre demande d'adhésion " . $type_label . ".\n\n";
    $confirmation_body .= "RÉCAPITULATIF DE VOTRE ADHÉSION:\n";
    $confirmation_body .= "Type: " . $type_label . "\n";
    $confirmation_body .= "Montant: " . $montant . " USD/an\n\n";
    $confirmation_body .= "PROCHAINES ÉTAPES:\n";
    $confirmation_body .= "1. Notre équipe va examiner votre demande (délai: 2-3 jours ouvrables)\n";
    $confirmation_body .= "2. Vous recevrez un email avec les instructions de paiement\n";
    $confirmation_body .= "3. Une fois le paiement effectué, votre compte membre sera activé\n";
    $confirmation_body .= "4. Vous recevrez vos identifiants d'accès à la plateforme\n\n";
    $confirmation_body .= "MODES DE PAIEMENT DISPONIBLES:\n";
    $confirmation_body .= "- Virement bancaire\n";
    $confirmation_body .= "- Mobile Money (Vodacom, Airtel, Orange)\n";
    $confirmation_body .= "- Paiement en espèces à nos bureaux\n\n";
    $confirmation_body .= "Si vous avez des questions, n'hésitez pas à nous contacter.\n\n";
    $confirmation_body .= "Cordialement,\n";
    $confirmation_body .= "L'équipe du Bureau d'Études\n\n";
    $confirmation_body .= "---\n";
    $confirmation_body .= "Bureau d'Études - Kinshasa\n";
    $confirmation_body .= "Mesurer aujourd'hui pour transformer demain\n";
    $confirmation_body .= "Email: adhesions@bunshconsulting.com\n";
    $confirmation_body .= "Tél: +243 0979 286 949\n";
    
    $confirmation_headers = "From: adhesions@bunshconsulting.com\r\n";
    $confirmation_headers .= "Reply-To: adhesions@bunshconsulting.com\r\n";
    $confirmation_headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    
    @mail($email, $confirmation_subject, $confirmation_body, $confirmation_headers);
    
    echo json_encode([
        'success' => true,
        'message' => 'Votre demande d\'adhésion a été envoyée avec succès ! Vous recevrez un email avec les instructions de paiement dans les prochains jours.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'envoi. Veuillez nous contacter directement à adhesions@bunshconsulting.com'
    ]);
}

exit;
?>

