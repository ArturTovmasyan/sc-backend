<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Table(name="tbl_vhost")
 * @ORM\Entity(repositoryClass="App\Repository\VhostRepository")
 * @UniqueEntity(fields="email", message="This email address was already in use.", groups={
 *     "api_vhost_add",
 *     "api_vhost_edit"
 * })
 * @Grid(
 *     api_vhost_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "v.id"
 *          },
 *          {
 *              "id"         = "db_name",
 *              "type"       = "string",
 *              "field"      = "v.name"
 *          },
 *          {
 *              "id"         = "db_user",
 *              "type"       = "string",
 *              "field"      = "v.user"
 *          },
 *          {
 *              "id"         = "db_password",
 *              "type"       = "string",
 *              "field"      = "v.password"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "v.email"
 *          },
 *          {
 *              "id"         = "path",
 *              "type"       = "string",
 *              "field"      = "v.path"
 *          }
 *     }
 * )
 */
class Vhost
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="db_name", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Database name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="db_user", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Database user cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(name="db_password", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Database password cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $password;


    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Email(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Groups({
     *     "api_vhost_list",
     *     "api_vhost_get"
     * })
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="path", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Path cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $path;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}
