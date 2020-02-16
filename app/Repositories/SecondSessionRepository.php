<?php


namespace App\Repositories;


use App\Models\SecondSession;
use InfyOm\Generator\Common\BaseRepository;

class SecondSessionRepository extends BaseRepository
{

    public function model()
    {
        // TODO: Implement model() method.
        return SecondSession::class;
    }

}