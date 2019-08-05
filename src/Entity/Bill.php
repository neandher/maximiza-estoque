<?php

namespace App\Entity;

use App\Resource\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BillRepository")
 */
class Bill
{
    use TimestampableTrait;

    const BILL_TYPE_PAY = 'pay';
    const BILL_TYPE_RECEIVE = 'receive';
    const BILL_TYPES = [
        Bill::BILL_TYPE_PAY => 'Despesa',
        Bill::BILL_TYPE_RECEIVE => 'Receita'
    ];

    const BILL_STATUS_OPEN = 'open';
    const BILL_STATUS_PAID = 'paid';
    const BILL_STATUS = [
        Bill::BILL_STATUS_OPEN => 'Em berto',
        Bill::BILL_STATUS_PAID => 'Pago'
    ];

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
    private $description;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value="0")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $dueDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date()
     */
    private $paymentDate;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $amountPaid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BillPlan")
     * @Assert\NotBlank()
     * @ORM\JoinColumn(nullable=false)
     */
    private $billPlan;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getAmountPaid()
    {
        return $this->amountPaid;
    }

    public function setAmountPaid($amountPaid): self
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    /**
     * @Assert\Callback()
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->getPaymentDate() === null && $this->getAmountPaid() !== null) {
            $context->buildViolation('bill.validator.paymentDate')
                ->atPath('paymentDate')
                ->addViolation();
        }

        if ($this->getAmountPaid() === null && $this->getPaymentDate() !== null) {
            $context->buildViolation('bill.validator.amountPaid')
                ->atPath('amountPaid')
                ->addViolation();
        }
    }

    public function getBillPlan(): ?BillPlan
    {
        return $this->billPlan;
    }

    public function setBillPlan(?BillPlan $billPlan): self
    {
        $this->billPlan = $billPlan;

        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isOverDue()
    {
        $isDateOverDue = false;
        if ($this->getPaymentDate() === null && $this->getDueDate() < (new \DateTime(date('y-m-d')))) {
            $isDateOverDue = true;
        }
        return $isDateOverDue;
    }
}
