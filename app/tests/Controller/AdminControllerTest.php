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


    // api/v1/admin/topic/add
    #[Test]
    #[TestDox('[api/v1/admin/topic/delete] Trying to use endpoint without authentication')]
    public function testAdminTopicDeleteRequiresJWT(): void
    {
        $crawler = self::$client->request('DELETE', "/api/v1/admin/topic/delete");
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(401);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/admin/topic/delete] Trying to use endpoint as user')]
    public function testAdminTopicDeleteMustBeAdmin(): void
    {
        $headers = static::createAuthenticatedClient(3);
    
        $crawler = self::$client->jsonRequest('DELETE', "/api/v1/admin/topic/delete", ['tid' => 1], $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(403);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminTopicDeleteInvalidValuesProvider')]
    #[TestDox('[api/v1/admin/topic/delete] Trying invalid request: $_dataName')]
    public function testAdminTopicDeleteInvalidValues(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('DELETE', "/api/v1/admin/topic/delete", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/admin/topic/delete] Trying to delete topic')]
    public function testAdminTopicDeleteRequest(): void
    {
        $headers = static::createAuthenticatedClient(20);

        $crawler = self::$client->jsonRequest('DELETE', "/api/v1/admin/topic/delete", ['tid' => 2], $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }


    // api/v1/admin/topic/edit
    #[Test]
    #[TestDox('[api/v1/admin/topic/edit] Trying to use endpoint without authentication')]
    public function testAdminTopicEditRequiresJWT(): void
    {
        $crawler = self::$client->request('PATCH', "/api/v1/admin/topic/edit");
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(401);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/admin/topic/edit] Trying to use endpoint as user')]
    public function testAdminTopicEditMustBeAdmin(): void
    {
        $headers = static::createAuthenticatedClient(3);
    
        $crawler = self::$client->jsonRequest('PATCH', "/api/v1/admin/topic/edit", ['tid' => 1, 'title' => "new_title"], $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(403);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminTopicEditIncorrectValuesProvider')]
    #[TestDox('[api/v1/admin/topic/edit] Trying incorrect request: $_dataName')]
    public function testAdminTopicEditIncorrectValues(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('PATCH', "/api/v1/admin/topic/edit", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminTopicEditInvalidValuesProvider')]
    #[TestDox('[api/v1/admin/topic/add] Trying invalid request: $_dataName')]
    public function testAdminTopicEditInvalidValues(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('PATCH', "/api/v1/admin/topic/edit", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminTopicEditRequestProvider')]
    #[TestDox('[api/v1/admin/topic/add] Try various modifications of topic: $_dataName')]
    public function testAdminTopicEditRequest(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('PATCH', "/api/v1/admin/topic/edit", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }


    // api/v1/admin/post/edit
    #[Test]
    #[TestDox('[api/v1/admin/post/edit] Trying to use endpoint without authentication')]
    public function testAdminPostEditRequiresJWT(): void
    {
        $crawler = self::$client->request('PATCH', "/api/v1/admin/post/edit");
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(401);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/admin/post/edit] Trying to use endpoint as user')]
    public function testAdminPostEditMustBeAdmin(): void
    {
        $headers = static::createAuthenticatedClient(3);
    
        $crawler = self::$client->jsonRequest('PATCH', "/api/v1/admin/post/edit", ['pid' => 11, 'archived' => true], $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(403);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminPostEditIncorrectValuesProvider')]
    #[TestDox('[api/v1/admin/post/edit] Trying incorrect request: $_dataName')]
    public function testAdminPostEditIncorrectValues(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('PATCH', "/api/v1/admin/post/edit", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataAdminPostEditRequestProvider')]
    #[TestDox('[api/v1/admin/post/edit] Try various modifications of post: $_dataName')]
    public function testAdminPostEditRequest(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $crawler = self::$client->jsonRequest('PATCH', "/api/v1/admin/post/edit", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(200);
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

    public static function dataAdminTopicDeleteInvalidValuesProvider(): array
    {
        $params = [
            "too_big_1" => [10, ["tid" => 1000]],
            "too_big_2" => [10, ["tid" => 2140414]],
            "negative" => [10, ["tid" => -1]]
        ];

        return $params;
    }

    public static function dataAdminTopicEditIncorrectValuesProvider(): array
    {
        $params = [
            "too_big_1" => [10, ["tid" => 1000]],
            "too_big_2" => [10, ["tid" => 2140414]],
            "negative" => [10, ["tid" => -1]]
        ];

        return $params;
    }

    public static function dataAdminTopicEditInvalidValuesProvider(): array
    {
        $params = [
            "title_too_long" => [10, [
                "tid" => 1,
                "title" => "asasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasas",
            ]],
            "content_too_long" => [20, [
                "tid" => 1,
                "content" => "asasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasasassasa"
            ]],
        ];

        return $params;
    }
    
    public static function dataAdminTopicEditRequestProvider(): array
    {
        $params = [
            "edit_title" => [10, [
                'tid' => 1,
                'title' => "new_title"
            ]],
            "edit_content" => [10, [
                'tid' => 1,
                'title' => "new_content"
            ]],
            "edit_archived" => [10, [
                'tid' => 1,
                'archived' => "true"
            ]]
        ];

        return $params;
    }    

    public static function dataAdminPostEditIncorrectValuesProvider(): array
    {
        $params = [
            "too_big_1" => [10, ["pid" => 1000]],
            "too_big_2" => [10, ["pid" => 2140414]],
            "negative" => [10, ["pid" => -1]]
        ];

        return $params;
    }

    public static function dataAdminPostEditRequestProvider(): array
    {
        $params = [
            "edit_archived_true" => [10, [
                'pid' => 11,
                'archived' => true
            ]],
            "edit_closed_true" => [10, [
                'pid' => 11,
                'closed' => true
            ]],
            "edit_archived_true" => [10, [
                'pid' => 13,
                'archived' => false
            ]],
            "edit_closed_true" => [10, [
                'pid' => 13,
                'closed' => false
            ]]
        ];

        return $params;
    }   

}