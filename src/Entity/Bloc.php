<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BlocRepository")
 * @Vich\Uploadable()
 */
class Bloc
{
    const TYPETICKET = [
        0 => 'Aucun',
        1 => 'Incident',
        2 => 'Demande'
    ];
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @var File|null
     * @Assert\Image(mimeTypes="image/png")
     * @Vich\UploadableField(mapping="property_image", fileNameProperty="filename")
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $glpicategory;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $information;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $affiche;

    /**
     * @ORM\Column(type="boolean")
     */
    private $logiciel;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Bloc", inversedBy="blocs")
     * @ORM\JoinColumn(nullable=true)
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bloc", mappedBy="parent")
     */
    private $blocs;


    public function __construct()
    {
        $this->blocs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getGlpicategory(): ?int
    {
        return $this->glpicategory;
    }

    public function setGlpicategory(?int $glpicategory): self
    {
        $this->glpicategory = $glpicategory;

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): self
    {
        $this->information = $information;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getTypeLib(): string
    {
        return self::TYPETICKET[$this->type];
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAffiche(): ?bool
    {
        return $this->affiche;
    }

    public function setAffiche(bool $affiche): self
    {
        $this->affiche = $affiche;

        return $this;
    }

    public function getLogiciel(): ?bool
    {
        return $this->logiciel;
    }

    public function setLogiciel(bool $logiciel): self
    {
        $this->logiciel = $logiciel;

        return $this;
    }



    /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param File|null $imageFile
     * @return Bloc
     */
    public function setImageFile(?File $imageFile): Bloc
    {
        $this->imageFile = $imageFile;
        if($this->imageFile instanceof UploadedFile) {
            $this->updated_at = new \Datetime('now');
        }
        return $this;
    }



    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     * @return Bloc
     */
    public function setFilename(?string $filename): Bloc
    {
        $this->filename = $filename;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getBlocs(): Collection
    {
        return $this->blocs;
    }

    public function addBloc(self $bloc): self
    {
        if (!$this->blocs->contains($bloc)) {
            $this->blocs[] = $bloc;
            $bloc->setParent($this);
        }

        return $this;
    }

    public function removeBloc(self $bloc): self
    {
        if ($this->blocs->contains($bloc)) {
            $this->blocs->removeElement($bloc);
            // set the owning side to null (unless already changed)
            if ($bloc->getParent() === $this) {
                $bloc->setParent(null);
            }
        }

        return $this;
    }


}
