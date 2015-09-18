<?php
namespace Freshdesk;

use Freshdesk\Model\Contact as ContactM;
use Freshdesk\Model\Ticket as TicketM,
    Freshdesk\Model\Note,
    \InvalidArgumentException,
    \RuntimeException;

class Ticket extends Rest
{

    const SEARCH_FILTER = 'filter';
    const SEARCH_REQUESTER = 'requester';
    const SEARCH_COMPANY_NAME = 'company_name';
    const SEARCH_COMPANY_ID = 'company_id';
    const SEARCH_EMAIL = 'email';

    const FILTER_ALL = 'all_tickets';
    const FILTER_OPEN = 'open';
    const FILTER_HOLD = 'on_hold';
    const FILTER_OVERDUE = 'overdue';
    const FILTER_TODAY = 'due_today';
    const FILTER_NEW = 'new';
    const FILTER_SPAM = 'spam';
    const FILTER_DELETED = 'deleted';

    const SORT_CREATED = 'created_at';
    const SORT_DUE = 'due_by';
    const SORT_UPDATED = 'updated_at';
    const SORT_PRIORITY = 'priority';
    const SORT_STATUS = 'status';

    const SORTDIR_ASC = 'asc';
    const SORTDIR_DESC = 'desc';

    /**
     * Returns formatted url
     * @param array $options
     * array(
     *   'search' => self::SEARCH_*,
     *   'filter' => self::FILTER_*,
     *   'value' => 'optional value',
     *   'sort' => self::SORT_*,
     *   'sortdir' => self::SORTDIR_*,
     *   'page' => 2,
     * );
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getGetTicketUrl($options = null)
    {
        $configration = array(
            'search' => self::SEARCH_FILTER,
            'filter' => self::FILTER_ALL,
            'value' => null,
            'sort' => null,
            'sortdir' => null,
            'page' => null,
        );

        $url = '';

        if (null === $options) {
            $url = '/helpdesk/tickets.json';
        } elseif (is_array($options)) {
            $options = array_merge(
                $configration,
                $options
            );

            $this->checkSearch($options['search']);

            switch ($options['search']) {
            case self::SEARCH_REQUESTER:
                $url = '/helpdesk/tickets/filter/requester/';
                if (empty($options['value'])) {
                    throw new InvalidArgumentException(
                        'you must pass requester_id in the value'
                    );
                }
                $url .= $options['value'];
                // @todo check if we can add a filter here
                break;
            case self::SEARCH_COMPANY_NAME:
            case self::SEARCH_COMPANY_ID:
            case self::SEARCH_EMAIL:
                if (empty($options['value'])) {
                    throw new InvalidArgumentException(
                        'you must pass ' . $options['search'] . ' in the value'
                    );
                }

                // search
                $url = '/helpdesk/tickets.json?' . $options['search']
                    . '=' . $options['value'];

                // filter
                $url .= '&filter_name=' . $this->checkFilter($options['filter']);
                break;
            case self::SEARCH_FILTER:
            default:
                $url = '/helpdesk/tickets/filter/';

                if (empty($options['filter'])) {
                    throw new InvalidArgumentException(
                        'filter must be set when using filter search'
                    );
                }
                $url .= $this->checkFilter($options['filter']);
                break;
            }

            // must add format=json
            if ($options['search'] !== self::SEARCH_COMPANY_NAME
                && $options['search'] !== self::SEARCH_COMPANY_ID
                && $options['search'] !== self::SEARCH_EMAIL) {
                if (stristr($url, '?')) {
                    $url .= '&';
                } else {
                    $url .= '?';
                }
                $url .= "format=json";
            }

            // sort if set
            if (!empty($options['sort'])) {
                $sort = $this->checkSort($options['sort']);
                $url .= '&wf_order=' . $sort;
                // sort direction if set
                if (!empty($options['sortdir'])) {
                    $sortdir = $this->checkSortdir($options['sortdir']);
                    $url .= '&wf_order_type=' . $sortdir;
                }
            }

            // paging
            if (!empty($options['page']) && is_numeric($options['page'])) {
                $url .= '&page=' . (int) $options['page'];
            }
        } else {
            throw new InvalidArgumentException('options must be null or array');
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
        if (self::SEARCH_FILTER !== $search
            && self::SEARCH_REQUESTER !== $search
            && self::SEARCH_COMPANY_NAME !== $search
            && self::SEARCH_COMPANY_ID !== $search
            && self::SEARCH_EMAIL !== $search) {
            throw new InvalidArgumentException(
                'search must match one of the predefined constants'
            );
        }
        return $search;
    }

    /**
     * check given filter
     *
     * @param string $filter
     * @return string
     * @throws InvalidArgumentException
     */
    protected function checkFilter($filter)
    {
        if (self::FILTER_ALL !== $filter
            && self::FILTER_OPEN !== $filter
            && self::FILTER_HOLD !== $filter
            && self::FILTER_OVERDUE !== $filter
            && self::FILTER_TODAY !== $filter
            && self::FILTER_NEW !== $filter
            && self::FILTER_SPAM !== $filter
            && self::FILTER_DELETED !== $filter) {
            throw new InvalidArgumentException(
                'filter must be one of the predefined constants'
            );
        }
        return $filter;
    }

    /**
     * check given sort
     *
     * @param string $sort
     * @return string
     * @throws InvalidArgumentException
     */
    protected function checkSort($sort)
    {
        if (self::SORT_CREATED !== $sort
            && self::SORT_DUE !== $sort
            && self::SORT_UPDATED !== $sort
            && self::SORT_PRIORITY !== $sort
            && self::SORT_STATUS !== $sort) {
            throw new InvalidArgumentException(
                'sort must be one of the predefined constants'
            );
        }
        return $sort;
    }

    /**
     * check given sortdir
     *
     * @param string $sortdir
     * @return string
     * @throws InvalidArgumentException
     */
    protected function checkSortdir($sortdir)
    {
        if (self::SORTDIR_ASC !== $sortdir
            && self::SORTDIR_DESC !== $sortdir) {
            throw new InvalidArgumentException(
                'sortdir must be one of the predefined constants'
            );
        }
        return $sortdir;
    }

    /**
     * Returns all the open tickets of the API user's credentials used for the request
     * @return null|array
     */
    public function getApiUserTickets()
    {
        $json = json_decode(
            $this->restCall(
                '/helpdesk/tickets.json',
                self::METHOD_GET
            )
        );

        if (!$json)
            return null;
        $models = array();
        foreach ($json as $ticket)
        {
            $models[] = new TicketM($ticket);
        }
        return $models;
    }

    /**
     * Returns searched tickets
     * @param array $options
     * array(
     *   'search' => self::SEARCH_*,
     *   'filter' => self::FILTER_*,
     *   'value' => 'optional value',
     *   'sort' => self::SORT_*,
     *   'sortdir' => self::SORTDIR_*,
     *   'page' => 2,
     * );
     * @return array
     * @throws InvalidArgumentException
     */
    public function getTickets($options, $models = true)
    {
        $json = $this->restCall(
            $this->getGetTicketUrl($options),
            self::METHOD_GET
        );
        if (!$json)
            return null;

        return $this->returnTickets($json, $models);
    }

    /**
     * return ticket models or array<array>
     *
     * @return void
     * @author Ike Devolder <ike.devolder@studioemma.eu>
     */
    protected function returnTickets($json, $models)
    {
        $data = json_decode($json, true);
        if (true === $models) {
            $out = array();
            foreach ($data as $ticket)
            {
                $out[] = new TicketM($ticket);
            }
            return $out;
        } else {
            return $data;
        }
    }

    /**
     * Get all tickets from user (based on email)
     * @param string $email
     * @param bool $models
     * @return null|\stdClass|array
     * @throws \InvalidArgumentException
     */
    public function getTicketsByEmail($email, $models = true)
    {
        if (!filter_var($email,\FILTER_VALIDATE_EMAIL))
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid email address',
                    $email
                )
            );

        return $this->getTickets(
            array(
                'search' => self::SEARCH_EMAIL,
                'value' => $email,
            ),
            $models
        );
    }

    /**
     * Get open tickets for $email
     * @param string $email
     * @param bool $models
     * @return null|\stdClass|array
     * @throws \InvalidArgumentException
     */
    public function getOpenTicketsByEmail($email, $models = true)
    {
        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid email address',
                    $email
                )
            );
        }

        return $this->getTickets(
            array(
                'search' => self::SEARCH_EMAIL,
                'filter' => self::FILTER_OPEN,
                'value' => $email,
            ),
            $models
        );
    }

    /**
     * Get tickets that are neither closed or resolved
     * @todo can we use a filter
     * @param string $email
     * @return null|array
     */
    public function getActiveTicketsByEmail($email)
    {
        $tickets = $this->getTicketsByEmail($email);
        if (!$tickets)
            return null;
        $return = array();
        for ($i=0, $j=count($tickets);$i<$j;++$i)
        {
            if ($tickets[$i]->status < TicketM::STATUS_RESOLVED)
                $return[] = $tickets[$i];
        }
        return $return;
    }

    /**
     * @todo can we use a filter
     * @param string $email
     * @return array<\stdClass>
     */
    public function getResolvedTicketsByEmail($email)
    {
        $tickets = $this->getTicketsByEmail($email);
        $return = array();
        for ($i=0, $j=count($tickets);$i<$j;++$i)
        {
            if ($tickets[$i]->status === TicketM::STATUS_RESOLVED)
                $return[] = $tickets[$i]->display_id;
        }
        return $return;
    }

    /**
     * get organized array of tickets by email
     *
     * @todo filter ?
     *
     * @param ContactM|string $contact
     * @param bool $assoc = true
     * @return array
     */
    public function getGroupedTicketsByEmail($email, $assoc = true)
    {
        $getter = $assoc === true ? 'getStatusName' : 'getStatus';
        $tickets = $this->getTicketsByEmail($email);
        $groups = array();
        foreach ($tickets as $ticket)
        {
            $model = new TicketM($ticket);
            $key = $model->{$getter}();
            if (!isset($groups[$key]))
                $groups[$key] = array();
            $groups[$key][] = $model;
        }
        return $groups;
    }

    /**
     * @todo filter ?
     *
     * @param $email
     * @param int $status
     * @return array|null
     */
    public function getTicketIdsByEmail($email, $status = TicketM::STATUS_ALL)
    {
        $tickets = $this->getTicketsByEmail($email);
        if (!$tickets)
            return null;
        $return = array();
        for ($i=0, $j=count($tickets);$i<$j;++$i)
        {
            if ($status === TicketM::STATUS_ALL || $tickets[$i]->status == $status)
                $return[] = $tickets[$i]->display_id;
        }
        return $return;
    }

    /**
     * get all tickets knowing the requesterId
     *
     * @param int $requesterId
     * @param bool $models
     * @return null|array<Freshdesk\Model\Ticket>|array<array>
     */
    public function getTicketsByRequesterId($requesterId, $models = true)
    {
        return $this->getTickets(
            array(
                'search' => self::SEARCH_REQUESTER,
                'value' => $requesterId,
            ),
            $models
        );
    }

    /**
     * Get tickets in view, specify page, defaults to 0 === get all pages
     *
     * @todo getGetTicketUrl
     *
     * @param int $viewId
     * @param int $page = 0
     * @return array
     */
    public function getTicketsByView($viewId, $page = 0)
    {
        if ($page === 0)
        {
            $data = array();
            $current = 1;
            while ($tickets = $this->getTicketsByView($viewId, $current))
                $data[$current++] = $tickets;
            return $data;
        }
        $request = sprintf(
            '/helpdesk/tickets/view/%d?format=json&page=%d',
            (int) $viewId,
            (int) $page
        );
        return json_decode(
            $this->restCall(
                $request,
                self::METHOD_GET
            )
        );
    }

    /**
     * @param int $id
     * @param TicketM $model = null
     * @return TicketM
     * @throws \RuntimeException
     */
    public function getTicketById($id, TicketM $model = null)
    {
        $ticket = json_decode(
            $this->restCall(
                sprintf(
                    '/helpdesk/tickets/%s.json',
                    (int) $id
                ),
                self::METHOD_GET
            )
        );
        if (property_exists($ticket, 'errors'))
            throw new RuntimeException(
                sprintf(
                    'Ticket %d not found: %s',
                    $id,
                    $ticket->errors->error
                )
            );
        if ($model)
            return $model->setAll(
                $ticket->helpdesk_ticket
            );
        return new TicketM($ticket->helpdesk_ticket);
    }

    /**
     * get "pure" json data
     * @param TicketM $model
     * @return \stdClass
     */
    public function getRawTicket(TicketM $model)
    {
        return json_decode(
            $this->restCall(
                sprintf(
                    '/helpdesk/tickets/%s.json',
                    $model->getDisplayId() // @todo must this not be getId ?
                ),
                self::METHOD_GET
            )
        );
    }

    /**
     * @param TicketM $model
     * @param bool $requesterOnly = true
     * @param bool $includePrivate = false
     * @return array<\Freshdesk\Model\Ticket>
     */
    public function getTicketNotes(TicketM $model, $requesterOnly = true, $includePrivate = false)
    {
        $notes = $model->getNotes();
        if (empty($notes))
        {
            $model = $this->getFullTicket(
                $model->getDisplayId(), // @todo must this not be getId ?
                $model
            );
            $notes = $model->getNotes();
        }
        $return = array();
        foreach ($notes as $note)
        {
            /** @var \Freshdesk\Model\Note $note */
            if ($includePrivate === false && $note->getPrivate())
                continue;//do not include private tickets
            if ($requesterOnly === true && $note->getUserId() === $model->getRequesterId())
                $return[] = $note;
            else
                $return[] = $note;
        }
        return $return;
    }

    /**
     * Set displayId on model, pass to this function to auto-complete
     * @param TicketM $ticket
     * @return TicketM
     */
    public function getFullTicket(TicketM $ticket)
    {
        $response = json_decode(
            $this->restCall(
                sprintf(
                    '/helpdesk/tickets/%d.json',
                    $ticket->getDisplayId() // @todo must this not be getId ?
                ),
                self::METHOD_GET
            )
        );
        return $ticket->setAll($response);
    }

    /**
     * Create new ticket, returns model after setting createdAt property
     * @param TicketM $ticket
     * @return \Freshdesk\Model\Ticket
     * @throws \RuntimeException
     */
    public function createNewTicket(TicketM $ticket)
    {
        $data = $ticket->toJsonData();
        $response = $this->restCall(
            '/helpdesk/tickets.json',
            self::METHOD_POST,
            $data
        );
        if (!$response)
            throw new RuntimeException(
                sprintf(
                    'Failed to create ticket with data: %s',
                    $data
                )
            );
        $json = json_decode(
            $response
        );
        //update ticket model, set ids and created timestamp
        return $ticket->setAll(
            $json->helpdesk_ticket
        );
    }

    /**
     * Update the ticket
     * @param TicketM $ticket
     * @return $this
     */
    public function updateTicket(TicketM $ticket)
    {
        $url = sprintf(
            '/helpdesk/tickets/%d.json',
            $ticket->getDisplayId() // @todo must this not be getId ?
        );
        $data = $ticket->toJsonData();
        $response = json_decode(
            $this->restCall(
                $url,
                self::METHOD_PUT,
                $data
            )
        );
        return $ticket->setAll(
            $response->ticket
        );
    }

    /**
     * Delete a ticket, optionally make a second API call, to verify success
     * just in case the API response proves to be unreliable
     * @param TicketM $ticket
     * @param bool $reload = false
     * @return TicketM
     */
    public function deleteTicket(TicketM $ticket, $reload = false)
    {
        $url = sprintf(
            '/helpdesk/tickets/%d.json',
            $ticket->getDisplayId() // @todo must this not be getId ?
        );
        $response = $ticket->toJsonData();
        $response = json_decode(
            $this->restCall(
                $url,
                self::METHOD_DEL
            )
        );
        if ($reload === true)
            return $this->getFullTicket($ticket);
        return $ticket->setDeleted(true);
    }

    /**
     * Restore a previously deleted ticket
     * @param TicketM $ticket
     * @return TicketM
     */
    public function restoreTicket(TicketM $ticket)
    {
        $url = sprintf(
            '/helpdesk/tickets/%d/restore.json',
            $ticket->getDisplayId() // @todo must this not be getId ?
        );
        $response = json_decode(
            $this->restCall(
                $url,
                self::METHOD_PUT
            )
        );
        if (is_array($response))
        {//API documentation is a tad unclear: according to freshdesk.com/api, the response is an array
            $response = $response[0];
        }
        return $ticket->setAll($response);
    }

    /**
     * Assign given ticket to responder by id
     * @param TicketM $ticket
     * @param int $responder
     * @return TicketM
     * @throws \InvalidArgumentException
     */
    public function assignTicket(TicketM $ticket, $responder)
    {
        if (!is_numeric($responder) || $responder < 1)
        {
            throw new \InvalidArgumentException(
                sprintf(
                    'Failed to assign ticket #%d to "%s", responder must be a positive numeric value',
                    $ticket->getDisplayId(), // @todo must this not be getId ?
                    $responder
                )
            );
        }
        $url = sprintf(
            '/helpdesk/tickets/%d/assign.json?responder_id=%d',
            $ticket->getDisplayId(), // @todo must this not be getId ?
            (int) $responder
        );
        $response = json_decode(
            $this->restCall(
                $url,
                self::METHOD_PUT
            )
        );
        if (is_array($response))
        {//again, the docs on freshdesk.com/api are unclear. This call seems to be returning an array
            $response = $response[0];
        }
        return $ticket->setAll($response);
    }

    /**
     * Add note to ticket, ticket model is expected to be set on Note model
     * @param Note $note
     * @return Note
     * @throws \RuntimeException
     */
    public function addNoteToTicket(Note $note)
    {
        $url = sprintf(
            '/helpdesk/tickets/%d/conversations/note.json',
            $note->getTicket()
                ->getDisplayId() // @todo must this not be getId ?
        );
        $response = json_decode(
            $this->restCall(
                $url,
                self::METHOD_POST,
                $note->toJsonData()
            )
        );
        if (!property_exists($response, 'note'))
            throw new RuntimeException(
                sprintf(
                    'Failed to add note: %s',
                    json_encode($response)
                )
            );
        //todo set properties on Note instance
        return $note->setAll($response);
    }

    /**
     * get the available ticket fields
     *
     * @return array<stdClass>
     */
    public function getTicketFields()
    {
        $url = '/ticket_fields.json';

        $responseKey = 'ticket_field';

        $response = json_decode(
            $this->restCall(
                $url,
                self::METHOD_GET
            )
        );

        $out = array();

        foreach ($response as $ticketField) {
            if (isset($ticketField->ticket_field)
                && is_object($ticketField->ticket_field)) {
                $out[] = $ticketField->ticket_field;
            }
        }

        return $out;
    }
}
