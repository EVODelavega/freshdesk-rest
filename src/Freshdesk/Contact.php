<?php

namespace Freshdesk;

use Freshdesk\Model\Contact as ContactM,
    \RuntimeException;

class Contact extends Rest
{

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
}
