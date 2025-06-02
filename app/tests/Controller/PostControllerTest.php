<?php declare(strict_types=1);
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Entity\User;
use App\Entity\Topic;
use App\Entity\Post;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;

final class PostControllerTest extends WebTestCase
{
    #[Test]
    #[DataProvider('dataPostGetIncorrectValuesProvider')]
    #[TestDox('[api/v1/post/get] Trying incorrect request $_dataName')]
    public function testPostGetIncorrectValues(array $rq)
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/post/get', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataPostGetInvalidValuesProvider')]
    #[TestDox('[api/v1/post/get] Trying invalid request $_dataName')]
    public function testPostGetInvalidValues(array $rq)
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/post/get', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataPostGetProvider')]
    #[TestDox('[api/v1/post/get] Trying valid request $_dataName')]
    public function testPostGet(array $rq): void
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/post/get', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }



    public static function dataPostGetIncorrectValuesProvider(): array
    {
        $params = [
            "empty" => [],
            "invalid" => ["x" => "1"],
            "only_one1" => ["tid" => 1],
            "only_one2" => ["offset" => 0],
            "only_one3" => ["limit" => 10],
            "only_two1" => ["tid" => 1, "offset" => 0],
            "only_two2" => ["tid" => 1, "limit" => 10],
            "only_two3" => ["offset" => 0, "limit" => 10]
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataPostGetInvalidValuesProvider(): array
    {
        $params = [
            "negative_offset" => ['tid' => 1, 'offset' => -69, 'limit' => 10],
            "negative_limit" => ['tid' => 1, 'offset' => 1, 'limit' => -420],
            "both_invalid" => ['tid' => 1, 'limit' => -1, 'offset' => 0],
            "nonexistant_topic" => ['tid' => 999, 'limit' => 0, 'offset' => 10],
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataPostGetProvider(): array
    {
        $params = [
            "archived" => ['tid' => 1, 'offset' => 1, 'limit' => 10],
            "not_archived" => ['tid' => 2, 'offset' => 1, 'limit' => 10]
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }
}