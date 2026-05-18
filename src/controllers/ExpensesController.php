<?php

require_once 'AppController.php';

class ExpensesController extends AppController {

    public function index(): void
    {
        $this->render("expenses", ["title" => "Expenses"]);
    }

    public function create(): void
    {
        $this->render("expense-create", ["title" => "Create expense"]);
    }
}
