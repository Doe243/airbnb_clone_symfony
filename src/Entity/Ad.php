<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use App\Repository\AdRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AdRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    
    fields: ['title'],
    message: 'Une autre annonce possède déjà ce titre, veullez le modifié !',
    
    )]
class Ad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Length(
        min: 10,
        max: 2555,
        minMessage: 'Le titre doit faire plus de 10 caractères !',
        maxMessage: 'Le titre ne peut pas dépassé plus de 255 caractères',
    )]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?float $price = null;


    #[Assert\Length(
        min: 20,
        minMessage: 'Votre introduction doit faire plus de 20 caractères'
    )]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $introduction = null;

    #[Assert\Length(
        min: 100,
        minMessage: 'Votre description ne peut pas faire moins de 100 caractères'
    )]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;
    
    #[Assert\Url(
        message: 'Votre url {{ value }} n\'est pas une url valide',
    )]
    #[ORM\Column(length: 255)]
    private ?string $coverImage = null;

    #[ORM\Column]
    private ?int $rooms = null;

    #[ORM\OneToMany(mappedBy: 'ad', targetEntity: Image::class, orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $images;

    #[ORM\ManyToOne(inversedBy: 'ads')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToMany(mappedBy: 'ad', targetEntity: Booking::class)]
    private Collection $bookings;

    #[ORM\OneToMany(mappedBy: 'ad', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

 

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initialize() {

        if (empty($this->slug)) {

            $slugify = new Slugify();

            $this->slug = $slugify->slugify($this->title);

        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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
            $booking->setAd($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getAd() === $this) {
                $booking->setAd(null);
            }
        }

        return $this;
    }

    /**
     * Permet d'obtenir un tableau des jours qui ne sont pas disponibles pour cette annonce
     *
     * @return array Un tableau d'objets DateTime représentant les jours d'occupation
     */
    public function getNotAvailbleDays() {

        $notAvailableDays = [];

        //On commence par calculer les jours qui 
        //se trouvent entre la date d'arrivée et la date de départ (résultat en timestamp)

        foreach($this->bookings as $booking) {

            $resultat = range(

                $booking->getStartDate()->getTimestamp(),

                $booking->getEndDate()->getTimestamp(),

                24 * 60 * 60

            );

            // Ensuite on transforme le timestamp en jour

            $days = array_map(function($dayTimestamp) {

                return new \DateTime(date('Y-m-d', $dayTimestamp));

            }, $resultat);

            $notAvailableDays = array_merge($notAvailableDays, $days);
        }

        return $notAvailableDays;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAd($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAd() === $this) {
                $comment->setAd(null);
            }
        }

        return $this;
    }

    /**
     * Permet d'avoir la moyenne globale des commentaires
     * 
     *@return float
     */
    public function getAvgRatings() {

        $sum = array_reduce($this->comments->toArray(), function($total, $comment) {

            return $total + $comment->getRating();
        }, 0);

        if (count($this->comments) > 0) {

            return $sum  /count($this->comments);
        }

        return 0;

    }

    /**
     * Permet de récuperer le commentaire d'un auteur par rapport à une annonce
     * @param User $author
     * @return Comment | null
     */

    public function getCommentFromAuthor(User $author) {

        foreach ($this->comments as $comment) {

            if ($comment->getAuthor() === $author) {

                return $comment;

            }

        }
        
        return null;
    }
}
