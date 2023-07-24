<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Comment
{
    /**
     * @ORM\ManyToOne(targetEntity=\EonX\EasyActivity\Tests\Fixtures\Article::class, inversedBy="comments")
     *
     * @var \EonX\EasyActivity\Tests\Fixtures\Article
     */
    private $article;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(name="message", type="text")
     * @var string
     */
    private $message;

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
