<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Table(name="tbl_customer")
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 * @UniqueEntity(
 *     fields="email",
 *     message="This email address was already in use.",
 *     groups={
 *       "api_customer_add",
 *       "api_customer_edit"
 * })
 * @UniqueEntity(
 *     fields="domain",
 *     message="This domain was already in use.",
 *     groups={
 *       "api_customer_add",
 *       "api_customer_edit"
 * })
 * @Grid(
 *     api_customer_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "c.id"
 *          },
 *          {
 *              "id"         = "domain",
 *              "type"       = "string",
 *              "field"      = "c.domain"
 *          },
 *          {
 *              "id"         = "organization",
 *              "type"       = "string",
 *              "field"      = "c.organization"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(c.firstName, ''), ' ', COALESCE(c.lastName, ''))",
 *              "link"       = "/customer/:id"
 *          },
 *          {
 *              "id"         = "phone",
 *              "type"       = "string",
 *              "field"      = "c.phone"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "c.email"
 *          },
 *          {
 *              "id"         = "address",
 *              "type"       = "string",
 *              "field"      = "c.address"
 *          },
 *          {
 *              "id"         = "csz",
 *              "type"       = "string",
 *              "field"      = "c.csz"
 *          }
 *     }
 * )
 */
class Customer
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *      "api_customer_list",
     *      "api_customer_get",
     *      "api_job_list",
     *      "api_job_get",
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="domain", type="string", length=100)
     * @Assert\NotBlank(groups={
     *     "api_customer_add",
     *     "api_customer_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Domain cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_customer_add",
     *          "api_customer_edit"
     * })
     * @Groups({
     *      "api_customer_list",
     *      "api_customer_get",
     * })
     */
    private $domain;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=40)
     * @Assert\NotBlank(groups={
     *     "api_customer_add",
     *     "api_customer_edit"
     * })
     * @Assert\Length(
     *      max = 40,
     *      maxMessage = "FirstName cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_customer_add",
     *          "api_customer_edit"
     * })
     * @Groups({
     *      "api_customer_list",
     *      "api_customer_get",
     *      "api_job_list",
     *      "api_job_get",
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=40)
     * @Assert\NotBlank(groups={
     *     "api_customer_add",
     *     "api_customer_edit"
     * })
     * @Assert\Length(
     *      max = 40,
     *      maxMessage = "LastName cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_customer_add",
     *          "api_customer_edit"
     * })
     * @Groups({
     *      "api_customer_list",
     *      "api_customer_get",
     *      "api_job_list",
     *      "api_job_get",
     * })
     */
    private $lastName;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_customer_add",
     *     "api_customer_edit"
     * })
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid phone number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *          "api_customer_add",
     *          "api_customer_edit"
     * })
     * @ORM\Column(name="phone", type="string", length=20)
     * @Groups({
     *     "api_customer_list",
     *     "api_customer_get"
     * })
     */
    private $phone;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_customer_add",
     *     "api_customer_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_customer_add",
     *          "api_customer_edit"
     * })
     * @ORM\Column(name="address", type="string", length=100)
     * @Groups({
     *     "api_customer_list",
     *     "api_customer_get"
     * })
     */
    private $address;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_customer_add",
     *     "api_customer_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_customer_add",
     *          "api_customer_edit"
     * })
     * @ORM\Column(name="csz", type="string", length=100)
     * @Groups({
     *     "api_customer_list",
     *     "api_customer_get"
     * })
     */
    private $csz;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={
     *     "api_customer_add",
     *     "api_customer_edit"
     * })
     * @Assert\Email(groups={
     *     "api_customer_add",
     *     "api_customer_edit"
     * })
     * @Groups({
     *     "api_customer_list",
     *     "api_customer_get",
     *     "api_job_list",
     *     "api_job_get"
     * })
     */
    private $email;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_customer_add",
     *     "api_customer_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Organization cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_customer_add",
     *          "api_customer_edit"
     * })
     * @ORM\Column(name="organization", type="string", length=100)
     * @Groups({
     *     "api_customer_list",
     *     "api_customer_get"
     * })
     */
    private $organization;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Job", mappedBy="customer", cascade={"remove", "persist"})
     */
    private $jobs;

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
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getCsz(): string
    {
        return $this->csz;
    }

    /**
     * @param string $csz
     */
    public function setCsz(string $csz): void
    {
        $this->csz = $csz;
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
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param string $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return ArrayCollection
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param ArrayCollection $jobs
     */
    public function setJobs($jobs)
    {
        $this->jobs = $jobs;
    }
}
