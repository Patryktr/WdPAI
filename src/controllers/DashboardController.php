<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/ExpensesRepository.php';

class DashboardController extends AppController {

    public function index() {
        $this->requireLogin();

        $userId = $this->currentUserId();
        $title = "Dashboard";
        $expensesRepository = new ExpensesRepository();
        $categorySummary = $expensesRepository->getCategorySummaryByUserId($userId);
        $biggestCategory = null;

        foreach ($categorySummary as $category) {
            if ((float) $category['total'] > 0) {
                $biggestCategory = $category;
                break;
            }
        }

        $this->render("index", [
            "title" => $title,
            "totalExpenses" => $expensesRepository->getTotalByUserId($userId),
            "monthlyTotal" => $expensesRepository->getMonthlyTotalByUserId($userId),
            "monthlyCount" => $expensesRepository->getMonthlyCountByUserId($userId),
            "biggestExpense" => $expensesRepository->getBiggestExpenseByUserId($userId),
            "biggestCategory" => $biggestCategory,
            "recentExpenses" => $expensesRepository->getRecentExpensesByUserId($userId, 5),
            "categorySummary" => $categorySummary,
        ]);
    }
}
