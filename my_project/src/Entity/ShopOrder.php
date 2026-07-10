<?php

namespace App\Entity;

use App\Repository\ShopOrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopOrderRepository::class)]
#[ORM\Table(name: 'shop_order')]
class ShopOrder
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(length: 255)]
    private ?string $customerName = null;

    #[ORM\Column(length: 255)]
    private ?string $customerEmail = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $customerPhone = null;

    #[ORM\Column(length: 255)]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine2 = null;

    #[ORM\Column(length: 100)]
    private ?string $city = null;

    #[ORM\Column(length: 20)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100)]
    private ?string $country = null;

    #[ORM\Column]
    private ?int $quantity = 1;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $totalPrice = null;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): static { $this->product = $product; return $this; }

    public function getCustomerName(): ?string { return $this->customerName; }
    public function setCustomerName(string $customerName): static { $this->customerName = $customerName; return $this; }

    public function getCustomerEmail(): ?string { return $this->customerEmail; }
    public function setCustomerEmail(string $customerEmail): static { $this->customerEmail = $customerEmail; return $this; }

    public function getCustomerPhone(): ?string { return $this->customerPhone; }
    public function setCustomerPhone(?string $customerPhone): static { $this->customerPhone = $customerPhone; return $this; }

    public function getAddressLine1(): ?string { return $this->addressLine1; }
    public function setAddressLine1(string $addressLine1): static { $this->addressLine1 = $addressLine1; return $this; }

    public function getAddressLine2(): ?string { return $this->addressLine2; }
    public function setAddressLine2(?string $addressLine2): static { $this->addressLine2 = $addressLine2; return $this; }

    public function getCity(): ?string { return $this->city; }
    public function setCity(string $city): static { $this->city = $city; return $this; }

    public function getPostalCode(): ?string { return $this->postalCode; }
    public function setPostalCode(string $postalCode): static { $this->postalCode = $postalCode; return $this; }

    public function getCountry(): ?string { return $this->country; }
    public function setCountry(string $country): static { $this->country = $country; return $this; }

    public function getQuantity(): ?int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }

    public function getTotalPrice(): ?string { return $this->totalPrice; }
    public function setTotalPrice(string $totalPrice): static { $this->totalPrice = $totalPrice; return $this; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
}