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


final class UserControllerTest extends WebTestCase
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
    #[DataProvider('dataPublicProfileIncorrectUidProvider')]
    #[TestDox('[api/v1/user/get] Trying different nonexistant indices $_dataName')]
    public function testPublicProfileIncorrectUid(array $uid): void
    {
        $crawler = self::$client->jsonRequest('POST', "api/v1/user/get", $uid);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataPublicProfileCorrectUidProvider')]
    #[TestDox('[api/v1/user/get] Trying valid request $_dataName')]
    public function testPublicProfileCorrectUid(array $uid): void
    {
        $crawler = self::$client->jsonRequest('POST', "api/v1/user/get", $uid);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('[api/v1/user/me] Trying to use endpoint without authentication')]
    public function testUpdateMeRequiresJWT(): void
    {
        $crawler = self::$client->request('PATCH', "/api/v1/user/me");
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(500);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataUpdateMeChangeThingsProvider')]
    #[TestDox('[api/v1/user/me] Trying to change things about logged user: $_dataName')]
    public function testUpdateMeChangeThings(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        // $user_before = $this->repo->find($uid);

        $crawler = self::$client->jsonRequest('PATCH', "/api/v1/user/me", $rq, $headers);
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());

        $user_after = $this->repo->find($uid);

        return;

        // Hmmm...
        throw new \LogicException($user_after->getMotto());

        if (isset($rq["motto"]))
        {
            $this->assertNotSame($user_before->getMotto(), $user_after->getMotto());
        }
        else
        {
            $this->assertSame($user_before->getMotto(), $user_after->getMotto());
        }

        if (isset($rq["provenance"]))
        {
            $this->assertNotSame($user_before->getProvenance(), $user_after->getProvenance());
        }
        else
        {
            $this->assertSame($user_before->getProvenance(), $user_after->getProvenance());
        }

        if (isset($rq["password"]))
        {
            $this->assertNotSame($user_before->getPasshash(), $user_after->getPasshash());
        }
        else
        {
            $this->assertSame($user_before->getPasshash(), $user_after->getPasshash());
        }
    }

    public static function dataPublicProfileIncorrectUidProvider(): array
    {
        $params = [
            "big1" => ['uid' => 1024],
            "big2" => ['uid' => 100100],
            "negative" => ['uid' => -1]
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataPublicProfileCorrectUidProvider(): array
    {
        $params = [
            "correct1" => ['uid' => 1],
            "correct2" => ['uid' => 2],
            "correct3" => ['uid' => 10],
            "correct4" => ['uid' => 11],
            "correct5" => ['uid' => 53],
        ];

        array_walk($params, function (array &$item) { $item = array($item); });

        return $params;
    }

    public static function dataUpdateMeChangeThingsProvider(): array
    {
        $params = [
            "nothing" => [1, []],
            "motto" => [2, ["motto" => "something_different"]],
            "provenance" => [10, ["provenance" => "something_different"]],
            "password" => [11, ["password" => "something_different"]],
            "all" => [53, ["motto" => "something_different", "provenance" => "something_different", "password" => "something_different"]],
        ];

        return $params;
    }

    
}