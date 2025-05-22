<?php
include '../include/config.php';

// Récupérer l'ID du produit
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../index.php');
    exit;
}
$product_id = (int)$_GET['id'];

// Récupérer les détails du produit
$sql = "SELECT p.*, c.name AS category_name, u.username AS freelancer_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: ../../index.php');
    exit;
}

// Vérifier si l'utilisateur est connecté pour déterminer son rôle
$user_id = null;
$user_role = null;
if (isset($_COOKIE['PHPSESSID'])) {
    session_start();
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['role'];
    }
}

// Vérifier si l'utilisateur est un freelancer pour autoriser la modification/suppression
$is_freelancer = $user_id && $user_role === 'freelancer' && $user_id === $product['user_id'];

// Vérifier si l'utilisateur est un client pour autoriser l'ajout au panier
$is_client = $user_id && $user_role === 'client';

// Gérer l'ajout au panier pour les clients
if ($is_client && isset($_GET['action']) && $_GET['action'] == 'add_to_cart') {
    try {
        // Vérifier si le produit est déjà dans le panier
        $sql = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart_item) {
            // Si le produit existe déjà, incrémenter la quantité
            $sql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
        } else {
            // Sinon, ajouter le produit au panier
            $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
        }

        // Rediriger avec un message de confirmation
        header('Location: product-details.php?id=' . $product_id . '&message=Produit ajouté au panier');
        exit;
    } catch (PDOException $e) {
        // Gérer l'erreur (par exemple, table manquante)
        $error_message = "Erreur lors de l'ajout au panier : " . $e->getMessage();
    }
}

// Gérer la suppression (uniquement pour les freelancers)
if ($is_freelancer && isset($_GET['action']) && $_GET['action'] == 'delete') {
    $sql = "DELETE FROM products WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $product_id, 'user_id' => $user_id]);
    header('Location: freelancer-home.php');
    exit;
}

// Gérer la mise à jour (uniquement pour les freelancers)
if ($is_freelancer && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $image = $product['image'];

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $image = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png']) && $_FILES['image']['size'] <= 5000000) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                if ($product['image'] && file_exists($target_dir . $product['image'])) {
                    unlink($target_dir . $product['image']);
                }
            }
        }
    }

    $sql = "UPDATE products 
            SET name = :name, description = :description, price = :price, image = :image, category_id = :category_id 
            WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'image' => $image,
        'category_id' => $category_id,
        'id' => $product_id,
        'user_id' => $user_id
    ]);
    header('Location: product-details.php?id=' . $product_id);
    exit;
}

// Récupérer les catégories pour le formulaire de modification (uniquement pour les freelancers)
$categories = [];
if ($is_freelancer) {
    $sql = "SELECT * FROM categories";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du produit</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        nav { background-color: #34495e; padding: 10px; color: white; }
        nav .logo { font-size: 1.5em; }
        nav ul { list-style: none; padding: 0; }
        nav ul li { display: inline; margin-right: 15px; }
        nav ul li a { color: #ecf0f1; text-decoration: none; }
        nav ul li a:hover { color: #bdc3c7; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .product-image img { max-width: 200px; }
        .btn { padding: 10px 20px; background-color: #34495e; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background-color: #2c3e50; }
        .btn-danger { background-color: #e74c3c; }
        .btn-danger:hover { background-color: #c0392b; }
        .btn-cart { background-color: #2ecc71; }
        .btn-cart:hover { background-color: #27ae60; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 100px; resize: vertical; }
        .message { color: green; text-align: center; margin-bottom: 15px; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="../../index.php">Accueil</a></li>
            <?php if ($user_id): ?>
                <?php if ($user_role == 'freelancer'): ?>
                    <li><a href="freelancer-home.php#products">Produits</a></li>
                    <li><a href="add-product.php">Ajouter un produit</a></li>
                <?php elseif ($user_role == 'client'): ?>
                    <li><a href="client-home.php#products">Produits</a></li>
                    <li><a href="cart.php">Panier</a></li>
                <?php endif; ?>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <section class="container">
        <h2>Détails du produit</h2>
        <?php if (isset($_GET['message'])): ?>
            <p class="message"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <div class="product-image">
            <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
        <p><strong>Description :</strong> <?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Prix :</strong> <?php echo number_format($product['price'], 2); ?> €</p>
        <p><strong>Catégorie :</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
        <p><strong>Freelancer :</strong> <?php echo htmlspecialchars($product['freelancer_name']); ?></p>

        <?php if ($is_client): ?>
            <a href="product-details.php?id=<?php echo $product_id; ?>&action=add_to_cart" class="btn btn-cart">Ajouter au panier</a>
        <?php endif; ?>

        <?php if ($is_freelancer): ?>
            <a href="product-details.php?id=<?php echo $product_id; ?>&action=delete" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">Supprimer</a>
            <a href="product-details.php?id=<?php echo $product_id; ?>#edit" class="btn">Modifier</a>

            <div id="edit" style="display: none;">
                <h3>Modifier le produit</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nom du produit</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Prix (€)</label>
                        <input type="number" name="price" id="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Catégorie</label>
                        <select name="category_id" id="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image">Image du produit (laisser vide pour conserver l'image actuelle)</label>
                        <input type="file" name="image" id="image" accept="image/*">
                    </div>
                    <button type="submit" name="update" class="btn">Mettre à jour</button>
                </form>
            </div>

            <script>
                document.querySelectorAll('.btn:not(.btn-danger):not(.btn-cart)').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('edit').style.display = 'block';
                    });
                });
            </script>
        <?php endif; ?>
    </section>
    <?php include '../include/footer.php'; ?>
</body>
</html>