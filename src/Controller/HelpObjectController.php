<?php

namespace App\Controller;

use App\Entity\HelpObject;
use App\Service\HelpObjectService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/help-object")
 */
class HelpObjectController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_help_object_grid", methods={"GET"})
     *
     * @param Request $request
     * @param HelpObjectService $helpObjectService
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function gridAction(Request $request, HelpObjectService $helpObjectService)
    {
        return $this->respondGrid(
            $request,
            HelpObject::class,
            'api_help_object_grid',
            $helpObjectService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_help_object_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(
            HelpObject::class,
            'api_help_object_grid'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_help_object_list", methods={"GET"})
     *
     * @param Request $request
     * @param HelpObjectService $helpObjectService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function listAction(Request $request, HelpObjectService $helpObjectService)
    {
        return $this->respondList(
            $request,
            HelpObject::class,
            'api_help_object_list',
            $helpObjectService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_help_object_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param HelpObjectService $helpObjectService
     * @return JsonResponse
     * @throws NonUniqueResultException
     */
    public function getAction(Request $request, $id, HelpObjectService $helpObjectService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $helpObjectService->getById($id),
            ['api_help_object_get']
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_help_object_add", methods={"POST"})
     *
     * @param Request $request
     * @param HelpObjectService $helpObjectService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, HelpObjectService $helpObjectService)
    {
        $id = $helpObjectService->add(
            [
                'title' => $request->get('title'),
                'description' => $request->get('description'),
                'type' => $request->get('type'),
                'grants' => $request->get('grants'),
                'grant_inherit' => $request->get('grant_inherit'),
                'category_id' => $request->get('category_id')
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_help_object_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param HelpObjectService $helpObjectService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, HelpObjectService $helpObjectService)
    {
        $helpObjectService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'description' => $request->get('description'),
                'type' => $request->get('type'),
                'grants' => $request->get('grants'),
                'grant_inherit' => $request->get('grant_inherit'),
                'category_id' => $request->get('category_id')
            ]
        );

        return $this->respondSuccess(
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_help_object_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param HelpObjectService $helpObjectService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, HelpObjectService $helpObjectService)
    {
        $helpObjectService->remove($id);

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_help_object_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param HelpObjectService $helpObjectService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, HelpObjectService $helpObjectService)
    {
        $helpObjectService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }
}
