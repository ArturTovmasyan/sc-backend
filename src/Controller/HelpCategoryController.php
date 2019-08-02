<?php

namespace App\Controller;

use App\Entity\HelpCategory;
use App\Service\HelpCategoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/help-category")
 */
class HelpCategoryController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_help_category_grid", methods={"GET"})
     *
     * @param Request $request
     * @param HelpCategoryService $helpCategoryService
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function gridAction(Request $request, HelpCategoryService $helpCategoryService)
    {
        return $this->respondGrid(
            $request,
            HelpCategory::class,
            'api_help_category_grid',
            $helpCategoryService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_help_category_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(
            HelpCategory::class,
            'api_help_category_grid'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_help_category_list", methods={"GET"})
     *
     * @param Request $request
     * @param HelpCategoryService $helpCategoryService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function listAction(Request $request, HelpCategoryService $helpCategoryService)
    {
        return $this->respondList(
            $request,
            HelpCategory::class,
            'api_help_category_list',
            $helpCategoryService,
            [
                'permissions' => $request->get('permissions')
            ]
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_help_category_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param HelpCategoryService $helpCategoryService
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAction(Request $request, $id, HelpCategoryService $helpCategoryService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $helpCategoryService->getById($id),
            ['api_help_category_get']
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_help_category_add", methods={"POST"})
     *
     * @param Request $request
     * @param HelpCategoryService $helpCategoryService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, HelpCategoryService $helpCategoryService)
    {
        $id = $helpCategoryService->add(
            [
                'title' => $request->get('title'),
                'grants' => $request->get('grants'),
                'grant_inherit' => $request->get('grant_inherit'),
                'parent_id' => $request->get('parent_id')
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_help_category_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param HelpCategoryService $helpCategoryService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, HelpCategoryService $helpCategoryService)
    {
        $helpCategoryService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'grants' => $request->get('grants'),
                'grant_inherit' => $request->get('grant_inherit'),
                'parent_id' => $request->get('parent_id')
            ]
        );

        return $this->respondSuccess(
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_help_category_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param HelpCategoryService $helpCategoryService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, HelpCategoryService $helpCategoryService)
    {
        $helpCategoryService->remove($id);

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_help_category_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param HelpCategoryService $helpCategoryService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, HelpCategoryService $helpCategoryService)
    {
        $helpCategoryService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }

}
