<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource
 * @ORM\Entity
 */
class Book
{

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    private $subject = '';

    /**
     * @var string
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    private $predicate = '';

    /**
     * @var string
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    private $object = '';

    /**
     * Book constructor.
     *
     * @param string $uuid
     * @param string $subject
     * @param string $predicate
     * @param string $object
     */
    public function __construct($uuid, $subject, $predicate, $object)
    {
        $this->id        = 1;
        $this->uuid      = $uuid;
        $this->subject   = $subject;
        $this->predicate = $predicate;
        $this->object    = $object;
    }

    /**
     * @return int
     */
    public function getUUID()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getPredicate(): string
    {
        return $this->predicate;
    }

    /**
     * @return string
     */
    public function getObject(): string
    {
        return $this->object;
    }

}
