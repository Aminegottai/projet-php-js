<?php
session_start();
include '../include/config.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'freelancer') {
        header('Location: freelancer-home.php');
        exit;
    } elseif ($_SESSION['role'] == 'client') {
        header('Location: client-home.php');
        exit;
    } else {
        header('Location: ../../index.php');
        exit;
    }
}

// Gestion du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $stay_logged_in = isset($_POST['stay_logged_in']) && $_POST['stay_logged_in'] == 'on';

    if ($email && $password) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['stay_logged_in'] = $stay_logged_in;

            if ($user['role'] == 'freelancer') {
                header('Location: freelancer-home.php');
                exit;
            } elseif ($user['role'] == 'client') {
                header('Location: client-home.php');
                exit;
            } else {
                header('Location: ../../index.php');
                exit;
            }
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: 50px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; color: #34495e; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        input[type="checkbox"] { margin-right: 5px; }
        button { width: 100%; padding: 10px; background-color: #34495e; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #2c3e50; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="stay_logged_in">
                    <input type="checkbox" name="stay_logged_in" id="stay_logged_in"> Rester connecté
                </label>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        <p>Pas de compte ? <a href="register.php">Inscrivez-vous</a></p>
    </div>
</body>
</html>s