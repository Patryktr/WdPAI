<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/ExpensesRepository.php';

class StatisticsController extends AppController {

    public function index(): void
    {
        $this->requireLogin();

        $userId = $this->currentUserId();
        $expensesRepository = new ExpensesRepository();
        $monthlySummary = $expensesRepository->getMonthlySummaryByUserId($userId);
        $categorySummary = $expensesRepository->getCategorySummaryByUserId($userId);
        $biggestCategory = null;
        $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

        foreach ($categorySummary as $category) {
            if ((float) $category['total'] > 0) {
                $biggestCategory = $category;
                break;
            }
        }

        $this->render("statistics", [
            "title" => "Statistics",
            "totalExpenses" => $expensesRepository->getTotalByUserId($userId),
            "averageExpense" => $expensesRepository->getAverageExpenseByUserId($userId),
            "expensesCount" => $expensesRepository->getExpensesCountByUserId($userId),
            "biggestCategory" => $biggestCategory,
            "monthlySummary" => $monthlySummary,
            "categorySummary" => $categorySummary,
            "monthlyChartJson" => json_encode($this->monthlyChartData($monthlySummary), $jsonFlags) ?: '[]',
            "categoryChartJson" => json_encode($this->categoryChartData($categorySummary), $jsonFlags) ?: '[]',
        ]);
    }

    private function monthlyChartData(array $monthlySummary): array
    {
        return array_map(static function (array $month): array {
            return [
                'key' => (string) $month['month_key'],
                'label' => (string) $month['month_label'],
                'total' => (float) $month['total'],
                'count' => (int) $month['expense_count'],
            ];
        }, $monthlySummary);
    }

    private function categoryChartData(array $categorySummary): array
    {
        $chartData = [];

        foreach ($categorySummary as $category) {
            $total = (float) $category['total'];

            if ($total <= 0) {
                continue;
            }

            $chartData[] = [
                'label' => (string) $category['name'],
                'total' => $total,
                'count' => (int) $category['expense_count'],
            ];
        }

        return $chartData;
    }
}
