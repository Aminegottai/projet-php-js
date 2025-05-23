<style>
    /* S'assurer que le contenu principal prend au moins toute la hauteur de la fenêtre */
    html, body {
        height: 100%;
        margin: 0;
    }

   footer .logo {
        margin-bottom: 10px; /* Espacement entre le logo et le texte */
    }
    footer .logo img {
        height: 40px; /* Ajuste la hauteur du logo selon tes besoins */
        width: auto;
        vertical-align: middle;
    }
    footer {
        flex-shrink: 0; /* Le footer ne se réduit pas */
        background-color: #34495e;
        color: white;
        text-align: center;
        padding: 15px;
        width: 100%;
    }
    footer p {
        margin: 0 0 10px 0; /* Ajouter une marge en bas du texte de copyright */
    }
    footer ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    footer ul li {
        display: inline;
        margin: 0 15px; /* Espacement entre les liens */
    }
    footer ul li a {
        color: #ecf0f1;
        text-decoration: none;
    }
    footer ul li a:hover {
        color: #bdc3c7;
    }
</style>
<title>Freelancia</title>

<footer>
    <div class="logo">
        <img src="../uploads/logo.png ">
    </div>
    <div class="content">
        <p>© 2025 SkillBridge. Tous droits réservés.</p>
        <ul>
            <li><a href="../../contact.php">Contact</a></li>
            <li><a href="../../about.php">About</a></li>
        </ul>
    </div>
</footer>