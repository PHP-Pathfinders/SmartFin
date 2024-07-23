<?php

namespace App\Entity;

use AllowDynamicProperties;
use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AllowDynamicProperties] #[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 80)]
    private ?string $fullName = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isVerified = false;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isActive = true;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $birthday = null;

    #[ORM\Column(nullable: true)]
    private ?string $avatarFileName = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt;


    #[ORM\Column]
    private ?int $jwtVersion = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $scheduledDeletionDate = null;

    private string $plainPassword;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'user')]
    private Collection $categories;

    /**
     * @var Collection<int, Budget>
     */
    #[ORM\OneToMany(targetEntity: Budget::class, mappedBy: 'user')]
    private Collection $budgets;

    /**
     * @var Collection<int, TransactionTemplate>
     */
    #[ORM\OneToMany(targetEntity: TransactionTemplate::class, mappedBy: 'user')]
    private Collection $transactionTemplates;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'user')]
    private Collection $transactions;

    /**
     * @var Collection<int, Export>
     */
    #[ORM\OneToMany(targetEntity: Export::class, mappedBy: 'user')]
    private Collection $exports;


    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->categories = new ArrayCollection();
        $this->budgets = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->transactionTemplates = new ArrayCollection();
        $this->exports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = '';
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(?bool $isVerified): void
    {
        $this->isVerified = $isVerified;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?DateTimeInterface $birthday): static
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getAvatarFileName(): ?string
    {
        return $this->avatarFileName;
    }

    public function setAvatarFileName(?string $avatarFileName): void
    {
        $this->avatarFileName = $avatarFileName;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword():string
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setUser($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        // set the owning side to null (unless already changed)
        if ($this->categories->removeElement($category) && $category->getUser() === $this) {
            $category->setUser(null);
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
            $budget->setUser($this);
        }

        return $this;
    }

    public function removeBudget(Budget $budget): static
    {
        if ($this->budgets->removeElement($budget)) {
            // set the owning side to null (unless already changed)
            if ($budget->getUser() === $this) {
                $budget->setUser(null);
            }
        }

        return $this;
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
            $transaction->setUser($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
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
            $transactionTemplate->setUser($this);
        }

        return $this;
    }

    public function removeTransactionTemplate(TransactionTemplate $transactionTemplate): static
    {
        if ($this->transactionTemplates->removeElement($transactionTemplate)) {
            // set the owning side to null (unless already changed)
            if ($transactionTemplate->getUser() === $this) {
                $transactionTemplate->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Export>
     */
    public function getExports(): Collection
    {
        return $this->exports;
    }

    public function addExport(Export $export): static
    {
        if (!$this->exports->contains($export)) {
            $this->exports->add($export);
            $export->setUser($this);
        }

        return $this;
    }

    public function removeExport(Export $export): static
    {
        if ($this->exports->removeElement($export)) {
            // set the owning side to null (unless already changed)
            if ($export->getUser() === $this) {
                $export->setUser(null);
            }
        }

        return $this;
    }

    public function getScheduledDeletionDate(): ?\DateTimeInterface
    {
        return $this->scheduledDeletionDate;
    }

    public function setScheduledDeletionDate(?\DateTimeInterface $scheduledDeletionDate): static
    {
        $this->scheduledDeletionDate = $scheduledDeletionDate;

        return $this;
    }

    public function getJwtVersion(): int
    {
        return $this->jwtVersion;
    }

    public function incrementJwtVersion(): void
    {
        $this->jwtVersion++;
    }

}
