<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

class EBICDataRepository
{

    public array $data;

    public array $table;

    public function __construct(
    )
    {
        $this->data = json_decode(
            file_get_contents(
                database_path('datasets/ebic.json')
            ), true
        );

        $this->table =  json_decode(
            file_get_contents(
                database_path('datasets/ebic-table.json')
            ), true
        );
    }

    public function getTableContent(): Collection
    {
        return collect($this->table['body']['rows'])
            ->map(function ($row) {
                $row['cells'] = collect($row['cells'])
                    ->map(function ($item) {
                        if ($item['type'] === 'filled')
                            $item['content'] = $this->getContentById($item['contentId']);
                        return $item;
                    });
                return $row;
            });
    }
    public function getContentById($contentID)
    {
        return collect($this->data['categories'])->pluck('items')->flatten(1)->where('id','=', $contentID)->first();
    }
}
