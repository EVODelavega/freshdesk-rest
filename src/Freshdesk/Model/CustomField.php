<?php
namespace Freshdesk\Model;

use \Traversable;

class CustomField extends Base
{
    //use to remove numeric appendix from field-names
    //This constant is checked by Base class
    const RESPONSE_KEY = '/_\d+$/';

    /**
     * @var string
     */
    protected $numericAppendix = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @var \Freshdesk\Model\Ticket
     */
    protected $ticket = null;

    /**
     * @param \Traversable $obj
     * @return $this
     */
    public function setByTraversable(Traversable $obj)
    {
        if (!$obj instanceof Ticket)
            return parent::setByTraversable($obj);
        $custom = $obj->getCustomField();
        $fields = array();
        foreach ($custom as $k => $v)
        {
            $fields[] = new CustomField(
                array(
                    'name'      => $k,
                    'value'     => $v,
                    'ticket'    => $obj
                )
            );
        }
        $last = array_pop($fields);
        /** @noinspection PhpUndefinedMethodInspection */
        $fields[] = $this->setName(
            $last->getName()
            )->setValue(
                $last->getValue()
            )->setTicket($obj);
        $obj->setCustomField($fields);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        if (preg_match(self::RESPONSE_KEY, $name, $match))
        {
            $this->numericAppendix = $match[0];
            $name = str_replace(
                $this->numericAppendix,
                '',
                $name
            );
        }
        $this->name = $name;
        return $this;
    }

    /**
     * @param bool $full = false
     * @return string
     */
    public function getName($full = false)
    {
        if ($full === true)
            return $this->name.$this->numericAppendix;
        return $this->name;
    }

    /**
     * @param mixed $mix
     * @return $this
     */
    public function setValue($mix)
    {
        $this->value = $mix;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param \Freshdesk\Model\Ticket $mod
     * @return $this
     */
    public function setTicket(Ticket $mod)
    {
        $this->ticket = $mod;
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
     * @param string $numericAppendix
     * @return $this
     */
    public function setNumericAppendix($numericAppendix)
    {
        $numericAppendix = (string) $numericAppendix;//force string
        if ($numericAppendix{0} !== '_')
            $numericAppendix = '_'.$numericAppendix;
        $this->numericAppendix = $numericAppendix;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumericAppendix()
    {
        return $this->numericAppendix;
    }

    /**
     * @return string
     */
    public function toJsonData($json = true)
    {
        return json_encode(
            array(
                'custom_field'  => array(
                    $this->name => $this->value
                )
            )
        );
    }
}
