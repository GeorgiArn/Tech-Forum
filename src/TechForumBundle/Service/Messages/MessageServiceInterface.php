<?php


namespace TechForumBundle\Service\Messages;


use Symfony\Component\Form\FormInterface;
use TechForumBundle\Entity\Message;

interface MessageServiceInterface
{
    public function create(Message $message, int $receiverId): bool;
    public function getMessagesByCurrentUser(): array;
    public function getMyMessages(): array;
    public function messageById(int $id): ?Message;
    public function update(Message $message): bool;
    public function readAllMessages(): void;
    public function delete(Message $message): bool;
    public function validateLength(FormInterface $form): bool;
}