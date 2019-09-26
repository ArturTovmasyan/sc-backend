<?php

namespace App\Controller;

use App\Service\ConfigService;
use App\Service\FeedbackService;
use App\Service\HelpCategoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
 * @Route("/5766d45bdba1152105abfd9662e55140")
 */
class AnonymousController extends BaseController
{
    /**
     * @Route("/help", name="api_help_category_all", methods={"POST"})
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

    /**
     * @Route("/config", name="api_global_config", methods={"GET"})
     *
     * @param Request $request
     * @param ConfigService $configService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function configAction(Request $request, ConfigService $configService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $configService->assoc(),
            ['api_global_config']
        );
    }

    /**
     * @Route("/feedback", name="api_feedback", methods={"POST"})
     *
     * @param Request $request
     * @param FeedbackService $feedbackService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function feedbackAction(Request $request, FeedbackService $feedbackService)
    {
        $id = $feedbackService->add(
            [
                'domain' => $request->get('domain'),
                'username' => $request->get('username'),
                'email' => $request->get('email'),
                'full_name' => $request->get('full_name'),
                'subject' => $request->get('subject'),
                'message' => $request->get('message'),
                'date' => $request->get('date'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }
}
