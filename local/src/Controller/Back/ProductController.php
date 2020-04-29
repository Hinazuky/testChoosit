<?php

namespace App\Controller\Back;

use App\Entity\Product;
use App\Repository\ProductRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use  Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
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
            ->findby(array(), array('name' => 'asc'));
        return $this->render('product/index', ['products' => $products]);
    }

    /**
     * @Route("/product/{slug}", name="product_show", methods={"GET"})
     */
    public function show($slug)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findOneBy(array('slug' => $slug));

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for slug ' . $slug
            );
        }

        $form = $this->createFormBuilder(null)
            ->add('quantity', TextType::class)
            ->add('Ajouter', SubmitType::class)
            ->getForm();

        return $this->render('product/show', ['product' => $product, 'form' => $form->createView()]);
    }

    /**
     * @Route("/product/{slug}", name="addProductSession", methods={"POST"})
     */
    public function addSession($slug, Request $request)
    {
        $this->get('session')->set('error', null);
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findOneBy(array('slug' => $slug));
        $validator = Validation::createValidator();

        $violations = $validator->validate($request->get('form')['quantity'], [
            new Positive(),
            new NotBlank(),
        ]);

        if (0 !== count($violations)) {
            // there are errors, now you can show them
            foreach ($violations as $violation) {
                $this->get('session')->set('error', 'Entrée incorrect');
                return $this->redirect('/product/' . $product->getId());
            }
        }

        $array = $this->get('session')->get('basket') ?? [];

        $idProductInArray = null;

            for ($i=0;$i<count($array);$i++){
                if ($product->getId() === $array[$i]->getId()){
                    $product->quantity = $array[$i]->quantity;
                    $idProductInArray = $i;
                }
            }

        if (in_array($product,$array)){

            $array[$idProductInArray]->quantity += $request->get('form')['quantity'];
            $this->get('session')->set('basket', $array);

        } elseif (!in_array($product,$array)) {

            $product->quantity = $request->get('form')['quantity'];
            array_push($array, $product);
            $this->get('session')->set('basket', $array);
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
        $arrayFormDelete = [];
        $arrayFormChange = [];

        if (count($array)>=1){
            for ($i = 0; $i<count($array); $i++){

                $id = ['id' => $i];

                $formProductRemove = $this->createFormBuilder($id)
                    ->setAction($this->generateUrl('deleteItemBasket'))
                    ->add('id', HiddenType::class)
                    ->add('supprimer', SubmitType::class)
                    ->getForm();

                $formProductQuantity = $this->createFormBuilder($id)
                    ->setAction($this->generateUrl('changeQuantity'))
                    ->add('id', HiddenType::class)
                    ->add('quantity', TextType::class)
                    ->add('Changer', SubmitType::class)
                    ->getForm();

                array_push($arrayFormDelete,$formProductRemove->createView());
                array_push($arrayFormChange,$formProductQuantity->createView());
            }
        }
//        dd(ray);
        $this->get('session')->set('basket', $array);

        $price = $this->calculateAmountBasket($array);

        return $this->render('basket', ['price' => $price, 'form' => $form->createView(),'arrayFormDelete' => $arrayFormDelete,'arrayFormChange' => $arrayFormChange]);
    }

    public function calculateAmountBasket(array $array)
    {

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

    /**
     * @Route("/basket/delete-item", name="deleteItemBasket", methods={"POST"})
     */
    public function deleteItemBasket(Request $request)
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($request->get('form')['id'], [
            new PositiveOrZero(),
            new NotBlank(),
        ]);

        if (0 !== count($violations)) {
            // there are errors, now you can show them
            foreach ($violations as $violation) {
                $this->get('session')->set('error', 'Entrée incorrect');
                return $this->redirect('/basket');
            }
        }
        $id = $request->get('form')['id'];
        $array = $this->get('session')->get('basket');
        array_splice($array,$id,1);
        $this->get('session')->set('basket', $array);
        return $this->redirect('/basket');
    }
    /**
     * @Route("/basket/change-item-quantity", name="changeQuantity", methods={"POST"})
     */
    public function changeQuantity(Request $request)
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($request->get('form')['id'], [
            new PositiveOrZero(),
            new NotBlank(),
        ]);

        if (0 !== count($violations)) {
            // there are errors, now you can show them
            foreach ($violations as $violation) {
                $this->get('session')->set('error', 'Entrée incorrect');
                return $this->redirect('/basket');
            }
        }
        $violations = $validator->validate($request->get('form')['quantity'], [
            new PositiveOrZero(),
            new NotBlank(),
        ]);

        if (0 !== count($violations)) {
            // there are errors, now you can show them
            foreach ($violations as $violation) {
                $this->get('session')->set('error', 'Entrée incorrect');
                return $this->redirect('/basket');
            }
        }

        $id = $request->get('form')['id'];
        $array = $this->get('session')->get('basket');

//        dd($request->get('form')['quantity']);
        if ($request->get('form')['quantity'] == 0 ){
            array_splice($array,$id,1);
            $this->get('session')->set('basket', $array);
            return $this->redirect('/basket');
        }else{
            $array[$id]->quantity = $request->get('form')['quantity'];
            $this->get('session')->set('basket', $array);
            return $this->redirect('/basket');
        }

    }
}
