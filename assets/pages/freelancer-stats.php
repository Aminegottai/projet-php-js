<?php
session_start();
include '../include/config.php';

// Vérifier si l'utilisateur est connecté et a le rôle 'freelancer'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'freelancer') {
    header('Location: ../../index.php?return_to=freelancer-stats');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les ventes du freelancer
try {
    $sql = "
        SELECT p.id, p.name, c.quantity, p.price, (p.price * c.quantity) AS total_per_item
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE p.user_id = :user_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculer les statistiques
    $gross_revenue = 0;
    $total_sales = 0; // Nombre total d'articles vendus
    $product_quantities = []; // Pour trouver le produit le plus vendu

    foreach ($sales as $sale) {
        $gross_revenue += $sale['total_per_item'];
        $total_sales += $sale['quantity'];
        
        // Accumuler les quantités par produit
        $product_id = $sale['id'];
        if (!isset($product_quantities[$product_id])) {
            $product_quantities[$product_id] = [
                'name' => $sale['name'],
                'quantity' => 0
            ];
        }
        $product_quantities[$product_id]['quantity'] += $sale['quantity'];
    }

    // Trouver le produit le plus vendu
    $top_product = null;
    $max_quantity = 0;
    foreach ($product_quantities as $product) {
        if ($product['quantity'] > $max_quantity) {
            $max_quantity = $product['quantity'];
            $top_product = $product['name'];
        }
    }

    $platform_fee = $gross_revenue * 0.10; // 10% pour la plateforme
    $net_revenue = $gross_revenue - $platform_fee; // 90% pour le freelancer

} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des ventes : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques Freelancer</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        nav { background-color: #34495e; padding: 10px; color: white; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { font-size: 1.5em; }
        nav ul { list-style: none; padding: 0; margin: 0; }
        nav ul li { display: inline; margin-left: 15px; }
        nav ul li a { color: #ecf0f1; text-decoration: none; }
        nav ul li a:hover { color: #bdc3c7; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .stats-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .stats-table th, .stats-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .stats-table th { background-color: #34495e; color: white; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="freelancer-home.php">Accueil</a></li>
            <li><a href="freelancer-home.php#products">Produits</a></li>
            <li><a href="add-product.php">Ajouter un produit</a></li>
            <li><a href="profile.php">Profil</a></li>
            <li><a href="freelancer-stats.php">Statistiques</a></li>
            <li><a href="../../logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    <main>
        <section class="container">
            <h2>Vos Statistiques de Ventes</h2>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <?php if (empty($sales)): ?>
                <p>Aucune vente enregistrée pour le moment.</p>
            <?php else: ?>
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire (€)</th>
                            <th>Total par Article (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['name']); ?></td>
                                <td><?php echo htmlspecialchars($sale['quantity']); ?></td>
                                <td><?php echo number_format($sale['price'], 2); ?></td>
                                <td><?php echo number_format($sale['total_per_item'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h3>Résumé des Statistiques</h3>
                <p><strong>Nombre Total de Ventes (articles vendus) :</strong> <?php echo $total_sales; ?></p>
                <p><strong>Produit le Plus Vendu :</strong> <?php echo htmlspecialchars($top_product ?? 'Aucun'); ?> (<?php echo $max_quantity; ?> unités)</p>
                <h3>Résumé Financier</h3>
                <p><strong>Revenu Brut Total :</strong> <?php echo number_format($gross_revenue, 2); ?> €</p>
                <p><strong>Commission Plateforme (10 %) :</strong> <?php echo number_format($platform_fee, 2); ?> €</p>
                <p><strong>Revenu Net :</strong> <?php echo number_format($net_revenue, 2); ?> €</p>
            <?php endif; ?>
        </section>
    </main>
    <?php include '../include/footer.php'; ?>
</body>
</html>