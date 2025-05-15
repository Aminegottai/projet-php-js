<?php
include '../include/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $sql = "INSERT INTO orders (user_id, total) VALUES (:user_id, :total)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'total' => $total]);
    $order_id = $pdo->lastInsertId();
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (:order_id, :product_id, :quantity, :price)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'order_id' => $order_id,
            'product_id' => $product_id,
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ]);
    }
    unset($_SESSION['cart']);
    $success = "Commande passée avec succès !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement</title>
    <link rel="stylesheet" href="../css/checkout.css">
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="../../index.php">Accueil</a></li>
            <li><a href="../../index.php#products">Produits</a></li>
            <?php if (isset($_SESSION['user ✗id'])): ?>
                <li><a href="cart.php">Panier</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <section class="form-container">
        <h2>Paiement</h2>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
            <a href="profile.php" class="btn">Voir mes commandes</a>
        <?php else: ?>
            <form method="POST">
                <p>Total : <?php echo number_format(array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $_SESSION['cart'])), 2); ?> €</p>
                <button type="submit" class="btn">Confirmer le paiement</button>
            </form>
        <?php endif; ?>
    </section>
    <?php include '../include/footer.php'; ?>
</body>
</html>