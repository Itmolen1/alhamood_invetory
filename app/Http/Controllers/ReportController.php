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

    public function GetCustomerStatement()
    {
        return $this->reportRepository->GetCustomerStatement();
    }

    public function PrintCustomerStatement()
    {
        return $this->reportRepository->PrintCustomerStatement();
    }

    public function GetPaidAdvancesSummary()
    {
        return $this->reportRepository->GetPaidAdvancesSummary();
    }

    public function PrintPaidAdvancesSummary()
    {
        return $this->reportRepository->PrintPaidAdvancesSummary();
    }

    public function PrintReceivedAdvancesSummary()
    {
        return $this->reportRepository->PrintReceivedAdvancesSummary();
    }

    public function GetReceivedAdvancesSummary()
    {
        return $this->reportRepository->GetReceivedAdvancesSummary();
    }

    public function GetDetailCustomerStatement()
    {
        return $this->reportRepository->GetDetailCustomerStatement();
    }

    public function PrintDetailCustomerStatement(Request $request)
    {
        return $this->reportRepository->PrintDetailCustomerStatement($request);
    }

    public function ViewDetailCustomerStatement(Request $request)
    {
        return $this->reportRepository->ViewDetailCustomerStatement($request);
    }

    public function GetSupplierStatement()
    {
        return $this->reportRepository->GetSupplierStatement();
    }

    public function PrintSupplierStatement()
    {
        return $this->reportRepository->PrintSupplierStatement();
    }

    public function GetDetailSupplierStatement()
    {
        return $this->reportRepository->GetDetailSupplierStatement();
    }

    public function PrintDetailSupplierStatement(Request $request)
    {
        return $this->reportRepository->PrintDetailSupplierStatement($request);
    }

    public function ViewDetailSupplierStatement(Request $request)
    {
        return $this->reportRepository->ViewDetailSupplierStatement($request);
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

    public function SalesReportByCustomer()
    {
        return $this->reportRepository->SalesReportByCustomer();
    }

    public function PrintSalesReportByCustomer(Request $request)
    {
        return $this->reportRepository->PrintSalesReportByCustomer($request);
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

    public function ViewCashReport(Request $request)
    {
        return $this->reportRepository->ViewCashReport($request);
    }

    public function BankReport()
    {
        return $this->reportRepository->BankReport();
    }

    public function GetReceivableSummaryAnalysis()
    {
        return $this->reportRepository->GetReceivableSummaryAnalysis();
    }

    public function ViewReceivableSummaryAnalysis(Request $request)
    {
        return $this->reportRepository->ViewReceivableSummaryAnalysis($request);
    }

    public function GetExpenseAnalysis()
    {
        return $this->reportRepository->GetExpenseAnalysis();
    }

    public function ViewExpenseAnalysis(Request $request)
    {
        return $this->reportRepository->ViewExpenseAnalysis($request);
    }

    public function PrintBankReport(Request $request)
    {
        return $this->reportRepository->PrintBankReport($request);
    }

    public function ViewBankReport(Request $request)
    {
        return $this->reportRepository->ViewBankReport($request);
    }

    public function GeneralLedger()
    {
        return $this->reportRepository->GeneralLedger();
    }

    public function PrintGeneralLedger(Request $request)
    {
        return $this->reportRepository->PrintGeneralLedger($request);
    }

    public function Profit_loss()
    {
        return $this->reportRepository->Profit_loss();
    }

    public function PrintProfit_loss(Request $request)
    {
        return $this->reportRepository->PrintProfit_loss($request);
    }

    public function Garage_value()
    {
        return $this->reportRepository->Garage_value();
    }

    public function PrintGarage_value(Request $request)
    {
        return $this->reportRepository->PrintGarage_value($request);
    }

    public function GetExpenseAnalysisByCategory()
    {
        return $this->reportRepository->GetExpenseAnalysisByCategory();
    }

    public function ViewExpenseAnalysisByCategory(Request $request)
    {
        return $this->reportRepository->ViewExpenseAnalysisByCategory($request);
    }

    public function GetExpenseAnalysisByEmployee()
    {
        return $this->reportRepository->GetExpenseAnalysisByEmployee();
    }

    public function ViewExpenseAnalysisByEmployee(Request $request)
    {
        return $this->reportRepository->ViewExpenseAnalysisByEmployee($request);
    }

    public function GetExpenseAnalysisBySupplier()
    {
        return $this->reportRepository->GetExpenseAnalysisBySupplier();
    }

    public function ViewExpenseAnalysisBySupplier(Request $request)
    {
        return $this->reportRepository->ViewExpenseAnalysisBySupplier($request);
    }

    public function GetSalesQuantitySummary()
    {
        return $this->reportRepository->GetSalesQuantitySummary();
    }

    public function PrintSalesQuantitySummary(Request $request)
    {
        return $this->reportRepository->PrintSalesQuantitySummary($request);
    }

    public function GetInwardLoanStatement()
    {
        return $this->reportRepository->GetInwardLoanStatement();
    }

    public function PrintInwardLoanStatement(Request $request)
    {
        return $this->reportRepository->PrintInwardLoanStatement($request);
    }

    public function GetOutwardLoanStatement()
    {
        return $this->reportRepository->GetOutwardLoanStatement();
    }

    public function PrintOutwardLoanStatement(Request $request)
    {
        return $this->reportRepository->PrintOutwardLoanStatement($request);
    }
}
