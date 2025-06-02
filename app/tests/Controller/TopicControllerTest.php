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
use App\Repository\UserRepository;
use App\Repository\TopicRepository;

final class TopicControllerTest extends WebTestCase
{
    #[Test]
    #[DataProvider('dataTopicGetIncorrectValuesProvider')]
    #[TestDox('[api/v1/topic/get] Trying incorrect request $_dataName')]
    public function testTopicGetIncorrectValues(array $rq)
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/topic/get', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataTopicGetInvalidValuesProvider')]
    #[TestDox('[api/v1/topic/get] Trying invalid request $_dataName')]
    public function testTopicGetInvalidValues(array $rq)
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/topic/get', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/topic/get] Trying valid request')]
    public function testTopicGet(): void
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/topic/get', ['limit' => 10, 'offset' => 0]);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }



    public static function dataTopicGetIncorrectValuesProvider(): array
    {
        $params = [
            "empty" => [],
            "only_offset" => ['offset' => 0],
            "only_limit" => ['limit' => 10]
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataTopicGetInvalidValuesProvider(): array
    {
        $params = [
            "negative_offset" => ['offset' => -69, 'limit' => 10],
            "negative_limit" => ['offset' => 1, 'limit' => -420],
            "both_invalid" => ['limit' => -1, 'offset' => 0]
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }
}