<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("Script add-product.php démarré");
echo "Script démarré !"; // Vérifie si le script s'exécute
include '../include/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'freelancer') {
    header('Location: ../../index.php');
    exit;
}
$sql = "SELECT * FROM categories";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "Formulaire soumis !"; // Vérifie la soumission
    error_log("Formulaire soumis");
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 0;
    error_log("Données reçues : name=$name, description=$description, price=$price, category_id=$category_id, user_id=$user_id");
    if (empty($name) || empty($description) || empty($price) || empty($category_id) || $user_id == 0) {
        $error = "Tous les champs sont requis et vous devez être connecté.";
        error_log("Erreur : champs manquants ou user_id non défini");
    } else {
        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "../uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
                error_log("Dossier uploads créé");
            }
            $image = basename($_FILES['image']['name']);
            $target_file = $target_dir . $image;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            error_log("Image : $image, Type : $imageFileType, Taille : " . $_FILES['image']['size']);
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                $error = "Seuls les fichiers JPG, JPEG, PNG sont autorisés.";
                error_log("Erreur : format d'image non autorisé");
            } elseif ($_FILES['image']['size'] > 5000000) {
                $error = "L'image est trop grande (max 5MB).";
                error_log("Erreur : image trop grande");
            } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $error = "Erreur lors de l'upload de l'image.";
                error_log("Erreur : échec de l'upload de l'image");
            } else {
                error_log("Image uploadée avec succès dans : $target_file");
            }
        } else {
            $error = "Veuillez uploader une image.";
            error_log("Erreur : aucune image uploadée");
        }
        if (!isset($error)) {
            try {
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
                $success = "Produit ajouté avec succès !";
                error_log("Produit ajouté avec succès");
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout du produit : " . $e->getMessage();
                error_log("Erreur SQL : " . $e->getMessage());
            }
        }
    }
    // Débogage forcé
    echo "<pre>";
    var_dump("Error:", $error);
    var_dump("Success:", $success);
    echo "</pre>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit</title>
    <link rel="stylesheet" href="../css/add-product.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #e8f0f2; margin: 0; padding: 0; }
        nav { background-color: #34495e; padding: 10px; color: white; }
        nav .logo { font-size: 1.5em; }
        nav ul { list-style: none; padding: 0; }
        nav ul li { display: inline; margin-right: 15px; }
        nav ul li a { color: #ecf0f1; text-decoration: none; }
        nav ul li a:hover { color: #bdc3c7; }
        .form-container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .form-container h2 { text-align: center; color: #34495e; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 100px; resize: vertical; }
        .error { color: red; text-align: center; }
        .success { color: green; text-align: center; }
        button { width: 100%; padding: 10px; background-color: #34495e; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #2c3e50; }
        #image_preview { display: none; max-width: 150px; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px; }
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
            <li><a href="../../logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    <section class="form-container">
        <h2>Ajouter un produit</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nom du produit</label>
                <input type="text" name="name" id="name" placeholder="Nom du produit" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Description" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Prix (€)</label>
                <input type="number" name="price" id="price" placeholder="Prix (€)" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="category_id">Catégorie</label>
                <select name="category_id" id="category_id" required>
                    <option value="">Choisir une catégorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="product_image">Image du produit</label>
                <input type="file" name="image" id="product_image" accept="image/*" required>
                <img id="image_preview" src="#" alt="Aperçu de l'image" style="display: none; max-width: 150px; margin-top: 10px;">
            </div>
            <button type="submit">Ajouter</button>
        </form>
    </section>
    <?php include '../include/footer.php'; ?>
    <script>
        document.getElementById('product_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('image_preview');
            const submitButton = document.querySelector('button[type="submit"]');
            const errorMessage = document.createElement('p');
            errorMessage.className = 'error';
            errorMessage.style.display = 'none';
            document.querySelector('.form-container').prepend(errorMessage);

            errorMessage.style.display = 'none';
            preview.style.display = 'none';
            submitButton.disabled = false;

            if (file) {
                const validFormats = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validFormats.includes(file.type)) {
                    errorMessage.textContent = 'Seuls les fichiers JPG, JPEG, PNG sont autorisés.';
                    errorMessage.style.display = 'block';
                    return;
                }
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    errorMessage.textContent = 'L\'image est trop grande (max 5 Mo).';
                    errorMessage.style.display = 'block';
                    return;
                }
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });
    </script>
</body>
</html>