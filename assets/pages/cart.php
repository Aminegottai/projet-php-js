<?php
include '../include/config.php';
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    $sql = "SELECT * FROM products WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][$product_id] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['quantity'] + 1 : 1
        ];
    }
}
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity']--;
        if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
    <link rel="stylesheet" href="../css/cart.css">
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
    <section class="cart">
        <h2>Votre Panier</h2>
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Votre panier est vide.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> €</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> €</td>
                            <td><a href="cart.php?remove=<?php echo $id; ?>" class="btn remove">Retirer</a></td>
                        </tr>
                        <?php $total += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong><?php echo number_format($total, 2); ?> €</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <a href="checkout.php" class="btn checkout">Payer</a>
        <?php endif; ?>
    </section>
    <?php include '../include/footer.php'; ?>
</body>
</html>