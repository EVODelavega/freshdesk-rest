<?php

namespace Freshdesk;

use Freshdesk\Model\Contact as ContactM,
    \RuntimeException;

class Contact extends Rest
{
    const SEARCH_EMAIL = 'email';
    const SEARCH_MOBILE = 'mobile';
    const SEARCH_PHONE = 'phone';
    const SEARCH_STATE = 'state';
    const SEARCH_NAME = 'letter';

    const STATE_VERIFIED = 'verified';
    const STATE_UNVERIFIED = 'unverified';
    const STATE_ALL = 'all';
    const STATE_DELETED = 'deleted';

    /**
     * get the contact url
     *
     * @param array $options
     * array(
     *   'search' => self::SEARCH_*,
     *   'value' => <any> or self::STATE_*,
     * )
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getGetContactUrl($options = null)
    {
        $url = '/contacts.json';

        if (is_array($options)) {
            if (!empty($options['search'])) {
                $search = $this->checkSearch($options['search']);
                if (empty($options['value'])) {
                    throw new InvalidArgumentException(
                        'when searching for ' . $search . ' value must be given'
                    );
                }
                switch ($search) {
                case self::SEARCH_EMAIL:
                case self::SEARCH_MOBILE:
                case self::SEARCH_PHONE:
                    $url .= '?query='
                        . urlencode($search . ' is ' . $options['value']);
                    break;
                case self::SEARCH_STATE:
                    $state = $this->checkState($options['value']);
                    $url .= '?' . $search . '=' . $state;
                    break;
                case self::SEARCH_NAME:
                    $url .= '?' . $search . '=' . $options['value'];
                    break;
                }
            }
        }

        return $url;
    }

    /**
     * check given search
     *
     * @param string $search
     * @return string
     * @throws InvalidArgumentException
     */
    protected function checkSearch($search)
    {
        if (self::SEARCH_EMAIL !== $search
            && self::SEARCH_MOBILE !== $search
            && self::SEARCH_PHONE !== $search
            && self::SEARCH_STATE !== $search
            && self::SEARCH_NAME !== $search) {
            throw new InvalidArgumentException(
                'search must match one of the predefined constants'
            );
        }
        return $search;
    }

    /**
     * check given state
     *
     * @param string $state
     * @return string
     * @throws InvalidArgumentException
     */
    protected function checkState($state)
    {
        if (self::STATE_VERIFIED !== $state
            && self::STATE_UNVERIFIED !== $state
            && self::STATE_ALL !== $state
            && self::STATE_DELETED !== $state) {
            throw new InvalidArgumentException(
                'state must match one of the predefined constants'
            );
        }
        return $state;
    }

    /**
     * @param $id
     * @param ContactM $model
     * @return \Freshdesk\Model\Contact
     * @throws \RuntimeException
     */
    public function getContactById($id, ContactM $model = null)
    {
        if ($id instanceof ContactM)
        {
            $model = $id;
            $id = $model->getId();
        }

        $url = '/contacts/' . $id . '.json';

        $response = json_decode(
            $this->restCall(
                $url,
                Rest::METHOD_GET
            )
        );
        if (property_exists($response, 'errors'))
            throw new RuntimeException(
                sprintf('Error: %s', $response->errors->error)
            );
        if ($model === null)
            $model = new ContactM();
        return $model->setAll(
            $response
        );
    }

    /**
     * add a new user/contact to freshdesk
     *
     * @param \Freshdesk\Model\Contact $contact
     * @return \Freshdesk\Model\Contact
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function createNewContact(ContactM $contact)
    {
        $url = '/contacts.json';

        $data = $contact->toJsonData();
        $response = $this->restCall(
            $url,
            self::METHOD_POST,
            $data
        );
        if (!$response) {
            throw new RuntimeException(
                sprintf(
                    'Failed to create user with data: %s',
                    $data
                )
            );
        }
        $json = json_decode(
            $response
        );
        if (property_exists($response, 'errors')) {
            throw new RuntimeException(
                sprintf('Error: %s', $response->errors->error)
            );
        }
        //update contact
        return $contact->setAll(
            $json->{ContactM::RESPONSE_KEY}
        );
    }

    /**
     * update user/contact in freshdesk
     *
     * @param \Freshdesk\Model\Contact $contact
     * @return \Freshdesk\Model\Contact
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function updateContact(ContactM $contact)
    {
        $url = '/contacts/%d.json';

        $id = $contact->getId();

        $url = sprintf($url, $id);

        $data = $contact->toJsonData();
        $response = $this->restCall(
            $url,
            self::METHOD_PUT,
            $data
        );
        if (!$response) {
            throw new RuntimeException(
                sprintf(
                    'Failed to create user with data: %s',
                    $data
                )
            );
        }
        $json = json_decode(
            $response
        );
        if (property_exists($response, 'errors')) {
            throw new RuntimeException(
                sprintf('Error: %s', $response->errors->error)
            );
        }
        //update contact
        return $contact->setAll(
            $json->{ContactM::RESPONSE_KEY}
        );
    }

    /**
     * delete/remove user/contact from freshdesk
     *
     * @param \Freshdesk\Model\Contact $contact
     * @return \Freshdesk\Model\Contact
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function deleteContact(ContactM $contact)
    {
        $url = '/contacts/%d.json';

        $id = $contact->getId();

        $url = sprintf($url, $id);

        $response = $this->restCall(
            $url,
            self::METHOD_DEL
        );

        $contact->setDeleted(true);
        return $contact;
    }

    /**
     * search contacts
     *
     * @param array $options
     * array(
     *   'search' => self::SEARCH_*,
     *   'value' => <any> or self::STATE_*,
     * )
     * @return array<Freshdesk\Model\Contact>
     * @throws InvalidArgumentException
     */
    public function searchContacts($options = null)
    {
        $url = $this->getGetContactUrl($options);

        $response = $this->restCall(
            $url,
            self::METHOD_GET
        );

        $json = json_decode($response);

        $out = array();

        foreach ($json as $contactJson) {
            if (isset($contactJson->{ContactM::RESPONSE_KEY})) {
                $contact = new ContactM($contactJson->{ContactM::RESPONSE_KEY});
                $out[] = $contact;
            }
        }

        return $out;
    }

    /**
     * search contacts using email
     *
     * @param string $email
     * @return array<Freshdesk\Model\Contact>|Freshdesk\Model\Contact
     */
    public function searchContactsByEmail($email)
    {
        if (!filter_var($email,\FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid email address',
                    $email
                )
            );
        }

        $contacts = $this->searchContacts(
            array(
                'search' => self::SEARCH_EMAIL,
                'value' => $email,
            )
        );

        if (1 === count($contacts)) {
            return reset($contacts);
        } else {
            return $contacts;
        }
    }

    /**
     * search contacts using mobile phone
     *
     * @param string $mobile
     * @return array<Freshdesk\Model\Contact>|Freshdesk\Model\Contact
     */
    public function searchContactsByMobile($mobile)
    {
        $contacts = $this->searchContacts(
            array(
                'search' => self::SEARCH_MOBILE,
                'value' => $mobile,
            )
        );

        if (1 === count($contacts)) {
            return reset($contacts);
        } else {
            return $contacts;
        }
    }

    /**
     * search contacts using phone
     *
     * @param string $phone
     * @return array<Freshdesk\Model\Contact>|Freshdesk\Model\Contact
     */
    public function searchContactsByPhone($phone)
    {
        $contacts = $this->searchContacts(
            array(
                'search' => self::SEARCH_PHONE,
                'value' => $phone,
            )
        );

        if (1 === count($contacts)) {
            return reset($contacts);
        } else {
            return $contacts;
        }
    }

    /**
     * search contacts using the state
     *
     * all verified contacts
     * all unverified contacts
     * all contacts
     * all deleted contacts
     *
     * @param string $state
     * @return array<Freshdesk\Model\Contact>
     */
    public function searchContactsByState($state)
    {
        $contacts = $this->searchContacts(
            array(
                'search' => self::SEARCH_STATE,
                'value' => $state,
            )
        );

        return $contacts;
    }

    /**
     * search contacts using their name or part of the name
     *
     * @param string $name
     * @return array<Freshdesk\Model\Contact>|Freshdesk\Model\Contact
     */
    public function searchContactsByName($name)
    {
        $contacts = $this->searchContacts(
            array(
                'search' => self::SEARCH_NAME,
                'value' => $name,
            )
        );

        if (1 === count($contacts)) {
            return reset($contacts);
        } else {
            return $contacts;
        }
    }
}
