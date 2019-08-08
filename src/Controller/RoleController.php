<?php

namespace App\Controller;

use App\Entity\Role;
use App\Service\GrantService;
use App\Service\RoleService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/role")
 */
class RoleController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_role_grid", methods={"GET"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function gridAction(Request $request, RoleService $roleService)
    {
        return $this->respondGrid(
            $request,
            Role::class,
            'api_role_grid',
            $roleService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_role_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Role::class, 'api_role_grid');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_role_list", methods={"GET"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function listAction(Request $request, RoleService $roleService)
    {
        return $this->respondList(
            $request,
            Role::class,
            'api_role_list',
            $roleService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_role_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @param GrantService $grantService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, RoleService $roleService, GrantService $grantService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $roleService->getById($id, $grantService),
            ['api_role_get']
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_role_add", methods={"POST"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, RoleService $roleService)
    {
        $id = $roleService->add(
            [
                'name' => $request->get('name'),
                'grants' => $request->get('grants')
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_role_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, RoleService $roleService)
    {
        $roleService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'grants' => $request->get('grants')
            ]
        );

        return $this->respondSuccess(
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_role_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, RoleService $roleService)
    {
        $roleService->remove($id);

        return $this->respondSuccess(JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_role_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, RoleService $roleService)
    {
        $roleService->removeBulk($request->get('ids'));

        return $this->respondSuccess(JsonResponse::HTTP_NO_CONTENT);
    }
}