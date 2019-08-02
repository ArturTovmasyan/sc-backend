<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_help_category")
 * @ORM\Entity(repositoryClass="App\Repository\HelpCategoryRepository")
 * @UniqueEntity(
 *     fields={"title"},
 *     errorPath="title",
 *     message="This title is already in use.",
 *     groups={
 *          "api_help_category_add",
 *          "api_help_category_edit"
 *     }
 * )
 * @Grid(
 *     api_help_category_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "hc.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "hc.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "parent",
 *              "type"       = "string",
 *              "field"      = "COALESCE(hcp.title, '(root)')"
 *          }
 *     }
 * )
 */
class HelpCategory
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_help_category_list",
     *     "api_help_category_get",
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
     *     "api_help_category_list",
     *     "api_help_category_get",
     *     "api_help_category_all"
     * })
     * @Assert\NotBlank(groups={
     *     "api_help_category_add",
     *     "api_help_category_edit"
     * })
     */
    private $title;

    /**
     * @var array
     * @ORM\Column(name="grants", type="json_array", nullable=false)
     * @Groups({
     *     "api_help_category_list",
     *     "api_help_category_get"
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
     * @var HelpCategory
     * @ORM\ManyToOne(targetEntity="HelpCategory", inversedBy="children")
     * @ORM\JoinColumn(name="id_help_category", referencedColumnName="id")
     * @Groups({
     *     "api_help_category_get"
     * })
     */
    private $parent;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="HelpCategory", mappedBy="parent")
     * @Groups({
     *     "api_help_category_list",
     *     "api_help_category_get",
     *     "api_help_category_all"
     * })
     */
    private $children;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="HelpObject", mappedBy="category")
     * @Groups({
     *     "api_help_category_all"
     * })
     */
    private $objects;

    /**
     * HelpCategory constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->objects = new ArrayCollection();
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
     * @return array
     */
    public function getGrants(): array
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
     * @return HelpCategory
     */
    public function getParent(): ?HelpCategory
    {
        return $this->parent;
    }

    /**
     * @param HelpCategory $parent
     */
    public function setParent(?HelpCategory $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ArrayCollection $children
     */
    public function setChildren($children): void
    {
        $this->children = $children;
    }

    /**
     * @return ArrayCollection
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param ArrayCollection $objects
     */
    public function setObjects($objects): void
    {
        $this->objects = $objects;
    }

}
