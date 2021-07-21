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
     * @Route("/cart", name="cart_show")
     * @param Request $request
     * @return Response
     */
    public function showCart(Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('panier');

        if($cart == null) {
            $this->createCart($request);
            $cart = $session->get('panier');
        }

        foreach ($cart as &$item) {
            $repository = $this->getDoctrine()->getRepository(Product::class);
            $productFind = $repository->find($item['id']);

            $item['product'] = $productFind;
        }

        $emptyCart = 0;
        if(empty($cart)) {
            $emptyCart = 1;
        }

        return $this->render('cart.html.twig', [
            'emptyCart' => $emptyCart,
            'cart' => $cart
        ]);
    }

    /**
     * @Route("product/delete/{id}", name="cart_delete")
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function delete(int $id, Request $request): Response {
        $session = $request->getSession();
        $cart = $session->get('panier');

        $lengthBefore = count($cart);
        unset($cart[$id]);
        $lengthAfter = count($cart);

        $session->set('panier', $cart);

        if($lengthAfter < $lengthBefore) {
            $this->addFlash('warning', 'Impossible de supprimer l\'article');
        }
        return $this->redirectToRoute('cart_show');
    }

    /**
     * @Route("/cart/trash", name="cart_trash")
     * @param Request $request
     */
    public function deleteAll(Request $request): Response {
        $session = $request->getSession();
        $session->set('panier', []);

        return $this->redirectToRoute('cart_show');
    }

    /**
     * @Route("product/{id}/add/{quantite}", name="cart_add")
     * @param int $id
     * @param int $quantite
     * @param Request $request
     * @return Response
     */
    public function add(int $id, int $quantite,Request $request): Response {

        $how = $request->request->get('how');

        if($how != null) {
            $quantite = $how;
        }

        if($this->verifProduct($id, $request) == null) {
            $this->addFlash('warning', 'impossible d\'ajouter le produit au panier');
            return $this->render('base.html.twig');
        }

        $session = $request->getSession();

        if($session->get('panier') == NULL) {
            $this->createCart($request);
        }
        $cart = $session->get('panier');

        array_push($cart, array(
            'id' => $id,
            'quantite' => $quantite
        ));

        $session->set('panier', $cart);

        return $this->redirectToRoute('cart_show');
    }

    public function createCart(Request $request) {
        $session = $request->getSession();
        $session->set('panier', array());
        return null;
    }

    public function verifProduct(int $id, Request $request) {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $product = $repository->find($id);

        if($product->getDesignation() == null) {
            return null;
        }
        return 1;
    }

}
