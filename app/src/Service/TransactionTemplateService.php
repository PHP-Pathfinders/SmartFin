<?php

namespace App\Service;

use App\Dto\TransactionTemplate\TransactionTemplateCreateDto;
use App\Dto\TransactionTemplate\TransactionTemplateQueryDto;
use App\Dto\TransactionTemplate\TransactionTemplateUpdateDto;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\TransactionTemplateRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class TransactionTemplateService
{

    public function __construct(
        private Security                      $security,
        private TransactionTemplateRepository $transactionTemplateRepository,
        private CategoryRepository            $categoryRepository
    )
    {
    }


    public function search(?TransactionTemplateQueryDto $transactionTemplateQueryDto): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->transactionTemplateRepository->search($transactionTemplateQueryDto, $user);

    }

    public function create(TransactionTemplateCreateDto $transactionTemplateCreateDto): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $category = $transactionTemplateCreateDto->categoryId ? $this->categoryRepository->findByIdAndUser($transactionTemplateCreateDto->categoryId, $user) : null;


        $this->transactionTemplateRepository->create($transactionTemplateCreateDto, $user, $category);

    }

    public function update(TransactionTemplateUpdateDto $transactionTemplateUpdateDto): string
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $id = $transactionTemplateUpdateDto->id;
        $transactionName = $transactionTemplateUpdateDto->transactionName;
        $category = $transactionTemplateUpdateDto->categoryId ? $this->categoryRepository->findByIdAndUser($transactionTemplateUpdateDto->categoryId, $user) : null;
        $paymentType = $transactionTemplateUpdateDto->paymentType;
        $partyName = $transactionTemplateUpdateDto->partyName;
        $transactionNotes = $transactionTemplateUpdateDto->transactionNotes;
        $moneyAmount = $transactionTemplateUpdateDto->moneyAmount;


        if (!$transactionName && !$category && !$paymentType && !$partyName && !$transactionNotes && !$moneyAmount) {
            return 'Nothing to update';
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $this->transactionTemplateRepository->update(
            $id, $transactionName, $category, $paymentType, $partyName, $transactionNotes, $moneyAmount, $user
        );

        return 'Update successful';

    }

    public function delete($id): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $this->transactionTemplateRepository->delete($id, $user);

    }


}