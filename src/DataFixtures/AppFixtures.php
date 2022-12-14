<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $encoder) {}

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('FR-fr');

        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setFirstName('René')
                  ->setLastName('MUMBA')
                  ->setEmail('renemumba@gmail.com')
                  ->setHash($this->encoder->hashPassword($adminUser, 'password' ))  
                  ->setPicture('https://astra-environmental.com/sites/astra-environmental.com/files/styles/default/public/default_images/author-thumb-min.png?itok=X5Iju55M')
                  ->setIntroduction($faker->sentence())
                  ->setDescription('<p>'. join('</p><p>', $faker->paragraphs(3)).'</p>')
                  ->addUserRole($adminRole);
        
        $manager->persist($adminUser);

        /// Nous gérons les utilisateurs

        $users = [];

        $genres = ['male', 'female'];

        for ($u = 0; $u <= 10 ; $u++) { 
            
            $user = new User();

            $genre = $faker->randomElement($genres);

            $picture = "https://randomuser.me/api/portraits/";

            $pictureId = $faker->numberBetween(1, 99) .'.jpg';

            $picture = $picture.($genre == "male" ? 'men/' : 'women/' ). $pictureId;
            
            $plainPassword = 'password';

            $hash = $this->encoder->hashPassword($user, $plainPassword );

            $user->setFirstName($faker->firstName($genre))
                 ->setLastName($faker->lastName)
                 ->setEmail($faker->email)
                 ->setIntroduction($faker->sentence())
                 ->setDescription('<p>'. join('</p><p>', $faker->paragraphs(5)).'</p>')
                 ->setHash($hash)
                 ->setPicture($picture) ;

            $manager->persist($user);

            $users[] = $user;
        }

        /// Nous gérons les annonces

        for ($i = 1; $i < 30; $i++) { 
            
            $ad           = new Ad();

            $title        = $faker->sentence();
            

            $coverImage   = "https://prod-saint-gobain-fr.content.saint-gobain.io/sites/saint-gobain.fr/files/2020-10/moderniser-appartement-la-maison-saint-gobain.jpg";

            $introduction = $faker->paragraph(2);

            $content      = '<p>'. join('</p><p>', $faker->paragraphs(5)).'</p>';

            $user         =  $users[mt_rand(0, count($users) - 1)];
    
            $ad->setTitle($title)
               ->setCoverImage($coverImage)
               ->setIntroduction($introduction)
               ->setContent($content)
               ->setPrice(mt_rand(40, 200))
               ->setRooms(mt_rand(1,5))
               ->setAuthor($user);

               for ($j = 1; $j < mt_rand(2,5) ; $j++) { 
                
                    $image = new Image();

                    $image->setUrl($faker->imageUrl())
                          ->setCaption($faker->sentence())
                          ->setAd($ad);
                          
                    $manager->persist($image);
                }

            /// Nous gérons les réservations

            for ($b = 0; $b < mt_rand(0, 10) ; $b++) { 
                
                $booking = new Booking();

                $createdAt = $faker->dateTimeBetween('-6 months');

                $startDate = $faker->dateTimeBetween('-3 months');

                $duration = mt_rand(3, 10);

                /// Ici, on crée un clone de startDate pour n'est pas modifier la valeur de celle qui se trouve au dessus

                $endDate = (clone $startDate)->modify("+$duration days");

                $amount = $ad->getPrice() * $duration;

                $booker = $users[mt_rand(0, count($users) - 1)];

                $booking->setBooker($booker)
                        ->setAd($ad)
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                        ->setCreatedAt($createdAt)
                        ->setAmount($amount)
                        ->setComment($faker->paragraph());

                $manager->persist($booking);

                //Gestion des commentaires

                if (mt_rand(0, 1)) {

                    $comment = new Comment();

                    $comment->setContent($faker->paragraph())
                            ->setRating(mt_rand(1,5))
                            ->setAuthor($booker)
                            ->setAd($ad);
                            
                    $manager->persist($comment);
                }

            }
    
            $manager->persist($ad);
        }
        
        $manager->flush();
    }
}
