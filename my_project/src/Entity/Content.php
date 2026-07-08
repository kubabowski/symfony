<?php

namespace App\Entity;

use App\Repository\ContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
class Content
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null; // e.g. "page", "hero", "about", "cta"

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[ORM\Column]
    private ?int $position = 0;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Content $parent = null;

    /**
     * @var Collection<int, Content>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $children;

    // --- new dynamic fields ---

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text1 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text3 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text4 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text5 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text6 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image4 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $btnText1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $btnLink1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $btnText2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $btnLink2 = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, Content>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    // --- getters/setters for the new fields ---

    public function getText1(): ?string { return $this->text1; }
    public function setText1(?string $text1): static { $this->text1 = $text1; return $this; }

    public function getText2(): ?string { return $this->text2; }
    public function setText2(?string $text2): static { $this->text2 = $text2; return $this; }

    public function getText3(): ?string { return $this->text3; }
    public function setText3(?string $text3): static { $this->text3 = $text3; return $this; }

    public function getText4(): ?string { return $this->text4; }
    public function setText4(?string $text4): static { $this->text4 = $text4; return $this; }

    public function getText5(): ?string { return $this->text5; }
    public function setText5(?string $text5): static { $this->text5 = $text5; return $this; }

    public function getText6(): ?string { return $this->text6; }
    public function setText6(?string $text6): static { $this->text6 = $text6; return $this; }

    public function getImage1(): ?string { return $this->image1; }
    public function setImage1(?string $image1): static { $this->image1 = $image1; return $this; }

    public function getImage2(): ?string { return $this->image2; }
    public function setImage2(?string $image2): static { $this->image2 = $image2; return $this; }

    public function getImage3(): ?string { return $this->image3; }
    public function setImage3(?string $image3): static { $this->image3 = $image3; return $this; }

    public function getImage4(): ?string { return $this->image4; }
    public function setImage4(?string $image4): static { $this->image4 = $image4; return $this; }

    public function getBtnText1(): ?string { return $this->btnText1; }
    public function setBtnText1(?string $btnText1): static { $this->btnText1 = $btnText1; return $this; }

    public function getBtnLink1(): ?string { return $this->btnLink1; }
    public function setBtnLink1(?string $btnLink1): static { $this->btnLink1 = $btnLink1; return $this; }

    public function getBtnText2(): ?string { return $this->btnText2; }
    public function setBtnText2(?string $btnText2): static { $this->btnText2 = $btnText2; return $this; }

    public function getBtnLink2(): ?string { return $this->btnLink2; }
    public function setBtnLink2(?string $btnLink2): static { $this->btnLink2 = $btnLink2; return $this; }
}