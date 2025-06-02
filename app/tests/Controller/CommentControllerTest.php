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
use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;

final class CommentControllerTest extends WebTestCase
{
    #[Test]
    #[DataProvider('dataCommentGetIncorrectValuesProvider')]
    #[TestDox('[api/v1/comment/get] Trying incorrect request $_dataName')]
    public function testCommentGetIncorrectValues(array $rq)
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/comment/get', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataCommentGetInvalidValuesProvider')]
    #[TestDox('[api/v1/comment/get] Trying invalid request $_dataName')]
    public function testCommentGetInvalidValues(array $rq)
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/comment/get', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataCommentGetProvider')]
    #[TestDox('[api/v1/Comment/get] Trying valid request $_dataName')]
    public function testCommentGet(array $rq): void
    {
        $client = static::createClient();

        $crawler = $client->jsonRequest('POST', 'api/v1/comment/get', $rq);
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }



    public static function dataCommentGetIncorrectValuesProvider(): array
    {
        $params = [
            "empty" => [],
            "invalid" => ["x" => "1"],
            "only_one1" => ["pid" => 1],
            "only_one2" => ["offset" => 0],
            "only_one3" => ["limit" => 10],
            "only_two1" => ["pid" => 1, "offset" => 0],
            "only_two2" => ["pid" => 1, "limit" => 10],
            "only_two3" => ["offset" => 0, "limit" => 10]
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataCommentGetInvalidValuesProvider(): array
    {
        $params = [
            "negative_offset" => ['pid' => 1, 'offset' => -69, 'limit' => 10],
            "negative_limit" => ['pid' => 1, 'offset' => 1, 'limit' => -420],
            "both_invalid" => ['pid' => 1, 'limit' => -1, 'offset' => 0],
            "nonexistant_topic" => ['pid' => 999, 'limit' => 0, 'offset' => 10],
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataCommentGetProvider(): array
    {
        $params = [
            "post_1" => ['pid' => 1, 'offset' => 1, 'limit' => 7],
            "post_2" => ['pid' => 2, 'offset' => 0, 'limit' => 10],
            "post_3" => ['pid' => 3, 'offset' => 1, 'limit' => 3],
            "post_4" => ['pid' => 4, 'offset' => 3, 'limit' => 2],
            "post_5" => ['pid' => 5, 'offset' => 2, 'limit' => 11],
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }
}