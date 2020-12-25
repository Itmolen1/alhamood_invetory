<?php


namespace App\ApiRepositories\Interfaces;


use Illuminate\Http\Request;

interface IReportRepositoryInterface
{
    public  function SalesReport(Request $request);

    public  function PurchaseReport(Request $request);

    public  function SalesReportByVehicle(Request $request);
}
