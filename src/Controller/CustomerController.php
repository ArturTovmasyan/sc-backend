<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Service\CustomerService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/customer")
 */
class CustomerController extends BaseController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_customer_grid", methods={"GET"})
     *
     * @param Request $request
     * @param CustomerService $customerService
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function gridAction(Request $request, CustomerService $customerService)
    {
        return $this->respondGrid(
            $request,
            Customer::class,
            'api_customer_grid',
            $customerService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grid", name="api_customer_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(
            Customer::class,
            'api_customer_grid'
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_customer_list", methods={"GET"})
     *
     * @param Request $request
     * @param CustomerService $customerService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function listAction(Request $request, CustomerService $customerService)
    {
        return $this->respondList(
            $request,
            Customer::class,
            'api_customer_list',
            $customerService
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_customer_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param CustomerService $customerService
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAction(Request $request, $id, CustomerService $customerService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $customerService->getById($id),
            ['api_customer_get']
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_customer_add", methods={"POST"})
     *
     * @param Request $request
     * @param CustomerService $customerService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, CustomerService $customerService)
    {
        $id = $customerService->add(
            [
                'domain' => $request->get('domain'),
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'phone' => $request->get('phone'),
                'address' => $request->get('address'),
                'csz' => $request->get('csz'),
                'email' => $request->get('email'),
                'organization' => $request->get('organization'),
                'enable_ledger_commands' => $request->get('enable_ledger_commands'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_customer_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param CustomerService $customerService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, CustomerService $customerService)
    {
        $customerService->edit(
            $id,
            [
                'domain' => $request->get('domain'),
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'phone' => $request->get('phone'),
                'address' => $request->get('address'),
                'csz' => $request->get('csz'),
                'email' => $request->get('email'),
                'organization' => $request->get('organization'),
                'enable_ledger_commands' => $request->get('enable_ledger_commands'),
            ]
        );

        return $this->respondSuccess(
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_customer_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param CustomerService $customerService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, CustomerService $customerService)
    {
        $customerService->remove($id);

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("", name="api_customer_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param CustomerService $customerService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, CustomerService $customerService)
    {
        $customerService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            JsonResponse::HTTP_NO_CONTENT
        );
    }
}
