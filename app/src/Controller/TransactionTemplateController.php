<?php

namespace App\Controller;

use App\Dto\Transaction\TransactionUpdateDto;
use App\Dto\TransactionTemplate\TransactionTemplateCreateDto;
use App\Dto\TransactionTemplate\TransactionTemplateQueryDto;
use App\Dto\TransactionTemplate\TransactionTemplateUpdateDto;
use App\Service\TransactionService;
use App\Service\TransactionTemplateService;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;
use OpenApi\Attributes as OA;


#[Route('/api/transaction-templates')]
class TransactionTemplateController extends AbstractController
{

    /**
     * Finds transaction templates by category, payment type, transaction name, party name, transaction notes
     * - Example url: localhost:8080/api/transaction-templates?paymentType=cash&limit=5
     */
    #[Route('', name: 'api_find_transaction_templates', methods: ['GET'])]
    #[OA\Get(
        description: 'Returns array of transaction templates filtered by different parameters, if no parameters given it returns every transaction template for logged user',
        summary: 'Finds transaction templates by category, payment type, transaction name, party name and transaction notes',
        tags: ['Transaction Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/Template')
            ),
            new OA\Response(
                response: 404,
                description: 'No transaction templates found',
                content: new OA\JsonContent(ref: '#/components/schemas/TemplateNotFound')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryInputError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    public function search(
        #[MapQueryString] ?TransactionTemplateQueryDto $transactionTemplateQueryDto,
        TransactionTemplateService                     $transactionTemplateService
    ): JsonResponse
    {

        $data = $transactionTemplateService->search($transactionTemplateQueryDto);

        // If no templates are found
        if (empty($data['transactionTemplates'])) {
            return $this->json([
                'success' => false,
                'message' => 'No transaction template found'
            ],404);
        }

        // Return found templates
        return $this->json([
            'success' => true,
            'data' => $data
        ]);

    }



    #[Route('', name: 'api_add_transaction_template', methods: ['POST'])]
    #[OA\Post(
        description: 'Used for creating new transaction templates with current logged user as its owner',
        summary: "Creates an income/expense transaction template",
        tags: ['Transaction Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful transaction template creation',
                content: new OA\JsonContent(ref: '#/components/schemas/TemplateInputSuccess')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetInputError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid category given',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetInputFail')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    public function create(
        #[MapRequestPayload] TransactionTemplateCreateDto $transactionTemplateCreateDto,
        TransactionTemplateService                        $transactionTemplateService
    ): JsonResponse
    {
        $transactionTemplateService->create($transactionTemplateCreateDto);

        return $this->json([
            'success' => true,
            'message' => 'New transaction template created'
        ]);

    }


    #[Route('', name: 'api_update_transaction_templates', methods: ['PATCH'])]
    #[OA\Patch(
        description: "Make changes to any transaction template that is in ownership of logged in user",
        summary: "Makes changes to certain transaction template for logged user",
        tags: ['Transaction Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful transaction template update or nothing to change',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetUpdateSuccess')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetInputError')
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid category given or you do not have ownership of the template',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetInputFail')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    public function update(
        #[MapRequestPayload] TransactionTemplateUpdateDto $transactionUpdateDto,
        TransactionTemplateService                        $transactionTemplateService,
    ): JsonResponse
    {
        $message = $transactionTemplateService->update($transactionUpdateDto);

        return $this->json([
            'success' => true,
            'message' => $message
        ]);

    }


    #[Route('/{id<\d+>}', name: 'api_delete_transaction_template', methods: ['DELETE'])]
    #[OA\Delete(
        description: "Removes transaction template that is in ownership of logged user",
        summary: "Deletes specific transaction template based on given id",
        tags: ['Transaction Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful transaction template deletion',
                content: new OA\JsonContent(ref: '#/components/schemas/TemplateDeleteSuccess')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 404,
                description: 'Transaction template you selected is either not owned by you or does not exist',
                content: new OA\JsonContent(ref: '#/components/schemas/TemplateDeletionError')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )

        ]
    )]
    #[Security(name: 'Bearer')]
    public function delete(int $id, TransactionTemplateService $transactionTemplateService): JsonResponse
    {
        $transactionTemplateService->delete($id);

        return $this->json([
            'success' => true,
            'message' => "Transaction template with id $id successfully deleted"
        ]);

    }

}