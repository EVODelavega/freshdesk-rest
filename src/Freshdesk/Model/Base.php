<?php

namespace Freshdesk\Model;

use \Traversable,
    \InvalidArgumentException,
    \BadMethodCallException,
    \LogicException,
    \RuntimeException,
    \stdClass,
    \Iterator,
    \DateTime,
    \ReflectionClass,
    \ReflectionMethod;


abstract class Base implements \Freshdesk\Model, Iterator
{
    const RESPONSE_KEY = '';

    /**
     * @var string
     */
    private $class = null;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array
     */
    private $getters = array();

    /**
     * @var array
     */
    protected $toDateTime = array();

    /**
     * mandatory fields to be able to post something to freshdesk
     * fieldName or an array of [ fieldName, fieldName2, ... ]
     *
     * @var array
     */
    protected $mandatory = array();

    /**
     * fields who are set by freshdesk and are not modifiable via the api
     *
     * @var array
     */
    protected $readOnlyFields = array();

    /**
     * $data should be an array, an instance of stdClass
     * OR an object that implements the \Traversable interface
     * @param null|array|\stdClass|\Traversable $data = null
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct($data = null)
    {
        $this->class = $class = get_class($this);
        /** @noinspection PhpUndefinedFieldInspection */
        if ($class::RESPONSE_KEY === '')
            throw new \RuntimeException(
                sprintf(
                    '%s does not have a RESPONSE_KEY defined!',
                    $class
                )
            );
        $reflectionClass = new ReflectionClass(get_class($this));
        $methods = $reflectionClass->getMethods(
            ReflectionMethod::IS_PUBLIC
        );
        foreach ($methods as $method)
        {//use GETTERS for iterator interface
            $methodName = $method->name;
            if (substr($methodName, 0, 3) === 'get')
                $this->getters[] = $methodName;
        }
        if ($data === null)
            return $this;
        return $this->setAll($data);
    }

    /**
     * Disallow magic methods like this: they are slow
     * and actively encourage laziness, sloppyness,
     * They defeat the point of data-hiding,
     * encourage instance overloading,
     * AND can, possibly, circumvent validation!
     * @throws \LogicException
     */
    final public function __get($name)
    {
        throw new LogicException(
            sprintf(
                'Direct access of%s::%s not allowed, use getter',
                $this->class,
                $name
            )
        );
    }

    /**
     * Disallow magic setter, for the same reasons mentioned
     * in comments for __get method.
     * @throws \LogicException
     */
    final public function __set($name, $val)
    {
        throw new LogicException(
            sprintf(
                'Cannot assign %s::%s directly, use setter',
                $this->class,
                $name
            )
        );
    }

    /**
     * create final __call method, to avoid children
     * to implement this rubbish (same reasons as __get, __set)
     * @throws \BadMethodCallException
     */
    final public function __call($method, array $args)
    {
        throw new BadMethodCallException(
            sprintf(
                '%s::%s() either does not exist, or is not callable',
                $this->class,
                $method
            )
        );
    }

    /**
     * Set all properties by traversabele, stdClass or array
     * @param $mixed
     * @return $this
     * @throws \InvalidArgumentException
     */
    final public function setAll($mixed)
    {
        if ($mixed instanceof Traversable)
            return $this->setByTraversable($mixed);
        elseif (is_object($mixed) && !$mixed instanceof stdClass)
            throw new InvalidArgumentException(
                sprintf(
                    '%s::%s expects array, stdClass instance or Traversable object',
                    $this->class,
                    __FUNCTION__
                )
            );
        /** @noinspection PhpParamsInspection */
        return $this->setByObject(
            (object) $mixed
        );
    }

    /**
     * Non-final, as extended models might implement specific methods
     * used in child classes of related models...
     * ATM, this is a copy-paste version of the setByObj function, though
     * @param Traversable $obj
     * @return $this
     */
    protected function setByTraversable(Traversable $obj)
    {
        foreach ($obj as $p => $v)
        {
            $setter = 'set'.implode(
                    '',
                    array_map(
                        'ucfirst',
                        explode(
                            '_',
                            $p
                        )
                    )
                );
            if (method_exists($this, $setter))
                $this->{$setter}(
                    in_array($setter, $this->toDateTime) ? new DateTime($v) : $v
                );
        }
        return $this;
    }

    /**
     * @param \stdClass $obj
     * @return $this
     * @throws \InvalidArgumentException
     */
    final protected function setByObject(\stdClass $obj)
    {
        $class = $this->class;
        if (property_exists($obj, 'errors'))
            throw new InvalidArgumentException(
                sprintf(
                    'Failed to set %s, data was error response: %s',
                    $class,
                    $obj->errors->error
                )
            );
        /** @noinspection PhpUndefinedFieldInspection */
        if (property_exists($obj, $class::RESPONSE_KEY))
            $obj = $obj->{$class::RESPONSE_KEY};
        foreach ($obj as $p => $v)
        {
            $setter = 'set'.implode(
                    '',
                    array_map(
                        'ucfirst',
                        explode(
                            '_',
                            $p
                        )
                    )
                );
            if (method_exists($this, $setter))
                $this->{$setter}(
                    in_array($setter, $this->toDateTime) ? new DateTime($v) : $v
                );
        }
        return $this;
    }

    /**
     * generate json string that can be used to post/put to the freshdesk
     * api
     *
     * @return string
     */
    public function toJsonData($json = true)
    {
        /**
         * first check if all mandatory fields are there
         * else throw exception not everything is there
         */
        $mandatoryFieldsOk = true;
        foreach ($this->mandatory as $key => $mandatory) {
            if (! is_numeric($key) && is_string($key)) {
                $getter = 'get' . ucfirst($key);
                $value = $this->{$getter}();
                if (null !== $value) {
                    $mandatoryFieldsOk &= true;
                } else {
                    if (is_array($mandatory)) {
                        $ok = false;
                        foreach ($mandatory as $oneOfSome) {
                            $getter = 'get' . ucfirst($oneOfSome);
                            $value = $this->{$getter}();
                            if (null !== $value && $ok === false) {
                                $ok = true;
                            }
                        }
                        $mandatoryFieldsOk &= $ok;
                    } elseif (is_string($mandatory)) {
                        $getter = 'get' . ucfirst($mandatory);
                        $value = $this->{$getter}();
                        if (null !== $value) {
                            $mandatoryFieldsOk &= true;
                        } else {
                            $mandatoryFieldsOk &= false;
                        }
                    } else {
                        throw new Exception('Mandatory fields misconfigured!');
                    }
                }
            } elseif (is_array($mandatory)) {
                $ok = false;
                foreach ($mandatory as $oneOfSome) {
                    $getter = 'get' . ucfirst($oneOfSome);
                    $value = $this->{$getter}();
                    if (null !== $value && $ok === false) {
                        $ok = true;
                    }
                }
                $mandatoryFieldsOk &= $ok;
            } elseif (is_string($mandatory)) {
                $getter = 'get' . ucfirst($mandatory);
                $value = $this->{$getter}();
                if (null !== $value) {
                    $mandatoryFieldsOk &= true;
                } else {
                    $mandatoryFieldsOk &= false;
                }
            } else {
                throw new Exception('Mandatory fields misconfigured!');
            }
        }

        if ($mandatoryFieldsOk != true) {
            throw new RuntimeException('Not all mandatory fields are set.');
        }

        $jsonArray = array();

        foreach ($this as $position => $value) {
            $getter = $this->getters[$position];
            $fieldName = $this->getFieldName($getter);
            if (in_array($fieldName, $this->readOnlyFields)) {
                continue;
            }
            if (null !== $value) {
                $jsonFieldName = $this->getJsonFieldName($getter);
                if ($value instanceof \Freshdesk\Model) {
                    $jsonArray[$jsonFieldName] = $value->toJsonData(false);
                } else {
                    $jsonArray[$jsonFieldName] = $value;
                }
            }
        }

        $data = array(
            static::RESPONSE_KEY => $jsonArray
        );

        if ($json === true) {
            return json_encode($data);
        } else {
            return $data;
        }
    }

    protected function getFieldName($getter)
    {
        $field = lcfirst(str_replace('get', '', $getter));
        return $field;
    }

    protected function getJsonFieldName($getter)
    {
        $getter = str_replace('get', '', $getter);
        $jsonField = preg_replace("/(?<=[a-zA-Z])(?=[A-Z])/", "_", $getter);
        $jsonField = strtolower($jsonField);
        return $jsonField;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->{$this->getters[$this->position]}();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return (
            isset($this->getters[$this->position])
            && is_callable(array($this, $this->getters[$this->position]))
        );
    }

}
