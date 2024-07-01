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
    private ?Category $category = null;

    #[ORM\Column]
    private ?float $monthly_budget = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $monthly_budget_date = null;

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
        return $this->monthly_budget;
    }

    public function setMonthlyBudget(float $monthly_budget): static
    {
        $this->monthly_budget = $monthly_budget;

        return $this;
    }

    public function getMonthlyBudgetDate(): ?DateTimeInterface
    {
        return $this->monthly_budget_date;
    }

    public function setMonthlyBudgetDate(DateTimeInterface $monthly_budget_date): static
    {
        $this->monthly_budget_date = $monthly_budget_date;

        return $this;
    }
}
