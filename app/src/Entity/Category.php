<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'category')]
    private Collection $transactions;

    /**
     * @var Collection<int, TransactionTemplate>
     */
    #[ORM\OneToMany(targetEntity: TransactionTemplate::class, mappedBy: 'category')]
    private Collection $transactionTemplates;

    /**
     * @var Collection<int, Budget>
     */
    #[ORM\OneToMany(targetEntity: Budget::class, mappedBy: 'category')]
    private Collection $budgets;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->transactionTemplates = new ArrayCollection();
        $this->budgets = new ArrayCollection();
    }

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

    public function getIsCustom(): ?bool
    {
        return $this->is_custom;
    }

    public function setIsCustom(?bool $is_custom): void
    {
        $this->is_custom = $is_custom;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setCategory($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCategory() === $this) {
                $transaction->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransactionTemplate>
     */
    public function getTransactionTemplates(): Collection
    {
        return $this->transactionTemplates;
    }

    public function addTransactionTemplate(TransactionTemplate $transactionTemplate): static
    {
        if (!$this->transactionTemplates->contains($transactionTemplate)) {
            $this->transactionTemplates->add($transactionTemplate);
            $transactionTemplate->setCategory($this);
        }

        return $this;
    }

    public function removeTransactionTemplate(TransactionTemplate $transactionTemplate): static
    {
        if ($this->transactionTemplates->removeElement($transactionTemplate)) {
            // set the owning side to null (unless already changed)
            if ($transactionTemplate->getCategory() === $this) {
                $transactionTemplate->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Budget>
     */
    public function getBudgets(): Collection
    {
        return $this->budgets;
    }

    public function addBudget(Budget $budget): static
    {
        if (!$this->budgets->contains($budget)) {
            $this->budgets->add($budget);
            $budget->setCategory($this);
        }

        return $this;
    }

    public function removeBudget(Budget $budget): static
    {
        if ($this->budgets->removeElement($budget)) {
            // set the owning side to null (unless already changed)
            if ($budget->getCategory() === $this) {
                $budget->setCategory(null);
            }
        }

        return $this;
    }
}
