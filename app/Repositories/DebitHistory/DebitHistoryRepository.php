<?php

namespace App\Repositories\DebitHistory;

interface DebitHistoryRepository
{

    /**
     * Find list debit histories by visit id 
     * @param visitId
     * @author ThamNT
     */
    public function getDebitHistoriesByVisitId($visitId);

    /**
     * Modify or create one list debit histories by visit id 
     * @param inputDebitHistoryList: one array of list debit histories
     * @param visitId
     * @author ThamNT
     */
    public function modifyDebitHistoryList($inputDebitHistoryList, $visitId);
}
