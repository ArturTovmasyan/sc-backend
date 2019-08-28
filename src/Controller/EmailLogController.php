<?php

namespace App\Controller;

use App\Entity\EmailLog;
use App\Service\EmailLogService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/email-log")
 */
class EmailLogController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_email_log_grid", methods={"GET"})
     *
     * @param Request $request
     * @param EmailLogService $email_logService
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function gridAction(Request $request, EmailLogService $email_logService)
    {
        return $this->respondGrid(
            $request,
            EmailLog::class,
            'api_email_log_grid',
            $email_logService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_email_log_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(
            EmailLog::class,
            'api_email_log_grid'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_email_log_list", methods={"GET"})
     *
     * @param Request $request
     * @param EmailLogService $email_logService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function listAction(Request $request, EmailLogService $email_logService)
    {
        return $this->respondList(
            $request,
            EmailLog::class,
            'api_email_log_list',
            $email_logService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_email_log_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param EmailLogService $email_logService
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAction(Request $request, $id, EmailLogService $email_logService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $email_logService->getById($id),
            ['api_email_log_get']
        );
    }

}
