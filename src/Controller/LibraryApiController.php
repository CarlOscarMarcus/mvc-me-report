<?php

namespace App\Controller;

use App\Entity\Library;
use App\Repository\LibraryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LibraryApiController extends AbstractController
{
    #[Route('/api/library/books', name: 'api_library_books')]
    public function getAllBooks(
        LibraryRepository $libraryRepository,
    ): Response {
        $books = $libraryRepository->findAll();

        $response = $this->json($books);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/library/book/{isbn}', name: 'api_library_book', methods: ['GET'])]
    public function getBookByIsbn(
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

