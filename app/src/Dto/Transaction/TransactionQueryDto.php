<?php
namespace App\Dto\Transaction;


use App\Entity\Category;
use Symfony\Component\Validator\Constraints as Assert;

readonly class TransactionQueryDto
{
    public function __construct(
        #[Assert\NotBlank(allowNull: true)]
        public ?string $paymentType = null,

        #[Assert\NotBlank(allowNull: true)]
        #[Assert\Date(message: "Transaction date should be a valid date.")]
        public ?\DateTimeInterface $transactionDate = null,

    )
    {

    }



}