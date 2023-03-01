<?php
namespace Library\Custom\Publish\Special\Prepare;

use App\Traits\FetchSpecialRowset;
use App\Traits\GenerateParams;

class Api extends PrepareAbstract {

    use FetchSpecialRowset, GenerateParams;
}