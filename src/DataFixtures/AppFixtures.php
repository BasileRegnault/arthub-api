<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Artist;
use App\Entity\Artwork;
use App\Entity\Gallery;
use App\Entity\Rating;
use App\Enum\ArtworkStyle;
use App\Enum\ArtworkType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private \Faker\Generator $faker;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create('fr_FR'); // ou 'en_US'
    }

    public function load(ObjectManager $manager): void
    {
        // --- USERS ---
        $users = [];

        // Admin
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ADMIN']);
        $admin->setProfilePicture($this->faker->imageUrl(200,200,'people'));
        $admin->setCreatedAt(new \DateTimeImmutable('-2 years'));
        $admin->setUpdatedAt(new \DateTimeImmutable('-1 year'));
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
        $manager->persist($admin);
        $users[] = $admin;

        // Several normal users
        for ($i = 0; $i < 8; $i++) {
            $user = new User();
            $user->setUsername($this->faker->userName . $i);
            $user->setEmail($this->faker->unique()->safeEmail);
            $user->setRoles(['USER']);
            $user->setProfilePicture($this->faker->imageUrl(200,200,'people'));
            $created = $this->faker->dateTimeBetween('-2 years', 'now');
            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($created));
            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        // --- ARTISTS ---
        $artists = [];
        for ($i = 0; $i < 12; $i++) {
            $artist = new Artist();
            $artist->setFirstname($this->faker->firstName);
            $artist->setLastname($this->faker->lastName);
            $born = $this->faker->dateTimeBetween('-120 years', '-60 years');
            $artist->setBornAt(\DateTimeImmutable::createFromMutable($born));
            // some artists still alive
            if ($this->faker->boolean(60)) {
                $artist->setDiedAt(null);
            } else {
                $died = $this->faker->dateTimeBetween($born, 'now');
                $artist->setDiedAt(\DateTimeImmutable::createFromMutable($died));
            }
            $artist->setNationality($this->faker->country);
            $artist->setBiography($this->faker->paragraphs(3, true));
            $artist->setProfilePicture($this->faker->imageUrl(400,400,'people'));
            $artist->setCreatedAt(new \DateTimeImmutable('-1 year'));
            $artist->setUpdatedAt(new \DateTimeImmutable());
            $manager->persist($artist);
            $artists[] = $artist;
        }

        // --- ARTWORKS ---
        $types = ArtworkType::cases();
        $styles = ArtworkStyle::cases();

        $artworks = [];
        for ($i = 0; $i < 40; $i++) {
            $art = new Artwork();
            $art->setTitle(ucfirst($this->faker->words(3, true)));
            $art->setType($this->faker->randomElement($types));
            $art->setStyle($this->faker->randomElement($styles));
            $art->setCreationDate(new \DateTimeImmutable('-3 months'));
            $art->setDescription($this->faker->paragraphs(2, true));
            $art->setImageUrl($this->faker->imageUrl(800,600,'art'));
            $artist = $this->faker->randomElement($artists);
            $art->setArtist($artist);
            $art->setLocation($this->faker->city);
            $art->setViews($this->faker->numberBetween(0, 1000));
            $art->setIsDisplay($this->faker->boolean(70));
            $art->setCreatedAt(new \DateTimeImmutable('-6 months'));
            $art->setUpdatedAt(new \DateTimeImmutable());
            $manager->persist($art);
            $artworks[] = $art;
        }

        // --- GALLERIES ---
        $galleries = [];
        foreach ($users as $idx => $owner) {
            // give some users no gallery
            if ($this->faker->boolean(80)) {
                $gallery = new Gallery();
                $gallery->setName($owner->getUsername() . "'s gallery");
                $gallery->setDescription($this->faker->sentence(12));
                $gallery->setOwner($owner);
                $gallery->setCoverImage($this->faker->imageUrl(1200,400,'city'));
                $gallery->setIsPublic($this->faker->boolean(60));
                $gallery->setViews($this->faker->numberBetween(0, 100));
                $gallery->setCreatedAt(new \DateTimeImmutable('-3 months'));
                $gallery->setUpdatedAt(new \DateTimeImmutable());

                // add some artworks
                $count = $this->faker->numberBetween(1, 8);
                for ($k = 0; $k < $count; $k++) {
                    $art = $this->faker->randomElement($artworks);
                    // addArtwork method on entity; if using direct collection use add
                    $gallery->addArtwork($art);
                }

                $manager->persist($gallery);
                $galleries[] = $gallery;
            }
        }

        // --- RATINGS ---
        foreach ($artworks as $art) {
            // add random number of ratings per artwork
            $ratingCount = $this->faker->numberBetween(0, 6);
            for ($r = 0; $r < $ratingCount; $r++) {
                $rating = new Rating();
                $rating->setScore($this->faker->numberBetween(1, 5));
                $rating->setComment($this->faker->boolean(70) ? $this->faker->sentence(12) : null);
                $rating->setAuthor($this->faker->randomElement($users));
                $rating->setArtwork($art);
                $manager->persist($rating);
            }
        }

        $manager->flush();
    }
}
