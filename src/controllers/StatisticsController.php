<?php

require_once 'AppController.php';

class StatisticsController extends AppController {

    public function index(): void
    {
        $this->requireLogin();

        $this->render("statistics", ["title" => "Statistics"]);
    }
}
