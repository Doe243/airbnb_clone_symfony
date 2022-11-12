<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\HasLifecycleCallbacks]


class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $booker = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ad $ad = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\GreaterThan(
        "today", 
        message: "La date d'arrivée doit être ultérieur à la date d'aujourd'hui !",
        groups: ["front"])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\GreaterThan(propertyPath: "startDate", message: "La date de départ doit être égale ou plus éloignée que la date d'arrivée!")]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function prePerist() {

        if (empty($this->createdAt)) {

            $this->createdAt = new \DateTime();
        }

        if (empty($this->amount)) {

            $this->amount = $this->ad->getPrice() * $this->getDuration();
        }
    }

    /**
     * Permet de retourne la difference des jours entre la date de dépar bet la date d'arrivée
     *
     */
    public function getDuration()
    {
        $diff = $this->endDate->diff($this->startDate);

        return $diff->days;
    }

    public function isBookableDates() {

        /// On regarde les dates qui sont impossible pour l'annonce

        $notAvailableDays = $this->ad->getNotAvailbleDays();

        // On compare les dates choisies avec les adtes impossibles 

        $bookingDays = $this->getDays();

        $formatDay = function($day) { return $day->format('Y-m-d'); };

        //On transforme le tableau des chaines de caractères de mes journées

        $days = array_map($formatDay, $bookingDays);

        $notAvailable = array_map($formatDay, $notAvailableDays);

        ///On check oui ou non si les dates sont disponibles

        foreach($days as $day) {

            if(array_search($day, $notAvailable) !== false) return false;

        }

        return true;


    }

    /**
     * Permet de récuperer un tableau des journées qui correspondent à ma reservation
     * @return array Un tableau d'objets DateTime répresentant les jours de la réservation
     */
    public function getDays()
    {
        $resultat = range(
            $this->startDate->getTimestamp(),

            $this->endDate->getTimestamp(),

            24 * 60 * 60
        );

        $days = array_map(function($dayTimestamp) {

            return new \DateTime(date('Y-m-d', $dayTimestamp));

        }, $resultat);

        return $days ;
    }
}
