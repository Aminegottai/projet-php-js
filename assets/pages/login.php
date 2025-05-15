<?php
include '../include/config.php';
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] == 'client' ? 'client-home.php' : 'freelancer-home.php'));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: ' . ($user['role'] == 'client' ? 'client-home.php' : 'freelancer-home.php'));
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="../../index.php">Accueil</a></li>
            <li><a href="../../index.php#products">Produits</a></li>
            <li><a href="login.php">Connexion</a></li>
            <li><a href="register.php">Inscription</a></li>
        </ul>
    </nav>
    <section class="form-container">
        <h2>Connexion</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" class="btn">Se connecter</button>
        </form>
        <p>Pas de compte ? <a href="register.php">S'inscrire</a></p>
    </section>
    <?php include '../include/footer.php'; ?>
</body>
</html>