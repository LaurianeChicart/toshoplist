<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ListItemsRepository")
 */
class ListItems
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Planning", inversedBy="listItems", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $planning;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ListDepartment", mappedBy="list_items", orphanRemoval=true)
     */
    private $listDepartments;

    public function __construct()
    {
        $this->listDepartments = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(Planning $planning): self
    {
        $this->planning = $planning;

        return $this;
    }

    /**
     * @return Collection|ListDepartment[]
     */
    public function getListDepartments(): Collection
    {
        return $this->listDepartments;
    }

    public function addListDepartment(ListDepartment $listDepartment): self
    {
        if (!$this->listDepartments->contains($listDepartment)) {
            $this->listDepartments[] = $listDepartment;
            $listDepartment->setListItems($this);
        }

        return $this;
    }

    public function removeListDepartment(ListDepartment $listDepartment): self
    {
        if ($this->listDepartments->contains($listDepartment)) {
            $this->listDepartments->removeElement($listDepartment);
            // set the owning side to null (unless already changed)
            if ($listDepartment->getListItems() === $this) {
                $listDepartment->setListItems(null);
            }
        }

        return $this;
    }
}
