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

    // #[Test]
    // #[DataProvider('dataValidUserControllerTestProvider')]
    // #[TestDox('Trying valid request $_dataName')]
    // public function testValidArgumentsCreateUsers(array $rq): void
    // {
    //     $client = static::createClient();

    //     $crawler = $client->jsonRequest('POST', 'api/users/add', $rq);
    //     $response = $client->getResponse();

    //     $this->assertResponseStatusCodeSame(201);
    //     $this->assertJson($response->getContent());
    //     $this->assertSame($response->getContent(), json_encode(
    //         [
    //             "desc" => "Account succesfully created"
    //         ]
    //         ));
    // }

    public static function dataValidDiscussionControllerTestProvider(): array
    {
        // $params = array_map(
        //     'json_encode',
        //     [
        //         [],
        //         ["x" => "1"],
        //         ["nick" => "alfa"],
        //         ["email" => "beta"],
        //         ["passhash" => "gamma"],
        //         ["nick" => "alfa", "email" => "beta"],
        //         ["nick" => "alfa", "passhash" => "gamma"],
        //         ["email" => "beta", "passhash" => "gamma"]
        //     ]
        // );

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

    // public static function dataInvalidDiscussionControllerTestProvider(): array
    // {
    //     // $params = array_map(
    //     //     'json_encode',
    //     //     [
    //     //         ["nick" => "alfa", "email" => "beta@theta.pl", "passhash" => "gamma"],
    //     //         ["email" => "beta", "passhash" => "gamma", "nick" => "alfa"],
    //     //         ["email" => "beta2", "passhash" => "gamma2", "nick" => "alfa2", "provenance" => "delta2", "motto" => "epsilon2"],
    //     //     ]
    //     // );

    //     $params = [
    //         "simple" => ["nick" => "alfa", "email" => "beta@theta.pl", "passhash" => "gamma"],
    //         "out_of_order" => ["email" => "beta", "passhash" => "gamma", "nick" => "alfa"],
    //         "all" => ["email" => "beta2", "passhash" => "gamma2", "nick" => "alfa2", "provenance" => "delta2", "motto" => "epsilon2"],
    //     ];

    //     array_walk($params, function (array &$item) { $item = array($item); });

    //     return $params;
    // }
}