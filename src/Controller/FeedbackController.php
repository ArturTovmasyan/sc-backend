<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Service\FeedbackService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/feedback")
 */
class FeedbackController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_feedback_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FeedbackService $feedbackService
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function gridAction(Request $request, FeedbackService $feedbackService)
    {
        return $this->respondGrid(
            $request,
            Feedback::class,
            'api_feedback_grid',
            $feedbackService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_feedback_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(
            Feedback::class,
            'api_feedback_grid'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_feedback_list", methods={"GET"})
     *
     * @param Request $request
     * @param FeedbackService $feedbackService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function listAction(Request $request, FeedbackService $feedbackService)
    {
        return $this->respondList(
            $request,
            Feedback::class,
            'api_feedback_list',
            $feedbackService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_feedback_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param FeedbackService $feedbackService
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAction(Request $request, $id, FeedbackService $feedbackService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $feedbackService->getById($id),
            ['api_feedback_get']
        );
    }

}
