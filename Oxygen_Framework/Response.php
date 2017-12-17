<?php
class Response_Exception extends Exception {}

class Response
{
    protected $headers = array();
    protected $content = array();
    protected $statusCode = 200;

    /**
     * Render the response (headers + content) to the browser
     *
     * @throws Response_Exception
     *      If the headers are already sent to the browser and we want to add new ones
     */
    public function render()
    {
        if (!empty($this->headers) && headers_sent())
            throw new Response_Exception('Headers are already sent to the browser');
        elseif(!empty($this->headers))
        {
            // The status code
            header($_SERVER["SERVER_PROTOCOL"].' '.$this->statusCode.' '.self::$HTTP_STATUS_CODES[$this->statusCode]);

            foreach ($this->headers as $header)
            {
                header($header);
            }
        }

        echo $this->getContent();
    }

    /**
     * Get the response content
     *
     * @param string $joined
     *      if true, the response is sent as a string
     *      else the full raw content array is returned
     *
     * @return string|array
     */
    public function getContent($joined = true)
    {
        return $joined ? implode('', $this->content) : $this->content;
    }

    /**
     * Set the response content from a given value
     *
     * @param string|array $content
     * @return Response
     */
    public function setContent($content)
    {
        if (!is_array($content))
            $content = [$content];

        $this->content = $content;

        return $this;
    }

    /**
     * Append a specific content segment (with an optional name)
     *
     * @param string $content
     *      The content to append
     * @param string $name
     *      Optional name affected to the content
     * @return Response
     */
    public function appendContent($content, $name = null)
    {
        if (empty($name))
            $this->content[$name] = $content;
        else
            array_push($this->content, $content);

        return $this;
    }

    /**
     * Get HTTP header
     *
     * @param string $name
     *      the named header to get. If empty, all header are returned
     * @throws Response_Exception
     *      If the header provided cannot be found
     * @return string|array
     */
    public function getHeader($name = null)
    {
        if (!empty($name))
        {
            if (!isset($this->headers[$name]))
                throw new Response_Exception('Named header "'.$name.'" not found');

            return $this->headers[$name];
        }

        return $this->headers;
    }

    /**
     * Set HTTP header
     *
     * @param string $header
     *      The Raw header
     * @param string $name
     *      Optional name affected to the header
     * @throws Response_Exception
     *      If the header provided isn't a string
     * @return Response
     */
    public function setHeader($header, $name = null)
    {
        if (!is_string($header))
            throw new Response_Exception('The header must be a string !');

        if (!empty($name))
            $this->headers[$name] = $header;
        else
            $this->headers[] = $header;

        return $this;
    }

    /**
     * Get HTTP status code
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->statusCode;
    }

    /**
     * Set HTTP status code
     *
     * @param integer $code
     *      The HTTP code
     * @throws Response_Exception
     *      If the provided isn't a valid HTTP code
     * @return Response
     */
    public function setStatus($code)
    {
        if (!is_int($code) || !in_array($code, array_keys(self::$HTTP_STATUS_CODES)))
            throw new Response_Exception('Invalid HTTP status code '.$code);

        $this->statusCode = $code;

        return $this;
    }

     /**
     * HTTP status code to status message
     *
     * The list of status is based upon the list at
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2017-04-14).
     *
     * 1xx: Informational - Request received, continuing process
     * 2xx: Success - The action was successfully received, understood, and accepted
     * 3xx: Redirection - Further action must be taken in order to complete the request
     * 4xx: Client Error - The request contains bad syntax or cannot be fulfilled
     * 5xx: Server Error - The server failed to fulfill an apparently valid request
     *
     */
    public static $HTTP_STATUS_CODES = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',                                     // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    );
}