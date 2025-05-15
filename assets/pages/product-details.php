<?php
include '../include/config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT p.*, c.name AS category_name, u.username AS freelancer_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    die("Produit non trouvé");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="../css/product-details.css">
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="../../index.php">Accueil</a></li>
            <li><a href="../../index.php#products">Produits</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="cart.php">Panier</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <section class="product-details">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <p><strong>Catégorie :</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
        <p><strong>Freelancer :</strong> <?php echo htmlspecialchars($product['freelancer_name']); ?></p>
        <p><strong>Description :</strong> <?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Prix :</strong> <?php echo number_format($product['price'], 2); ?> €</p>
        <a href="cart.php?add=<?php echo $product['id']; ?>" class="btn">Ajouter au Panier</a>
    </section>
    <?php include '../include/footer.php'; ?>
</body>
</html>