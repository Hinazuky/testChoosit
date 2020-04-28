<?php

namespace App\Controller\Back;

use App\Entity\Product;
use App\Repository\ProductRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use  Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product")
     * @return Response
     */
    public function index()
    {
        $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findby(array(),array('name' => 'asc'));
        return $this->render('product/index', ['products' => $products]);
    }

    /**
     * @Route("/product/{id}", name="product_show", methods={"GET"})
     */
    public function show($id)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        $form = $this->createFormBuilder(null)
            ->add('quantity', TextType::class)
            ->add('Ajouter', SubmitType::class)
            ->getForm();

        return $this->render('product/show', ['product' => $product, 'form' => $form->createView()]);
    }

    /**
     * @Route("/product/{id}", name="addProductSession", methods={"POST"})
     */
    public function addSession($id, Request $request)
    {
        $this->get('session')->set('error', null);
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        $validator = Validation::createValidator();

        $violations = $validator->validate($request->get('form')['quantity'], [
            new Positive(),
            new NotBlank(),
        ]);

        if (0 !== count($violations)) {
            // there are errors, now you can show them
            foreach ($violations as $violation) {
                $this->get('session')->set('error', 'Entrée incorrect');
                return $this->redirect('/product/'.$product->getId());
            }
        }

        $array = $this->get('session')->get('basket') ?? [];

        foreach ($array as $productInArray) {

            if ($product->getId() === $productInArray->getId()) {

                $productInArray->quantity += $request->get('form')['quantity'];

                $this->get('session')->set('basket', $array);
            } else {
                $product->quantity = $request->get('form')['quantity'];
                array_push($array, $product);
                $this->get('session')->set('basket', $array);
            }
        }

        if (count($array) === 0) {
            $product->quantity = $request->get('form')['quantity'];
            array_push($array, $product);
            $this->get('session')->set('basket', $array);
        }

        return $this->redirect('/');
    }

    /**
     * @Route("/basket", name="productBasket", methods={"GET"})
     */
    public function basket()
    {
        $form = $this->createFormBuilder(null)
            ->setAction($this->generateUrl('deleteBasket'))
            ->add('supprimer', SubmitType::class)
            ->getForm();

        $array = $this->get('session')->get('basket') ?? [];
        $price = $this->calculateAmountBasket($array);
        return $this->render('basket', ['price' => $price, 'form' => $form->createView()]);
    }

    public function calculateAmountBasket(Array $array){

        $price = 0;
        foreach ($array as $product) {
            $price += $product->quantity * $product->getPrice();
        }
        return $price;
    }

    /**
     * @Route("/basket/delete", name="deleteBasket", methods={"POST"})
     */
    public function deleteBasket()
    {
        $array = $this->get('session')->set('basket', null);
        return $this->redirect('/basket');
    }
}
