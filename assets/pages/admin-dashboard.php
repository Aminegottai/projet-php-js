<?php
session_start();
include '../include/config.php';

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../../index.php?return_to=admin-dashboard');
    exit;
}

// Gérer la suppression d'un utilisateur
if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['user_id'])) {
    $user_id_to_delete = (int)$_GET['user_id'];
    try {
        $sql = "DELETE FROM users WHERE id = :user_id AND is_admin = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id_to_delete]);
        header('Location: admin-dashboard.php?message=Utilisateur supprimé');
        exit;
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
    }
}

// Gérer la suppression d'un produit
if (isset($_GET['action']) && $_GET['action'] == 'delete_product' && isset($_GET['product_id'])) {
    $product_id_to_delete = (int)$_GET['product_id'];
    try {
        // Commencer une transaction pour garantir l'intégrité
        $pdo->beginTransaction();

        // Supprimer les entrées dépendantes dans cart
        $sql_cart = "DELETE FROM cart WHERE product_id = :product_id";
        $stmt_cart = $pdo->prepare($sql_cart);
        $stmt_cart->execute(['product_id' => $product_id_to_delete]);

        // Supprimer le produit
        $sql_product = "DELETE FROM products WHERE id = :product_id";
        $stmt_product = $pdo->prepare($sql_product);
        $stmt_product->execute(['product_id' => $product_id_to_delete]);

        // Valider la transaction
        $pdo->commit();

        header('Location: admin-dashboard.php?message=Produit supprimé');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Erreur lors de la suppression du produit : " . $e->getMessage();
    }
}

// Récupérer tous les utilisateurs (sauf les admins)
try {
    $sql = "SELECT * FROM users WHERE is_admin = 0";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nombre total d'utilisateurs
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();

    // Nombre d'utilisateurs par rôle
    $sql = "SELECT role, COUNT(*) as count FROM users WHERE is_admin = 0 GROUP BY role";
    $stmt = $pdo->query($sql);
    $users_by_role = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
}

// Récupérer tous les produits et statistiques
try {
    $sql = "SELECT p.*, u.username AS freelancer_name 
            FROM products p 
            JOIN users u ON p.user_id = u.id";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nombre total de produits
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // Nombre de produits par freelancer
    $sql = "SELECT u.username, COUNT(p.id) as product_count 
            FROM users u 
            LEFT JOIN products p ON u.id = p.user_id 
            WHERE u.is_admin = 0 
            GROUP BY u.id, u.username";
    $stmt = $pdo->query($sql);
    $products_by_freelancer = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des produits : " . $e->getMessage();
}

// Calculer les commissions à partir de cart et products
try {
    // Somme totale des ventes (total_amount)
    $sql = "
        SELECT SUM(p.price * c.quantity) AS total_sales_amount
        FROM cart c
        JOIN products p ON c.product_id = p.id";
    $total_sales_amount = $pdo->query($sql)->fetchColumn();
    if ($total_sales_amount === null) {
        $total_sales_amount = 0;
    }

    // Calculer la commission totale (10 %)
    $total_commission = $total_sales_amount * 0.10;

    // Récupérer les commissions par freelancer
    $sql = "
        SELECT u.username, SUM(p.price * c.quantity) * 0.10 as total_commission
        FROM cart c
        JOIN products p ON c.product_id = p.id
        JOIN users u ON p.user_id = u.id
        GROUP BY p.user_id, u.username";
    $stmt = $pdo->query($sql);
    $commissions_by_freelancer = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des commissions : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        nav { background-color: #34495e; padding: 10px; color: white; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { font-size: 1.5em; }
        nav ul { list-style: none; padding: 0; margin: 0; }
        nav ul li { display: inline; margin-left: 15px; }
        nav ul li a { color: #ecf0f1; text-decoration: none; }
        nav ul li a:hover { color: #bdc3c7; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .table th { background-color: #34495e; color: white; }
        .btn { padding: 5px 10px; background-color: #e74c3c; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background-color: #c0392b; }
        .message { color: green; text-align: center; margin-bottom: 15px; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
        .stats { margin-bottom: 20px; }
        .stats p { margin: 5px 0; }
    </style>
</head>
<body>
    <nav>
        <div class="logo">SkillBridge</div>
        <ul>
            <li><a href="../../index.php">Accueil</a></li>
            <li><a href="admin-dashboard.php">Tableau de Bord</a></li>
            <li><a href="profile.php">Profil</a></li>
            <li><a href="../../logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    <main>
        <section class="container">
            <h2>Tableau de Bord Admin</h2>
            <?php if (isset($_GET['message'])): ?>
                <p class="message"><?php echo htmlspecialchars($_GET['message']); ?></p>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <h3>Statistiques Globales</h3>
            <div class="stats">
                <p><strong>Nombre total d'utilisateurs :</strong> <?php echo htmlspecialchars($total_users); ?></p>
                <p><strong>Répartition par rôle :</strong></p>
                <ul>
                    <?php foreach ($users_by_role as $role): ?>
                        <li><?php echo htmlspecialchars($role['role']); ?> : <?php echo htmlspecialchars($role['count']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Nombre total de produits :</strong> <?php echo htmlspecialchars($total_products); ?></p>
                <p><strong>Produits par freelancer :</strong></p>
                <ul>
                    <?php foreach ($products_by_freelancer as $freelancer): ?>
                        <li><?php echo htmlspecialchars($freelancer['username']); ?> : <?php echo htmlspecialchars($freelancer['product_count']); ?> produit(s)</li>
                    <?php endforeach; ?>
                </ul>
                <h4>Commissions de la Plateforme</h4>
                <p><strong>Somme totale des ventes :</strong> <?php echo number_format($total_sales_amount, 2); ?> €</p>
                <p><strong>Commission totale (10 %) :</strong> <?php echo number_format($total_commission, 2); ?> €</p>
                <p><strong>Commissions par freelancer :</strong></p>
                <ul>
                    <?php if (empty($commissions_by_freelancer)): ?>
                        <li>Aucune commission enregistrée.</li>
                    <?php else: ?>
                        <?php foreach ($commissions_by_freelancer as $freelancer): ?>
                            <li><?php echo htmlspecialchars($freelancer['username']); ?> : <?php echo number_format($freelancer['total_commission'], 2); ?> €</li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <h3>Gestion des Utilisateurs</h3>
            <?php if (empty($users)): ?>
                <p>Aucun utilisateur trouvé.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <a href="admin-dashboard.php?action=delete_user&user_id=<?php echo $user['id']; ?>" class="btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3>Gestion des Produits</h3>
            <?php if (empty($products)): ?>
                <p>Aucun produit trouvé.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prix (€)</th>
                            <th>Freelancer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($product['freelancer_name']); ?></td>
                                <td>
                                    <a href="admin-dashboard.php?action=delete_product&product_id=<?php echo $product['id']; ?>" class="btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
    <?php include '../include/footer.php'; ?>
</body>
</html>