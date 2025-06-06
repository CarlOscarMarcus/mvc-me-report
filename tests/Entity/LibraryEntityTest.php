<?php

namespace App\Tests\Entity;

use App\Entity\Library;
use PHPUnit\Framework\TestCase;

class LibraryEntityTest extends TestCase
{
    public function testLibraryEntity(): void
    {
        $library = new Library();

        $library->setTitle('Test Title');
        $library->setAuthor('Test Author');
        $library->setISBN('1234567890');
        $library->setImage('test.jpg');
        $library->setURL('1');

        $this->assertSame('Test Title', $library->getTitle());
        $this->assertSame('Test Author', $library->getAuthor());
        $this->assertSame('1234567890', $library->getISBN());
        $this->assertSame('test.jpg', $library->getImage());
        $this->assertSame('1', $library->getURL());
    }

    public function testToArray()
    {
        $library = new Library();
        $library->setTitle('Test Title');
        $library->setAuthor('John Doe');
        $library->setISBN('1234567890');
        $library->setImage('image.jpg');

        // ID will be null because it’s not persisted in test
        $expectedArray = [
            'Test Title',
            'John Doe',
            '1234567890',
            'image.jpg',
            null,
        ];

        $this->assertSame($expectedArray, $library->toArray());
    }

}
