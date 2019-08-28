<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Table(name="tbl_email_log")
 * @ORM\Entity(repositoryClass="App\Repository\EmailLogRepository")
 * @Grid(
 *     api_email_log_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "el.id"
 *          },
 *          {
 *              "id"         = "domain",
 *              "type"       = "string",
 *              "field"      = "el.domain"
 *          },
 *          {
 *              "id"         = "roles",
 *              "type"       = "string",
 *              "field"      = "el.roles"
 *          },
 *          {
 *              "id"         = "emails",
 *              "type"       = "string",
 *              "field"      = "el.emails"
 *          },
 *          {
 *              "id"         = "subject",
 *              "type"       = "string",
 *              "field"      = "el.subject"
 *          },
 *          {
 *              "id"         = "message",
 *              "type"       = "string",
 *              "field"      = "el.message"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "datetime",
 *              "field"      = "el.date"
 *          },
 *          {
 *              "id"         = "status",
 *              "type"       = "number",
 *              "field"      = "el.status"
 *          }
 *     }
 * )
 */
class EmailLog
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *      "api_email_log_list",
     *      "api_email_log_get",
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="domain", type="string", length=255)
     * @Groups({
     *     "api_email_log_list",
     *     "api_email_log_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_email_log_add",
     *     "api_email_log_edit"
     * })
     */
    private $domain;

    /**
     * @var string
     * @ORM\Column(name="subject", type="text")
     * @Groups({
     *     "api_email_log_list",
     *     "api_email_log_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_email_log_add",
     *     "api_email_log_edit"
     * })
     */
    private $subject;

    /**
     * @var string
     * @ORM\Column(name="message", type="text")
     * @Groups({
     *     "api_email_log_list",
     *     "api_email_log_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_email_log_add",
     *     "api_email_log_edit"
     * })
     */
    private $message;

    /**
     * @var string
     * @ORM\Column(name="roles", type="text")
     * @Groups({
     *     "api_email_log_list",
     *     "api_email_log_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_email_log_add",
     *     "api_email_log_edit"
     * })
     */
    private $roles;

    /**
     * @var string
     * @ORM\Column(name="emails", type="text")
     * @Groups({
     *     "api_email_log_list",
     *     "api_email_log_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_email_log_add",
     *     "api_email_log_edit"
     * })
     */
    private $emails;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_email_log_add",
     *     "api_email_log_edit"
     * })
     * @ORM\Column(name="date", type="datetime", nullable=true)
     * @Groups({
     *     "api_email_log_list",
     *     "api_email_log_get"
     * })
     */
    private $date;

    /**
     * @var int
     * @ORM\Column(name="status", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\JobStatus","getTypeValues"},
     *     groups={
     *          "api_email_log_add",
     *          "api_email_log_edit"
     *     }
     * )
     * @Groups({
     *      "api_email_log_list",
     *      "api_email_log_get",
     * })
     */
    private $status;

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
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getEmails(): ?string
    {
        return $this->emails;
    }

    /**
     * @param string $emails
     */
    public function setEmails(?string $emails): void
    {
        $this->emails = $emails;
    }

    /**
     * @return string
     */
    public function getRoles(): ?string
    {
        return $this->roles;
    }

    /**
     * @param string $roles
     */
    public function setRoles(?string $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }


}
