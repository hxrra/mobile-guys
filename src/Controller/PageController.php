<?php


namespace App\Controller;


use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Promo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function home(): Response
    {
        $categoryRepository = $this->getDoctrine()->getRepository(Promo::class);

        $promoList = $categoryRepository->findAll();
        return $this->render('home.html.twig', [
            'promoList' => $promoList
        ]);
    }

    /**
     * @Route("category/{slug}", name="category_show")
     * @param string $slug
     * @return Response
     */
    public function categoryShow(string $slug): Response
    {
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);

        $category = $categoryRepository->findOneBy(['slug' => $slug]);

        $products = $productRepository->findBy(['category' => $category->getId()]);

        return $this->render('category_show.html.twig', [
            'title' => $category->getTitle(),
            'products' => $products,
        ]);
    }

    /**
     * @Route("/contact", name="contactUs")
     * @return Response
     */
    public function contactUs(): Response
    {
        return $this->render('contact.html.twig');
    }

    /**
     * @Route("/aboutus", name="aboutUs")
     * @return Response
     */
    public function aboutUs(): Response
    {
        return $this->render('aboutus.html.twig');
    }

    /**
     * @Route("/terms-of-use", name="termsOfUse")
     * @return Response
     */
    public function termsOfUse(): Response
    {
        return $this->render('terms-of-use.html.twig');
    }

}