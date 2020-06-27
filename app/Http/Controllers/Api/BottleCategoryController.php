<?php


namespace App\Http\Controllers\Api;


use App\Enums\UserRole;
use App\Http\Resources\BottleCategoryResource;
use App\Http\Resources\BottleResource;
use App\Http\Resources\PaginationResource;
use App\Repositories\Bar\BarRepository;
use App\Repositories\BottleCategory\BottleCategoryRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class BottleCategoryController extends BaseController
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var BottleCategoryRepository
     */
    protected $bottleCategoryRepository;

    /**
     * @var BarRepository
     */
    protected $barRepository;

    public function __construct(UserRepository $userRepository,
                                BarRepository $barRepository,
                                BottleCategoryRepository $bottleCategoryRepository)
    {
        $this->userRepository = $userRepository;
        $this->barRepository = $barRepository;
        $this->bottleCategoryRepository = $bottleCategoryRepository;
    }

    public function getListBottleCategoryByBarId(Request $request, int $barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bottleCategories = array();
        switch ($role) {
            case UserRole::Admin:
                $bottleCategories = $this->bottleCategoryRepository->findByBarId($barId);
                break;
            default:
                $barIds = $this->userRepository->findAllBarIdByOwner($user);
                if (!in_array($barId, $barIds->toArray())) {
                    throw new AccessDeniedHttpException(trans('error.access_denied'));
                }
                $bottleCategories = $this->bottleCategoryRepository->findBottleCategoryByBarId($barId);
                break;
        }
        return $this->sendResponse(BottleCategoryResource::collection($bottleCategories), trans("api.list.success"), Response::HTTP_OK);
    }

    public function getListBottleCategory(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bottleCategories = array();
        switch ($role) {
            case UserRole::Admin:
                $barIds = $this->barRepository->getAll()->pluck('id');
                break;
            default:
                $barIds = $this->userRepository->findAllBarIdByOwner($user);
                break;
        }
        $bottleCategories = $this->bottleCategoryRepository->findBottleCategoryByListBarId($barIds, $request->query('sort'));
        return $this->sendResponse(BottleCategoryResource::collection($bottleCategories), trans("api.list.success"), Response::HTTP_OK);
    }

    public function modifyBottleCategory(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $dataInput = array();
        $errorList = array();
        $category_arr =  array();
        $serial_arr =  array();
        $barIds = array();
        $names = array();
        $serials = array();
        if ($role == UserRole::Staff || $role == UserRole::Cast ) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $inputCategoryList = $request->all()['data'];
        foreach($inputCategoryList as $key => $category) {
            $barId = null;
            if (is_null($category['id'])) {
                $barId = $category['bar_id'];
            } else {
                $barId = $this->bottleCategoryRepository->find($category['id'])->bar_id;
            }
            $error = array();
            if (is_null($category['name'])) {
                $error['name'] = trans('validation.custom.name.required');
            } else {
                if(in_array($category['bar_id'].'.'.$category['name'], $category_arr))
                $error['name'] = trans('validation.custom.name.duplicated');
                $category_arr[] = $category['bar_id'].'.'.$category['name'];
            }

            if (is_null($category['serial'])) {
                $category['serial'] = 0;
            } else {
                if ($category['serial'] != 0) {
                    if (in_array($category['bar_id'] . '.' . $category['serial'], $serial_arr))
                        $error['serial'] = trans('validation.custom.serial.duplicated');
                    $serial_arr[] = $category['bar_id'] . '.' . $category['serial'];
                }
                if (!preg_match('/^[0-9０-９]*$/i', $category['serial'])) {
                    $error['serial'] = trans('validation.custom.serial.invalid_format');
                }
            }
            $barIds[] = $category['bar_id'];
            if (count($error) > 0) {
                if (is_null($category['id'])) {
                    $errorList[$category['pre_insert_id']] = $error;
                } else {
                    $errorList[$category['id']] = $error;
                }
            }
            array_push($dataInput, $category);
        }

        $barIds = array_unique($barIds);
        foreach ($barIds as $barId) {
            $serials = array_merge($serials, $this->bottleCategoryRepository->getSerialForDuplicateByBar($barId));
            $names = array_merge($names, $this->bottleCategoryRepository->getNameForDuplicateByBaR($barId));
        }
        if (array_intersect($names, $category_arr)) {
            $error['name'] = trans('validation.custom.name.duplicated');
        }
        if (array_intersect($serials, $serial_arr)) {
            $error['serial'] = trans('validation.custom.serial.duplicated');
        }
        if (count($errorList) > 0) {
            return $this->sendError(trans('error.bad_request'), $errorList, Response::HTTP_BAD_REQUEST);
        }
        try {
            $this->bottleCategoryRepository->modifyBottleCategoryList($dataInput);
            return $this->sendResponse($request->all(), trans("api.category.update"), Response::HTTP_OK);
        } catch(\Exception $e) {
            return $this->sendError(trans('error.update_fail'), $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteListBottleCategory(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        if (is_null($request->query('category'))) {
            return $this->sendError(trans('validation.custom.query.empty_category'), 'empty category in query', Response::HTTP_BAD_REQUEST);
        }
        $categoryIds = explode(",", $request->query('category'));
        switch ($role) {
            case UserRole::Staff:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
            default:
                $bottles = $this->bottleCategoryRepository->deleteListBottleCategroy($categoryIds);
                return $this->sendResponse('', trans("api.delete.success"), Response::HTTP_OK);
        }
    }
}
