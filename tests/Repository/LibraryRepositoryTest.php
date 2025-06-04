<?php

namespace App\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Library;
use App\Repository\LibraryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class LibraryRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private LibraryRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->repo = self::getContainer()->get(LibraryRepository::class);

        // Rebuild in-memory schema
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testSaveAndFind(): void
    {
        $book = new Library();
        $book->setTitle('Example Book')
             ->setAuthor('Author')
             ->setISBN('1234567890')
             ->setImage('cover.jpg')
             ->setURL('');

        $this->repo->save($book, true);

        $book->setURL(strval($book->getId()));
        $this->repo->save($book, true);

        $found = $this->repo->find($book->getId());

        $this->assertNotNull($found);
        $this->assertSame('Example Book', $found->getTitle());
    }

    public function testRemove(): void
    {
        $book = new Library();
        $book->setTitle('Example Book')
             ->setAuthor('Author')
             ->setISBN('1234567890')
             ->setImage('cover.jpg')
             ->setURL('');

        $this->repo->save($book, true);

        $book->setURL(strval($book->getId()));
        $this->repo->save($book, true);

        $id = $book->getId();
        $this->repo->remove($book, true);

        $this->assertNull($this->repo->find($id));
    }
}
