<?php
session_start();
include '../include/config.php';

// Vérifier si l'utilisateur est connecté et a le rôle 'client'
$user_id = null;
if (isset($_COOKIE['PHPSESSID'])) {
    if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'client') {
        $user_id = $_SESSION['user_id'];
    } else {
        header('Location: ../../index.php?return_to=client-home');
        exit;
    }
} else {
    header('Location: ../../index.php?return_to=client-home');
    exit;
}

// Récupérer les produits
$sql = "SELECT p.*, c.name AS category_name, u.username AS freelancer_name, u.email AS freelancer_email 
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
    <title>Accueil Client</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        nav { background-color: #34495e; padding: 10px; color: white; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { font-size: 1.5em; }
        nav ul { list-style: none; padding: 0; margin: 0; }
        nav ul li { display: inline; margin-left: 15px; }
        nav ul li a { color: #ecf0f1; text-decoration: none; }
        nav ul li a:hover { color: #bdc3c7; }
        .hero { text-align: center; padding: 50px; background-color: #34495e; color: white; }
        .btn { padding: 10px 20px; background-color: #2ecc71; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background-color: #27ae60; }
        .products { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .product-card { background-color: white; padding: 15px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); text-align: center; }
        .product-card img { max-width: 100%; height: auto; border-radius: 4px; }
        .product-card p {
            margin: 5px 0;
            font-size: 0.9em;
            color: #34495e;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="client-home.php">Accueil</a></li>
            <li><a href="client-home.php#products">Produits</a></li>
            <li><a href="cart.php">Panier</a></li>
            <li><a href="profile.php">Profil</a></li>
            <li><a href="../../logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    <section class="hero">
        <h1>Explorez les produits disponibles.</h1>
        <a href="cart.php" class="btn">Voir le Panier</a>
    </section>
    <section class="products" id="products">
        <h2>Produits Disponibles</h2>
        <?php if (empty($products)): ?>
            <p>Aucun produit disponible.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php
                        $image_path = "../uploads/" . htmlspecialchars($product['image']);
                        
                        if (file_exists($image_path) && getimagesize($image_path)) {
                            $image_src = $image_path;
                        } else {
                            $image_src = $default_image;
                        }
                        ?>
                        <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p>Prix : <?php echo number_format($product['price'], 2); ?> €</p>
                        <p>Freelancer : <?php echo htmlspecialchars($product['freelancer_name']); ?> (Email : <?php echo htmlspecialchars($product['freelancer_email']); ?>)</p>
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn">Détails</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php include '../include/footer.php'; ?>
</body>
</html>