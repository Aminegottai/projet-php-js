<?php
include '../include/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die("Utilisateur non trouvé.");
}
$sql = "SELECT o.*, oi.product_id, oi.quantity, oi.price, p.name AS product_name 
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        JOIN products p ON oi.product_id = p.id 
        WHERE o.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $bio = $_POST['bio'] ?? '';
    $profile_image = $user['profile_image'] ?? '';
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../uploads/";
        $profile_image = basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $profile_image;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $error = "Seuls les fichiers JPG, JPEG, PNG sont autorisés.";
        } elseif ($_FILES['profile_image']['size'] > 5000000) {
            $error = "L'image est trop grande (max 5MB).";
        } elseif (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $error = "Erreur lors de l'upload de l'image.";
        }
    }
    if (!isset($error)) {
        $sql = "UPDATE users SET username = :username, email = :email, bio = :bio, profile_image = :profile_image WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'bio' => $bio,
                'profile_image' => $profile_image,
                'id' => $user_id
            ]);
            $_SESSION['username'] = $username;
            $success = "Profil mis à jour !";
        } catch (PDOException $e) {
            $error = "Erreur : Email déjà utilisé.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <nav>
        <div class="logo">Freelance Platform</div>
        <ul>
            <li><a href="<?php echo $_SESSION['role'] == 'client' ? 'client-home.php' : 'freelancer-home.php'; ?>">Accueil</a></li>
            <li><a href="cart.php">Panier</a></li>
            <li><a href="profile.php">Profil</a></li>
            <li><a href="../../logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    <section class="profile">
        <h2>Votre Profil</h2>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="profile-info">
            <?php if (isset($user['profile_image']) && $user['profile_image']): ?>
                <img src="../uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Photo de profil" class="profile-image">
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                <label>Bio</label>
                <textarea name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                <label>Photo de profil</label>
                <input type="file" name="profile_image" accept="image/*">
                <button type="submit" class="btn">Mettre à jour</button>
            </form>
        </div>
        <?php if ($_SESSION['role'] == 'client'): ?>
            <h3>Vos Commandes</h3>
            <?php if (empty($orders)): ?>
                <p>Aucune commande.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Commande ID</th>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td><?php echo number_format($order['price'], 2); ?> €</td>
                                <td><?php echo number_format($order['total'], 2); ?> €</td>
                                <td><?php echo $order['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php include '../include/footer.php'; ?>
    <script>
    document.getElementById('profile_image').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('image_preview');
        const submitButton = document.querySelector('button[type="submit"]');
        const errorMessage = document.createElement('p');
        errorMessage.className = 'error';
        errorMessage.style.display = 'none';
        document.querySelector('.profile-info').prepend(errorMessage);

        // Réinitialiser les messages d'erreur et l'aperçu
        errorMessage.style.display = 'none';
        preview.style.display = 'none';
        submitButton.disabled = false;

        if (file) {
            // Vérifier le format
            const validFormats = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validFormats.includes(file.type)) {
                errorMessage.textContent = 'Seuls les fichiers JPG, JPEG, PNG sont autorisés.';
                errorMessage.style.display = 'block';
                submitButton.disabled = true;
                return;
            }

            // Vérifier la taille (5 Mo = 5 * 1024 * 1024 octets)
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                errorMessage.textContent = 'L\'image est trop grande (max 5 Mo).';
                errorMessage.style.display = 'block';
                submitButton.disabled = true;
                return;
            }

            // Afficher l'aperçu
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
</script>
</body>
</html>