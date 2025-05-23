<?php
include '../include/config.php';

// Vérifier si l'utilisateur est connecté et a le rôle 'client'
$user_id = null;
if (isset($_COOKIE['PHPSESSID'])) {
    session_start();
    if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'client') {
        $user_id = $_SESSION['user_id'];
    } else {
        header('Location: ../../index.php?return_to=cart');
        exit;
    }
} else {
    header('Location: ../../index.php?return_to=cart');
    exit;
}

// Gérer la suppression d'un produit spécifique du panier
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['product_id'])) {
    $product_id_to_remove = (int)$_GET['product_id'];
    try {
        $sql = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id_to_remove]);
        header('Location: cart.php?message=Produit supprimé du panier');
        exit;
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression du produit : " . $e->getMessage();
    }
}

// Vider le panier si demandé
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    try {
        $sql = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        header('Location: cart.php?message=Panier vidé');
        exit;
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression du panier : " . $e->getMessage();
    }
}

// Récupérer les produits du panier
$cart_items = [];
try {
    $sql = "SELECT c.*, p.name, p.price 
            FROM cart c 
            LEFT JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération du panier : " . $e->getMessage();
}

// Calculer les statistiques
$total_items = 0; // Nombre total d'articles (somme des quantités)
$unique_products = count($cart_items); // Nombre de produits différents
foreach ($cart_items as $item) {
    $total_items += $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        nav { background-color: #34495e; padding: 10px; color: white; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { font-size: 1.5em; }
        nav ul { list-style: none; padding: 0; margin: 0; }
        nav ul li { display: inline; margin-left: 15px; }
        nav ul li a { color: #ecf0f1; text-decoration: none; }
        nav ul li a:hover { color: #bdc3c7; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #ddd; }
        .btn { padding: 10px 20px; background-color: #34495e; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background-color: #2c3e50; }
        .btn-danger { background-color: #e74c3c; }
        .btn-danger:hover { background-color: #c0392b; }
        .btn-remove { background-color: #e74c3c; padding: 5px 10px; font-size: 0.9em; }
        .btn-remove:hover { background-color: #c0392b; }
        .message { color: green; text-align: center; margin-bottom: 15px; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
        .stats { background-color: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .stats p { margin: 5px 0; }
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
    <section class="container">
        <h2>Votre Panier</h2>
        <?php if (isset($_GET['message'])): ?>
            <p class="message"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <?php if (empty($cart_items)): ?>
            <p>Votre panier est vide. <a href="client-home.php#products">Ajouter des produits</a></p>
        <?php else: ?>
            <!-- Afficher les statistiques -->
            <div class="stats">
                <p><strong>Nombre de produits différents :</strong> <?php echo $unique_products; ?></p>
                <p><strong>Nombre total d'articles :</strong> <?php echo $total_items; ?></p>
            </div>
            <?php $total = 0; ?>
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <span>
                        <?php 
                        if ($item['name']) {
                            echo htmlspecialchars($item['name']) . " (x" . $item['quantity'] . ")";
                        } else {
                            echo "Produit indisponible (x" . $item['quantity'] . ")";
                        }
                        ?>
                    </span>
                    <div>
                        <span>
                            <?php 
                            if ($item['price']) {
                                echo number_format($item['price'] * $item['quantity'], 2) . " €";
                                $total += $item['price'] * $item['quantity'];
                            } else {
                                echo "Prix indisponible";
                            }
                            ?>
                        </span>
                        <a href="cart.php?action=remove&product_id=<?php echo $item['product_id']; ?>" class="btn btn-remove" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit du panier ?');">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="cart-item">
                <strong>Total :</strong>
                <strong><?php echo number_format($total, 2); ?> €</strong>
            </div>
            <a href="cart.php?action=clear" class="btn btn-danger">Vider le panier</a>
        <?php endif; ?>
    </section>
    <?php include '../include/footer.php'; ?>
</body>
</html>