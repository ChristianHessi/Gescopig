<?php

namespace App\Http\Controllers;

use App\Repositories\ContratRepository;
use App\Repositories\EnseignementRepository;
use App\Repositories\SecondSessionRepository;
use Illuminate\Http\Request;

class SecondSessionController extends Controller
{
    protected $enseignementRepository;
    protected $contratRepository;
    protected $secondSessionRepository;

    public function __construct(EnseignementRepository $enseignementRepository, ContratRepository $contratRepository, SecondSessionRepository $secondSessionRepository)
    {
        $this->contratRepository = $contratRepository;
        $this->enseignementRepository = $enseignementRepository;
        $this->secondSessionRepository = $secondSessionRepository;
    }


}
