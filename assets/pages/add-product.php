<?php
session_start();
include '../include/config.php';

// Vérifier si l'utilisateur est connecté et a le rôle 'freelancer'
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] != 'freelancer')) {
    header('Location: ../../index.php?return_to=add-product');
    exit;
}

// Récupérer les catégories pour le formulaire
$sql = "SELECT * FROM categories";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gérer l'ajout d'un produit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Gestion de l'image
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $image = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Vérifier le type et la taille de l'image
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png']) && $_FILES['image']['size'] <= 5000000) {
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $error = "Erreur lors du téléchargement de l'image.";
            }
        } else {
            $error = "Seuls les fichiers JPG, JPEG et PNG de moins de 5 Mo sont autorisés.";
        }
    } else {
        $error = "Veuillez sélectionner une image.";
    }

    // Insérer le produit dans la base de données si aucune erreur
    if (!isset($error)) {
        $sql = "INSERT INTO products (name, description, price, image, user_id, category_id) 
                VALUES (:name, :description, :price, :image, :user_id, :category_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image' => $image,
            'user_id' => $user_id,
            'category_id' => $category_id
        ]);

        // Rediriger vers freelancer-home.php après ajout
        header('Location: freelancer-home.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        nav {
    background-color: #34495e;
    padding: 10px 20px;
    color: white;
    display: flex;
    justify-content: space-between; /* logo à gauche, menu à droite */
    align-items: center;
}

nav .logo {
    font-size: 1.5em;
    font-weight: bold;
}

nav ul {
    list-style: none;
    display: flex;
    gap: 20px;
    margin: 0;
    padding: 0;
}

nav ul li a {
    color: #ecf0f1; /* Blanc cassé, très lisible */
    text-decoration: none;
    font-weight: bold;
}

nav ul li a:hover {
    color: #bdc3c7; /* Gris clair au survol */
}

        .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 100px; resize: vertical; }
        button { padding: 10px 20px; background-color: #34495e; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #2c3e50; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="../../index.php">Accueil</a></li>
            <li><a href="freelancer-home.php#products">Produits</a></li>
            <li><a href="add-product.php">Ajouter un produit</a></li>
            <li><a href="profile.php">Profil</a></li>
            <li><a href="../../logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    <section class="container">
        <h2>Ajouter un produit</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nom du produit</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Prix (€)</label>
                <input type="number" name="price" id="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="category_id">Catégorie</label>
                <select name="category_id" id="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Image du produit</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>
            <button type="submit">Ajouter</button>
        </form>
    </section>
    <?php include '../include/footer.php'; ?>
</body>
</html>