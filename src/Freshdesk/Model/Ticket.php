<?php

namespace Freshdesk\Model;

use \DateTime,
    \InvalidArgumentException;

class Ticket extends Base
{

    const RESPONSE_KEY = 'helpdesk_ticket';

    const SOURCE_EMAIL = 1;
    const SOURCE_PORTAL = 2;
    const SOURCE_PHONE = 3;
    const SOURCE_FORUM = 4;
    const SOURCE_TWITTER = 5;
    const SOURCE_FACEBOOK = 6;
    const SOURCE_CHAT = 7;

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_URGENT = 4;

    const STATUS_ALL = 1;
    const STATUS_OPEN = 2;
    const STATUS_PENDING = 3;
    const STATUS_RESOLVED = 4;
    const STATUS_CLOSED = 5;

    const CC_EMAIL = '<your cc_email here>'; // obosolete

    /**
     * @var int
     */
    protected $displayId = null;

    /**
     * @var string
     */
    protected $email = null;

    /**
     * @var int
     */
    protected $phone = null;

    /**
     * @var string
     */
    protected $twitterId = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var int
     */
    protected $requesterId = null;

    /**
     * @var string
     */
    protected $subject = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @var string
     */
    protected $descriptionHtml = null;

    /**
     * @var int
     */
    protected $status = null;

    /**
     * @var int
     */
    protected $priority = null;

    /**
     * @var int
     */
    protected $source = null;

    /**
     * @var bool
     */
    protected $deleted = null;

    /**
     * @var bool
     */
    protected $spam = null;

    /**
     * @var int
     */
    protected $responderId = null;

    /**
     * @var int
     */
    protected $groupId = null;

    /**
     * @var string
     */
    protected $ticketType = null;

    /**
     * @var string
     */
    protected $ccEmailVal = null;

    /**
     * @var array<string>
     */
    //protected $ccEmails = null;

    /**
     * @var int
     */
    protected $emailConfigId = null;

    /**
     * @var bool
     */
    protected $isescalated = null;

    /**
     * @var DateTime
     */
    protected $dueBy = null;

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var array<mixed>
     */
    protected $attachments = null;

    /**
     * @todo change this to a CustomFields iterator
     *
     * @var array<CustomField>
     */
    protected $customField = array();

    /**
     * @var array
     */
    protected $tags = array();

    /**
     * @var \DateTime
     */
    protected $createdAt = null;

    /**
     * @var \DateTime
     */
    protected $updatedAt = null;

    /**
     * @var array<\Freshdesk\Model\Note>
     */
    protected $notes = array();

    /**
     * @var string
     */
    protected $statusName = null;

    /**
     * @var bool
     */
    protected $delta = null;

    /**
     * @var int
     */
    protected $ownerId = null;

    /**
     * @var string
     */
    protected $toEmail = null;

    /**
     * @var bool
     */
    protected $trained = null;

    /**
     * @var bool
     */
    protected $urgent = null;

    /**
     * @var string
     */
    protected $requesterStatusName = null;

    /**
     * @var string
     */
    protected $priorityName = null;

    /**
     * @var string
     */
    protected $sourceName = null;

    /**
     * @var string
     */
    protected $requesterName = null;

    /**
     * @var string
     */
    protected $responderName = null;

    /**
     * @var int
     */
    protected $productId = null;

    /**
     * @var string ?
     */
    protected $toEmails = null;

    /**
     * @var array - add all setters that require a DateTime instance as argument
     */
    protected $toDateTime = array(
        'setDueBy',
        'setCreatedAt', // dont post this to freshdesk
        'setUpdatedAt', // dont post this to freshdesk
    );

    /**
     * @var array
     */
    protected $mandatory = array(
        'requesterId' => array('email', 'phone', 'twitterId'),
        array('description', 'descriptionHtml'),
    );

    /**
     * @var array
     */
    protected $readOnlyFields = array(
        'id',
        'attachments',
        'displayId',
        'createdAt', // let freshdesk update this
        'updatedAt', // let freshdesk update this
        'ccEmailVal', // cheat
        'customField', // cheat
    );

    /**
     * @param int $dId
     * @return $this
     */
    public function setDisplayId($dId)
    {
        $this->displayId = $dId === null ? null : (int) $dId;
        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayId()
    {
        return $this->displayId;
    }

    /**
     * @param string $email
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setEmail($email)
    {
        if (!filter_var($email, \FILTER_VALIDATE_EMAIL))
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid email address',
                    $email
                )
            );
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return twitterId
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * @param $twitterId
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;
        return $this;
    }

    /**
     * @return name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param int $reqId
     * @return $this
     */
    public function setRequesterId($reqId)
    {
        $this->requesterId = (int) $reqId;
        return $this;
    }

    /**
     * @return int
     */
    public function getRequesterId()
    {
        return $this->requesterId;
    }

    /**
     * @param string $subj
     * @return $this
     */
    public function setSubject($subj)
    {
        $this->subject = $subj;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $desc
     * @return $this
     */
    public function setDescription($desc)
    {
        $this->description = (string) $desc;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return descriptionHtml
     */
    public function getDescriptionHtml()
    {
        return $this->descriptionHtml;
    }

    /**
     * @param $descriptionHtml
     */
    public function setDescriptionHtml($descriptionHtml)
    {
        $this->descriptionHtml = $descriptionHtml;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = (int) $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $p
     * @return $this
     */
    public function setPriority($p)
    {
        $this->priority = (int) $p;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param $source
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param bool $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = (bool) $deleted;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @return spam
     */
    public function getSpam()
    {
        return $this->spam;
    }

    /**
     * @param $spam
     */
    public function setSpam($spam)
    {
        $this->spam = $spam;
        return $this;
    }

    /**
     * @param int $respId
     * @return $this
     */
    public function setResponderId($respId)
    {
        $this->responderId = (int) $respId;
        return $this;
    }

    /**
     * @return int
     */
    public function getResponderId()
    {
        return $this->responderId;
    }

    /**
     * @return groupId
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return ticketType
     */
    public function getTicketType()
    {
        return $this->ticketType;
    }

    /**
     * @param $ticketType
     */
    public function setTicketType($ticketType)
    {
        $this->ticketType = $ticketType;
        return $this;
    }

    /**
     * @param string $ccemail
     * @return $this
     */
    public function setCcEmailVal($ccemail)
    {
        $this->ccEmailVal = $ccemail === null ? null : (string) $ccemail;
        return $this;
    }

    /**
     * @return string
     */
    public function getCcEmailVal()
    {
        if ($this->ccEmailVal === null)
            return self::CC_EMAIL;
        return $this->ccEmailVal;
    }

    /**
     * @return ccEmails
     */
    /*
    public function getCcEmails()
    {
        return $this->ccEmails;
    }
     */

    /**
     * @param $ccEmails
     */
    /*
    public function setCcEmails($ccEmails)
    {
        $this->ccEmails = $ccEmails;
        return $this;
    }
     */

    /**
     * @return emailConfigId
     */
    public function getEmailConfigId()
    {
        return $this->emailConfigId;
    }

    /**
     * @param $emailConfigId
     */
    public function setEmailConfigId($emailConfigId)
    {
        $this->emailConfigId = $emailConfigId;
        return $this;
    }

    /**
     * @return isescalated
     */
    public function getIsescalated()
    {
        return $this->isescalated;
    }

    /**
     * @param $isescalated
     */
    public function setIsescalated($isescalated)
    {
        $this->isescalated = $isescalated;
    }

    /**
     * @return dueBy
     */
    public function getDueBy()
    {
        return $this->dueBy;
    }

    /**
     * @param DateTime $dueBy
     */
    public function setDueBy(DateTime $dueBy)
    {
        $this->dueBy = $dueBy;
        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id === null ? null : (int) $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return attachments
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * @param mixed $mixed
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setCustomField($mixed)
    {
        if ($mixed instanceof \stdClass)
            $mixed = (array) $mixed;
        elseif ($mixed instanceof CustomField)
        {
            if (is_array($this->customField))
                return $this->addCustomField($mixed);
            $mixed = array($mixed);
        }
        if (!is_array($mixed))
            throw new InvalidArgumentException(
                sprintf(
                    '%s expects an array, stdClass instance or a CustomField model',
                    __METHOD__
                )
            );
        $this->customField = array();
        foreach ($mixed as $k => $v)
            $this->addCustomField($v, $k);
        return $this;
    }

    /**
     * @param null|string $name
     * @return \Freshdesk\Model\CustomField|null
     */
    public function getCustomField($name = null)
    {
        if ($name === null)
            return $this->customField;
        foreach ($this->customField as $k => $field)
        {
            if ($field->getName() == $name)
                return $field;
        }
        return null;
    }

    /**
     * @param string|\Freshdesk\Model\CustomField $mix
     * @param null|string|int $k
     * @return $this
     */
    public function addCustomField($mix, $k = null)
    {
        if ($mix instanceof CustomField)
            $this->customField[] = $mix;
        else
            $this->customField[] = new CustomField(
                array(
                    'name'  => $k,
                    'value' => $mix,
                    'ticket'=> $this
                )
            );
        return $this;
    }

    /**
     * Set multiple tags on the ticket
     *
     * @param mixed $tags Can be either an array, or a comma-delimited string of tags
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTags($tags)
    {
        if (is_string($tags))
        {
            $tags = explode(',', $tags);
        }
        if (!is_array($tags))
        {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s expects argument to be a string, or an array, %s given',
                    __METHOD__,
                    is_object($tags) ? get_class($tags) : gettype($tags)
                )
            );
        }
        $this->tags = $tags;

        return $this;
    }

    /**
     * Add a single tag to the ticket
     *
     * @param string $tag A single tag to add
     * @return $this
     */
    public function addTag($tag)
    {
        if (is_string($tag))
        {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * Retrieve any tags set on the ticket
     * @param bool $asString = true
     * @return string|array
     */
    public function getTags($asString = true)
    {
        if ($asString)
            return implode(',', $this->tags);
        return $this->tags;
    }

    /**
     * @param DateTime $d
     * @return $this
     */
    public function setCreatedAt(DateTime $d)
    {
        $this->createdAt = $d;
        return $this;
    }

    /**
     * @param bool $asString
     * @return DateTime|string|null
     */
    public function getCreatedAt($asString = true)
    {
        if (!$asString)
            return $this->createdAt;
        return ($this->createdAt === null ? '' : $this->createdAt->format('Y-m-d H:i:s'));
    }

    /**
     * @param bool $asString
     * @return DateTime|string|null
     */
    public function getUpdatedAt($asString = true)
    {
        if (!$asString)
            return $this->updatedAt;
        return ($this->updatedAt === null ? '' : $this->updatedAt->format('Y-m-d H:i:s'));
    }

    /**
     * @param $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @param array $notes
     * @return $this
     */
    public function setNotes(array $notes)
    {
        if (!empty($this->notes))
            $this->notes = array();
        foreach ($notes as $note)
            $this->notes[] = new Note($note);
        return $this;
    }

    /**
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Return notes that the requester added to ticket
     * @return array
     */
    public function getRequesterNotes()
    {
        $return = array();
        foreach ($this->notes as $note)
        {
            /** @var \Freshdesk\Model\Note $note */
            if ($note->getUserId() == $this->getRequesterId())
                $return[] = $note;
        }
        return $return;
    }

    /**
     * @return statusName
     */
    public function getStatusName()
    {
        return $this->statusName;
    }

    /**
     * @param $statusName
     */
    public function setStatusName($statusName)
    {
        $this->statusName = $statusName;
        return $this;
    }

    /**
     * @return delta
     */
    public function getDelta()
    {
        return $this->delta;
    }

    /**
     * @param $delta
     */
    public function setDelta($delta)
    {
        $this->delta = $delta;
        return $this;
    }

    /**
     * @return ownerId
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    /**
     * @return toEmail
     */
    public function getToEmail()
    {
        return $this->toEmail;
    }

    /**
     * @param $toEmail
     */
    public function setToEmail($toEmail)
    {
        $this->toEmail = $toEmail;
        return $this;
    }

    /**
     * @return trained
     */
    public function getTrained()
    {
        return $this->trained;
    }

    /**
     * @param $trained
     */
    public function setTrained($trained)
    {
        $this->trained = $trained;
        return $this;
    }

    /**
     * @return urgent
     */
    public function getUrgent()
    {
        return $this->urgent;
    }

    /**
     * @param $urgent
     */
    public function setUrgent($urgent)
    {
        $this->urgent = $urgent;
        return $this;
    }

    /**
     * @return requesterStatusName
     */
    public function getRequesterStatusName()
    {
        return $this->requesterStatusName;
    }

    /**
     * @param $requesterStatusName
     */
    public function setRequesterStatusName($requesterStatusName)
    {
        $this->requesterStatusName = $requesterStatusName;
        return $this;
    }

    /**
     * @return priorityName
     */
    public function getPriorityName()
    {
        return $this->priorityName;
    }

    /**
     * @param $priorityName
     */
    public function setPriorityName($priorityName)
    {
        $this->priorityName = $priorityName;
        return $this;
    }

    /**
     * @return sourceName
     */
    public function getSourceName()
    {
        return $this->sourceName;
    }

    /**
     * @param $sourceName
     */
    public function setSourceName($sourceName)
    {
        $this->sourceName = $sourceName;
        return $this;
    }

    /**
     * @return requesterName
     */
    public function getRequesterName()
    {
        return $this->requesterName;
    }

    /**
     * @param $requesterName
     */
    public function setRequesterName($requesterName)
    {
        $this->requesterName = $requesterName;
        return $this;
    }

    /**
     * @return responderName
     */
    public function getResponderName()
    {
        return $this->responderName;
    }

    /**
     * @param $responderName
     */
    public function setResponderName($responderName)
    {
        $this->responderName = $responderName;
        return $this;
    }

    /**
     * @return productId
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return toEmails
     */
    public function getToEmails()
    {
        return $this->toEmails;
    }

    /**
     * @param $toEmails
     */
    public function setToEmails($toEmails)
    {
        $this->toEmails = $toEmails;
        return $this;
    }

    /**
     * Get the json-string for this ticket instance
     * Ready-made to create a new freshdesk ticket
     * @return string
     */
    public function toJsonData($json = true)
    {
        $data = parent::toJsonData(false);
        $data['cc_emails'] = $this->getCcEmailVal();

        $custom = array();
        $customFields = $this->getCustomField();
        /** @var \Freshdesk\Model\CustomField $f */
        foreach ($customFields as $f)
        {
            $custom[$f->getName(true)] = $f->getValue();
        }

        if (!empty($custom))
        {
            $data[self::RESPONSE_KEY]['custom_field'] = $custom;
        }

        if ($json === true) {
            return json_encode($data);
        } else {
            return $data;
        }
    }
}
