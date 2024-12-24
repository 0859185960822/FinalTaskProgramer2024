<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait PaginationHelper
{
    public function linkCollection(LengthAwarePaginator $paginator)
    {
        return collect([
            'first_page_url' => $paginator->url(1),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'current_page' => $paginator->currentPage(),
        ]);
    }
}
