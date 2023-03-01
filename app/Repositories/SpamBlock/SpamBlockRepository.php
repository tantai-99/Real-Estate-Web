<?php

namespace App\Repositories\SpamBlock;

use App;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\CompanySpamBlock\CompanySpamBlockRepositoryInterface;

use function Symfony\Component\Translation\t;

class SpamBlockRepository extends BaseRepository implements SpamBlockRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\SpamBlock::class;
    }

    public function createOrUpdate($id = 0, $insertData) {
        if($this->update($id, $insertData)) {
            return $id;
        }
        $row = $this->create($insertData);
        return $row->id;
    }

    public function getCompanyListById($id) {
        $companySpamBlocks = $this->find($id)->companySpamBlock()->get();
        $companyIdList = [];
        foreach ($companySpamBlocks as $companySpamBlock) {
            array_push($companyIdList, $companySpamBlock->company_id);
        }
        $companyObj = App::make(CompanyRepositoryInterface::class);
        $companies = $companyObj->fetchAll(['whereIn' => ['id', $companyIdList]]);
        return $companies;
    }

    public function getDataForId($id) {
        return $this->find($id);
    }

    /**
     * 迷惑メール対象会員かどうか
     *
     * @param int $spamBlockId spam_block.id
     * @param string $memberNo 会員番号
     * @return bool
     */
    public function isTargetMember($spamBlockId, $memberNo) {
        $companies = $this->getCompanyListById($spamBlockId);
        foreach ($companies as $company) {
            if (strpos($company->member_no, $memberNo) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 迷惑メール設定されているかどうか
     *
     * @param $companyId
     * @param $email
     * @param $tel
     * @return bool
     */
    public function isSpamContact($companyId, $email, $tel) {
        $tel = str_replace('-', '', $tel);
        $companySpamBlockObj = App::make(CompanySpamBlockRepositoryInterface::class);
        $companySpamBlocks = $companySpamBlockObj->fetchAll([['company_id', $companyId]]);
        $spamBlocks = $this->fetchAll([['range_option', config('constants.spamblock.ALL_MEMBER')]]);
        foreach ($spamBlocks as $spamBlock) {
            if (self::isSpam($spamBlock, $email, $tel)) {
                return true;
            }
        }
        foreach ($companySpamBlocks as $companySpamBlock) {
            $spamBlock = $this->find($companySpamBlock->spam_block_id);
            if (self::isSpam($spamBlock, $email, $tel)) {
                return true;
            }
        }
        return false;
    }

    private static function isSpam($spamBlock, $email, $tel) {
        if (!empty($spamBlock->email) && !empty($spamBlock->tel)) {
            //メールと電話番号の両方に設定あるとき
            if ($spamBlock->email_option === config('constants.spamblock.PARTIAL_MATCH')) {
                //メールで部分一致のとき
                if ((strpos($email, $spamBlock->email) !== false) && $spamBlock->tel === $tel) {
                    return true;
                }
            } else if ($spamBlock->email_option === config('constants.spamblock.PERFECT_MATCH')) {
                //メールで完全一致のとき
                if ($email === $spamBlock->email && $spamBlock->tel === $tel) {
                    return true;
                }
            }
        } else if (!empty($spamBlock->email)) {
            //メールだけ設定あるとき
            if ($spamBlock->email_option === config('constants.spamblock.PARTIAL_MATCH')) {
                //メールで部分一致のとき
                if (strpos($email, $spamBlock->email) !== false) {
                    return true;
                }
            } else if ($spamBlock->email_option === config('constants.spamblock.PERFECT_MATCH')) {
                //メールで完全一致のとき
                if ($email === $spamBlock->email) {
                    return true;
                }
            }
        } else if (!empty($spamBlock->tel)) {
            //電話番号だけ設定あるとき
            if ($spamBlock->tel === $tel) {
                return true;
            }
        }
        return false;
    }
}