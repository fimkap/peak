<?php

namespace App\Entity;

use App\Repository\CallRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CallRepository::class)
 * @ORM\Table(name="`call`")
 */
class Call
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $customer_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dialed_number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $customer_ip;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerId(): ?string
    {
        return $this->customer_id;
    }

    public function setCustomerId(string $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDialedNumber(): ?string
    {
        return $this->dialed_number;
    }

    public function setDialedNumber(string $dialed_number): self
    {
        $this->dialed_number = $dialed_number;

        return $this;
    }

    public function getCustomerIp(): ?string
    {
        return $this->customer_ip;
    }

    public function setCustomerIp(string $customer_ip): self
    {
        $this->customer_ip = $customer_ip;

        return $this;
    }
}
