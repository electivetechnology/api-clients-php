<?php

namespace Elective\ApiClients\Acl\Authorisation;

use JsonSerializable;

/**
 * Elective\ApiClients\Acl\Authorisation\Check
 *
 * @author Kris Rybak <kris.rybak@electivegroup.com>
 */
class Check implements JsonSerializable
{
    /**
     * Name of this check. If none supplied 'main' will be used.
     */
    private $name;

    /**
     * Subject to evaluate
     */
    private $subject;

    /**
     * Permission to check for
     */
    private $permission;

    /**
     * Additional checks
     */
    private $checks;

    /**
     * Context. The check will be performed for given Organisation.
     */
    private $organisation;

    public function __construct($subject, $permission, $organisation, $name = 'main')
    {
        $this->checks = [];
        $this->setSubject($subject);
        $this->setPermission($permission);
        $this->setOrganisation($organisation);
        $this->setName($name);
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setPermission(?string $permission): self
    {
        $this->permission = $permission;

        return $this;
    }

    public function getPermission(): ?string
    {
        return $this->permission;
    }

    public function setOrganisation(?string $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getOrganisation(): ?string
    {
        return $this->organisation;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getChecks(): array
    {
        return $this->checks;
    }

    public function addCheck(Check $check): self
    {
        $this->checks[] = $check;

        return $this;
    }

    public function jsonSerialize()
    {
        $array  = [];
        $checks = [];

        $array['permission']    = $this->getPermission();
        $array['subject']       = $this->getSubject();
        $array['organisation']  = $this->getOrganisation();

        foreach ($this->getChecks() as $check) {
            $obj = new \StdClass();
            $obj->name = $check->getName();
            $obj->authorise = $check;

            $checks[] = $obj;
        }

        $array['checks'] = $checks;

        return $array;
    }
}
