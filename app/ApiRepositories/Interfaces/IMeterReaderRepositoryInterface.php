<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\MeterReaderRequest;
use Illuminate\Http\Request;

interface IMeterReaderRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(MeterReaderRequest $meterReaderRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);
}
