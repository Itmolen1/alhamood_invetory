<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IReportRepositoryInterface;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $reportRepository;

    public function __construct(IReportRepositoryInterface $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    public function GetBalanceSheet()
    {
        return $this->reportRepository->GetBalanceSheet();
    }

    public function PrintBalanceSheet()
    {
        return $this->reportRepository->PrintBalanceSheet();
    }

    public function SalesReport()
    {
        return $this->reportRepository->SalesReport();
    }

    public function PrintSalesReport(Request $request)
    {
        return $this->reportRepository->PrintSalesReport($request);
    }

    public function SalesReportByVehicle()
    {
        return $this->reportRepository->SalesReportByVehicle();
    }

    public function PrintSalesReportByVehicle(Request $request)
    {
        return $this->reportRepository->PrintSalesReportByVehicle($request);
    }

    public function PurchaseReport()
    {
        return $this->reportRepository->PurchaseReport();
    }

    public function PrintPurchaseReport(Request $request)
    {
        return $this->reportRepository->PrintPurchaseReport($request);
    }

    public function ExpenseReport()
    {
        return $this->reportRepository->ExpenseReport();
    }

    public function PrintExpenseReport(Request $request)
    {
        return $this->reportRepository->PrintExpenseReport($request);
    }

    public function CashReport()
    {
        return $this->reportRepository->CashReport();
    }

    public function PrintCashReport(Request $request)
    {
        return $this->reportRepository->PrintCashReport($request);
    }

    public function BankReport()
    {
        return $this->reportRepository->BankReport();
    }

    public function PrintBankReport(Request $request)
    {
        return $this->reportRepository->PrintBankReport($request);
    }
}
