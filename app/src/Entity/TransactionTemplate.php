<?php

namespace App\Entity;

use App\Repository\TransactionTemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TransactionTemplateRepository::class)]
#[ORM\Table(name: '`transaction_templates`')]
class TransactionTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['template'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactionTemplates')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['template'])]
    private ?User $user = null;
    #[ORM\ManyToOne(inversedBy: 'transactionTemplates')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['template'])]
    private ?Category $category = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['template'])]
    private ?string $paymentType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['template'])]
    private ?float $moneyAmount = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['template'])]
    private ?string $transactionName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['template'])]
    private ?string $partyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['template'])]
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

    public function getMoneyAmount(): ?float
    {
        return $this->moneyAmount;
    }

    public function setMoneyAmount(?float $moneyAmount): static
    {
        $this->moneyAmount = $moneyAmount;

        return $this;
    }

    public function getTransactionName(): ?string
    {
        return $this->transactionName;
    }

    public function setTransactionName(?string $transactionName): static
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
