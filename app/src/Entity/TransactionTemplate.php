<?php

namespace App\Entity;

use App\Repository\TransactionTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionTemplateRepository::class)]
#[ORM\Table(name: '`transaction_templates`')]
class TransactionTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactionTemplates')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $cash_or_card = null;

    #[ORM\Column(nullable: true)]
    private ?float $money_amount = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $transaction_name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $party_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transaction_notes = null;

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

    public function getCashOrCard(): ?string
    {
        return $this->cash_or_card;
    }

    public function setCashOrCard(?string $cash_or_card): static
    {
        $this->cash_or_card = $cash_or_card;

        return $this;
    }

    public function getMoneyAmount(): ?float
    {
        return $this->money_amount;
    }

    public function setMoneyAmount(?float $money_amount): static
    {
        $this->money_amount = $money_amount;

        return $this;
    }

    public function getTransactionName(): ?string
    {
        return $this->transaction_name;
    }

    public function setTransactionName(?string $transaction_name): static
    {
        $this->transaction_name = $transaction_name;

        return $this;
    }

    public function getPartyName(): ?string
    {
        return $this->party_name;
    }

    public function setPartyName(?string $party_name): static
    {
        $this->party_name = $party_name;

        return $this;
    }

    public function getTransactionNotes(): ?string
    {
        return $this->transaction_notes;
    }

    public function setTransactionNotes(?string $transaction_notes): static
    {
        $this->transaction_notes = $transaction_notes;

        return $this;
    }
}
