<?php

namespace App\Controller;

use App\Service\GrantService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("grant")
 */
class GrantController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/all", name="api_grant_all", methods={"GET"})
     *
     * @param Request $request
     * @param GrantService $grantService
     * @return JsonResponse
     */
    public function allAction(Request $request, GrantService $grantService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $grantService->getGrants([]),
            'api_grant_list'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/role", name="api_grant_role", methods={"POST"})
     *
     * @param Request $request
     * @param GrantService $grantService
     * @return JsonResponse
     */
    public function roleAction(Request $request, GrantService $grantService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $grantService->getGrantsByRoleIds($request->get('ids')),
            'api_grant_role'
        );
    }
}
