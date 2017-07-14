<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(attributes={
 *     "pagination_items_per_page"=50
 * },
 * collectionOperations={
 *     "get"={"method"="GET"},
 *     "search"={"route_name"="search"}
 * },
 * itemOperations={
 *     "get"={"route_name"="api_documents_get_item"},
 * })
 * @ORM\Entity
 */
class Document
{

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="string")
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
     * @param string $id
     * @param array $content
     */
    public function __construct($id, $content)
    {
        $this->id      = $id;
        $this->content = $content;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

}
