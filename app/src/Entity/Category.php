<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: '`categories`')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $category_name = null;

    #[ORM\Column(length: 10)]
    private ?string $income_or_expense = null;

    #[ORM\Column]
    private ?bool $is_custom = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function setCategoryName(string $category_name): static
    {
        $this->category_name = $category_name;

        return $this;
    }

    public function getIncomeOrExpense(): ?string
    {
        return $this->income_or_expense;
    }

    public function setIncomeOrExpense(string $income_or_expense): static
    {
        $this->income_or_expense = $income_or_expense;

        return $this;
    }

    public function isCustom(): ?bool
    {
        return $this->is_custom;
    }

    public function setCustom(bool $is_custom): static
    {
        $this->is_custom = $is_custom;

        return $this;
    }
}
