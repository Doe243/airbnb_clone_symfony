<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
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


            $coverImage   = $faker->imageUrl(1000, 350);

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
    
            $manager->persist($ad);
        }
        
        $manager->flush();
    }
}
