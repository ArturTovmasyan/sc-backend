<?php

namespace App\Controller;

use App\Entity\HelpCategory;
use App\Service\HelpCategoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
 * @Route("/5766d45bdba1152105abfd9662e55140")
 */
class HelpController extends BaseController
{
    /**
     * @Route("", name="api_help_category_all", methods={"POST"})
     *
     * @param Request $request
     * @param HelpCategoryService $helpCategoryService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function allAction(Request $request, HelpCategoryService $helpCategoryService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $helpCategoryService->all(['permissions' => $request->get('permissions')]),
            ['api_help_category_all']
        );
    }
}
