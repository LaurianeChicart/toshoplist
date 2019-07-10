<?php

namespace App\Entity;

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
     * 
     */

    private $password;

    /**
     * 
     * @Assert\EqualTo(propertyPath="password", message="Confirmation de mot de passe incorrecte")
     */
    private $confirm_password;

    /**
     * @Assert\Length(
     *      min = 8,
     *      minMessage = "Le mot de passe doit comporter 8 caractères minimum."
     * )
     * 
     */

    private $new_password;

    /**
     * 
     * @Assert\EqualTo(propertyPath="new_password", message="Confirmation de mot de passe incorrecte")
     */
    private $confirm_new_password;


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
}
