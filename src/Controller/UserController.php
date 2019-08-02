<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/user")
 */
class UserController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_user_grid", methods={"GET"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function gridAction(Request $request, UserService $userService)
    {
        return $this->respondGrid(
            $request,
            User::class,
            'api_user_grid',
            $userService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_user_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(
            User::class,
            'api_user_grid'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_user_list", methods={"GET"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function listAction(Request $request, UserService $userService)
    {
        return $this->respondList(
            $request,
            User::class,
            'api_user_list',
            $userService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_user_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAction(Request $request, $id, UserService $userService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $userService->getById($id),
            ['api_user_get']
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_user_add", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, UserService $userService)
    {
        $id = $userService->add(
            [
                'role' => $request->get('role'),
                'username' => $request->get('username'),
                'full_name' => $request->get('full_name'),
                'enabled' => $request->get('enabled'),
                'password' => $request->get('password'),
            ]
        );

        return $this->respondSuccess(
            JsonResponse::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_user_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, UserService $userService)
    {
        $userService->edit(
            $id,
            [
                'role' => $request->get('role'),
                'username' => $request->get('username'),
                'full_name' => $request->get('full_name'),
                'enabled' => $request->get('enabled'),
                'password' => $request->get('password'),
            ]
        );

        return $this->respondSuccess(
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_user_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, UserService $userService)
    {
        $userService->remove($id);

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_user_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, UserService $userService)
    {
        $userService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/me", name="api_user_me", methods={"GET"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function meAction(Request $request, UserService $userService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $this->getUser(),
            ['api_user_get']
        );
    }
}
