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

    public function GetDetailCustomerStatement()
    {
        return $this->reportRepository->GetDetailCustomerStatement();
    }

    public function PrintDetailCustomerStatement(Request $request)
    {
        return $this->reportRepository->PrintDetailCustomerStatement($request);
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

    public function BankReport()
    {
        return $this->reportRepository->BankReport();
    }

    public function PrintBankReport(Request $request)
    {
        return $this->reportRepository->PrintBankReport($request);
    }

    public function GeneralLedger()
    {
        return $this->reportRepository->GeneralLedger();
    }

    public function PrintGeneralLedger(Request $request)
    {
        return $this->reportRepository->PrintGeneralLedger($request);
    }
}
