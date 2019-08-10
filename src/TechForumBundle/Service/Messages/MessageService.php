<?php


namespace TechForumBundle\Service\Messages;

use Symfony\Component\Form\FormInterface;
use TechForumBundle\Entity\Message;
use TechForumBundle\Repository\MessageRepository;
use TechForumBundle\Service\Users\UserService;

class MessageService implements MessageServiceInterface
{
    private $messageRepository;
    private $userService;

    public function __construct(MessageRepository $messageRepository,
                                UserService $userService)
    {
        $this->messageRepository = $messageRepository;
        $this->userService = $userService;
    }

    public function create(Message $message, int $receiverId): bool
    {
        $sender = $this->userService->currentUser();

        $receiver = $this->userService->userById($receiverId);

        $message->setSender($sender);
        $message->setReceiver($receiver);

        return $this->messageRepository->insert($message);
    }

    public function getMessagesByCurrentUser(): array
    {
        $currentUser = $this->userService->currentUser();

        $messages = $this->messageRepository
            ->findBy(
                ['receiver' => $currentUser],
                ['dateAdded' => 'DESC']);

        return $messages;
    }

    public function getMyMessages(): array
    {
        $currentUser = $this->userService->currentUser();

        $messages = $this->messageRepository
            ->findBy(
                ['sender' => $currentUser],
                ['dateAdded' => 'DESC']);

        return $messages;
    }

    /**
     * @param int $id
     * @return Message|null|object
     */
    public function messageById(int $id): ?Message
    {
        $message = $this->messageRepository
            ->find($id);

        return $message;
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function update(Message $message): bool
    {
        return $this->messageRepository->merge($message);
    }

    public function readAllMessages(): void
    {
        $messages = $this->getMessagesByCurrentUser();

        /** @var Message $message */
        foreach ($messages as $message) {
            $message->setIsRead(true);
            $this->messageRepository->merge($message);
        }
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function delete(Message $message): bool
    {
        return $this->messageRepository->remove($message);
    }

    /**
     * @param FormInterface $form
     * @return bool
     * @throws \Exception
     */
    public function validateLength(FormInterface $form): bool
    {
        if (strlen($form['content']->getData()) < 2) {
            throw new \Exception("The message must be at least 2 symbols long!");
        }

        return true;
    }
}