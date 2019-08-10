<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\Message;
use TechForumBundle\Form\MessageType;
use TechForumBundle\Service\Messages\MessageService;
use TechForumBundle\Service\Users\UserService;

class MessageController extends Controller
{
    private $messageService;
    private $userService;

    public function __construct(MessageService $messageService,
                                UserService $userService)
    {
        $this->messageService = $messageService;
        $this->userService = $userService;
    }

    /**
     * @Route("/profile/{id}", name = "create_message", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param Request $request
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function create(Request $request, int $id)
    {
        $currentUser = $this->userService->currentUser();
        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        try {
            $this->messageService->validateLength($form);
        } catch (\Exception $ex) {
            $this->addFlash("errors", $ex->getMessage());
            return $this->redirectToRoute('user_profile', ['id' => $id]);
        }

        $this->messageService->create($message, $id);

        $this->userService->addUnreadMessage($message->getReceiver());
        $this->userService->edit($currentUser);

        $this->addFlash("infos", "Successfully sent message!");
        return $this->redirectToRoute('user_profile', ['id' => $id]);
    }

    /**
     * @Route("/mailbox", name="view_mailbox", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return Response
     */
    public function mailbox()
    {
        $messages = $this->messageService->getMessagesByCurrentUser();
        $this->messageService->readAllMessages();
        $this->userService->removeUnreadMessages();
        $this->userService->edit($this->userService->currentUser());

        return $this->render('message/all_messages.html.twig',
            ['messages' => $messages]);
    }

    /**
     * @Route("message/sent_messages", name="sent_messages", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return Response
     */
    public function sentMessages()
    {
        $messages = $this->messageService->getMyMessages();

        return $this->render('message/sent_messages.html.twig',
            ['messages' => $messages]);
    }

    /**
     * @param int $id
     *
     * @Route("/message/edit/{id}", name="edit_message", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return RedirectResponse|Response
     */
    public function edit(int $id)
    {
        $message = $this->messageService->messageById($id);

        if ($message === null) {
            return $this->redirectToRoute('forum_index');
        }

        $currentUser = $this->userService->currentUser();

        if (!$currentUser->isAuthorOnMessage($message)) {
            return $this->redirectToRoute('forum_index');
        }

        return $this->render('message/edit.html.twig',
            [
                'message' => $message,
                'form' => $this->createForm(MessageType::class)->createView()
            ]);
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @Route("/message/edit/{id}", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return RedirectResponse|Response
     */
    public function editProcess(int $id, Request $request)
    {
        $message = $this->messageService->messageById($id);

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        try {
            $this->messageService->validateLength($form);
        } catch (\Exception $ex) {
            $this->addFlash("errors", $ex->getMessage());
            return $this->redirectToRoute('edit_message', ['id' => $id]);
        }

        $this->messageService->update($message);

        $this->addFlash("infos", "Successfully edited message!");
        return $this->redirectToRoute('sent_messages');
    }

    /**
     * @Route("/message/delete/{id}", name = "message_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(int $id)
    {
        $message = $this->messageService->messageById($id);
        if ($message === null) {
            return $this->redirectToRoute('forum_index');
        }

        $currentUser = $this->userService->currentUser();
        if (!$currentUser->isAuthorOnMessage($message)) {
            return $this->redirectToRoute('forum_index');
        }

        $this->messageService->delete($message);
        $this->userService->removeUnreadMessages();
        $this->userService->edit($currentUser);

        $this->addFlash("infos", "Successfully deleted message!");
        return $this->redirectToRoute('sent_messages');
    }
}
