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

    #[Test]
    #[TestDox('Trying to use endpoint without authentication')]
    public function testDeleteUserRequiresJWT(): void
    {
        $crawler = self::$client->request('PATCH', "/api/v1/user/me");
        $response = self::$client->getResponse();

        $this->assertResponseStatusCodeSame(500);
        $this->assertJson($response->getContent());
    }
    
}