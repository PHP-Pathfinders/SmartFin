<?php

namespace App\Service;

use App\Dto\TransactionTemplate\TransactionTemplateCreateDto;
use App\Dto\TransactionTemplate\TransactionTemplateQueryDto;
use App\Dto\TransactionTemplate\TransactionTemplateUpdateDto;
use App\Entity\Category;
use App\Entity\TransactionTemplate;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\TransactionTemplateRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

        if (null === $transactionTemplateQueryDto) {
            throw new NotFoundHttpException('No parameters given');
        }

        return $this->transactionTemplateRepository->search($transactionTemplateQueryDto, $user);

    }

    public function create(TransactionTemplateCreateDto $transactionTemplateCreateDto): TransactionTemplate
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var Category $category */
        $category = $this->categoryRepository->findByIdUserAndType($transactionTemplateCreateDto->categoryId, $user, $transactionTemplateCreateDto->categoryType);

        if (!$category) {
            throw new NotFoundHttpException("Category could not be found or doesn't match given Type");
        }

        $transactionName = $transactionTemplateCreateDto->transactionName;
        $paymentType = $category->getType() === "expense" ? $transactionTemplateCreateDto->paymentType : null;
        $moneyAmount = $transactionTemplateCreateDto->moneyAmount;
        $partyName = $transactionTemplateCreateDto->partyName;
        $transactionNotes = $transactionTemplateCreateDto->transactionNotes;

        if (!$transactionName && !$paymentType && !$moneyAmount && !$partyName && !$transactionNotes) {
            throw new BadRequestHttpException("Template cannot be completly blank...");
        }


        $newTemplate = new TransactionTemplate();
        $newTemplate->setUser($user);
        $newTemplate->setCategory($category);
        $newTemplate->setTransactionName($transactionName);
        $newTemplate->setPaymentType($paymentType);
        $newTemplate->setMoneyAmount($moneyAmount);
        $newTemplate->setPartyName($partyName);
        $newTemplate->setTransactionNotes($transactionNotes);


        $this->transactionTemplateRepository->create($newTemplate);

        return $newTemplate;

    }

    public function update(TransactionTemplateUpdateDto $transactionTemplateUpdateDto): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $template = $this->transactionTemplateRepository->findBYIdAndUser($transactionTemplateUpdateDto->id, $user);

        if (!$template) {
            throw new NotFoundHttpException("Transaction template not found or doesn't belong to you");
        }


        $currentCategory = $template->getCategory();

        $transactionName = $transactionTemplateUpdateDto->transactionName;
        $category = $transactionTemplateUpdateDto->categoryId ? $this->categoryRepository->findByIdAndUser($transactionTemplateUpdateDto->categoryId, $user) : $currentCategory;

        if (!$category) {
            throw new NotFoundHttpException("Category could not be found");
        }

        $paymentType = $category->getType() === "expense" ? $transactionTemplateUpdateDto->paymentType : null;
        $partyName = $transactionTemplateUpdateDto->partyName;
        $transactionNotes = $transactionTemplateUpdateDto->transactionNotes;
        $moneyAmount = $transactionTemplateUpdateDto->moneyAmount;


        if (!$transactionName && !$paymentType && !$partyName && !$transactionNotes && !$moneyAmount && $category === $currentCategory) {
            return ['message' => 'Nothing to update'];
        }


        if ($transactionName) {
            $template->setTransactionName($transactionName);
        }

        if ($category->getType() === 'expense') {
            $template->setCategory($category);
        }

        if ($category->getType() === 'income') {
            $template->setCategory($category);
            $template->setPaymentType(null);
        }

        if ($paymentType) {
            $template->setPaymentType($paymentType);
        }

        if ($partyName) {
            $template->setPartyName($partyName);
        }

        if ($moneyAmount) {
            $template->setMoneyAmount($moneyAmount);
        }

        if ($transactionNotes) {
            $template->setTransactionNotes($transactionNotes);
        }


        $this->transactionTemplateRepository->update();

        return ['message' => 'Update successful', 'template' => $template];

    }

    public function delete($id): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $transactionTemplate = $this->transactionTemplateRepository->findByIdAndUser($id, $user);

        if (!$transactionTemplate) {
            throw new NotFoundHttpException('Template not found or doesn\'t belong to you');
        }

        $this->transactionTemplateRepository->delete($transactionTemplate);

    }


}