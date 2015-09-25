<?php

namespace Freshdesk\Model;

class Contact extends Base
{
    const RESPONSE_KEY = 'user';

    /**
     * @var boolean
     */
    protected $active = null;

    /**
     * @var mixed
     */
    protected $address = null;

    /**
     * @var \DateTime
     */
    protected $createdAt = null;

    /**
     * @var mixed
     */
    protected $customerId = null;

    /**
     * @var boolean
     */
    protected $deleted = null;

    /**
     * @var mixed
     */
    protected $description = null;

    /**
     * @var string
     */
    protected $email = null;

    /**
     * @var mixed
     */
    protected $externalId = null;

    /**
     * @var mixed
     */
    protected $fbProfileId = null;

    /**
     * @var boolean
     */
    protected $helpdeskAgent = null;

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var mixed
     */
    protected $jobTitle = null;

    /**
     * @var string
     */
    protected $language = null;

    /**
     * @var mixed
     */
    protected $mobile = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var mixed
     */
    protected $phone = null;

    /**
     * @var string
     */
    protected $timeZone = null;

    /**
     * @var mixed
     */
    protected $twitterId = null;

    /**
     * @var \DateTime
     */
    protected $updatedAt = null;

    /**
     * @var array
     */
    protected $toDateTime = array(
        'setCreatedAt',
        'setUpdatedAt',
    );

    /**
     * @var array
     */
    protected $mandatory  =array(
        'name',
        array('email', 'phone', 'mobile'),
    );

    /**
     * @var array
     */
    protected $readOnlyFields = array(
        'id',
        'helpdeskAgent',
        'active',
        'externalId',
        'createdAt', // dont post this to freshdesk
        'updatedAt', // dont post this to freshdesk
    );

    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $arg
     * @return $this
     */
    public function setActive($arg)
    {
        $this->active = $arg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $arg
     * @return $this
     */
    public function setAddress($arg)
    {
        $this->address = $arg;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $arg
     * @return $this
     */
    public function setCreatedAt(\DateTime $arg)
    {
        $this->createdAt = $arg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param mixed $arg
     * @return $this
     */
    public function setCustomerId($arg)
    {
        $this->customerId = $arg;
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
     * @param boolean $arg
     * @return $this
     */
    public function setDeleted($arg)
    {
        $this->deleted = $arg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $arg
     * @return $this
     */
    public function setDescription($arg)
    {
        $this->description = $arg;
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
     * @param string $arg
     * @return $this
     */
    public function setEmail($arg)
    {
        $this->email = $arg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param mixed $arg
     * @return $this
     */
    public function setExternalId($arg)
    {
        $this->externalId = $arg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFbProfileId()
    {
        return $this->fbProfileId;
    }

    /**
     * @param mixed $arg
     * @return $this
     */
    public function setFbProfileId($arg)
    {
        $this->fbProfileId = $arg;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHelpdeskAgent()
    {
        return $this->helpdeskAgent;
    }

    /**
     * @param boolean $arg
     * @return $this
     */
    public function setHelpdeskAgent($arg)
    {
        $this->helpdeskAgent = $arg;
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
     * @param int $arg
     * @return $this
     */
    public function setId($arg)
    {
        $this->id = $arg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param mixed $arg
     * @return $this
     */
    public function setJobTitle($arg)
    {
        $this->jobTitle = $arg;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $arg
     * @return $this
     */
    public function setLanguage($arg)
    {
        $this->language = $arg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $arg
     * @return $this
     */
    public function setMobile($arg)
    {
        $this->mobile = $arg;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $arg
     * @return $this
     */
    public function setName($arg)
    {
        $this->name = $arg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $arg
     * @return $this
     */
    public function setPhone($arg)
    {
        $this->phone = $arg;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @param string $arg
     * @return $this
     */
    public function setTimeZone($arg)
    {
        $this->timeZone = $arg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * @param mixed $arg
     * @return $this
     */
    public function setTwitterId($arg)
    {
        $this->twitterId = $arg;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $arg
     * @return $this
     */
    public function setUpdatedAt(\DateTime $arg)
    {
        $this->updatedAt = $arg;
        return $this;
    }
}
