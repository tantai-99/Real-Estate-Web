<?php

namespace App\Repositories\CompanySpamBlock;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Translation\t;

class CompanySpamBlockRepository extends BaseRepository implements CompanySpamBlockRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\CompanySpamBlock::class;
    }

    public function attach($company, $spamBlock)
    {
        $this->create([
            'company_id' => $company->id,
            'spam_block_id' => $spamBlock->id
        ]);
    }
}