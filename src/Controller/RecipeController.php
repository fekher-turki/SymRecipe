<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    /**
     * This controller display all recipes
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/recette', name: 'recipe.index', methods: ['GET'])]
    public function index(
        RecipeRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $recipes = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * this controller create a recipe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/nouveau', name: 'recipe.new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $manager
    ): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été créé avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
            
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * this controller update a recipe
     *
     * @param IngredientRepository $repository
     * @param integer $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/edition/{id}', 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(
        RecipeRepository $repository,
        int $id,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $recipe = $repository->findOneBy(["id" => $id]);
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été modifé avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * this controller delete a recipe
     *
     * @param RecipeRepository $repository
     * @param integer $id
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/suppression/{id}', 'recipe.delete', methods: ['GET'])]
    public function delete(
        RecipeRepository $repository,
        int $id,
        EntityManagerInterface $manager
    ): Response {
        $recipe = $repository->findOneBy(["id" => $id]);
        if (!$recipe) {
            $this->addFlash(
                'warning',
                'La recette en question n\'a pas été trouvé !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre recette a été supprimé avec succès !'
        );

        return $this->redirectToRoute('recipe.index');
    }
}
