<?php

namespace App\Entity;

use App\Repository\BudgetRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
#[ORM\Table(name: '`budgets`')]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;
    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column]
    private ?float $monthlyBudget = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $monthlyBudgetDate = null;

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

    public function getMonthlyBudget(): ?float
    {
        return $this->monthlyBudget;
    }

    public function setMonthlyBudget(float $monthlyBudget): static
    {
        $this->monthlyBudget = $monthlyBudget;

        return $this;
    }

    public function getMonthlyBudgetDate(): ?DateTimeInterface
    {
        return $this->monthlyBudgetDate;
    }

    public function setMonthlyBudgetDate(DateTimeInterface $monthlyBudgetDate): static
    {
        $this->monthlyBudgetDate = $monthlyBudgetDate;

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
