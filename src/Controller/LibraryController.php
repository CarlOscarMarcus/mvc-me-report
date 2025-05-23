<?php

namespace App\Controller;

use App\Entity\Library;
use App\Repository\LibraryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LibraryController extends AbstractController
{
    #[Route('/library', name: 'app_library')]
    public function index(): Response
    {
        return $this->render('library/index.html.twig', [
            'controller_name' => 'LibraryController',
        ]);
    }

    #[Route('/library/create', name: 'library_create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render('library/createForm.html.twig');
    }

    #[Route('/library/create', name: 'library_create_post', methods: ['POST'])]
    public function createLibrary(
        Request $request,
        ManagerRegistry $doctrine,
    ): Response {
        $entityManager = $doctrine->getManager();

        $book = new Library();
        $book->setTitle($request->request->get('bookTitle'));
        $book->setISBN($request->request->get('bookISBN'));
        $book->setAuthor($request->request->get('bookAuthor'));
        $book->setImage($request->request->get('bookImage'));
        $book->setURL('');

        $entityManager->persist($book);

        $entityManager->flush();
        $book->setURL(strval($book->getId()));

        $entityManager->flush();

        return $this->redirectToRoute('library_show_all');
    }

    #[Route('/library/show', name: 'library_show_all')]
    public function showAllLibrary(
        LibraryRepository $libraryRepository,
    ): Response {
        $library = $libraryRepository
            ->findAll();

        $temp = [];
        foreach ($library as $book) {
            $title = "{$book->getTitle()}";
            $author = "{$book->getAuthor()}";
            $ISBN = "{$book->getISBN()}";
            $image = "{$book->getImage()}";
            $id = $book->getId();
            array_push($temp, [$title, $author, $ISBN, $image, $id]);
        }
        $data = [
            'data' => $temp,
        ];

        return $this->render('library/showAllBooks.html.twig', $data);
    }

    #[Route('/library/show/{id}', name: 'library_by_id')]
    public function showProductById(
        LibraryRepository $libraryRepository,
        int $id,
    ): Response {
        $library = $libraryRepository
            ->find($id);

        $title = "{$library->getTitle()}";
        $author = "{$library->getAuthor()}";
        $ISBN = "{$library->getISBN()}";
        $image = "{$library->getImage()}";
        $id = "{$library->getId()}";

        $data = [
            'data' => [$title, $author, $ISBN, $image, $id],
        ];

        return $this->render('library/showBooksById.html.twig', $data);
    }

    #[Route('/library/delete/{id}', name: 'library_delete', methods: ['GET'])]
    public function deletelibraryById(
        ManagerRegistry $doctrine,
        int $id,
    ): Response {
        $entityManager = $doctrine->getManager();
        $library = $entityManager->getRepository(Library::class)->find($id);

        if (!$library) {
            throw $this->createNotFoundException('No library found for id '.$id);
        }

        $entityManager->remove($library);
        $entityManager->flush();

        return $this->redirectToRoute('library_show_all');
    }

    #[Route('/library/update/{id}', name: 'library_update', methods: ['GET'])]
    public function updateBook(
        LibraryRepository $libraryRepository,
        int $id,
    ): Response {
        $library = $libraryRepository->find($id);

        if (!$library) {
            throw $this->createNotFoundException('Book not found.');
        }

        return $this->render('library/libraryUpdate.html.twig', ['library' => $library]);
    }

    #[Route('/library/update/{id}', name: 'library_update_post', methods: ['POST'])]
    public function updateBookPost(
        Request $request,
        ManagerRegistry $doctrine,
        int $id
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Library::class)->find($id);

        if (!$book) {
            throw $this->createNotFoundException('Book not found.');
        }

        $book->setTitle($request->request->get('bookTitle'));
        $book->setISBN($request->request->get('bookISBN'));
        $book->setAuthor($request->request->get('bookAuthor'));
        $book->setImage($request->request->get('bookImage'));

        $entityManager->flush();
        return $this->redirectToRoute('library_show_all');
    }

    #[Route('/library/reset', name: 'library_reset')]
    public function libraryReset(
        ManagerRegistry $doctrine,
    ): Response {
        $entityManager = $doctrine->getManager();
        $library = $entityManager->getRepository(Library::class)->findAll();

        foreach ($library as $book) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('library_show_all');
    }

    #[Route('api/library/books', name: 'api_library_books')]
    public function apiShowAllBooks(
        LibraryRepository $LibraryRepository,
    ): Response {
        $books = $LibraryRepository
            ->findAll();

        $response = $this->json($books);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/library/book/{isbn}', name: 'api_library_book', methods: ['GET'])]
    public function apiShowBook(
        string $isbn,
        LibraryRepository $libraryRepository,
    ): Response {
        // Find a book by ISBN
        $book = $libraryRepository->findOneBy(['ISBN' => $isbn]);

        if (!$book) {
            // If no book is found, return a 404 response
            return $this->json([
                'error' => 'Book not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Return the book data as JSON with pretty print enabled
        $response = $this->json($book);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
