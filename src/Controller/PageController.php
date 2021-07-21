<?php


namespace App\Controller;


use App\Entity\Category;
use App\Entity\News;
use App\Entity\Product;
use App\Entity\Promo;
use Knp\Component\Pager\PaginatorInterface;
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

        $newsRepository = $this->getDoctrine()->getRepository(News::class);

        $newsList = $newsRepository->findAll();

        return $this->render('home.html.twig', [
            'promoList' => $promoList,
            'newsList' => $newsList
        ]);
    }

    /**
     * @Route("category/{slug}", name="category_show")
     * @param string $slug
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function categoryShow(string $slug, PaginatorInterface $paginator, Request $request): Response
    {
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);

        $category = $categoryRepository->findOneBy(['slug' => $slug]);

        $products = $productRepository->findBy(['category' => $category->getId()]);

        $articles = $paginator->paginate(
            $products, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            9 // Nombre de résultats par page
        );

        return $this->render('category/category_show.html.twig', [
            'title' => $category->getTitle(),
            'products' => $articles,
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

    /**
     * @Route("/legal-notice", name="legalNotice")
     * @return Response
     */
    public function legalNotice(): Response
    {
        return $this->render('legal-notice.html.twig');
    }

}