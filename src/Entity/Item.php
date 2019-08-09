<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 */
class Item
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $quantities;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $initial_quantities;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $checked;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ListDepartment", inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     */
    private $list_department;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getQuantities(): ?string
    {
        return $this->quantities;
    }

    public function setQuantities(?string $quantities): self
    {
        $this->quantities = $quantities;

        return $this;
    }

    public function getInitialQuantities(): ?string
    {
        return $this->initial_quantities;
    }

    public function setInitialQuantities(?string $initial_quantities): self
    {
        $this->initial_quantities = $initial_quantities;

        return $this;
    }



    public function getChecked(): ?bool
    {
        return $this->checked;
    }

    public function setChecked(?bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }

    public function getListDepartment(): ?ListDepartment
    {
        return $this->list_department;
    }

    public function setListDepartment(?ListDepartment $list_department): self
    {
        $this->list_department = $list_department;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
