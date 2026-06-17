<?php
session_start();

// Vérifier que c'est un admin ou propriétaire
if (empty($_SESSION['user_id'])) {
    die("Vous devez être connecté pour créer des annonces de démonstration.");
}

$host   = '127.0.0.1';
$dbname = 'smarthome';
$user   = 'root';
$pass   = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données.");
}

// Récupérer une ville (Paris par exemple)
$ville = $pdo->query("SELECT id FROM villes WHERE nom='Paris' LIMIT 1")->fetch();
if (!$ville) {
    $ville = $pdo->query("SELECT id FROM villes LIMIT 1")->fetch();
}
$ville_id = $ville['id'] ?? 1;

// Récupérer l'ID du propriétaire (l'utilisateur connecté)
$proprietaire_id = $_SESSION['user_id'];

// Annonces de démonstration
$annonces = [
    [
        'titre' => 'Salon Moderne Spacieux',
        'description' => 'Magnifique salon avec parquet authentique. Lumineux et spacieux, idéal pour se détendre. Accès direct à la terrasse.',
        'type_logement' => 'Appartement',
        'surface_m2' => 45,
        'nb_pieces' => 1,
        'loyer' => 600,
        'depot_garantie' => 1200,
        'quartier' => 'Centre-ville',
        'adresse' => '12 Rue de la Paix',
        'image_locale' => 'image/salon-moderne.jpg',
        'meuble' => 1,
        'charges_incluses' => 1,
        'ascenseur' => 1,
        'digicode' => 1,
        'gardien' => 0,
        'cave' => 0,
        'parking' => 1,
        'balcon' => 1,
        'fibre' => 1,
        'lave_linge' => 0
    ],
    [
        'titre' => 'Chambre Meublée Confortable',
        'description' => 'Chambre spacieuse, meublée et confortable. Idéale pour étudiant ou jeune professionnel. À proximité des transports.',
        'type_logement' => 'Chambre',
        'surface_m2' => 25,
        'nb_pieces' => 1,
        'loyer' => 450,
        'depot_garantie' => 900,
        'quartier' => 'Marais',
        'adresse' => '45 Boulevard Saint-Germain',
        'image_locale' => 'image/chambre-meublee.jpg',
        'meuble' => 1,
        'charges_incluses' => 1,
        'ascenseur' => 0,
        'digicode' => 1,
        'gardien' => 0,
        'cave' => 0,
        'parking' => 0,
        'balcon' => 0,
        'fibre' => 1,
        'lave_linge' => 1
    ],
    [
        'titre' => 'Appartement 2 Pièces Open Space',
        'description' => 'Magnifique appartement avec cuisine ouverte donnant sur le salon. Design moderne, tout équipé. Vue surplombante très agréable.',
        'type_logement' => 'Appartement',
        'surface_m2' => 65,
        'nb_pieces' => 2,
        'loyer' => 850,
        'depot_garantie' => 1700,
        'quartier' => 'Republique',
        'adresse' => '78 Avenue de la République',
        'image_locale' => 'image/appartement-2pieces.jpg',
        'meuble' => 1,
        'charges_incluses' => 0,
        'ascenseur' => 1,
        'digicode' => 1,
        'gardien' => 1,
        'cave' => 1,
        'parking' => 1,
        'balcon' => 1,
        'fibre' => 1,
        'lave_linge' => 1
    ],
    [
        'titre' => 'Studio avec Cuisine Équipée',
        'description' => 'Studio parfaitement aménagé avec cuisine moderne. Très lumineux avec accès à la terrasse. Idéal pour 1 personne.',
        'type_logement' => 'Studio',
        'surface_m2' => 35,
        'nb_pieces' => 0,
        'loyer' => 550,
        'depot_garantie' => 1100,
        'quartier' => 'Bastille',
        'adresse' => '23 Rue de Lappe',
        'image_locale' => 'image/studio-cuisine.jpg',
        'meuble' => 1,
        'charges_incluses' => 1,
        'ascenseur' => 1,
        'digicode' => 1,
        'gardien' => 0,
        'cave' => 0,
        'parking' => 0,
        'balcon' => 0,
        'fibre' => 1,
        'lave_linge' => 0
    ]
];

// Insérer les annonces
$count = 0;
foreach ($annonces as $ann) {
    try {
        // Vérifier si l'image existe
        if (!file_exists(__DIR__ . '/' . $ann['image_locale'])) {
            echo "⚠️ Attention : L'image {$ann['image_locale']} n'existe pas<br>";
            continue;
        }

        $stmt = $pdo->prepare("
            INSERT INTO annonces 
            (proprietaire_id, ville_id, titre, description, type_logement, surface_m2, nb_pieces,
             loyer, depot_garantie, quartier, adresse, meuble, charges_incluses,
             ascenseur, digicode, gardien, cave, parking, balcon, fibre, lave_linge,
             image_locale, statut)
            VALUES 
            (:proprietaire_id, :ville_id, :titre, :description, :type_logement, :surface_m2, :nb_pieces,
             :loyer, :depot_garantie, :quartier, :adresse, :meuble, :charges_incluses,
             :ascenseur, :digicode, :gardien, :cave, :parking, :balcon, :fibre, :lave_linge,
             :image_locale, 'disponible')
        ");

        $stmt->execute([
            ':proprietaire_id'  => $proprietaire_id,
            ':ville_id'         => $ville_id,
            ':titre'            => $ann['titre'],
            ':description'      => $ann['description'],
            ':type_logement'    => $ann['type_logement'],
            ':surface_m2'       => $ann['surface_m2'],
            ':nb_pieces'        => $ann['nb_pieces'],
            ':loyer'            => $ann['loyer'],
            ':depot_garantie'   => $ann['depot_garantie'],
            ':quartier'         => $ann['quartier'],
            ':adresse'          => $ann['adresse'],
            ':meuble'           => $ann['meuble'],
            ':charges_incluses' => $ann['charges_incluses'],
            ':ascenseur'        => $ann['ascenseur'],
            ':digicode'         => $ann['digicode'],
            ':gardien'          => $ann['gardien'],
            ':cave'             => $ann['cave'],
            ':parking'          => $ann['parking'],
            ':balcon'           => $ann['balcon'],
            ':fibre'            => $ann['fibre'],
            ':lave_linge'       => $ann['lave_linge'],
            ':image_locale'     => $ann['image_locale'],
        ]);

        echo "✅ Annonce ajoutée : <strong>{$ann['titre']}</strong><br>";
        $count++;
    } catch (Exception $e) {
        echo "❌ Erreur pour {$ann['titre']} : " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";
echo "<p style='font-size: 1.1rem; color: #00b894;'><strong>$count annonce(s) de démonstration créée(s) avec succès !</strong></p>";
echo "<a href='index.php' style='color: #00b894; font-weight: 600; text-decoration: none;'>← Retour à l'accueil</a>";
?>
