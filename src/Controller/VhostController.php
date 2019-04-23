<?php

namespace App\Controller;

use App\Entity\Vhost;
use App\Service\VhostService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/vhost")
 */
class VhostController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_vhost_grid", methods={"GET"})
     *
     * @param Request $request
     * @param VhostService $vhostService
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, VhostService $vhostService)
    {
        return $this->respondGrid(
            $request,
            Vhost::class,
            'api_vhost_grid',
            $vhostService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_vhost_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(
            Vhost::class,
            'api_vhost_grid'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_vhost_list", methods={"GET"})
     *
     * @param Request $request
     * @param VhostService $vhostService
     * @return JsonResponse
     * @throws \Exception
     */
    public function listAction(Request $request, VhostService $vhostService)
    {
        return $this->respondList(
            $request,
            Vhost::class,
            'api_vhost_list',
            $vhostService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_vhost_get", methods={"GET"})
     *
     * @param VhostService $vhostService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, VhostService $vhostService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $vhostService->getById($id),
            ['api_vhost_get']
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_vhost_add", methods={"POST"})
     *
     * @param Request $request
     * @param VhostService $vhostService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, VhostService $vhostService)
    {
        $id = $vhostService->add(
            [
                'db_name' => $request->get('db_name'),
                'db_user' => $request->get('db_user'),
                'db_password' => $request->get('db_password'),
                'email' => $request->get('email'),
                'path' => $request->get('path'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_vhost_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param VhostService $vhostService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, VhostService $vhostService)
    {
        $vhostService->edit(
            $id,
            [
                'db_name' => $request->get('db_name'),
                'db_user' => $request->get('db_user'),
                'db_password' => $request->get('db_password'),
                'email' => $request->get('email'),
                'path' => $request->get('path'),
            ]
        );

        return $this->respondSuccess(
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_vhost_delete", methods={"DELETE"})
     *
     * @param $id
     * @param VhostService $vhostService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, VhostService $vhostService)
    {
        $vhostService->remove($id);

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_vhost_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param VhostService $vhostService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, VhostService $vhostService)
    {
        $vhostService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }
}
