<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *  fields={"email"},
 *  message="L'email indiqué est déjà utilisé"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message = "Cet email n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
    
     * @Assert\Length(
     *      min = 8,
     *      minMessage = "Le mot de passe doit comporter 8 caractères minimum."
     * )
     * @Assert\Regex(
     *     pattern="/\s/",
     *     match=false,
     *     message="Le mot de passe ne doit pas contenir d'espace blanc."
     * )
     */

    private $password;

    /**
     * 
     * @Assert\EqualTo(propertyPath="password", message="Confirmation de mot de passe incorrecte")
     * 
     */
    private $confirm_password;

    private $current_password;

    /**
     * @Assert\Length(
     *      min = 8,
     *      minMessage = "Le mot de passe doit comporter 8 caractères minimum."
     * )
     * @Assert\Regex(
     *     pattern="/\s/",
     *     match=false,
     *     message="Le mot de passe ne doit pas contenir d'espace blanc."
     * )
     * 
     * @Assert\NotEqualTo(propertyPath="current_password", message="Le mot de passe saisi est identique au mot de passe actuel.")
     *
     *
     */

    private $new_password;

    /**
     * 
     * @Assert\EqualTo(propertyPath="new_password", message="Confirmation de mot de passe incorrecte")
     */
    private $confirm_new_password;


    /**
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Memo", mappedBy="user", orphanRemoval=true)
     */
    private $memos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Recipe", mappedBy="user", orphanRemoval=true)
     */
    private $recipes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Planning", mappedBy="user", orphanRemoval=true)
     */
    private $plannings;

    public function __construct()
    {
        $this->memos = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->plannings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $confirm_password): self
    {
        $this->confirm_password = $confirm_password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }
    public function eraseCredentials()
    { }

    public function getSalt()
    { }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * Get the value of new_password
     */
    public function getNewPassword()
    {
        return $this->new_password;
    }

    /**
     * Set the value of new_password
     *
     * @return  self
     */
    public function setNewPassword($new_password)
    {
        $this->new_password = $new_password;

        return $this;
    }

    /**
     * Get the value of confirm_new_password
     */
    public function getConfirmNewPassword()
    {
        return $this->confirm_new_password;
    }

    /**
     * Set the value of confirm_new_password
     *
     * @return  self
     */
    public function setConfirmNewPassword($confirm_new_password)
    {
        $this->confirm_new_password = $confirm_new_password;

        return $this;
    }

    public function getCurrentPassword()
    {
        return $this->current_password;
    }

    public function setCurrentPassword($current_password)
    {
        $this->current_password = $current_password;

        return $this;
    }

    /**
     * @return Collection|Memos
     */
    public function getMemos(): Collection
    {
        return $this->memos;
    }
    public function addMemo(Memo $memo): self
    {
        if (!$this->memos->contains($memo)) {
            $this->memos[] = $memo;
            $memo->setUser($this);
        }

        return $this;
    }

    public function removeMemo(Memo $memo): self
    {
        if ($this->memos->contains($memo)) {
            $this->memos->removeElement($memo);
            // set the owning side to null (unless already changed)
            if ($memo->getUser() === $this) {
                $memo->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Recipe[]
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): self
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes[] = $recipe;
            $recipe->setUser($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): self
    {
        if ($this->recipes->contains($recipe)) {
            $this->recipes->removeElement($recipe);
            // set the owning side to null (unless already changed)
            if ($recipe->getUser() === $this) {
                $recipe->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Planning[]
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): self
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings[] = $planning;
            $planning->setUser($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): self
    {
        if ($this->plannings->contains($planning)) {
            $this->plannings->removeElement($planning);
            // set the owning side to null (unless already changed)
            if ($planning->getUser() === $this) {
                $planning->setUser(null);
            }
        }

        return $this;
    }
}
