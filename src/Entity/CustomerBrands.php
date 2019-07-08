<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerBrandsRepository")
 */
class CustomerBrands
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Brand")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $brand;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="customerBrands")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    public function getId()
    {
        return $this->id;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
