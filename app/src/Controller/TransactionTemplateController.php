<?php

namespace App\Controller;

use App\Dto\Transaction\TransactionUpdateDto;
use App\Dto\TransactionTemplate\TransactionTemplateCreateDto;
use App\Dto\TransactionTemplate\TransactionTemplateQueryDto;
use App\Dto\TransactionTemplate\TransactionTemplateUpdateDto;
use App\Service\TransactionService;
use App\Service\TransactionTemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;

#[Route('/api/transaction-templates')]
class TransactionTemplateController extends AbstractController
{

    /**
     * Finds transaction templates by category, payment type, transaction name, party name, transaction notes
     * - Example url: localhost:8080/api/transaction-templates?paymentType=cash&limit=5
     */
    #[Route('', name: 'api_find_transaction_templates', methods: ['GET'])]
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
            ]);
        }

        // Return found templates
        return $this->json([
            'success' => true,
            'data' => $data
        ]);

    }



    #[Route('', name: 'api_add_transaction_template', methods: ['POST'])]
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
    public function delete(int $id, TransactionTemplateService $transactionTemplateService): JsonResponse
    {
        $transactionTemplateService->delete($id);

        return $this->json([
            'success' => true,
            'message' => "Transaction template with id $id successfully deleted"
        ]);

    }

}