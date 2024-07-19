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
            categoryName: true, type: true, color: true, paymentType: true, transactionDate: true, moneyAmount: true
        );

        $image = $user->getAvatarFileName() ? $this->imageToBase64($this->avatarDirectory . '/' . $user->getAvatarFileName()) : null;

        return [
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'imageSrc'  => $image,
            'transactions' => $transactions
        ];
    }

    public function generateXLS(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user){
            throw new NotFoundHttpException('User not found');
        }

         $results =  $this->transactionRepository->fetchSpecificColumns(user: $user,
            categoryName: true, type: true, paymentType: true, transactionDate: true, moneyAmount: true
        );
        foreach ($results as &$result) {
            if (isset($result['day'])) {
                $result['day'] = (int) $result['day'];
            }
            if (isset($result['month'])) {
                $result['month'] = (int) $result['month'];
            }
            if (isset($result['year'])) {
                $result['year'] = (int) $result['year'];
            }
        }
        return $results;
    }

    private function imageToBase64($path): string
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}