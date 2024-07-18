<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PdfXlsGeneratorService
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly Security              $security,
        private readonly string                $avatarDirectory,
    )
    {}

    public function generatePDF(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user){
           throw new NotFoundHttpException('User not found');
        }
        $transactions = $this->transactionRepository->fetchSpecificColumns(user: $user,
            categoryId: true, paymentType: true, transactionDate: true, moneyAmount: true
        );

        return [
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'imageSrc'  => $this->imageToBase64($this->avatarDirectory .'/'. $user->getAvatarFileName()),
            'transactions' => $transactions
        ];
    }

    private function imageToBase64($path): string
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}