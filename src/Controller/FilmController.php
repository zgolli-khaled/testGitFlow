<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Film;
use App\Repository\AuthorRepository;
use App\Repository\FilmRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/film', name: 'app_film')]
class FilmController extends AbstractController
{


    #[Route('/add/{autherId}', name: 'add', methods: ['POST'])]
    public function createFilm(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, AuthorRepository $authorRepository, int $autherId): JsonResponse
    {

        $author = $authorRepository->find($autherId);
        if (!$author) {
            return new JsonResponse("author not found", JsonResponse::HTTP_NOT_FOUND);
        }


        $film = $serializer->deserialize($request->getContent(), Film::class, 'json');

        $film->setAuthor($author);
        $em->persist($film);
        $em->flush();

        $jsonFilm = $serializer->serialize($film, 'json', ['groups' => ['getFilms']]);

        return new JsonResponse($jsonFilm, Response::HTTP_CREATED, [], true);
    }


    #[Route('/all', name: 'all', methods: ['GET'])]
    public function getAllFilms(FilmRepository $filmRepository, SerializerInterface $serializer): JsonResponse
    {
        $filmList=$filmRepository->findAll();
       
        $jsonFilmList = $serializer->serialize($filmList, 'json',['groups' => ['getFilms']]);
        return new JsonResponse($jsonFilmList, Response::HTTP_OK, [], true);

        
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Film $film,EntityManagerInterface $em): JsonResponse
    {
        $em->remove($film);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);

        
    }


    
    #[Route('/update/{id}', name: 'update', methods: ['PUT'])]
    public function updateFilm(Request $request, SerializerInterface $serializer, Film $currentFilm, EntityManagerInterface $em): JsonResponse
    {
        $updateFilm = $serializer->deserialize(
            $request->getContent(),
            Film::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentFilm]
        );
        // $content = $request->toArray();
        // $idAuthor=$content['author']['id'];

       

        $em->persist($updateFilm);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


    #[Route('/updates/{idFilm}/{id}', name: 'updateFilmAuthor', methods: ['PUT'])]
    public function updateFilmAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,int $id , AuthorRepository $authorRepository , int $idFilm, FilmRepository $filmRepository): JsonResponse
    {
       
       
      $author=$authorRepository->find($id);
      $film=$filmRepository->find($idFilm);
      $film->setAuthor($author);

        // $content = $request->toArray();
        // $idAuthor=$content['author']['id'];

       

       $em->persist($film);
       $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


}
