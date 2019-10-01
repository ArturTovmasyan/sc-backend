<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Table(name="tbl_feedback")
 * @ORM\Entity(repositoryClass="App\Repository\FeedbackRepository")
 * @Grid(
 *     api_feedback_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "f.id"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "datetime",
 *              "field"      = "f.date"
 *          },
 *          {
 *              "id"         = "domain",
 *              "type"       = "string",
 *              "field"      = "f.domain"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "f.email"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(f.fullName, ' (', f.username, ')')"
 *          },
 *          {
 *              "id"         = "subject",
 *              "type"       = "string",
 *              "field"      = "f.subject"
 *          },
 *          {
 *              "id"         = "message",
 *              "type"       = "string",
 *              "field"      = "f.message"
 *          },
 *          {
 *              "id"         = "status",
 *              "type"       = "enum",
 *              "field"      = "f.status",
 *              "values"     = "\App\Model\FeedbackStatus::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "comments",
 *              "type"       = "string",
 *              "field"      = "f.comments"
 *          }
 *     }
 * )
 */
class Feedback
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *      "api_feedback_list",
     *      "api_feedback_get",
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="domain", type="string", length=255)
     * @Groups({
     *     "api_feedback_list",
     *     "api_feedback_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_feedback_add",
     *     "api_feedback_edit"
     * })
     */
    private $domain;

    /**
     * @var string
     * @ORM\Column(name="full_name", type="string", length=255)
     * @Groups({
     *     "api_feedback_list",
     *     "api_feedback_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_feedback_add",
     *     "api_feedback_edit"
     * })
     */
    private $fullName;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=255)
     * @Groups({
     *     "api_feedback_list",
     *     "api_feedback_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_feedback_add",
     *     "api_feedback_edit"
     * })
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255)
     * @Groups({
     *     "api_feedback_list",
     *     "api_feedback_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_feedback_add",
     *     "api_feedback_edit"
     * })
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="subject", type="text")
     * @Groups({
     *     "api_feedback_list",
     *     "api_feedback_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_feedback_add",
     *     "api_feedback_edit"
     * })
     */
    private $subject;

    /**
     * @var string
     * @ORM\Column(name="message", type="text")
     * @Groups({
     *     "api_feedback_list",
     *     "api_feedback_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_feedback_add",
     *     "api_feedback_edit"
     * })
     */
    private $message;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_feedback_add",
     *     "api_feedback_edit"
     * })
     * @ORM\Column(name="date", type="datetime", nullable=true)
     * @Groups({
     *     "api_feedback_list",
     *     "api_feedback_get"
     * })
     */
    private $date;



    /**
     * @var string $comments
     * @ORM\Column(name="comments", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_feedback_edit"
     * })
     * @Groups({
     *     "api_feedback_list",
     *     "api_feedback_get"
     * })
     */
    private $comments;



    /**
     * @var int
     * @ORM\Column(name="status", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\FeedbackStatus","getTypeValues"},
     *     groups={
     *          "api_feedback_edit"
     *     }
     * )
     * @Groups({
     *      "api_feedback_list",
     *      "api_feedback_get",
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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments(?string $comments): void
    {
        $this->comments = $comments;
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
