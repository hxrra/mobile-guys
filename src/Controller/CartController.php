<?php


namespace App\Controller;


use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Form\Type\ValidateCart;
use App\Form\Type\ValidateCartConnected;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{

    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordEncoder;

    public function __construct(RequestStack $requestStack, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
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

    public function getProduct(int $id) {
        $repository = $this->getDoctrine()->getRepository(Product::class);

        return $repository->find($id);
    }

    /**
     * @Route("/cart/validation", name="cart_validation")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function cartValidator(Request $request, \Swift_Mailer $mailer): Response
    {
        $repository = $this->getDoctrine()->getRepository(User::class);

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

        if( $securityContext = $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $order->setPrenom($this->getUser()->getPrenom());
            $order->setNom($this->getUser()->getNom());
            $order->setTel($this->getUser()->getTel());
            $order->setVille($this->getUser()->getVille());
            $order->setCodepostal($this->getUser()->getCodepostal());
            $order->setAdresse($this->getUser()->getAdresse());
            $order->setMail($this->getUser()->getEmail());
            $order->setAccount($this->getUser()->getId());
            $form = $this->createForm(ValidateCartConnected::class, $order);
        } else {
            $form = $this->createForm(ValidateCart::class, $order);
        }

        $orderInfo = "";

        // valider info
        foreach ($cart as $c) {
            if($this->verifStock($c['id'], $request) == 1) {
                $this->addFlash('warning', 'Des produits ne sont plus disponible, attention');
                $alert = 1;
            }
            $product = $this->getProduct($c['id']);
            $orderInfo = $orderInfo . " #" . $product->getId() . " " . $product->getDesignation() . "x" . $c['quantite']. " /";
        }
        if($alert == 1) {
            return $this->redirectToRoute('cart_show');
            // redirect alerte plus de stock
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $order->setInfo($orderInfo);

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

            if($order->getAccount() == 1) {
                if(!empty($repository->findOneBy(['email' => $order->getMail()]))) {
                    if( $securityContext = $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
                        if($this->getUser()->getEmail() != $order->getMail()) {
                            $this->addFlash('warning', 'Différence entre l\'adresse mail de votre compte et celle utilisée');
                            return $this->redirectToRoute('cart_show');
                        }
                    } else {
                        $this->addFlash('warning', 'Compte existant, merci de vous connecter');
                        return $this->redirectToRoute('app_login');
                    }
                }

                // generer un mot de passe aléatoire + création de l'user
                $password = $this->newPassword(4);
                $user = new User();
                $user
                    ->setPassword($this->passwordEncoder->hashPassword($user,$password))
                    ->setEmail($order->getMail())
                    ->setAdresse($order->getAdresse())
                    ->setCodepostal($order->getCodepostal())
                    ->setVille($order->getVille())
                    ->setNom($order->getNom())
                    ->setPrenom($order->getPrenom())
                    ->setTel($order->getTel());

                // envoyer les données vers la creation de compte

                $this->newUser($user);

                // envoyer son mot de passe par mail avec la création du compte

                $this->sendAccountCreatedMail($mailer, $user, $password);

            }

            // envoyer un mail récap de la commande
            $this->sendOrderMail($mailer, $session->get('panier'), $order );

            //suppresion du panier
            $session->set('panier', []);

            // envoi en BDD
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            $this->addFlash('success', 'Merci pour votre commande');
            return $this->redirectToRoute("cart_show");
        }

        // Envoi d'un email de validation ?

        return $this->render('order/order.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function newPassword(int $lenght) {
        return bin2hex(openssl_random_pseudo_bytes($lenght));
    }

    public function newUser(User $user) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
    }

    public function sendOrderMail(\Swift_Mailer $mailer, array $panier, Order $order)
    {
        $message = (new \Swift_Message('Votre commande est enregistré !'))
            ->setFrom('commande@mobile-guy.com')
            ->setTo($order->getMail())
            ->setBody( $this->renderView(
                'email/order.html.twig',
                ['panier' => $panier]
            ),
                'text/html'
            )
        ;

        $mailer->send($message);
    }

    public function sendAccountCreatedMail(\Swift_Mailer $mailer, User $user, string $password)
    {
        $message = (new \Swift_Message('Votre compte vient d\'être créé !'))
            ->setFrom('info@mobile-guy.com')
            ->setTo($user->getEmail())
            ->setBody(
                'Voici votre mdp bg'.$password
            )
        ;

        $mailer->send($message);
    }

}
