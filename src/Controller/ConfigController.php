<?php

namespace App\Controller;

use App\Entity\Config;
use App\Service\ConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/config")
 */
class ConfigController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_config_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ConfigService $configService
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function gridAction(Request $request, ConfigService $configService)
    {
        return $this->respondGrid(
            $request,
            Config::class,
            'api_config_grid',
            $configService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_config_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(
            Config::class,
            'api_config_grid'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_config_list", methods={"GET"})
     *
     * @param Request $request
     * @param ConfigService $configService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function listAction(Request $request, ConfigService $configService)
    {
        return $this->respondList(
            $request,
            Config::class,
            'api_config_list',
            $configService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_config_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ConfigService $configService
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAction(Request $request, $id, ConfigService $configService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $configService->getById($id),
            ['api_config_get']
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_config_add", methods={"POST"})
     *
     * @param Request $request
     * @param ConfigService $configService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ConfigService $configService)
    {
        $id = $configService->add(
            [
                'name' => $request->get('name'),
                'value' => $request->get('value')
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_config_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ConfigService $configService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ConfigService $configService)
    {
        $configService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'value' => $request->get('value')
            ]
        );

        return $this->respondSuccess(
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_config_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param ConfigService $configService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ConfigService $configService)
    {
        $configService->remove($id);

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_config_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ConfigService $configService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ConfigService $configService)
    {
        $configService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }
}
