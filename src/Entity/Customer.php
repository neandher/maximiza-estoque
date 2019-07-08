<?php

namespace App\Entity;

use App\Resource\Model\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 */
class Customer
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     */
    private $cnpj;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CustomerAddresses", mappedBy="customer", cascade={"persist"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $customerAddresses;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CustomerObservations", mappedBy="customer", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy(value={"createdAt" = "DESC"})
     * @Assert\Valid()
     */
    private $customerObservations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CustomerBrands", mappedBy="customer", cascade={"persist"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $customerBrands;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomerState")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomerCategory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    public function __construct()
    {
        $this->customerAddresses = new ArrayCollection();
        $this->customerObservations = new ArrayCollection();
        $this->customerBrands = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setCnpj(string $cnpj): self
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return Collection|CustomerAddresses[]
     */
    public function getCustomerAddresses(): Collection
    {
        return $this->customerAddresses;
    }

    public function addCustomerAddress(CustomerAddresses $customerAddress): self
    {
        if (!$this->customerAddresses->contains($customerAddress)) {
            $this->customerAddresses[] = $customerAddress;
            $customerAddress->setCustomer($this);
        }

        return $this;
    }

    public function removeCustomerAddress(CustomerAddresses $customerAddress): self
    {
        if ($this->customerAddresses->contains($customerAddress)) {
            $this->customerAddresses->removeElement($customerAddress);
            // set the owning side to null (unless already changed)
            if ($customerAddress->getCustomer() === $this) {
                $customerAddress->setCustomer(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|CustomerObservations[]
     */
    public function getCustomerObservations(): Collection
    {
        return $this->customerObservations;
    }

    public function addCustomerObservation(CustomerObservations $customerObservation): self
    {
        if (!$this->customerObservations->contains($customerObservation)) {
            $this->customerObservations[] = $customerObservation;
            $customerObservation->setCustomer($this);
        }

        return $this;
    }

    public function removeCustomerObservation(CustomerObservations $customerObservation): self
    {
        if ($this->customerObservations->contains($customerObservation)) {
            $this->customerObservations->removeElement($customerObservation);
            // set the owning side to null (unless already changed)
            if ($customerObservation->getCustomer() === $this) {
                $customerObservation->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CustomerBrands[]
     */
    public function getCustomerBrands(): Collection
    {
        return $this->customerBrands;
    }

    public function addCustomerBrand(CustomerBrands $customerBrand): self
    {
        if (!$this->customerBrands->contains($customerBrand)) {
            $this->customerBrands[] = $customerBrand;
            $customerBrand->setCustomer($this);
        }

        return $this;
    }

    public function removeCustomerBrand(CustomerBrands $customerBrand): self
    {
        if ($this->customerBrands->contains($customerBrand)) {
            $this->customerBrands->removeElement($customerBrand);
            // set the owning side to null (unless already changed)
            if ($customerBrand->getCustomer() === $this) {
                $customerBrand->setCustomer(null);
            }
        }

        return $this;
    }

    public function getState(): ?CustomerState
    {
        return $this->state;
    }

    public function setState(?CustomerState $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCategory(): ?CustomerCategory
    {
        return $this->category;
    }

    public function setCategory(?CustomerCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getNameWithCategory(){
        return $this->getCategory()->getName() . ' - ' . $this->getName();
    }
}
