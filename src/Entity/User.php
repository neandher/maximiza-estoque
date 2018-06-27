<?php

namespace App\Entity;

use App\Resource\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 */
class User extends BaseUser
{
    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"Registration", "Profile"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"Registration", "Profile"})
     */
    private $lastName;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $receiveEmails = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReceiveEmails(): ?bool
    {
        return $this->receiveEmails;
    }

    public function setReceiveEmails(?bool $receiveEmails): self
    {
        $this->receiveEmails = $receiveEmails;

        return $this;
    }
}