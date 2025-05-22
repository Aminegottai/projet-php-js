<?php
session_start();

// Forcer la déconnexion si "Rester connecté" n'est pas actif
if (!isset($_SESSION['stay_logged_in']) || $_SESSION['stay_logged_in'] !== true) {
    $_SESSION = array();
    session_destroy();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

include 'assets/include/config.php';

$sql = "SELECT p.*, c.name AS category_name, u.username AS freelancer_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        JOIN users u ON p.user_id = u.id";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plateforme de Freelancers</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        nav { background-color: #34495e; padding: 10px; color: white; }
        nav .logo { font-size: 1.5em; }
        nav ul { list-style: none; padding: 0; }
        nav ul li { display: inline; margin-right: 15px; }
        nav ul li a { color: #ecf0f1; text-decoration: none; }
        nav ul li a:hover { color: #bdc3c7; }
        .hero { text-align: center; padding: 50px; background-color: #34495e; color: white; }
        .btn { padding: 10px 20px; background-color: #2ecc71; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background-color: "27ae60; }
        .products { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .product-card { background-color: white; padding: 15px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); text-align: center; }
        .product-card img { max-width: 100%; height: auto; border-radius: 4px; }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="index.php#products">Produits</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] == 'freelancer'): ?>
                    <li><a href="assets/pages/freelancer-home.php">Tableau de bord</a></li>
                <?php endif; ?>
                <li><a href="assets/pages/cart.php">Panier</a></li>
                <li><a href="assets/pages/profile.php">Profil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="assets/pages/login.php">Connexion</a></li>
                <li><a href="assets/pages/register.php">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <section class="hero">
        <h1>Produits Uniques</h1>
        <p>Explorez les créations des freelancers.</p>
        <a href="#products" class="btn">Voir les Produits</a>
    </section>
    <section class="products" id="products">
        <h2>Produits</h2>
        <?php if (empty($products)): ?>
            <p>Aucun produit disponible.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="assets/uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p>Prix : <?php echo number_format($product['price'], 2); ?> €</p>
                        <a href="assets/pages/product-details.php?id=<?php echo $product['id']; ?>" class="btn">Détails</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php include 'assets/include/footer.php'; ?>
</body>
</html>