<?php


namespace App\Controller;


use App\Entity\Order;
use App\Entity\Product;
use App\Form\Type\ValidateCart;
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
        $price = 0;
        foreach ($cart as &$item) {
            $repository = $this->getDoctrine()->getRepository(Product::class);
            $productFind = $repository->find($item['id']);

            $item['product'] = $productFind;

            $price = $price + $productFind->getPrice() * $item['quantite'];
        }

        $emptyCart = 0;
        if(empty($cart)) {
            $emptyCart = 1;
        }

        return $this->render('cart.html.twig', [
            'emptyCart' => $emptyCart,
            'cart' => $cart,
            'total' => $price
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

    public function verifStock(int $id, Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $product = $repository->find($id);

        if($product->getStock() == 0) {
            $this->delete($id, $request);
            return 1;
        }
        return 0;

    }

    public function updateStock(int $id, int $quantite, Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $product = $repository->find($id);

        if($product->getStock() != 0) {
            $product->setStock($product->getStock() - $quantite);
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            return 1;
        }
        return 0;

    }

    /**
     * @Route("/cart/validation", name="cart_validation")
     * @param Request $request
     * @return Response
     */
    public function cartValidator(Request $request): Response
    {
        $alert = 0;
        // récup panier
        $session = $request->getSession();
        $cart = $session->get('panier');

        if(empty($cart)) {
            return $this->render('cart.html.twig', [
                'emptyCart' => 1
            ]);
        }

        // demander info
        $order = new Order();

        $form = $this->createForm(ValidateCart::class, $order);

        // valider info
        foreach ($cart as $c) {
            if($this->verifStock($c['id'], $request) == 1) {
                $this->addFlash('warning', 'Des produits ne sont plus disponible, attention');
                $alert = 1;
            }
        }
        if($alert == 1) {
            return $this->redirectToRoute('cart_show');
            // redirect alerte plus de stock
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // gestion du stock
            foreach ($cart as $c) {
                if($this->updateStock($c['id'], $c['quantite'] , $request) == 0) {
                    $this->addFlash('warning', 'Erreur lors de la mise à jours du stock');
                    $alert = 1;
                }
            }
            if($alert == 1) {
                return $this->redirectToRoute('cart_show');
                // redirect alerte plus de stock
            }

            // envoi en BDD
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            return $this->redirectToRoute("cart_show");
        }

        // Envoi d'un email de validation ?

        return $this->render('order/order.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
