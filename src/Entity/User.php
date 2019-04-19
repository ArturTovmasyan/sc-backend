<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Annotation\Grid;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="tbl_user")
 * @Grid(
 *     api_user_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "u.id"
 *          },
 *          {
 *              "id"         = "username",
 *              "type"       = "string",
 *              "field"      = "u.username",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "u.fullName",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "enabled",
 *              "type"       = "boolean",
 *              "field"      = "u.enabled"
 *          }
 *     }
 * )
 */
class User implements UserInterface
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @Groups({
     *     "api_user_list",
     *     "api_user_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=25, unique=true, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_user_add",
     *     "api_user_edit"
     * })
     * @Groups({
     *     "api_user_list",
     *     "api_user_get"
     * })
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="full_name", type="string", length=255, unique=true, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_user_add",
     *     "api_user_edit"
     * })
     * @Groups({
     *     "api_user_list",
     *     "api_user_get"
     * })
     */
    private $fullName;

    /**
     * @var array
     * @ORM\Column(name="roles", type="json_array")
     */
    private $roles = [];

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\NotNull(groups={
     *     "api_user_add",
     *     "api_user_edit"
     * })
     * @Groups({
     *     "api_user_list",
     *     "api_user_get"
     * })
     */
    private $enabled;

    /**
     * @var string The hashed password
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_user_add"
     * })
     * @Assert\Regex(
     *     pattern="/(\S*(?=\S{6,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*)/",
     *     message="The password must be at least 6 characters long and contain at least one lowercase letter, one uppercase letter, one number and one special character (non-word characters).",
     *     groups={
     *     "api_user_add",
     *     "api_user_edit"
     *     }
     * )
     */
    private $password;

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName(?string $fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @return bool
     */
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("role")
     * @Groups({
     *     "api_user_get"
     * })
     */
    public function getFirstRole()
    {
        return count($this->roles) > 0 ? $this->roles[0] : null;
    }
}
