<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IngredientController extends AbstractController
{
    /**
     * This controller display all ingredients
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/ingredient', name: 'ingredient.index', methods: ['GET'])]
    public function index(
        IngredientRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $ingredients = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    /**
     * this controller create an ingredient
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/ingredient/nouveau', 'ingredient.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été créé avec succès !'
            );

            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * this controller update an ingredient
     */
    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    #[Route('/ingredient/edition/{id}', 'ingredient.edit', methods: ['GET', 'POST'])]
    public function edit(
        Ingredient $ingredient,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été modifé avec succès !'
            );

            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * this controller delete an ingredient
     */
    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    #[Route('/ingredient/suppression/{id}', 'ingredient.delete', methods: ['GET'])]
    public function delete(Ingredient $ingredient, EntityManagerInterface $manager): Response
    {
        if (!$ingredient) {
            $this->addFlash(
                'warning',
                'L\'ingrédient en question n\'a pas été trouvé !'
            );

            return $this->redirectToRoute('ingredient.index');
        }

        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre ingrédient a été supprimé avec succès !'
        );

        return $this->redirectToRoute('ingredient.index');
    }
}
