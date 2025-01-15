<?php


session_start();

// Vérifie si l'utilisateur a déjà envoyé des requêtes
if (!isset($_SESSION['last_request_time'])) {
    $_SESSION['last_request_time'] = time();
    $_SESSION['request_count'] = 1;
} else {
    $current_time = time();
    $time_diff = $current_time - $_SESSION['last_request_time'];

    // Réinitialiser le compteur après 1 minute
    if ($time_diff > 60) {
        $_SESSION['last_request_time'] = $current_time;
        $_SESSION['request_count'] = 1;
    } else {
        $_SESSION['request_count']++;
    }

    // Limiter à 10 requêtes par minute
    if ($_SESSION['request_count'] > 10) {
        die("Trop de requêtes. Veuillez réessayer plus tard.");
    }
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=secure_db", "user", "password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (:name, :email, :message)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':message', $message);
    $stmt->execute();

    echo "Données enregistrées avec succès.";
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8'); //htmlspecialchars permet d'éviter les XSS
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL); // validation de l'adresse mail grace à filter_var
    $message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

    
    if (!$email) {
        die("Adresse e-mail invalide.");
    }

    
    $to = "titouanmnr@example.com"; 
    $subject = "Nouveau message de contact";
    $body = "Nom : $name\nE-mail : $email\nMessage :\n$message";
    $headers = "From: no-reply@example.com";

    if (mail($to, $subject, $body, $headers)) {
        echo "Message envoyé avec succès.";
    } else {
        echo "Une erreur est survenue lors de l'envoi du message.";
    }
}
?>
