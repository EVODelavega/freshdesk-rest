<?php

namespace Freshdesk\Model;
use \DateTime;

class Note extends Base
{
    const RESPONSE_KEY = 'note';

    const SOURCE_NOTE = 2;
    const SOURCE_MEDIA_INFO = 4;

    /**
     * @var \Freshdesk\Model\Ticket
     */
    protected $ticket = null;

    /**
     * @var string
     */
    protected $body = null;

    /**
     * @var bool
     */
    protected $private = false;

    /**
     * @var string
     */
    protected $bodyHtml = null;

    /**
     * @var \DateTime
     */
    protected $createdAt = null;

    /**
     * @var bool
     */
    protected $deleted = null;

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var bool
     */
    protected $incoming = null;

    /**
     * @var int
     */
    protected $source = null;

    /**
     * @var \DateTime
     */
    protected $updatedAt = null;

    /**
     * @var int
     */
    protected $userId = null;

    /**
     * @var array
     */
    protected $attachments = null;

    /**
     * @var array
     */
    protected $toDateTime = array(
        'setUpdatedAt',
        'setCreatedAt'
    );

    /**
     * @var array
     */
    protected $mandatory  =array(
        'body',
        'private',
    );

    /**
     * @var array
     */
    protected $readOnlyFields = array(
        'id',
        'createdAt', // dont post this to freshdesk
        'updatedAt', // dont post this to freshdesk
        'ticket', // hack to avoid sending ticket inside the note
    );

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $bodyHtml
     * @return $this
     */
    public function setBodyHtml($bodyHtml)
    {
        $this->bodyHtml = $bodyHtml;
        return $this;
    }

    /**
     * @return string
     */
    public function getBodyHtml()
    {
        return $this->bodyHtml;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @param bool $asString
     * @return \DateTime|string
     */
    public function getCreatedAt($asString = true)
    {
        if ($asString === true && $this->createdAt instanceof DateTime)
            return $this->createdAt->format('Y-m-d H:i:s');
        return $this->createdAt;
    }

    /**
     * @param boolean $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @param boolean $incoming
     * @return $this
     */
    public function setIncoming($incoming)
    {
        $this->incoming = $incoming;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIncoming()
    {
        return $this->incoming;
    }

    /**
     * @param boolean $private
     * @return $this
     */
    public function setPrivate($private)
    {
        $this->private = $private;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * @param int $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return int
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param \Freshdesk\Model\Ticket $ticket
     * @return $this
     */
    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;
        return $this;
    }

    /**
     * @return \Freshdesk\Model\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param array $attachments
     * @return $this
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt($asString = true)
    {
        if ($asString === true)
        {
            if ($this->updatedAt instanceof \DateTime)
                return $this->updatedAt->format('Y-m-d H:i:s');
            return '';
        }
        return $this->updatedAt;
    }

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the json-string for this ticket instance
     * Ready-made to create a new freshdesk ticket
     * @return string
     */
    public function toJsonData($json = true)
    {
        $data = parent::toJsonData(false);
        $data['helpdesk_' . self::RESPONSE_KEY] = $data[self::RESPONSE_KEY];
        unset($data[self::RESPONSE_KEY]);

        if ($json === true) {
            return json_encode($data);
        } else {
            return $data;
        }
    }

} 
