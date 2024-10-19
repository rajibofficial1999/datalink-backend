<?php

namespace App\Interfaces;

// This interface has been implemented in 'Services/ServiceImplementations/AccountProcess.php'
use Illuminate\Http\Request;

interface AccountManage
{
    public function create(array $data);

    public function update(Request $request);
}
