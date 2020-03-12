<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderItemsRepository")
 */
class OrderItems
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $referency;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank()
     */
    private $quantity;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotBlank()
     */
    private $subtotal;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotBlank()
     */
    private $discount = 0.00;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotBlank()
     */
    private $total;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotBlank()
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="orderItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $orderEntity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferency(): ?string
    {
        return $this->referency;
    }

    public function setReferency(string $referency): self
    {
        $this->referency = $referency;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSubtotal()
    {
        return $this->subtotal;
    }

    public function setSubtotal($subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setDiscount($discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getOrderEntity(): ?Order
    {
        return $this->orderEntity;
    }

    public function setOrderEntity(?Order $orderEntity): self
    {
        $this->orderEntity = $orderEntity;

        return $this;
    }
}
