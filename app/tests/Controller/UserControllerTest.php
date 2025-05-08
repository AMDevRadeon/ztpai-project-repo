<?php declare(strict_types=1);
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


final class UserControllerTest extends WebTestCase
{

    #[Test]
    #[DataProvider('dataInvalidUserControllerTestProvider')]
    #[TestDox('Trying invalid request $_dataName')]
    public function testInvalidArgumentsReturnBadRequest(array $rq): void
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/users/add', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
        $this->assertSame($response->getContent(), json_encode(
            [
                "desc" => "Required data values empty"
            ]
            ));
    }

    #[Test]
    #[DataProvider('dataValidUserControllerTestProvider')]
    #[TestDox('Trying valid request $_dataName')]
    public function testValidArgumentsCreateUsers(array $rq): void
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/users/add', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($response->getContent());
        $this->assertSame($response->getContent(), json_encode(
            [
                "desc" => "Account succesfully created"
            ]
            ));
    }

    public static function dataInvalidUserControllerTestProvider(): array
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
            "empty" => [],
            "invalid" => ["x" => "1"],
            "only_one1" => ["nick" => "alfa"],
            "only_one2" => ["email" => "beta"],
            "only_one3" => ["passhash" => "gamma"],
            "only_two1" => ["nick" => "alfa", "email" => "beta"],
            "only_two2" => ["nick" => "alfa", "passhash" => "gamma"],
            "only_two3" => ["email" => "beta", "passhash" => "gamma"]
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataValidUserControllerTestProvider(): array
    {
        // $params = array_map(
        //     'json_encode',
        //     [
        //         ["nick" => "alfa", "email" => "beta@theta.pl", "passhash" => "gamma"],
        //         ["email" => "beta", "passhash" => "gamma", "nick" => "alfa"],
        //         ["email" => "beta2", "passhash" => "gamma2", "nick" => "alfa2", "provenance" => "delta2", "motto" => "epsilon2"],
        //     ]
        // );

        $params = [
            "simple" => ["nick" => "alfa", "email" => "beta@theta.pl", "passhash" => "gamma"],
            "out_of_order" => ["email" => "beta", "passhash" => "gamma", "nick" => "alfa"],
            "all" => ["email" => "beta2", "passhash" => "gamma2", "nick" => "alfa2", "provenance" => "delta2", "motto" => "epsilon2"],
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }
}