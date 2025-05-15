<?php
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
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="index.php#products">Produits</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
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