<?php

namespace Elective\ApiClients;

/**
 * Elective\ApiClients\Result
 *
 * @author Kris Rybak <kris.rybak@electivegroup.com>
 */
class Result
{
    /**
     * Http status code
     */
    private $code;

    /**
     * Data from Response
     */
    private $data;

    /**
     * Client error message
     */
    private $errorMessage;

    /**
     * Response Headers
     */
    private $headers = [];

    /**
     * Original message from Response
     */
    private $info;


    /**
     * Client error message
     */
    private $message;


    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    public function setHeaders(?array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getHeader($key)
    {
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }

        return null;
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo($info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage($errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage($message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Is response successful?
     *
     * @final
     */
    public function isSuccessful(): bool
    {
        return $this->code >= 200 && $this->code < 300;
    }
}
