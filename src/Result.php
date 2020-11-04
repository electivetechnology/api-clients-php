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

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage($errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @depricated
     * This method will be removed
     */
    public function getMessage(): ?string
    {
        return $this->getErrorMessage();
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
