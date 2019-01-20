<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 */
class Item
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ListTodo", inversedBy="items")
     */
    private $listTodo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $checked;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Attachment", cascade={"persist", "remove"})
     */
    private $attachment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getListTodo(): ?ListTodo
    {
        return $this->listTodo;
    }

    public function setListTodo(?ListTodo $listTodo): self
    {
        $this->listTodo = $listTodo;
        return $this;
    }

    public function getChecked(): ?bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): self
    {
        $this->checked = $checked;
        return $this;
    }

    public function getAttachment(): ?Attachment
    {
        return $this->attachment;
    }
    public function setAttachment(?Attachment $attachment): self
    {
        $this->attachment = $attachment;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'checked' => $this->getChecked(),
            'listTodo' => $this->getListTodo()->getName(),
            'user' => $this->getListTodo()->getUser(),
            'attachment' => $this->getAttachment()
        ];
    }
}