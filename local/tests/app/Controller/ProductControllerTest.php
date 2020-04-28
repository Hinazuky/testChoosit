<?php


namespace App\Tests\app\Controller;


use App\Controller\Back\ProductController;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;


class ProductControllerTest extends webTestCase
{

    public function testHomepageIsUp()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );
    }
    public function testNumberEntity()
    {
        $kernel = self::bootKernel();

        $entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $products = $entityManager
            ->getRepository(Product::class)
            ->findby(array(),array('name' => 'asc'));

        static::assertEquals(
            12,
            sizeof($products)
        );
    }

    public function testAmountBasket(){

        $kernel = self::bootKernel();

        $entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $products = $entityManager
            ->getRepository(Product::class)
            ->findby(array(),array('name' => 'asc'));

        $price = 0;

        foreach ($products as $product){
            $product->quantity = 1;
            $price += $product->getPrice();
        }


        $controller = new ProductController();
        $priceFromController = $controller->calculateAmountBasket($products);

        $this->assertEquals($price, $priceFromController);
    }
}