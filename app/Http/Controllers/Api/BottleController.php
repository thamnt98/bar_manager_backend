<?php


namespace App\Http\Controllers\Api;


use App\Enums\UserRole;
use App\Http\Resources\BottleResource;
use App\Repositories\Bar\BarRepository;
use App\Repositories\Bottle\BottleRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BottleController extends BaseController
{
    /**
     * @var UserRepository|\App\Repositories\Repository
     */
    protected $userRepository;

    /**
     * @var BottleRepository
     */
    protected $bottleRepository;

    /**
     * @var BarRepository
     */
    protected $barRepository;

    public function __construct(UserRepository $userRepository,
                                BarRepository $barRepository,
                                BottleRepository $bottleRepository)
    {
        $this->userRepository = $userRepository;
        $this->barRepository = $barRepository;
        $this->bottleRepository = $bottleRepository;
    }

    public function getListBottleByBarId(Request $request, int $barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bottles = array();
        switch ($role) {
            case UserRole::Admin:
                $bottles = $this->bottleRepository->findBottleByBarId($barId);
                break;
            default:
                $barIds = $this->userRepository->findAllBarIdByOwner($user);
                if (!in_array($barId, $barIds->toArray())) {
                    throw new AccessDeniedHttpException(trans('error.access_denied'));
                }
                $bottles = $this->bottleRepository->findBottleByBarId($barId);
                break;
        }
        return $this->sendResponse(BottleResource::collection($bottles), trans("api.list.success"), Response::HTTP_OK);
    }

    public function getListBottle(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bottles = array();
        switch ($role) {
            case UserRole::Admin:
                $barIds = $this->barRepository->getAll()->pluck('id');
                break;
            default:
                $barIds = $this->userRepository->findAllBarIdByOwner($user);
                break;
        }
        $bottles = $this->bottleRepository->findBottleByListBarId($barIds, $request->query('sort'));
        return $this->sendResponse(BottleResource::collection($bottles), trans("api.list.success"), Response::HTTP_OK);
    }

    public function modifyBottle(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        if ($role == UserRole::Staff || $role == UserRole::Cast ) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $dataInput = array();
        $errorList = array();
        $bottle_arr = array();
        $serial_arr = array();
        $categoryIds = array();
        $names = array();
        $serials = array();
        $inputBottleList = $request->all()['data'];
        foreach($inputBottleList as $key => $bottle) {
            $categoryId = null;
            if (is_null($bottle['id'])) {
                $categoryId = $bottle['category_id'];
            } else {
                $categoryId = $this->bottleRepository->find($bottle['id'])->category_id;
            }
            $error = array();
            if (is_null($bottle['name'])) {
                $error['name'] = trans('validation.custom.name.required');
            } else {
                if( in_array($bottle['category_id'].'.'.$bottle['name'], $bottle_arr))
                    $error['name'] = trans('validation.custom.name.duplicated');
                    $bottle_arr[] = $bottle['category_id'].'.'.$bottle['name'];
            }

            if (is_null($bottle['serial'])) {
                $bottle['serial'] = 0;
            } else {
                if ($bottle['serial'] != 0) {
                    if (in_array($bottle['category_id'] . '.' . $bottle['serial'], $serial_arr))
                        $error['serial'] = trans('validation.custom.serial.duplicated');
                    $serial_arr[] = $bottle['category_id'] . '.' . $bottle['serial'];
                }
                if (!preg_match('/^[0-9０-９]*$/i', $bottle['serial'])) {
                    $error['serial'] = trans('validation.custom.serial.invalid_format');
                }
            }
            $categoryIds[] = $bottle['category_id'];
            if (count($error) > 0) {
                if (is_null($bottle['id'])) {
                    $errorList[$bottle['pre_insert_id']] = $error;
                } else {
                    $errorList[$bottle['id']] = $error;
                }
            }
            array_push($dataInput, $bottle);
        }
        $categoryIds = array_unique($categoryIds);
        foreach ($categoryIds as $categoryId) {
            $serials = array_merge($serials, $this->bottleRepository->getSerialForDuplicateByCategory($categoryId));
            $names = array_merge($names, $this->bottleRepository->getNameForDuplicateByCategory($categoryId));
        }
        if (array_intersect($names, $bottle_arr)) {
            $error['name'] = trans('validation.custom.name.duplicated');
        }
        if (array_intersect($serials, $serial_arr)) {
            $error['serial'] = trans('validation.custom.serial.duplicated');
        }
        if (count($errorList) > 0) {
            return $this->sendError(trans('error.bad_request'), $errorList, Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->bottleRepository->modifyBottleList($dataInput);
            return $this->sendResponse($request->all(), trans("api.bottle.update"), Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->sendError(trans('error.update_fail'), $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
    public function deleteListBottle(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        if (is_null($request->query('bottle'))) {
            return $this->sendError(trans('validation.custom.query.empty_bottle'), 'empty bottle in query', Response::HTTP_BAD_REQUEST);
        }
        $bottleIds = explode(",", $request->query('bottle'));
        switch ($role) {
            case UserRole::Staff:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
            default:
                $bottles = $this->bottleRepository->deleteListBottle($bottleIds);
                return $this->sendResponse('', trans("api.delete.success"), Response::HTTP_OK);
        }
    }
}
