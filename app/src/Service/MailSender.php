<?php
namespace App\Service;

// use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Psr\Log\LoggerInterface;
use App\Entity\User;
use App\Message\UserDataForEmailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MailSender/* implements MessageHandlerInterface */
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(UserDataForEmailMessage $userDataForEmail): void
    {
        if (mail(
            $userDataForEmail->getEmail(),
            "Welcome!",
            <<<MESSAGE
                Thank you for registering on Physcussions forum!
                Hope you will enjoy your stay!
                Remember - stay respectful to your fellow users!
            MESSAGE,
            [
                'From' => 'physcussions.no.reply@example.com',
                'X-Mailer' => 'PHP/' . phpversion()
            ]
        ))
        {
            $this->logger->info('Sent mail to ' . $userDataForEmail->getEmail());
        }
        else
        {
            $error = error_get_last();
            if ($error !== null && isset($error['message']))
            {
                $this->logger->error('Couldn\'t send mail to ' . $userDataForEmail->getEmail() . ': ' . $error['message']);
            }
            else
            {
                $this->logger->error('Couldn\'t send mail to ' . $userDataForEmail->getEmail() . ': Unidetifiable error');
            }
        }
    }
}