<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/product/{slug}", name="product_show")
     * @param string $slug
     * @return Response
     */
    public function index(string $slug): Response
    {
        $productRepository = $this->getDoctrine()->getRepository(Product::class);

        $product = $productRepository->findOneBy(['slug' => $slug]);

        $nextProduct = $productRepository->findBy(['category' => $product->getCategory()->getId()], null, 3, $product->getId());


        return $this->render('product/index.html.twig', [
            'product' => $product,
            'nextProduct' => $nextProduct
        ]);
    }


}
