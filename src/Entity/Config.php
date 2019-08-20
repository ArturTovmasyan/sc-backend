<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Table(name="tbl_config")
 * @ORM\Entity(repositoryClass="App\Repository\ConfigRepository")
 * @Grid(
 *     api_config_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "c.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "c.name"
 *          },
 *          {
 *              "id"         = "value",
 *              "type"       = "string",
 *              "field"      = "c.value"
 *          }
 *     }
 * )
 */
class Config
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *      "api_config_list",
     *      "api_config_get",
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\Length(
     *      max = 25,
     *      maxMessage = "This value cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_config_add",
     *          "api_config_edit"
     * })
     * @Assert\Regex(
     *     pattern="/^[A-Z_][0-9A-Z_]*$/",
     *     message="Invalid pattern. Valid characters are A-Z, 0-9, and _.",
     *     groups={
     *          "api_config_add",
     *          "api_config_edit"
     * })
     * @ORM\Column(name="name", type="string", length=25, nullable=false)
     * @Groups({
     *     "api_config_list",
     *     "api_config_get"
     * })
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     * @Groups({
     *     "api_config_list",
     *     "api_config_get"
     * })
     */
    private $value;

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
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }
}
