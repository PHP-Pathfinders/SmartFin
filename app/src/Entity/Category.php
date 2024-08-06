<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: '`categories`')]
#[ORM\Index(name: 'search_idx', columns: ['category_name', 'type', 'user_id'])]
#[ORM\UniqueConstraint(name: 'unique_name_idx', columns: ['category_name', 'type', 'user_id'])]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category','budget','transaction','template'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['category','budget','transaction','template'])]
    private ?string $categoryName = null;

    #[ORM\Column(length: 10)]
    #[Groups(['category','budget','transaction','template'])]
    private ?string $type = null;

    #[ORM\Column(length: 10)]
    #[Groups(['category','budget','transaction','template'])]
    private ?string $color;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'category', cascade: ['remove'])]
    private Collection $transactions;

    /**
     * @var Collection<int, TransactionTemplate>
     */
    #[ORM\OneToMany(targetEntity: TransactionTemplate::class, mappedBy: 'category', cascade: ['remove'])]
    private Collection $transactionTemplates;

    /**
     * @var Collection<int, Budget>
     */
    #[ORM\OneToMany(targetEntity: Budget::class, mappedBy: 'category', cascade: ['remove'])]
    private Collection $budgets;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

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
        return $this->categoryName;
    }

    public function setCategoryName(string $categoryName): static
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
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
