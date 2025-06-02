<?php declare(strict_types=1);
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthControllerTest extends WebTestCase
{
    #[Test]
    #[DataProvider('dataRegisterIncorrectValuesProvider')]
    #[TestDox('[api/v1/register] Trying incorrect request $_dataName')]
    public function testRegisterIncorrectValues(array $rq)
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/register', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataRegisterInvalidValuesProvider')]
    #[TestDox('[api/v1/register] Trying invalid request $_dataName')]
    public function testRegisterInvalidValues(array $rq)
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/register', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataRegisterValidValuesProvider')]
    #[TestDox('[api/v1/register] Trying valid request $_dataName')]
    public function testRegisterValidValues(array $rq): void
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/register', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($response->getContent());
    }



    public static function dataRegisterIncorrectValuesProvider(): array
    {
        $params = [
            "empty" => [],
            "invalid" => ["x" => "1"],
            "only_one1" => ["nick" => "alfa"],
            "only_one2" => ["email" => "beta"],
            "only_one3" => ["password" => "gamma"],
            "only_two1" => ["nick" => "alfa", "email" => "beta"],
            "only_two2" => ["nick" => "alfa", "password" => "gamma"],
            "only_two3" => ["email" => "beta", "password" => "gamma"]
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataRegisterInvalidValuesProvider(): array
    {
        $params = [
            "nick_too_long" => ["nick" => "alfaalfaalfaalfaalfaalfaalfaalfaalfaalfaalfaalfaalfaalfaalfaalfaalfaalfa", "email" => "beta", "password" => "gamma"],
            "email_too_long" => ["nick" => "alfa", "email" => "betabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabetabeta", "password" => "gamma"]
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataRegisterValidValuesProvider(): array
    {
        $params = [
            "simple" => ["nick" => "alfa", "email" => "beta@theta.pl", "password" => "gamma"],
            "out_of_order" => ["email" => "beta", "password" => "gamma", "nick" => "alfa"],
            "all" => ["email" => "beta2", "password" => "gamma2", "nick" => "alfa2", "provenance" => "delta2", "motto" => "epsilon2"],
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }
}