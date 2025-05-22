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
            '/api/login_check',
            [
                'email' => "test_case_$i@email.com",
                'password' => "passwd$i"
            ]
        );

        $data = json_decode(self::$client->getResponse()->getContent(), true);

        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $data['token']
        ];
    }

    #[Test]
    #[DataProvider('dataPublicProfileIncorrectUidProvider')]
    #[TestDox('Trying different nonexistant indices $_dataName')]
    public function testPublicProfileIncorrectUid(int $uid): void
    {
        $crawler = self::$client->request('GET', "api/user/$uid");
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataPublicProfileCorrectUidProvider')]
    #[TestDox('Trying valid request $_dataName')]
    public function testPublicProfileCorrectUid(int $uid): void
    {
        $crawler = self::$client->request('GET', "api/user/$uid");
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[TestDox('Trying to use endpoint without authentication')]
    public function testUpdateMeRequiresJWT(): void
    {
        $crawler = self::$client->request('PATCH', "/api/user/me");
        $response = self::$client->getResponse();

        // TODO
        $this->assertResponseStatusCodeSame(500);
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[DataProvider('dataUpdateMeChangeThingsProvider')]
    #[TestDox('Trying to change things about logged user: $_dataName')]
    public function testUpdateMeChangeThings(int $uid, array $rq): void
    {
        $headers = static::createAuthenticatedClient($uid);

        $user_before = $this->repo->find($uid);

        $crawler = self::$client->jsonRequest('PATCH', "/api/user/me", $rq, $headers);
        $response = self::$client->getResponse();

        // TODO
        $this->assertResponseStatusCodeSame(500);
        $this->assertJson($response->getContent());

        $user_after = $this->repo->find($uid);

        return;

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
            "big1" => [1024],
            "big2" => [100100],
            "negative" => [-1]
        ];

        return $params;
    }

    public static function dataPublicProfileCorrectUidProvider(): array
    {
        $params = [
            "correct1" => [1],
            "correct2" => [2],
            "correct3" => [10],
            "correct4" => [11],
            "correct5" => [53],
        ];

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