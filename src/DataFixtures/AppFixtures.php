<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin users
        $admins = [
            ['email' => 'abderrahmen@admin.com', 'firstName' => 'Abderrahmen', 'lastName' => 'Admin'],
            ['email' => 'aziz@admin.com', 'firstName' => 'Aziz', 'lastName' => 'Admin'],
            ['email' => 'adam@admin.com', 'firstName' => 'Adam', 'lastName' => 'Admin'],
        ];

        $adminUsers = [];
        foreach ($admins as $adminData) {
            $admin = new User();
            $admin->setEmail($adminData['email']);
            $admin->setFirstName($adminData['firstName']);
            $admin->setLastName($adminData['lastName']);
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
            $manager->persist($admin);
            $adminUsers[] = $admin;
        }

        // Create regular users
        $clients = [
            ['email' => 'adam@client.com', 'firstName' => 'Adam', 'lastName' => 'Saidane'],
            ['email' => 'aziz@client.com', 'firstName' => 'Aziz', 'lastName' => 'Maamouri'],
            ['email' => 'abderrahmen@client.com', 'firstName' => 'Abderrahmen', 'lastName' => 'Bouhali'],
            ['email' => 'marie@client.com', 'firstName' => 'Marie', 'lastName' => 'Dubois'],
            ['email' => 'pierre@client.com', 'firstName' => 'Pierre', 'lastName' => 'Martin'],
            ['email' => 'sophie@client.com', 'firstName' => 'Sophie', 'lastName' => 'Bernard'],
            ['email' => 'julien@client.com', 'firstName' => 'Julien', 'lastName' => 'Moreau'],
            ['email' => 'claire@client.com', 'firstName' => 'Claire', 'lastName' => 'Petit'],
        ];

        $clientUsers = [];
        foreach ($clients as $clientData) {
            $user = new User();
            $user->setEmail($clientData['email']);
            $user->setFirstName($clientData['firstName']);
            $user->setLastName($clientData['lastName']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'client123'));
            $manager->persist($user);
            $clientUsers[] = $user;
        }

        // Create categories
        $categories = [
            ['name' => 'Romans classiques', 'slug' => 'romans-classiques', 'description' => 'Les grands classiques de la littérature'],
            ['name' => 'Livres d\'art', 'slug' => 'livres-art', 'description' => 'Beaux livres et ouvrages d\'art'],
            ['name' => 'Histoire', 'slug' => 'histoire', 'description' => 'Livres d\'histoire et documents historiques'],
            ['name' => 'Sciences', 'slug' => 'sciences', 'description' => 'Ouvrages scientifiques et techniques'],
            ['name' => 'Philosophie', 'slug' => 'philosophie', 'description' => 'Textes philosophiques et essais'],
            ['name' => 'Poésie', 'slug' => 'poesie', 'description' => 'Recueils de poésie et œuvres poétiques'],
            ['name' => 'Théâtre', 'slug' => 'theatre', 'description' => 'Pièces de théâtre et œuvres dramatiques'],
            ['name' => 'Biographies', 'slug' => 'biographies', 'description' => 'Biographies et mémoires'],
        ];

        $categoryEntities = [];
        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['name']);
            $category->setSlug($categoryData['slug']);
            $category->setDescription($categoryData['description']);
            $manager->persist($category);
            $categoryEntities[] = $category;
        }

        // Create products (expanded list)
        $products = [
            // Romans classiques
            [
                'title' => 'Les Misérables',
                'author' => 'Victor Hugo',
                'description' => 'Édition originale de 1862 en excellent état. Un des chefs-d\'œuvre de la littérature française qui raconte l\'histoire de Jean Valjean et de sa quête de rédemption dans la France du XIXe siècle.',
                'price' => '1250.00',
                'isbn' => '978-2-07-036057-1',
                'publicationYear' => 1862,
                'publisher' => 'Pagnerre',
                'pages' => 1900,
                'condition' => 'tres_bon',
                'stock' => 1,
                'category' => 0,
                'image'=>'1.jpg'
            ],
            [
                'title' => 'Le Rouge et le Noir',
                'author' => 'Stendhal',
                'description' => 'Édition originale de 1830. Reliure d\'époque en demi-cuir. Roman psychologique majeur du XIXe siècle suivant l\'ascension sociale de Julien Sorel.',
                'price' => '980.00',
                'isbn' => '978-2-07-036789-1',
                'publicationYear' => 1830,
                'publisher' => 'Levavasseur',
                'pages' => 512,
                'condition' => 'tres_bon',
                'stock' => 2,
                'category' => 0,
                'image'=>'2.jpg'
            ],
            [
                'title' => 'Madame Bovary',
                'author' => 'Gustave Flaubert',
                'description' => 'Première édition de 1857 chez Michel Lévy. État remarquable. Chef-d\'œuvre du réalisme français.',
                'price' => '1450.00',
                'isbn' => '978-2-07-036123-3',
                'publicationYear' => 1857,
                'publisher' => 'Michel Lévy',
                'pages' => 464,
                'condition' => 'neuf',
                'stock' => 1,
                'category' => 0,
                'image'=>'3.jpg'
            ],
            [
                'title' => 'Notre-Dame de Paris',
                'author' => 'Victor Hugo',
                'description' => 'Édition de 1831, reliure d\'époque. Roman gothique emblématique se déroulant au Moyen Âge.',
                'price' => '1100.00',
                'isbn' => '978-2-07-036234-6',
                'publicationYear' => 1831,
                'publisher' => 'Gosselin',
                'pages' => 634,
                'condition' => 'bon',
                'stock' => 2,
                'category' => 0,
                'image'=>'4.jpg'
            ],
            [
                'title' => 'Le Père Goriot',
                'author' => 'Honoré de Balzac',
                'description' => 'Édition originale de 1835. Partie intégrante de La Comédie humaine, portrait saisissant de la société parisienne.',
                'price' => '850.00',
                'isbn' => '978-2-07-036345-9',
                'publicationYear' => 1835,
                'publisher' => 'Werdet',
                'pages' => 374,
                'condition' => 'tres_bon',
                'stock' => 4,
                'category' => 0,
                'image'=>'5.jpg'
            ],

            // Livres d'art
            [
                'title' => 'L\'Art de la Renaissance',
                'author' => 'André Chastel',
                'description' => 'Magnifique ouvrage illustré sur l\'art de la Renaissance italienne. Reliure cuir avec dorures.',
                'price' => '890.00',
                'isbn' => '978-2-08-012345-6',
                'publicationYear' => 1965,
                'publisher' => 'Flammarion',
                'pages' => 450,
                'condition' => 'neuf',
                'stock' => 3,
                'category' => 1,
                'image'=>'6.jpg'
            ],
            [
                'title' => 'Les Impressionnistes',
                'author' => 'John Rewald',
                'description' => 'Étude complète du mouvement impressionniste avec reproductions en couleur exceptionnelles.',
                'price' => '650.00',
                'isbn' => '978-2-08-013456-7',
                'publicationYear' => 1973,
                'publisher' => 'Albin Michel',
                'pages' => 520,
                'condition' => 'tres_bon',
                'stock' => 2,
                'category' => 1,
                'image'=>'7.jpg'
            ],

            // Histoire
            [
                'title' => 'Histoire de France',
                'author' => 'Jules Michelet',
                'description' => 'Collection complète en 19 volumes. Édition de 1876 reliée cuir. Œuvre monumentale de l\'historiographie française.',
                'price' => '2800.00',
                'isbn' => '978-2-01-234567-8',
                'publicationYear' => 1876,
                'publisher' => 'Hachette',
                'pages' => 8500,
                'condition' => 'bon',
                'stock' => 1,
                'category' => 2,
                'image'=>'8.jpg'
            ],
            [
                'title' => 'Mémoires de Guerre',
                'author' => 'Charles de Gaulle',
                'description' => 'Édition originale en 3 volumes. Témoignage historique majeur du XXe siècle.',
                'price' => '750.00',
                'isbn' => '978-2-259-000123-4',
                'publicationYear' => 1954,
                'publisher' => 'Plon',
                'pages' => 1200,
                'condition' => 'tres_bon',
                'stock' => 6,
                'category' => 2,
                'image'=>'9.jpg'
            ],

            // Sciences
            [
                'title' => 'Principia Mathematica',
                'author' => 'Isaac Newton',
                'description' => 'Réimpression fidèle de l\'édition de 1687. Document historique exceptionnel qui révolutionna la physique.',
                'price' => '3200.00',
                'isbn' => '978-0-52-107647-4',
                'publicationYear' => 1687,
                'publisher' => 'Royal Society',
                'pages' => 512,
                'condition' => 'tres_bon',
                'stock' => 2,
                'category' => 3,
                'image'=>'10.jpg'
            ],
            [
                'title' => 'L\'Origine des espèces',
                'author' => 'Charles Darwin',
                'description' => 'Première édition française de 1862. Ouvrage fondateur de la biologie moderne.',
                'price' => '1800.00',
                'isbn' => '978-2-07-011234-5',
                'publicationYear' => 1862,
                'publisher' => 'Guillaumin',
                'pages' => 480,
                'condition' => 'bon',
                'stock' => 3,
                'category' => 3,
                'image'=>'11.jpg'
            ],

            // Philosophie
            [
                'title' => 'Critique de la raison pure',
                'author' => 'Emmanuel Kant',
                'description' => 'Première édition française de 1781. Traduction de Jules Barni. Œuvre fondamentale de la philosophie moderne.',
                'price' => '1650.00',
                'isbn' => '978-2-13-045678-9',
                'publicationYear' => 1781,
                'publisher' => 'Ladrange',
                'pages' => 856,
                'condition' => 'bon',
                'stock' => 1,
                'category' => 4,
                'image'=>'12.jpg'
            ],
            [
                'title' => 'Être et Temps',
                'author' => 'Martin Heidegger',
                'description' => 'Première édition française de 1964. Œuvre majeure de la philosophie existentialiste.',
                'price' => '450.00',
                'isbn' => '978-2-07-012345-6',
                'publicationYear' => 1964,
                'publisher' => 'Gallimard',
                'pages' => 589,
                'condition' => 'tres_bon',
                'stock' => 3,
                'category' => 4,
                'image'=>'13.jpg'
            ],

            // Poésie
            [
                'title' => 'Les Fleurs du Mal',
                'author' => 'Charles Baudelaire',
                'description' => 'Édition de 1857, première édition. Exemplaire rare avec les pièces condamnées. Recueil révolutionnaire de la poésie moderne.',
                'price' => '2100.00',
                'isbn' => '978-2-07-032456-7',
                'publicationYear' => 1857,
                'publisher' => 'Poulet-Malassis',
                'pages' => 248,
                'condition' => 'bon',
                'stock' => 1,
                'category' => 5,
                'image'=>'14.jpg'
            ],
            [
                'title' => 'Alcools',
                'author' => 'Guillaume Apollinaire',
                'description' => 'Édition originale de 1913. Recueil majeur de la poésie moderne française.',
                'price' => '680.00',
                'isbn' => '978-2-07-033567-8',
                'publicationYear' => 1913,
                'publisher' => 'Mercure de France',
                'pages' => 156,
                'condition' => 'tres_bon',
                'stock' => 2,
                'category' => 5,
                'image'=>'15.jpg'
            ],

            // Théâtre
            [
                'title' => 'Dom Juan',
                'author' => 'Molière',
                'description' => 'Édition de 1682, reliure d\'époque. Comédie emblématique du théâtre français classique.',
                'price' => '1200.00',
                'isbn' => '978-2-07-034678-9',
                'publicationYear' => 1682,
                'publisher' => 'Thierry',
                'pages' => 124,
                'condition' => 'bon',
                'stock' => 1,
                'category' => 6,
                'image'=>'16.jpg'
            ],

            // Biographies
            [
                'title' => 'Mémoires d\'outre-tombe',
                'author' => 'François-René de Chateaubriand',
                'description' => 'Édition complète en 6 volumes, 1849-1850. Autobiographie monumentale du romantisme français.',
                'price' => '1500.00',
                'isbn' => '978-2-07-035789-0',
                'publicationYear' => 1849,
                'publisher' => 'Penaud',
                'pages' => 2400,
                'condition' => 'tres_bon',
                'stock' => 4,
                'category' => 7,
                'image'=>'17.jpg'
            ],
        ];

        $productEntities = [];
        foreach ($products as $productData) {
            $product = new Product();
            $product->setTitle($productData['title']);
            $product->setAuthor($productData['author']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setIsbn($productData['isbn']);
            $product->setPublicationYear($productData['publicationYear']);
            $product->setPublisher($productData['publisher']);
            $product->setPages($productData['pages']);
            $product->setBookCondition($productData['condition']);
            $product->setStock($productData['stock']);
            $product->setCategory($categoryEntities[$productData['category']]);
            $product->setImage($productData['image']);
            $product->setIsActive(true);
            $manager->persist($product);
            $productEntities[] = $product;
        }

        // Flush to get IDs
        $manager->flush();

        // Create reviews for each product
        $reviewsData = [
            // Reviews pour Les Misérables
            [
                'product' => 0,
                'reviews' => [
                    ['user' => 0, 'rating' => 5, 'comment' => 'Chef-d\'œuvre absolu ! L\'édition est magnifique et l\'état de conservation exceptionnel. Un incontournable de la littérature française.'],
                    ['user' => 1, 'rating' => 5, 'comment' => 'Livre fascinant, Hugo nous transporte dans le Paris du XIXe siècle. L\'édition originale est un véritable trésor.'],
                    ['user' => 2, 'rating' => 4, 'comment' => 'Très belle édition, quelques traces d\'usure mais normal pour l\'âge. Le contenu reste intemporel.'],
                ]
            ],
            // Reviews pour Le Rouge et le Noir
            [
                'product' => 1,
                'reviews' => [
                    ['user' => 3, 'rating' => 5, 'comment' => 'Stendhal à son apogée ! L\'analyse psychologique de Julien Sorel est remarquable. Édition de qualité.'],
                    ['user' => 4, 'rating' => 4, 'comment' => 'Roman captivant, la reliure d\'époque ajoute au charme de cette œuvre majeure.'],
                ]
            ],
            // Reviews pour Madame Bovary
            [
                'product' => 2,
                'reviews' => [
                    ['user' => 5, 'rating' => 5, 'comment' => 'Flaubert dans toute sa splendeur ! L\'état neuf de cette première édition est remarquable.'],
                    ['user' => 6, 'rating' => 5, 'comment' => 'Un classique indémodable. La précision du style de Flaubert est saisissante.'],
                    ['user' => 7, 'rating' => 4, 'comment' => 'Excellent livre, livraison rapide et emballage soigné.'],
                ]
            ],
            // Reviews pour Notre-Dame de Paris
            [
                'product' => 3,
                'reviews' => [
                    ['user' => 0, 'rating' => 4, 'comment' => 'Hugo nous fait revivre le Paris médiéval avec un talent extraordinaire. Belle édition ancienne.'],
                    ['user' => 1, 'rating' => 5, 'comment' => 'Roman gothique par excellence ! L\'atmosphère du Moyen Âge est parfaitement rendue.'],
                ]
            ],
            // Reviews pour Le Père Goriot
            [
                'product' => 4,
                'reviews' => [
                    ['user' => 2, 'rating' => 4, 'comment' => 'Balzac dépeint magistralement la société parisienne. Édition en bon état.'],
                    ['user' => 3, 'rating' => 5, 'comment' => 'Portrait saisissant de l\'ambition et de la corruption. Un Balzac incontournable.'],
                ]
            ],
            // Reviews pour L'Art de la Renaissance
            [
                'product' => 5,
                'reviews' => [
                    ['user' => 4, 'rating' => 5, 'comment' => 'Ouvrage magnifique ! Les illustrations sont d\'une qualité exceptionnelle. Parfait pour les amateurs d\'art.'],
                    ['user' => 5, 'rating' => 5, 'comment' => 'André Chastel nous offre une analyse brillante de la Renaissance. La reliure cuir est somptueuse.'],
                ]
            ],
            // Reviews pour Les Impressionnistes
            [
                'product' => 6,
                'reviews' => [
                    ['user' => 6, 'rating' => 4, 'comment' => 'Très bel ouvrage sur l\'impressionnisme. Les reproductions en couleur sont fidèles.'],
                    ['user' => 7, 'rating' => 5, 'comment' => 'Référence incontournable pour comprendre ce mouvement artistique majeur.'],
                ]
            ],
            // Reviews pour Histoire de France
            [
                'product' => 7,
                'reviews' => [
                    ['user' => 0, 'rating' => 5, 'comment' => 'Collection exceptionnelle ! Michelet reste le grand historien romantique de la France.'],
                    ['user' => 1, 'rating' => 4, 'comment' => 'Œuvre monumentale, quelques volumes montrent leur âge mais l\'ensemble reste remarquable.'],
                ]
            ],
            // Reviews pour Mémoires de Guerre
            [
                'product' => 8,
                'reviews' => [
                    ['user' => 2, 'rating' => 5, 'comment' => 'Témoignage historique majeur ! De Gaulle nous livre sa vision de la guerre et de la France.'],
                    ['user' => 3, 'rating' => 4, 'comment' => 'Document historique passionnant, style gaullien reconnaissable.'],
                ]
            ],
            // Reviews pour Principia Mathematica
            [
                'product' => 9,
                'reviews' => [
                    ['user' => 4, 'rating' => 5, 'comment' => 'Document historique exceptionnel ! Newton a révolutionné notre compréhension de l\'univers.'],
                    ['user' => 5, 'rating' => 5, 'comment' => 'Réimpression fidèle d\'une œuvre fondatrice de la physique moderne. Indispensable.'],
                ]
            ],
            // Reviews pour L'Origine des espèces
            [
                'product' => 10,
                'reviews' => [
                    ['user' => 6, 'rating' => 5, 'comment' => 'Darwin a changé notre vision du monde. Cette première édition française est un trésor.'],
                    ['user' => 7, 'rating' => 4, 'comment' => 'Ouvrage fondamental de la biologie, malgré quelques traces d\'âge.'],
                ]
            ],
            // Reviews pour Critique de la raison pure
            [
                'product' => 11,
                'reviews' => [
                    ['user' => 0, 'rating' => 4, 'comment' => 'Kant dans toute sa complexité ! Œuvre difficile mais fondamentale pour la philosophie.'],
                    ['user' => 1, 'rating' => 5, 'comment' => 'Révolution philosophique majeure. La traduction de Barni reste une référence.'],
                ]
            ],
            // Reviews pour Être et Temps
            [
                'product' => 12,
                'reviews' => [
                    ['user' => 2, 'rating' => 4, 'comment' => 'Heidegger interroge l\'être avec une profondeur remarquable. Lecture exigeante mais enrichissante.'],
                    ['user' => 3, 'rating' => 3, 'comment' => 'Philosophie dense et complexe, pas accessible à tous mais importante.'],
                ]
            ],
            // Reviews pour Les Fleurs du Mal
            [
                'product' => 13,
                'reviews' => [
                    ['user' => 4, 'rating' => 5, 'comment' => 'Baudelaire révolutionne la poésie ! Cette édition avec les pièces condamnées est exceptionnelle.'],
                    ['user' => 5, 'rating' => 5, 'comment' => 'Poésie moderne par excellence. L\'exemplaire est en remarquable état de conservation.'],
                    ['user' => 6, 'rating' => 4, 'comment' => 'Recueil majeur de la littérature française, quelques pages jaunies mais normal.'],
                ]
            ],
            // Reviews pour Alcools
            [
                'product' => 14,
                'reviews' => [
                    ['user' => 7, 'rating' => 5, 'comment' => 'Apollinaire nous offre une poésie moderne et innovante. Édition originale précieuse.'],
                    ['user' => 0, 'rating' => 4, 'comment' => 'Recueil important de la poésie du XXe siècle, très bon état.'],
                ]
            ],
            // Reviews pour Dom Juan
            [
                'product' => 15,
                'reviews' => [
                    ['user' => 1, 'rating' => 5, 'comment' => 'Molière éternel ! Cette édition ancienne a un charme incomparable.'],
                    ['user' => 2, 'rating' => 4, 'comment' => 'Comédie intemporelle, la reliure d\'époque ajoute à l\'authenticité.'],
                ]
            ],
            // Reviews pour Mémoires d'outre-tombe
            [
                'product' => 16,
                'reviews' => [
                    ['user' => 3, 'rating' => 5, 'comment' => 'Chateaubriand nous livre ses mémoires avec un style incomparable. Collection complète remarquable.'],
                    ['user' => 4, 'rating' => 4, 'comment' => 'Autobiographie monumentale du romantisme français, quelques volumes un peu fatigués.'],
                ]
            ],
        ];

        foreach ($reviewsData as $productReviews) {
            $product = $productEntities[$productReviews['product']];

            foreach ($productReviews['reviews'] as $reviewData) {
                $review = new Review();
                $review->setProduct($product);
                $review->setUser($clientUsers[$reviewData['user']]);
                $review->setRating($reviewData['rating']);
                $review->setComment($reviewData['comment']);

                // Dates aléatoires dans les 6 derniers mois
                $randomDays = rand(1, 180);
                $createdAt = new \DateTimeImmutable('-' . $randomDays . ' days');
                $review->setCreatedAt($createdAt);

                $manager->persist($review);
            }
        }

        // Create orders
        $ordersData = [
            // Commande 1 - Adam Saidane
            [
                'user' => 0, // Adam Saidane
                'status' => 'delivered',
                'items' => [
                    ['product' => 0, 'quantity' => 1], // Les Misérables
                    ['product' => 4, 'quantity' => 1], // Le Père Goriot
                ],
                'shippingAddress' => [
                    'firstName' => 'Adam',
                    'lastName' => 'Saidane',
                    'address' => '123 Rue de la Paix',
                    'city' => 'Paris',
                    'postalCode' => '75001',
                    'country' => 'France',
                    'phone' => '0123456789'
                ],
                'daysAgo' => 45
            ],
            // Commande 2 - Marie Dubois
            [
                'user' => 3, // Marie Dubois
                'status' => 'delivered',
                'items' => [
                    ['product' => 5, 'quantity' => 1], // L'Art de la Renaissance
                    ['product' => 6, 'quantity' => 1], // Les Impressionnistes
                ],
                'shippingAddress' => [
                    'firstName' => 'Marie',
                    'lastName' => 'Dubois',
                    'address' => '456 Avenue des Champs',
                    'city' => 'Lyon',
                    'postalCode' => '69001',
                    'country' => 'France',
                    'phone' => '0234567890'
                ],
                'daysAgo' => 30
            ],
            // Commande 3 - Pierre Martin
            [
                'user' => 4, // Pierre Martin
                'status' => 'shipped',
                'items' => [
                    ['product' => 9, 'quantity' => 1], // Principia Mathematica
                ],
                'shippingAddress' => [
                    'firstName' => 'Pierre',
                    'lastName' => 'Martin',
                    'address' => '789 Boulevard Saint-Germain',
                    'city' => 'Marseille',
                    'postalCode' => '13001',
                    'country' => 'France',
                    'phone' => '0345678901'
                ],
                'daysAgo' => 5
            ],
            // Commande 4 - Sophie Bernard
            [
                'user' => 5, // Sophie Bernard
                'status' => 'delivered',
                'items' => [
                    ['product' => 2, 'quantity' => 1], // Madame Bovary
                    ['product' => 13, 'quantity' => 1], // Les Fleurs du Mal
                    ['product' => 14, 'quantity' => 1], // Alcools
                ],
                'shippingAddress' => [
                    'firstName' => 'Sophie',
                    'lastName' => 'Bernard',
                    'address' => '321 Rue Victor Hugo',
                    'city' => 'Toulouse',
                    'postalCode' => '31000',
                    'country' => 'France',
                    'phone' => '0456789012'
                ],
                'daysAgo' => 60
            ],
            // Commande 5 - Julien Moreau
            [
                'user' => 6, // Julien Moreau
                'status' => 'pending',
                'items' => [
                    ['product' => 8, 'quantity' => 1], // Mémoires de Guerre
                    ['product' => 10, 'quantity' => 1], // L'Origine des espèces
                ],
                'shippingAddress' => [
                    'firstName' => 'Julien',
                    'lastName' => 'Moreau',
                    'address' => '654 Place de la République',
                    'city' => 'Bordeaux',
                    'postalCode' => '33000',
                    'country' => 'France',
                    'phone' => '0567890123'
                ],
                'daysAgo' => 2
            ],
            // Commande 6 - Claire Petit
            [
                'user' => 7, // Claire Petit
                'status' => 'delivered',
                'items' => [
                    ['product' => 15, 'quantity' => 1], // Dom Juan
                    ['product' => 16, 'quantity' => 1], // Mémoires d'outre-tombe
                ],
                'shippingAddress' => [
                    'firstName' => 'Claire',
                    'lastName' => 'Petit',
                    'address' => '987 Rue de Rivoli',
                    'city' => 'Nice',
                    'postalCode' => '06000',
                    'country' => 'France',
                    'phone' => '0678901234'
                ],
                'daysAgo' => 90
            ],
            // Commande 7 - Aziz Maamouri
            [
                'user' => 1, // Aziz Maamouri
                'status' => 'delivered',
                'items' => [
                    ['product' => 1, 'quantity' => 1], // Le Rouge et le Noir
                    ['product' => 3, 'quantity' => 1], // Notre-Dame de Paris
                ],
                'shippingAddress' => [
                    'firstName' => 'Aziz',
                    'lastName' => 'Maamouri',
                    'address' => '147 Avenue Montaigne',
                    'city' => 'Strasbourg',
                    'postalCode' => '67000',
                    'country' => 'France',
                    'phone' => '0789012345'
                ],
                'daysAgo' => 75
            ],
            // Commande 8 - Abderrahmen Bouhali
            [
                'user' => 2, // Abderrahmen Bouhali
                'status' => 'cancelled',
                'items' => [
                    ['product' => 7, 'quantity' => 1], // Histoire de France
                ],
                'shippingAddress' => [
                    'firstName' => 'Abderrahmen',
                    'lastName' => 'Bouhali',
                    'address' => '258 Rue de la Liberté',
                    'city' => 'Lille',
                    'postalCode' => '59000',
                    'country' => 'France',
                    'phone' => '0890123456'
                ],
                'daysAgo' => 15
            ],
            // Commande 9 - Adam Saidane (deuxième commande)
            [
                'user' => 0, // Adam Saidane
                'status' => 'pending',
                'items' => [
                    ['product' => 11, 'quantity' => 1], // Critique de la raison pure
                    ['product' => 12, 'quantity' => 1], // Être et Temps
                ],
                'shippingAddress' => [
                    'firstName' => 'Adam',
                    'lastName' => 'Saidane',
                    'address' => '123 Rue de la Paix',
                    'city' => 'Paris',
                    'postalCode' => '75001',
                    'country' => 'France',
                    'phone' => '0123456789'
                ],
                'daysAgo' => 1
            ],
            // Commande 10 - Marie Dubois (deuxième commande)
            [
                'user' => 3, // Marie Dubois
                'status' => 'shipped',
                'items' => [
                    ['product' => 4, 'quantity' => 2], // Le Père Goriot (2 exemplaires)
                ],
                'shippingAddress' => [
                    'firstName' => 'Marie',
                    'lastName' => 'Dubois',
                    'address' => '456 Avenue des Champs',
                    'city' => 'Lyon',
                    'postalCode' => '69001',
                    'country' => 'France',
                    'phone' => '0234567890'
                ],
                'daysAgo' => 7
            ],
        ];

        foreach ($ordersData as $orderData) {
            $order = new Order();
            $order->setUser($clientUsers[$orderData['user']]);
            $order->setStatus($orderData['status']);

            // Calculer les dates
            $createdAt = new \DateTimeImmutable('-' . $orderData['daysAgo'] . ' days');
            $order->setCreatedAt($createdAt);

            // Définir les dates selon le statut


            // Adresse de livraison
            $order->setShippingAddress($orderData['shippingAddress']['address']);


            // Calculer le total
            $totalPrice = 0;
            foreach ($orderData['items'] as $itemData) {
                $product = $productEntities[$itemData['product']];
                $totalPrice += $product->getPrice() * $itemData['quantity'];
            }
            $order->setTotalAmount($totalPrice);

            $manager->persist($order);

            // Créer les items de commande
            foreach ($orderData['items'] as $itemData) {
                $product = $productEntities[$itemData['product']];

                $orderItem = new OrderItem();
                $orderItem->setOrder($order);
                $orderItem->setProduct($product);
                $orderItem->setQuantity($itemData['quantity']);
                $orderItem->setPrice($product->getPrice());

                $manager->persist($orderItem);
            }
        }

        $manager->flush();
    }
}
