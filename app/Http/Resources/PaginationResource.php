<?php


namespace App\Http\Resources;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PaginationResource
{
    /**
     * generate a collection to paginate
     *
     * @param array|Collection      $items
     * @param int   $perPage
     * @param int  $page
     * @param array $options
     *
     * @return LengthAwarePaginator
     */
    public function paginate($items, $perPage = 1000000, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($this->forPage($items->toArray(), $page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function forPage($items, $page, $perPage)
    {
        $offset = max(0, ($page - 1) * $perPage);

        return array_slice($items, $offset, $perPage, false);
    }
}


