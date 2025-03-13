<?php
require 'paypal_config.php';

// Connexion à la base de données
$host = 'localhost';
$dbname = 'plugin_paiement';
$user = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les données du formulaire
$cardNumber = $_POST['card-number'] ?? '';
$expiryDate = $_POST['expiry-date'] ?? '';
$email = $_POST['email'] ?? '';

// Fonction pour valider le numéro de carte (simplifié)
function validateCardNumber($cardNumber) {
  return preg_match('/^\d{16}$/', $cardNumber); // Vérifie que le numéro a 16 chiffres
}

// Fonction pour valider la date d'expiration (simplifié)
function validateExpiryDate($expiryDate) {
  return preg_match('/^\d{2}\/\d{2}$/', $expiryDate); // Vérifie le format MM/AA
}

// Fonction pour valider l'email
function validateEmail($email) {
  return filter_var($email, FILTER_VALIDATE_EMAIL); // Vérifie que l'email est valide
}

// Validation des données
if (validateCardNumber($cardNumber) && validateExpiryDate($expiryDate) && validateEmail($email)) {
  // Chiffrer le numéro de carte avant de l'enregistrer en base de données
  $hashedCardNumber = password_hash($cardNumber, PASSWORD_DEFAULT);

  // Enregistrer le numéro de carte chiffré
  $sql = "INSERT INTO paiements (card_number, expiry_date, email, statut) VALUES (:card_number, :expiry_date, :email, 'en attente')";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':card_number' => $hashedCardNumber,
    ':expiry_date' => $expiryDate,
    ':email' => $email,
    ':statut' => 'en attente'
  ]);

  // Créer une transaction PayPal
  $transaction = createPaypalTransaction('10.00'); // Montant à payer
  if ($transaction && isset($transaction['links'][1]['href'])) {
    // Rediriger l'utilisateur vers PayPal pour le paiement
    header('Location: ' . $transaction['links'][1]['href']);
    exit;
  } else {
    $message = "Erreur lors de la création de la transaction PayPal.";
  }
} else {
  $message = "Erreur de paiement. Veuillez vérifier vos informations.";
}

// Afficher le résultat à l'utilisateur
echo "<h1>$message</h1>";
?>