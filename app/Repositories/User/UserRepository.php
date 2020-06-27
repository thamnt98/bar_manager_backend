<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/24/19
 * Time: 5:05 PM
 */

namespace App\Repositories\User;

interface UserRepository
{
    /**
     * Find user by email
     * @param $email
     * @return mixed
     */
    public function findByEmail($email);

    /**
     * generate invite code
     * @return mixed
     */
    public function generateInviteCode();

    /**
     * find user id by invite code
     * @param $code
     * @return mixed
     */
    public function findUserIdByInviteCode($code);

    /**
     * @param $user
     * @param $bar
     * @return mixed
     */
    public function insertOwnerBarMemberships($user, $bar);

    /**
     * @param $input
     * @return mixed
     */
    public function createOwner($input);

    public function insertStaffBarMemberships($user, $bar, $role, $canEdit);

    /**
     * find bar by user
     * @param $user
     * @return mixed
     */
    public function findBarByUser($user);

    /**
     * find bar by user and bar id
     * @param $user
     * @return mixed
     */
    public function findBarByUserAndBarId($user, $barId);

    /**
     * verify bar can be edited by user
     * @param $user
     * @return mixed
     */
    public function canEditBar($user, $barId);

    /**
     * get all bar of owner
     * @param $user
     * @return mixed
     */
    public function findAllBarIdByOwner($user);

    /**
     * get all user by barIds
     * @param $barIds
     * @return mixed
     */
    public function findUserByBarIds($barIds);

    /**
     * @param $user
     * @param $barId
     * @param $role
     * @param $canEdit
     * @return mixed
     */
    public function createStaffBarMemberships($user, $barId, $role, $canEdit);

    /**
     * @param $user
     * @param $barId
     * @return mixed
     */
    public function findAllBarIdByUserAndBar($user, $barId);

    /**
     * @param $barIds
     * @return mixed
     */
    public function findUserOfManagerByBarIds($barIds);

    /**
     * @param $barIds
     * @return mixed
     */
    public function findUserOfAdminByBarIds($barIds);

    /**
     * @param $ownerId
     * @param $staffId
     * @return mixed
     */
    public function findStaffByOwner($ownerId, $staffId);
    public function findCastByOwner($ownerId, $castId);


    /**
     * @param $user
     * @param $barId
     * @return mixed
     */
    public function removeStaffBarMemberships($user, $barId);

    /**
     * @param $user
     * @param $barId
     * @param $role
     * @param $canEdit
     * @return mixed
     */
    public function updateStaffBarMemberships($user, $barId, $role, $canEdit);

    /**
     * @param $barIds
     * @param $orders
     * @param $role
     * @return mixed
     */
    public function findStaffByBarIds($barIds, $orders, $role);
    public function findCastByBarIds($barIds, $sorts);
    public function findCastInBarByCastIdAndBarIds($castId, $barIds);
    public function findBarIdByUser($user);
    public function findCastOrStaffByBarId($barId, $role);
    public function findStaffByStaffIdAndUser($staffId, $barId);
    
     /**
     * Find staff by list bar's id
     * @param barIds:array
     * @author ThamNT
     */
    public function findStaffByListBarIds($barIds);
}
