<?php
namespace App\Dto\Transaction;


use App\Entity\Category;
use App\Validator\IntegerType;
use App\Validator\LessThanOrEqual;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

readonly class TransactionQueryDto
{
    public function __construct(

        #[PositiveNumber]
        #[IntegerType]
        #[Assert\NotBlank(message: 'Page cannot be blank')]
        public string $page = '1',

        #[PositiveNumber]
        #[IntegerType]
        #[Assert\NotBlank(message: 'Limit cannot be blank')]
        #[LessThanOrEqual(200)]
        public string $limit = '200',

        #[Assert\NotBlank(message: 'Payment type cannot be blank', allowNull: true)]
        public ?string $paymentType = null,

        #[Assert\NotBlank(message: 'Transaction date cannot be blank', allowNull: true)]
        public ?\DateTimeInterface $dateStart = null,

        #[Assert\NotBlank(message: 'Transaction date cannot be blank', allowNull: true)]
        public ?\DateTimeInterface $dateEnd = null,

        #[Assert\NotBlank(message: 'Transaction name cannot be blank', allowNull: true)]
        public ?string $transactionName = null,

        #[Assert\NotBlank(message: 'Party name cannot be blank', allowNull: true)]
        public ?string $partyName = null,

        #[Assert\NotBlank(message: 'Transaction notes cannot be blank', allowNull: true)]
        public ?string $transactionNotes = null,

        #[Assert\NotBlank(message: 'Category name cannot be blank', allowNull: true)]
        public ?string $categoryName = null,

        #[Assert\NotBlank(message: 'Category Type cannot be blank', allowNull: true)]
        #[Assert\Choice(options: ['income', 'expense'],message: 'Category type must be \'income\' or \'expense\'.')]
        public ?string $categoryType = null,

        #[Assert\NotBlank(message: 'Category id cannot be blank', allowNull: true)]
        public ?int $categoryId = null,

    )
    {

    }



}