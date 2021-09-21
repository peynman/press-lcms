<?php

namespace Larapress\LCMS\Services\SupportGroup\Compositions;

use Larapress\CRUD\Services\CRUD\CRUDProviderComposition;

class ProductComposition extends CRUDProviderComposition
{
    /**
     * Undocumented function
     *
     * @return array
     */
    public function getValidSortColumns(): array
    {
        return array_merge(parent::getValidSortColumns(), [
            'starts_at' => function ($query, $dir) {
                $query->orderBy('data->types->session->start_at', $dir);
            },
        ]);
    }
}
