<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use  Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{


    /**
     * @Route("api/products", name="Product", methods={"GET"})
     */
    public function Products()
    {
        $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findby(array(),array('name' => 'asc'));
        if (empty($products)){
            return new response(['Message' => 'no resource found'], 404, array(
                'Content-Type' => 'application/json'
            ));
        }
        return new response(json_encode($products), 200, array(
        'Content-Type' => 'application/json'
    ));
    }
}
