<?php

namespace App\Http\Controllers;

use IdCore\CoreStarter\Http\Controllers\BaseCoreController;

class MahasiswaController extends BaseCoreController
{
    public function index()
    {
        return 'Anda punya izin melihat daftar mahasiswa ✅';
    }

    public function create()
    {
        return 'Anda punya izin membuat mahasiswa ✅';
    }
}
