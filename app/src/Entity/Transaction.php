<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: '`transactions`')]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;
    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column(length: 10)]
    private ?string $paymentType = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $transactionDate = null;

    #[ORM\Column]
    private ?float $moneyAmount = null;

    #[ORM\Column(length: 50)]
    private ?string $transactionName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $partyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transactionNotes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(?string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }

    public function getTransactionDate(): ?DateTimeInterface
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(DateTimeInterface $transactionDate): static
    {
        $this->transactionDate = $transactionDate;

        return $this;
    }

    public function getMoneyAmount(): ?float
    {
        return $this->moneyAmount;
    }

    public function setMoneyAmount(float $moneyAmount): static
    {
        $this->moneyAmount = $moneyAmount;

        return $this;
    }

    public function getTransactionName(): ?string
    {
        return $this->transactionName;
    }

    public function setTransactionName(string $transactionName): static
    {
        $this->transactionName = $transactionName;

        return $this;
    }

    public function getPartyName(): ?string
    {
        return $this->partyName;
    }

    public function setPartyName(?string $partyName): static
    {
        $this->partyName = $partyName;

        return $this;
    }

    public function getTransactionNotes(): ?string
    {
        return $this->transactionNotes;
    }

    public function setTransactionNotes(?string $transactionNotes): static
    {
        $this->transactionNotes = $transactionNotes;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
