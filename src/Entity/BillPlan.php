<?php

namespace App\Entity;

use App\Resource\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BillPlanRepository")
 */
class BillPlan
{
    use TimestampableTrait;

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
     * @var BillPlanCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BillPlanCategory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $billPlanCategory;

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

    public function getBillPlanCategory(): ?BillPlanCategory
    {
        return $this->billPlanCategory;
    }

    public function setBillPlanCategory(?BillPlanCategory $billPlanCategory): self
    {
        $this->billPlanCategory = $billPlanCategory;

        return $this;
    }

    public function getDescriptionWithType()
    {
        return $this->billPlanCategory->getDescription() . ' - ' . $this->getDescription();
    }
}
