<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    
    fields: ['email'],
    message: 'Un autre urilisateur s\'est déjà inscrit avec cette adresse email, merci de le modifier !',
    
    )]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre prénom' 
    )]
    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre nom'
    )]
    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[Assert\Email(
        message: 'Veuillez renseigner un email valide'
    )]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[Assert\Url(
        message: 'Veuiller donner une URL valide pour votre avatar !'
    )]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(length: 255)]
    private ?string $hash = null;

    #[Assert\EqualTo(
        propertyPath: "hash", 
        message: "Vos deux mots de passe ne correspondent pas !!!")]
    public $passwordConfirm;

    #[Assert\Length(
        min: 10,
        minMessage: "Votre introduction doit faire au minimum 10 caractères")]
    #[ORM\Column(length: 255)]
    private ?string $introduction = null;

    #[Assert\Length(
        min: 100,
        minMessage: "Votre introduction doit faire au minimum 100 caractères")]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Ad::class)]
    private Collection $ads;

    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'users')]
    private Collection $userRoles;

    #[ORM\OneToMany(mappedBy: 'booker', targetEntity: Booking::class)]
    private Collection $bookings;

    public function __construct()
    {
        $this->ads = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getFullName() {
        return "{$this->firstName} {$this->lastName}";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
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

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, Ad>
     */
    public function getAds(): Collection
    {
        return $this->ads;
    }

    public function addAd(Ad $ad): self
    {
        if (!$this->ads->contains($ad)) {
            $this->ads->add($ad);
            $ad->setAuthor($this);
        }

        return $this;
    }

    public function removeAd(Ad $ad): self
    {
        if ($this->ads->removeElement($ad)) {
            // set the owning side to null (unless already changed)
            if ($ad->getAuthor() === $this) {
                $ad->setAuthor(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    ///#[ORM\PreUpdate]
    public function initialize() {

        if (empty($this->slug)) {

            $slugify = new Slugify();

            $this->slug = $slugify->slugify($this->firstName. ' '. $this->lastName);

        }
    }

   /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array
    {
        ///On parcourt notre tableau des rôles pour récupérer nos rôles 
        ///et de le changer en chaîne de caractères

        $roles = $this->userRoles->map(function($role) {

            return $role->getTitle();

        })->toArray();

        $roles[] = "ROLE_USER";
        
        return $roles;

    }

    public function getPassword(): ?string
    {
        return $this->hash;
    }

    public function getSalt(){}

    public function eraseCredentials(){}

    /**
     * @return Collection<int, Role>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(Role $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->addUser($this);
        }

        return $this;
    }

    public function removeUserRole(Role $userRole): self
    {
        if ($this->userRoles->removeElement($userRole)) {
            $userRole->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setBooker($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getBooker() === $this) {
                $booking->setBooker(null);
            }
        }

        return $this;
    }

}
