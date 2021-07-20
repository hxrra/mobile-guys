<?php


namespace App\Controller;


use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("product/{id}/add", name="cart_add")
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function add(int $id, Request $request): Response {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $product = $repository->find($id);

        $session = $request->getSession();

        if($session->get('panier') == NULL) {
            $this->createCart($request);
        }
        $cart = $session->get('panier');

        array_push($cart, $id);

        $session->set('panier', $cart);

        return $this->render('base.html.twig');
    }

    public function createCart(Request $request) {
        $session = $request->getSession();
        $session->set('panier', array());
        return null;
    }

}
