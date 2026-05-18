<?php

require_once 'AppController.php';

class CategoriesController extends AppController {

    public function index(): void
    {
        $this->render("categories", ["title" => "Categories"]);
    }
}
