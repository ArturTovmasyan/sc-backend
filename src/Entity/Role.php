<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_role")
 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="This name is already in use.",
 *     groups={
 *          "api_role_add",
 *          "api_role_edit"
 *     }
 * )
 * @Grid(
 *     api_role_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "r.name",
 *              "link"       = ":edit"
 *          }
 *     }
 * )
 */
class Role
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_role_list",
     *     "api_role_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({
     *     "api_role_list",
     *     "api_role_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_role_customer",
     *     "api_role_add",
     *     "api_role_edit"
     * })
     */
    private $name;

    /**
     * @var array $grants
     * @ORM\Column(name="grants", type="json_array", nullable=false)
     * @Groups({
     *     "api_role_list",
     *     "api_role_get"
     * })
     */
    private $grants = [];

    /**
     * Role constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getGrants(): ?array
    {
        if(is_string($this->grants)) {
            $this->grants = json_decode($this->grants, true);
        }

        return $this->grants;
    }

    /**
     * @param array $grants
     */
    public function setGrants(?array $grants): void
    {
        $this->grants = $grants;
    }

}
