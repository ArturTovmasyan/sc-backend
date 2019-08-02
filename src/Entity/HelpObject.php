<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_help_object")
 * @ORM\Entity(repositoryClass="App\Repository\HelpObjectRepository")
 * @UniqueEntity(
 *     fields={"title"},
 *     errorPath="title",
 *     message="This title is already in use.",
 *     groups={
 *          "api_help_object_add",
 *          "api_help_object_edit"
 *     }
 * )
 * @Grid(
 *     api_help_object_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ho.id"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "enum",
 *              "field"      = "ho.type",
 *              "values"     = "\App\Model\HelpObjectType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "category",
 *              "type"       = "string",
 *              "field"      = "hc.title",
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ho.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "ho.description",
 *          }
 *     }
 * )
 */
class HelpObject
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_help_object_list",
     *     "api_help_object_get",
     *     "api_help_category_all"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_help_object_list",
     *     "api_help_object_get",
     *     "api_help_category_all"
     * })
     * @Assert\NotBlank(groups={
     *     "api_help_object_add",
     *     "api_help_object_edit"
     * })
     */
    private $title;

    /**
     * @var int
     * @ORM\Column(name="type", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\HelpObjectType","getTypeValues"},
     *     groups={
     *          "api_help_object_add",
     *          "api_help_object_edit"
     *     }
     * )
     * @Groups({
     *      "api_help_object_list",
     *      "api_help_object_get",
     *     "api_help_category_all"
     * })
     */
    private $type;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_help_object_add",
     *          "api_help_object_edit"
     * })
     * @Groups({
     *     "api_help_object_list",
     *     "api_help_object_get",
     *     "api_help_category_all"
     * })
     */
    private $description;

    /**
     * @var array
     * @ORM\Column(name="grants", type="json_array", nullable=false)
     * @Groups({
     *     "api_help_object_list",
     *     "api_help_object_get"
     * })
     */
    private $grants = [];

    /**
     * @var boolean
     * @ORM\Column(name="grant_inherit", type="boolean")
     * @Groups({
     *     "api_help_object_list",
     *     "api_help_object_get"
     * })
     */
    private $grantInherit;

    /**
     * @var string
     * @ORM\Column(name="hash", type="string", length=64)
     * @Groups({
     *     "api_help_object_list",
     *     "api_help_object_get",
     *     "api_help_category_all"
     * })
     */
    private $hash;

    /**
     * @var HelpCategory
     * @ORM\ManyToOne(targetEntity="HelpCategory", inversedBy="objects")
     * @ORM\JoinColumn(name="id_help_category", referencedColumnName="id")
     * @Groups({
     *     "api_help_object_list",
     *     "api_help_object_get"
     * })
     */
    private $category;

    /**
     * HelpObject constructor.
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
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getGrants(): ?array
    {
        return $this->grants;
    }

    /**
     * @param array $grants
     */
    public function setGrants(?array $grants): void
    {
        $this->grants = $grants;
    }

    /**
     * @return bool
     */
    public function isGrantInherit(): ?bool
    {
        return $this->grantInherit;
    }

    /**
     * @param bool $grantInherit
     */
    public function setgrantInherit(?bool $grantInherit): void
    {
        $this->grantInherit = $grantInherit;
    }

    /**
     * @return string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(?string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return HelpCategory
     */
    public function getCategory(): ?HelpCategory
    {
        return $this->category;
    }

    /**
     * @param HelpCategory $category
     */
    public function setCategory(?HelpCategory $category): void
    {
        $this->category = $category;
    }

}
