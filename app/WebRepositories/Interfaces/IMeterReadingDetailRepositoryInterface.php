<?php
/**
 * Created by PhpStorm.
 * User: rizwanafridi
 * Date: 11/25/20
 * Time: 11:12
 */

namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface IMeterReadingDetailRepositoryInterface
{

    public function index();

    public function create();

    public function store(Request $request);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();
}