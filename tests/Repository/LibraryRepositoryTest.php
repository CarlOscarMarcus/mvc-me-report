<?php

namespace App\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Library;
use Doctrine\ORM\EntityManagerInterface;

class LibraryRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        // Create the schema (ONLY needed for SQLite in-memory DB)
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);

    }

    public function testSaveAndFind(): void
    {
        $book = new Library();
        $book->setTitle('Example Book');
        $book->setAuthor('Author');
        $book->setISBN('1234567890');
        $book->setImage('cover.jpg');
        $book->setURL('');

        $repo = $this->entityManager->getRepository(Library::class);
        $repo->save($book, true);

        $book->setURL(strval($book->getId()));

        $repo->save($book, true);



        $found = $repo->find($book->getId());

        $this->assertNotNull($found);
        $this->assertSame('Example Book', $found->getTitle());
    }
}
