<?php
session_start();
include '../include/config.php';

if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] != 'freelancer')) {
    header('Location: ../../index.php?return_to=freelancer-home');
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Freelancer</title>
    <link rel="stylesheet" href="../css/freelancer-home.css">
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
        .btn:hover { background-color: #27ae60; }
        .products { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .product-card { background-color: white; padding: 15px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); text-align: center; }
        .product-card img { max-width: 100%; height: auto; border-radius: 4px; }
    </style>
</head>
<body>
    <nav>
        <div class="logo"></div>
        <ul>
            <li><a href="freelancer-home.php">Accueil</a></li>
            <li><a href="freelancer-home.php#products">Produits</a></li>
            <li><a href="add-product.php">Ajouter un produit</a></li>
            <li><a href="freelancer-stats.php">Statistiques</a></li>
            <li><a href="profile.php">Profil</a></li>
            <li><a href="../../logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    <section class="hero">
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> !</h1>
        <p>Gérez vos produits et votre profil.</p>
        <a href="add-product.php" class="btn">Ajouter un produit</a>
    </section>
    <section class="products" id="products">
        <h2>Vos Produits</h2>
        <?php if (empty($products)): ?>
            <p>Aucun produit. <a href="add-product.php">Ajoutez-en un !</a></p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p>Prix : <?php echo number_format($product['price'], 2); ?> €</p>
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn">Détails</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php include '../include/footer.php'; ?>
</body>
</html>