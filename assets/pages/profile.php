<?php
session_start();
include '../include/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ../../index.php');
    exit;
}

// Déterminer le rôle de l'utilisateur
$user_role = $_SESSION['role'];

// Gérer la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "UPDATE users SET username = :username, email = :email";
    $params = ['username' => $username, 'email' => $email, 'id' => $user_id];
    
    if (!empty($password)) {
        $sql .= ", password = :password";
        $params['password'] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    $sql .= " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Mettre à jour les informations de la session
    $_SESSION['username'] = $username;
    
    header('Location: profile.php?message=Profil mis à jour');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        nav { background-color: #34495e; padding: 10px; color: white; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { font-size: 1.5em; }
        nav ul { list-style: none; padding: 0; margin: 0; }
        nav ul li { display: inline; margin-left: 15px; }
        nav ul li a { color: #ecf0f1; text-decoration: none; }
        nav ul li a:hover { color: #bdc3c7; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; background-color: #34495e; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #2c3e50; }
        .message { color: green; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <?php if ($user_role == 'freelancer'): ?>
                <li><a href="freelancer-home.php">Accueil</a></li>
            <?php elseif ($user_role == 'client'): ?>
                <li><a href="client-home.php">Accueil</a></li>
            <?php endif; ?>

            <?php if ($user_role == 'freelancer'): ?>
                <li><a href="freelancer-home.php#products">Produits</a></li>
                <li><a href="add-product.php">Ajouter un produit</a></li>
            <?php elseif ($user_role == 'client'): ?>
                <li><a href="client-home.php#products">Produits</a></li>
                <li><a href="cart.php">Panier</a></li>
            <?php endif; ?>
            <li><a href="profile.php">Profil</a></li>
            <li><a href="../../logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    <main class="container">
        <h2>Votre Profil</h2>
        <?php if (isset($_GET['message'])): ?>
            <p class="message"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" name="password" id="password">
            </div>
            <button type="submit">Mettre à jour</button>
        </form>
        </main>
    <?php include '../include/footer.php'; ?>
</body>
</html>