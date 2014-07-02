<?php
namespace Freshdesk;

use Freshdesk\Config\Connection;
/**
 * Composer-aware fork of blak3r's initial freshdesk API wrapper
 * Based on the https://github.com/phikai/freshdesk-rest fork
 * of Blake's initial work.
 *
 * The end-goal of this repo is to generate a more generic,
 * easily extendable starting-point for your API calls.
 */

class Rest
{

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_DEL = 'DELETE';
    const METHOD_PUT = 'PUT';

    /**
     * @var \Freshdesk\Config\Connection
     */
    protected $config = null;

    /**
     * @var int
     */
    protected $lastHttpStatusCode = null;

    /**
     * @var string
     */
    protected $lastHttpResponseText = '';

    /**
     * @var string
     */
    protected $proxyServer = "";

    /**
     * @var array<string>
     */
    protected $debugLogs = array();

    public function __construct(Connection $config)
    {
        $this->config = $config;
    }

    /**
     * @param $urlMinusDomain - should start with /... example /solutions/categories.xml
     * @param $method - should be either GET, POST, PUT (and theoretically DELETE but that's untested).
     * @param string $postData - only specified if $method == POST or PUT
     * @param $debugMode {bool} optional - prints the request and response with headers
     * @return the raw response
     */
    protected function restCall($urlMinusDomain, $method, $postData = '',$debugMode=false)
    {
        if ($urlMinusDomain{0} !== '/')
            $urlMinusDomain = '/'.$urlMinusDomain;
        $url = $this->config->getScheme().
                $this->config->getDomain().
                $urlMinusDomain;

        $header = array(
            "Content-type: application/json"
        );

        $opts = array(
            \CURLOPT_USERPWD        => $this->config->getUsername().':'.$this->config->getPassword(),
            \CURLOPT_HTTPHEADER     => array(
                'Content-type: application/json'
            ),
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HTTPAUTH       => \CURLAUTH_BASIC,
            \CURLOPT_SSL_VERIFYHOST => 0,
            \CURLOPT_SSL_VERIFYPEER  => 0
        );
        if ($this->proxyServer)
            $opts[\CURLOPT_PROXY] = $this->proxyServer;
        if ($debugMode)
        {
            // CURLOPT_VERBOSE: TRUE to output verbose information. Writes output to STDERR,
            // or the file specified using CURLOPT_STDERR.
            $opts[\CURLOPT_STDERR] = fopen('php://temp', 'rw+');
            $opts[\CURLOPT_VERBOSE] = true;
        }
        switch (strtoupper(trim($method)))
        {
            case self::METHOD_POST:
                if (empty($postData))
                    $opts[\CURLOPT_HTTPHEADER][] = 'Content-length: 0';
                $opts[\CURLOPT_POST] = true;
                $opts[\CURLOPT_POSTFIELDS] = $postData;
                break;
            case self::METHOD_PUT:
                $opts[\CURLOPT_CUSTOMREQUEST] =  'PUT';
                $opts[\CURLOPT_POSTFIELDS] = $postData;
            case self::METHOD_DEL:
                $opts[\CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            case self::METHOD_GET:
                $opts[\CURLOPT_POST] = false;
                break;
            default:
                if ($debugMode)
                    fclose($opts[\CURLOPT_STDERR]);//close stream, we have an error
                throw new \InvalidArgumentException(
                    sprintf(
                        'Method "%s" is not a valid method, use %s::METHOD_* constants',
                        $method,
                        __CLASS__
                    )
                );
        }
        $ch = curl_init($url);
        if (!is_resource($ch))
        {
            fclose($opts[\CURLOPT_STDERR]);//close stream
            throw new \RuntimeException(
                'Could not init curl request'
            );
        }
        if (!curl_setopt_array($ch, $opts))
        {
            fclose($opts[\CURLOPT_STDERR]);
            throw new \RuntimeException('Could not set curl options');
        }

        $this->lastHttpResponseText = $httpResponse = curl_exec($ch);
        $this->lastHttpStatusCode = $httpCode = (int) curl_getinfo(
            $ch,
            \CURLINFO_HTTP_CODE
        );
        if ($httpCode < 200 || $httpCode > 299)
        {
            if (!$debugMode)
            {
                fclose($opts[\CURLOPT_STDERR]);//close stream
                curl_close($ch);//close curl
                throw new \RuntimeException(
                    sprintf(
                        '%s action to %s returned unexpected HTTP code (%d), repsonse: %s',
                        $method,
                        $url,
                        $httpCode,
                        $httpRespnse
                    )
                );
            }
        }
        if ( $debugMode )
        {
            if (rewind($opts[\CURLOPT_STDERR]))
                $this->debugLogs[] = array(
                    'URL'       => $url,
                    'Method'    => $method,
                    'HTTPCode'  => $httpCode,
                    'Stream'    => stream_get_contents($opts[\CURLOPT_STDERR])
                );
            else
                $this->debugLogs[] = array(
                    'URL'       => $url,
                    'Method'    => $method,
                    'HTTPCode'  => $httpCode,
                    'Stream'    => 'ERROR: rewind stream failed!'
                );
            fclose($opts[\CURLOPT_STDERR]);
        }
        curl_close($ch);

        return $httpResponse;
    }

    /**
     * Get the log data if calls were made in debug mode
     * @return array
     */
    public function logDebugData()
    {
        if (empty($this->debugLogs))
            return array();//nothing to log
        //first line => headers
        $data = array(
            implode(
                ' | ',
                array_keys(
                    $this->debugLogs[0]
                )
            )
        );
        while ($log = array_shift($this->debugLogs))
        {//keep shifting from the array, until it's empty
            $data[]  = implode(' | ', $log);
        }
        return $data;
    }

    /**
     * Returns the HTTP status code of the last call, useful for error checking.
     * @return int
     */
    public function getLastHttpStatus()
    {
        return $this->lastHttpStatusCode;
    }

    /**
     * Returns the HTTP Response Text of the last curl call, useful for error checking.
     * @return int
     */
    public function getLastHttpResponseText()
    {
        return $this->lastHttpResponseText;
    }

    /**
     * Will force cURL requests to use the proxy.  Can be useful to debug requests and responses
     * using Fiddler2 or WireShark.
     * curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888'); // Use with Fiddler to debug
     * @param $proxyServer - example for fiddler2 default: '127.0.0.1:8888'
     */
    public function setProxyServer($proxyServer)
    {
        $this->proxyServer = $proxyServer;
    }


    /**
     * Returns all the open tickets of the API user's credentials used for the request
     * @return null|\stdClass
     */
    public function getApiUserTickets()
    {
        $json = $this->restCall(
            '/helpdesk/tickets.json',
            self::METHOD_GET
        );

        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns all the tickets
     * @param int $page
     * @return null|\stdClass
     */
    public function getAllTickets($page)
    {
        $json = $this->restCall(
            '/helpdesk/tickets.json?filter_name=all_tickets&page='.$page,
            self::METHOD_GET
        );

        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns the Ticket, this method takes in the IDs for a ticket.
     * @param int $ticketId
     * @return null|\stdClass
     */
    public function getSingleTicket($ticketId)
    {
        $json = $this->restCall(
            '/helpdesk/tickets/'.$ticketId.'.json',
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decodE($json);
    }


    /**
     * Returns all tickets from the user specified by email address
     * @param string $email
     * @return null|\stdClass
     */
    public function getUserTickets($email)
    {
        $json = $this->restCall(
            '/helpdesk/ticket/user_ticket.json?email='.$email,
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns tickets for a specific view
     * @param int $viewId
     * @param int $page
     * @return null|\stdClass
     */
    public function getTicketView($viewId, $page)
    {
        $json = $this->restCall(
            '/helpdesk/tickets/view/'.$viedId.'?format=json&page='.$page,
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns the fields available to helpdesk tickets
     * @return null|\stdClass
     */
    public function getTicketFields()
    {
        $json = $this->restCall(
            '/ticket_fields.json',
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns the Survey for a given ticket, this method takes in the IDs for a ticket
     * @param int $ticketId
     * @return null|\stdClass
     */
    public function getTicketSurvey($ticketId)
    {
        $json = $this->restCall(
            '/helpdesk/tickets/'.$ticketId.'/surveys.json',
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }
}
