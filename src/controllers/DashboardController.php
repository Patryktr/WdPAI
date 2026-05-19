<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/ExpensesRepository.php';

class DashboardController extends AppController {

    public function index() {
        $this->requireLogin();

        $userId = $this->currentUserId();
        $title = "Dashboard";
        $expensesRepository = new ExpensesRepository();

        $this->render("index", [
            "title" => $title,
            "totalExpenses" => $expensesRepository->getTotalByUserId($userId),
            "monthlyTotal" => $expensesRepository->getMonthlyTotalByUserId($userId),
            "recentExpenses" => $expensesRepository->getRecentExpensesByUserId($userId, 5),
            "categorySummary" => $expensesRepository->getCategorySummaryByUserId($userId),
        ]);
    }
}
