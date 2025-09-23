<?php

class Task {
    private $id;
    private $title;
    private $description;
    private $status;public function __construct($id, $title, $description, $status) {
    $this->id = $id;
    $this->title = $title;
    $this->description = $description;
    $this->status = $status;
}

public function getId() { return $this->id; }
public function getTitle() { return $this->title; }
public function getDescription() { return $this->description; }
public function getStatus() { return $this->status; }

public function setTitle($title) { $this->title = $title; }
public function setDescription($description) { $this->description = $description; }
public function setStatus($status) { $this->status = $status; }

public function toArray() {
    return [
      'id' => $this->getId(),
      'title' => $this->getTitle(),
      'description' => $this->getDescription(),
      'status' => $this->getStatus()
    ];
}

            }

?>
