<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IReportRepositoryInterface;
use App\Http\Controllers\Controller;
use App\MISC\ServiceResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $reportRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IReportRepositoryInterface $reportRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->reportRepository=$reportRepository;
    }

    public function SalesReport(Request $request)
    {
        try
        {
            return $this->userResponse->Success($this->reportRepository->SalesReport($request));
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function PurchaseReport(Request $request)
    {
        try
        {
            return $this->userResponse->Success($this->reportRepository->PurchaseReport($request));
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function SalesReportByVehicle(Request $request)
    {
        try
        {
            return $this->userResponse->Success($this->reportRepository->SalesReportByVehicle($request));
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function ExpenseReport(Request $request)
    {
        try
        {
            return $this->userResponse->Success($this->reportRepository->ExpenseReport($request));
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function CashReport(Request $request)
    {
        try
        {
            return $this->userResponse->Success($this->reportRepository->CashReport($request));
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function BankReport(Request $request)
    {
        try
        {
            return $this->userResponse->Success($this->reportRepository->BankReport($request));
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }
}
