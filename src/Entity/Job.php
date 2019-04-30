<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Table(name="tbl_job")
 * @ORM\Entity(repositoryClass="App\Repository\JobRepository")
 * @Grid(
 *     api_job_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "j.id"
 *          },
 *          {
 *              "id"         = "customer",
 *              "type"       = "string",
 *              "field"      = "c.organization"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "enum",
 *              "field"      = "j.type",
 *              "values"     = "\App\Model\JobType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "status",
 *              "type"       = "enum",
 *              "field"      = "j.status",
 *              "values"     = "\App\Model\JobStatus::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "start_date",
 *              "type"       = "datetime",
 *              "field"      = "j.startDate"
 *          },
 *          {
 *              "id"         = "end_date",
 *              "type"       = "datetime",
 *              "field"      = "j.endDate"
 *          },
 *          {
 *              "id"         = "log",
 *              "type"       = "string",
 *              "field"      = "j.log"
 *          }
 *     }
 * )
 */
class Job
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *      "api_job_list",
     *      "api_job_get",
     * })
     */
    private $id;

    /**
     * @var Customer
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="jobs", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_customer", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *      "api_job_add",
     *      "api_job_edit"
     * })
     * @Groups({
     *      "api_job_list",
     *      "api_job_get",
     * })
     */
    private $customer;

    /**
     * @var int
     * @ORM\Column(name="type", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\JobType","getTypeValues"},
     *     groups={
     *          "api_job_add",
     *          "api_job_edit"
     *     }
     * )
     * @Groups({
     *      "api_job_list",
     *      "api_job_get",
     * })
     */
    private $type;

    /**
     * @var int
     * @ORM\Column(name="status", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\JobStatus","getTypeValues"},
     *     groups={
     *          "api_job_add",
     *          "api_job_edit"
     *     }
     * )
     * @Groups({
     *      "api_job_list",
     *      "api_job_get",
     * })
     */
    private $status;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_job_add",
     *     "api_job_edit"
     * })
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     * @Groups({
     *     "api_job_list",
     *     "api_job_get"
     * })
     */
    private $startDate;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_job_add",
     *     "api_job_edit"
     * })
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     * @Groups({
     *     "api_job_list",
     *     "api_job_get"
     * })
     */
    private $endDate;

    /**
     * @var string
     * @Assert\Length(
     *      max = 2048,
     *      maxMessage = "Log cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_job_add",
     *          "api_job_edit"
     * })
     * @ORM\Column(name="log", type="text", nullable=true)
     * @Groups({
     *     "api_job_list",
     *     "api_job_get"
     * })
     */
    private $log;

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
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
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
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $log
     */
    public function setLog($log)
    {
        $this->log = $log;
    }
}
