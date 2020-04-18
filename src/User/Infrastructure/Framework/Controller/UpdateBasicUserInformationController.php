<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Infrastructure\Framework\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use LaSalle\StudentTeacher\User\Application\BasicUserInformation\Update\UpdateBasicUserInformationById;
use LaSalle\StudentTeacher\User\Application\BasicUserInformation\Update\UpdateBasicUserInformationByIdRequest;
use LaSalle\StudentTeacher\User\Application\Exception\UserAlreadyExistsException;
use LaSalle\StudentTeacher\User\Application\Exception\UserNotFoundException;
use Symfony\Component\HttpFoundation\Response;

final class UpdateBasicUserInformationController extends AbstractFOSRestController
{
    private UpdateBasicUserInformationById $updateUser;

    public function __construct(UpdateBasicUserInformationById $updateUser)
    {
        $this->updateUser = $updateUser;
    }

    /**
     * @Rest\Patch("/api/account/{id}")
     * @RequestParam(name="username")
     * @RequestParam(name="firstName")
     * @RequestParam(name="lastName")
     * @RequestParam(name="image")
     * @RequestParam(name="education")
     * @RequestParam(name="experience")
     */
    public function patchAction(ParamFetcher $paramFetcher, int $id)
    {
        $email = $paramFetcher->get('username');
        $firstName = $paramFetcher->get('firstName');
        $lastName = $paramFetcher->get('lastName');
        $image = $paramFetcher->get('image');
        $education = $paramFetcher->get('education');
        $experience = $paramFetcher->get('experience');

        if ($id !== $this->getUser()->getId()) {
            $view = $this->view(
                ['message' => 'You don\'t have permission to update this profile'],
                Response::HTTP_FORBIDDEN
            );
            return $this->handleView($view);
        }

        try {
            ($this->updateUser)(
                new UpdateBasicUserInformationByIdRequest(
                    $id, $email, $firstName, $lastName, $image, $experience, $education
                )
            );
        } catch (UserNotFoundException $e) {
            $view = $this->view(['message' => 'There\'s no user with such id'], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        } catch (UserAlreadyExistsException $e) {
            $view = $this->view(['message' => 'Your new email is already registered'], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }

        $view = $this->view(['message' => 'Your account has been successfully changed'], Response::HTTP_OK);
        return $this->handleView($view);
    }
}