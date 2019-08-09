<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Table(name="tbl_vhost")
 * @ORM\Entity(repositoryClass="App\Repository\VhostRepository")
 * @Grid(
 *     api_vhost_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "v.id"
 *          },
 *          {
 *              "id"         = "customer",
 *              "type"       = "string",
 *              "field"      = "c.organization"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "v.name"
 *          },
 *          {
 *              "id"         = "www_root",
 *              "type"       = "string",
 *              "field"      = "v.wwwRoot"
 *          },
 *          {
 *              "id"         = "db_host",
 *              "type"       = "string",
 *              "field"      = "v.dbHost"
 *          },
 *          {
 *              "id"         = "db_name",
 *              "type"       = "string",
 *              "field"      = "v.dbName"
 *          },
 *          {
 *              "id"         = "db_user",
 *              "type"       = "string",
 *              "field"      = "v.dbUser"
 *          },
 *          {
 *              "id"         = "db_password",
 *              "type"       = "string",
 *              "field"      = "v.dbPassword"
 *          },
 *          {
 *              "id"         = "mailer_host",
 *              "type"       = "string",
 *              "field"      = "v.mailerHost"
 *          },
 *          {
 *              "id"         = "mailer_proto",
 *              "type"       = "string",
 *              "field"      = "v.mailerProto"
 *          },
 *          {
 *              "id"         = "mailer_user",
 *              "type"       = "string",
 *              "field"      = "v.mailerUser"
 *          },
 *          {
 *              "id"         = "mailer_password",
 *              "type"       = "string",
 *              "field"      = "v.mailerPassword"
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
     * @var Customer
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="vhosts", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_customer", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *      "api_vhost_add",
     *      "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $customer;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
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
     * @ORM\Column(name="www_root", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "WWW root cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $wwwRoot;

    /**
     * @var string
     * @ORM\Column(name="db_host", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Database host cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $dbHost;

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
    private $dbName;

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
    private $dbUser;

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
    private $dbPassword;

    /**
     * @var string
     * @ORM\Column(name="mailer_host", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Mailer host cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $mailerHost;

    /**
     * @var string
     * @ORM\Column(name="mailer_proto", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Mailer proto cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $mailerProto;

    /**
     * @var string
     * @ORM\Column(name="mailer_user", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Mailer user cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $mailerUser;

    /**
     * @var string
     * @ORM\Column(name="mailer_password", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_vhost_add",
     *     "api_vhost_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Mailer password cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_vhost_add",
     *          "api_vhost_edit"
     * })
     * @Groups({
     *      "api_vhost_list",
     *      "api_vhost_get",
     * })
     */
    private $mailerPassword;

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
    public function getWwwRoot()
    {
        return $this->wwwRoot;
    }

    /**
     * @param string $wwwRoot
     */
    public function setWwwRoot($wwwRoot)
    {
        $this->wwwRoot = $wwwRoot;
    }

    /**
     * @return string
     */
    public function getDbHost()
    {
        return $this->dbHost;
    }

    /**
     * @param string $dbHost
     */
    public function setDbHost($dbHost)
    {
        $this->dbHost = $dbHost;
    }

    /**
     * @return string
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * @param string $dbName
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }

    /**
     * @return string
     */
    public function getDbUser()
    {
        return $this->dbUser;
    }

    /**
     * @param string $dbUser
     */
    public function setDbUser($dbUser)
    {
        $this->dbUser = $dbUser;
    }

    /**
     * @return string
     */
    public function getDbPassword()
    {
        return $this->dbPassword;
    }

    /**
     * @param string $dbPassword
     */
    public function setDbPassword($dbPassword)
    {
        $this->dbPassword = $dbPassword;
    }

    /**
     * @return string
     */
    public function getMailerHost()
    {
        return $this->mailerHost;
    }

    /**
     * @param string $mailerHost
     */
    public function setMailerHost($mailerHost)
    {
        $this->mailerHost = $mailerHost;
    }

    /**
     * @return string
     */
    public function getMailerProto()
    {
        return $this->mailerProto;
    }

    /**
     * @param string $mailerProto
     */
    public function setMailerProto($mailerProto)
    {
        $this->mailerProto = $mailerProto;
    }

    /**
     * @return string
     */
    public function getMailerPassword()
    {
        return $this->mailerPassword;
    }

    /**
     * @param string $mailerPassword
     */
    public function setMailerPassword($mailerPassword)
    {
        $this->mailerPassword = $mailerPassword;
    }

    /**
     * @return string
     */
    public function getMailerUser()
    {
        return $this->mailerUser;
    }

    /**
     * @param string $mailerUser
     */
    public function setMailerUser($mailerUser)
    {
        $this->mailerUser = $mailerUser;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

}
