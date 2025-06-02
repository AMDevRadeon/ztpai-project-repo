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
use App\Repository\UserRepository;


final class AdminControllerTest extends WebTestCase
{
    private UserRepository $repo;
    private static KernelBrowser $client;

    protected function setUp(): void
    {
        self::$client = static::createClient();
        $this->repo = self::getContainer()
            ->get(UserRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // Assume correct fixtures
    protected function createAuthenticatedClient($uid)
    {
        self::$client->jsonRequest(
            'POST',
            '/api/v1/login_check',
            [
                'email' => "test_case_$uid@email.com",
                'password' => "passwd$uid"
            ]
        );

        $data = json_decode(self::$client->getResponse()->getContent(), true);

        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_BEARER' => self::$client->getCookieJar()->get('BEARER')->getValue()
        ];
    }

    #[Test]
    #[TestDox('[api/v1/admin/user/delete] Trying to use endpoint without authentication')]
    public function testAdminDeleteUserRequiresJWT(): void
    {
        $crawler = self::$client->request('DELETE', "/api/v1/admin/user/delete");
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(401);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/admin/user/delete] Trying to use endpoint as user')]
    public function testAdminDeleteUserMustBeAdmin(): void
    {
        $headers = static::createAuthenticatedClient(3);
    
        $crawler = self::$client->jsonRequest('DELETE', "/api/v1/admin/user/delete", ['uid' => 67], $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(403);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminDeleteIncorrectValuesProvider')]
    #[TestDox('[api/v1/admin/user/delete] Trying incorrect request: $_dataName')]
    public function testAdminDeleteIncorrectValues(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('DELETE', "/api/v1/admin/user/delete", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminDeleteInvalidValuesProvider')]
    #[TestDox('[api/v1/admin/user/delete] Trying invalid request: $_dataName')]
    public function testAdminDeleteInvalidValues(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('DELETE', "/api/v1/admin/user/delete", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/admin/user/delete] Trying to delete self')]
    public function testAdminDeleteSelfRequest(): void
    {
        $headers = static::createAuthenticatedClient(20);

        $crawler = self::$client->jsonRequest('DELETE', "/api/v1/admin/user/delete", ['uid' => 21], $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/admin/user/delete] Trying to delete existing user')]
    public function testAdminDeleteRequest(): void
    {
        $headers = static::createAuthenticatedClient(20);

        $crawler = self::$client->jsonRequest('DELETE', "/api/v1/admin/user/delete", ['uid' => 93], $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }

    // api/v1/admin/topic/add
    #[Test]
    #[TestDox('[api/v1/admin/topic/add] Trying to use endpoint without authentication')]
    public function testAdminTopicAddRequiresJWT(): void
    {
        $crawler = self::$client->request('POST', "/api/v1/admin/topic/add");
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(401);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/admin/topic/add] Trying to use endpoint as user')]
    public function testAdminTopicAddMustBeAdmin(): void
    {
        $headers = static::createAuthenticatedClient(3);
    
        $crawler = self::$client->jsonRequest('POST', "/api/v1/admin/topic/add", ['title' => 'title', 'content' => 'content'], $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(403);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminTopicAddIncorrectValuesProvider')]
    #[TestDox('[api/v1/admin/topic/add] Trying incorrect request: $_dataName')]
    public function testAdminTopicAddIncorrectValues(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('POST', "/api/v1/admin/topic/add", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminTopicAddInvalidValuesProvider')]
    #[TestDox('[api/v1/admin/topic/add] Trying invalid request: $_dataName')]
    public function testAdminTopicAddInvalidValues(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('POST', "/api/v1/admin/topic/add", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/admin/topic/add] Trying to add topic')]
    public function testAdminTopicAddRequest(): void
    {
        $headers = static::createAuthenticatedClient(20);

        $crawler = self::$client->jsonRequest('POST', "/api/v1/admin/topic/add", ['title' => 'topic', 'content' => 'content'], $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($response->getContent());
    }



    public static function dataAdminDeleteIncorrectValuesProvider(): array
    {
        $params = [
            "empty" => [10, []],
            "invalid" => [10, ["x" => "1"]]
        ];

        return $params;
    }

    public static function dataAdminDeleteInvalidValuesProvider(): array
    {
        $params = [
            "too_big_1" => [10, ['uid' => 2007]],
            "too_big_2" => [10, ['uid' => 20007]],
            "too_small" => [20, ['uid' => -1]],
        ];

        return $params;
    }

    public static function dataAdminTopicAddIncorrectValuesProvider(): array
    {
        $params = [
            "empty" => [10, []],
            "invalid" => [10, ["x" => "1"]],
            "only_one1" => [10, ["title" => "1"]],
            "only_one2" => [10, ["content" => "1"]],
        ];

        return $params;
    }

    public static function dataAdminTopicAddInvalidValuesProvider(): array
    {
        $params = [
            "title_too_long" => [10, [
                "title" => "asasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasas",
                "content" => "content"]],
            "content_too_long" => [20, [
                "title" => "title",
                "content" => "asasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasassasa"
            ]],
        ];

        return $params;
    }

    
}