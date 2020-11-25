<?php
/**
 * Created by PhpStorm.
 * User: rizwanafridi
 * Date: 11/24/20
 * Time: 12:36
 */

namespace App\WebRepositories\Interfaces;


use App\Http\Requests\MeterReaderRequest;
use Illuminate\Http\Request;

interface IMeterReaderRepositoryInterface
{
    public function index();

    public function create();

    public function store(MeterReaderRequest $meterReaderRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();
}