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
            $result=$this->reportRepository->SalesReport($request);
            if($result)
            {
                return $this->userResponse->Success($result);
            }
            else
            {
                return $this->userResponse->Failed($sales = (object)[],'No Records Found.');
            }
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
            $result=$this->reportRepository->PurchaseReport($request);
            if($result)
            {
                return $this->userResponse->Success($result);
            }
            else
            {
                return $this->userResponse->Failed($sales = (object)[],'No Records Found.');
            }
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
            $result=$this->reportRepository->SalesReportByVehicle($request);
            if($result)
            {
                return $this->userResponse->Success($result);
            }
            else
            {
                return $this->userResponse->Failed($sales = (object)[],'No Records Found.');
            }
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function SalesReportByCustomerVehicle(Request $request)
    {
        try
        {
            $result=$this->reportRepository->SalesReportByCustomerVehicle($request);
            if($result)
            {
                return $this->userResponse->Success($result);
            }
            else
            {
                return $this->userResponse->Failed($sales = (object)[],'No Records Found.');
            }
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
            $result=$this->reportRepository->ExpenseReport($request);
            if($result)
            {
                return $this->userResponse->Success($result);
            }
            else
            {
                return $this->userResponse->Failed($sales = (object)[],'No Records Found.');
            }
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
            $result=$this->reportRepository->CashReport($request);
            if($result)
            {
                return $this->userResponse->Success($result);
            }
            else
            {
                return $this->userResponse->Failed($sales = (object)[],'No Records Found.');
            }
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
            return $this->userResponse->Failed($sales = (object)[],'No Records Found.');
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function GetBalanceSheet()
    {
        try
        {
            return $this->userResponse->Failed($sales = (object)[],'No Records Found.');
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }
}
