<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use  Faker\Generator;
class AppFixtures extends Fixture
{
    /** @var Generator */
    protected $faker;

    public function load(ObjectManager $manager)
    {
       $nameProduct = ['Clavier Corsair K70',
            'Souris Corsair Scimitar',
            'écran ASUS ROG swift',
            'Barrette RAM 8Go G.Skill',
            'RTX 2080 Ti Founder Edition',
            'RTX 2080 Ti Strix A11G',
            'RTX 2080 Ti Aorus',
            'Clavier Roccat Ryos',
            'Souris Roccat Kone',
            'Processeur Intel Celeron G4920',
           'Processeur Intel Core i9 10980XE Extreme Edition',
           'Processeur AMD Ryzen Threadripper 3990X (2,9 GHz)'];
        $this->faker = Factory::create();

        for ($i = 0; $i < 12; $i++) {
            $product = new Product();
            $product->setName($nameProduct[$i]);
            $product->setSlug($nameProduct[$i]);
            $product->setPrice($this->faker->numberBetween(1,1000));
            $product->setDescription($this->faker->text);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
