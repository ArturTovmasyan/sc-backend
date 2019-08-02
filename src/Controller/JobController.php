<?php

namespace App\Controller;

use App\Entity\Job;
use App\Service\JobService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/job")
 */
class JobController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_job_grid", methods={"GET"})
     *
     * @param Request $request
     * @param JobService $jobService
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function gridAction(Request $request, JobService $jobService)
    {
        return $this->respondGrid(
            $request,
            Job::class,
            'api_job_grid',
            $jobService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_job_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(
            Job::class,
            'api_job_grid'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_job_list", methods={"GET"})
     *
     * @param Request $request
     * @param JobService $jobService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function listAction(Request $request, JobService $jobService)
    {
        return $this->respondList(
            $request,
            Job::class,
            'api_job_list',
            $jobService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_job_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param JobService $jobService
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAction(Request $request, $id, JobService $jobService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $jobService->getById($id),
            ['api_job_get']
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_job_add", methods={"POST"})
     *
     * @param Request $request
     * @param JobService $jobService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, JobService $jobService)
    {
        $id = $jobService->add(
            [
                'customer_id' => $request->get('customer_id'),
                'type' => $request->get('type'),
                'log' => $request->get('log')
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_job_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param JobService $jobService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, JobService $jobService)
    {
        $jobService->edit(
            $id,
            [
                'customer_id' => $request->get('customer_id'),
                'type' => $request->get('type'),
                'log' => $request->get('log')
            ]
        );

        return $this->respondSuccess(
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_job_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param JobService $jobService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, JobService $jobService)
    {
        $jobService->remove($id);

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_job_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param JobService $jobService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, JobService $jobService)
    {
        $jobService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }
}
