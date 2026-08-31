<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Wrap already-transformed `$rows` and a paginator into the `Paginated<T>`
     * shape the frontend `DataTable` hooks expect: `{ data, current_page,
     * last_page, per_page, total, links }`.
     *
     * @param  LengthAwarePaginator<int, Model>  $paginator
     * @param  list<array<string, mixed>>  $rows
     */
    protected function paginatedJson(LengthAwarePaginator $paginator, array $rows): JsonResponse
    {
        return response()->json([
            'data' => $rows,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'links' => $this->paginationLinks($paginator),
        ]);
    }

    /**
     * Build the {url,label,active} link list the frontend paginator expects,
     * using only the paginator contract's declared methods.
     *
     * @param  LengthAwarePaginator<int, Model>  $paginator
     * @return list<array{url: ?string, label: string, active: bool}>
     */
    private function paginationLinks(LengthAwarePaginator $paginator): array
    {
        $links = [
            ['url' => $paginator->previousPageUrl(), 'label' => '&laquo; Previous', 'active' => false],
        ];

        foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url) {
            $links[] = ['url' => $url, 'label' => (string) $page, 'active' => $page === $paginator->currentPage()];
        }

        $links[] = ['url' => $paginator->nextPageUrl(), 'label' => 'Next &raquo;', 'active' => false];

        return $links;
    }
}
