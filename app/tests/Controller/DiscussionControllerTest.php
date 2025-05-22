<?php declare(strict_types=1);
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


final class DiscussionControllerTest extends WebTestCase
{
    #[Test]
    #[DataProvider('dataValidDiscussionControllerTestProvider')]
    #[TestDox('Trying valid request $_dataName')]
    public function testValidArgumentsReturnNotFound(int $dc, int $comm): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', "api/discussions/$dc/$comm");
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }

    public static function dataValidDiscussionControllerTestProvider(): array
    {
        $params = [
            "comments1" => [1, 1],
            "comments2" => [1, 2],
            "comments3" => [1, 0],
            "comments4" => [2, 1],
            "comments5" => [2, 2],
            "comments6" => [2, 0]
        ];

        return $params;
    }
}