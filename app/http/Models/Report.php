<?php

namespace App\Http\Models;


use Phalcon\Mvc\Model;
use Exception;

class Report extends Model
{

    public function addReport($uid, $type, $reportId, $reportUid, $content)
    {
        $data = [
            'type'      => $type,
            'reportId'  => $reportId,
            'reportUid' => $reportUid,
            'content'   => $content,
            'status'    => 'pending',
            'uid'       => $uid,
            'cTime'     => time(),
        ];
        try {
            $this->getDI()['db']->insertAsDict("reports", $data);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }


    public function addFeedback($uid, $content)
    {
        $data = [
            'uid'     => $uid,
            'content' => $content,
            'cTime'   => time(),
        ];
        try {
            $this->getDI()['db']->insertAsDict("feedback", $data);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

}
