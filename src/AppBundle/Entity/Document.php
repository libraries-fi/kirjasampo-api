<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource
 * @ORM\Entity
 */
class Document
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
     * @var array
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    private $content = '';

    /**
     * Book constructor.
     *
     * @param array $content
     */
    public function __construct($content)
    {
        $this->id      = 1;
        $this->content = $content;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

}
