<?php

namespace App\Entity;

use App\Repository\BudgetRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
#[ORM\Table(name: '`budgets`')]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('budget')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('budget')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('budget')]
    private ?Category $category = null;

    #[ORM\Column]
    #[Groups('budget')]
    private ?float $monthlyBudget = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[Groups('budget')]
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
