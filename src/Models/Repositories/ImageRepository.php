<?php

namespace WalkerChiu\MorphImage\Models\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use WalkerChiu\Core\Models\Forms\FormHasHostTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryHasHostTrait;

class ImageRepository extends Repository
{
    use FormHasHostTrait;
    use RepositoryHasHostTrait;

    protected $entity;

    public function __construct()
    {
        $this->entity = App::make(config('wk-core.class.morph-image.image'));
    }

    /**
     * @param String  $host_type
     * @param Int     $host_id
     * @param String  $code
     * @param Array   $data
     * @param Int     $page
     * @param Int     $nums per page
     * @param Boolean $is_enabled
     * @param String  $target
     * @param Boolean $target_is_enabled
     * @return Array
     */
    public function list($host_type, $host_id, String $code, Array $data, $page = null, $nums = null, $is_enabled = null, $target = null, $target_is_enabled = null)
    {
        $this->assertForPagination($page, $nums);

        if (empty($host_type) || empty($host_id)) {
            $entity = $this->entity;
        } else {
            $entity = $this->baseQueryForRepository($host_type, $host_id, $target, $target_is_enabled);
        }
        if ($is_enabled === true)      $entity = $entity->ofEnabled();
        elseif ($is_enabled === false) $entity = $entity->ofDisabled();

        $data = array_map('trim', $data);
        $records = $entity->with(['langs' => function ($query) use ($code) {
                                $query->ofCurrent()
                                      ->ofCode($code);
                             }])
                             ->unless(empty(config('wk-core.class.morph-tag.tag')), function ($query) {
                                 return $query->with(['tags', 'tags.langs']);
                             })
                            ->when($data, function ($query, $data) {
                                return $query->unless(empty($data['id']), function ($query) use ($data) {
                                            return $query->where('id', $data['id']);
                                        })
                                        ->unless(empty($data['morph_type']), function ($query) use ($data) {
                                            return $query->where('morph_type', $data['morph_type']);
                                        })
                                        ->unless(empty($data['morph_id']), function ($query) use ($data) {
                                            return $query->where('morph_id', $data['morph_id']);
                                        })
                                        ->unless(empty($data['filename']), function ($query) use ($data) {
                                            return $query->where('filename', $data['filename']);
                                        })
                                        ->unless(empty($data['serial']), function ($query) use ($data) {
                                            return $query->where('serial', $data['serial']);
                                        })
                                        ->unless(empty($data['identifier']), function ($query) use ($data) {
                                            return $query->where('identifier', $data['identifier']);
                                        })
                                        ->when(isset($data['type']), function ($query) use ($data) {
                                            return $query->where('type', $data['type']);
                                        })
                                        ->unless(empty($data['size']), function ($query) use ($data) {
                                            return $query->where('size', $data['size']);
                                        })
                                        ->when(isset($data['is_visible']), function ($query) use ($data) {
                                            return $query->where('is_visible', $data['is_visible']);
                                        })
                                        ->unless(empty($data['name']), function ($query) use ($data) {
                                            return $query->whereHas('langs', function($query) use ($data) {
                                                $query->ofCurrent()
                                                      ->where('key', 'name')
                                                      ->where('value', 'LIKE', "%".$data['name']."%");
                                            });
                                        })
                                        ->unless(empty($data['alt']), function ($query) use ($data) {
                                            return $query->whereHas('langs', function($query) use ($data) {
                                                $query->ofCurrent()
                                                      ->where('key', 'alt')
                                                      ->where('value', 'LIKE', "%".$data['alt']."%");
                                            });
                                        })
                                        ->unless(empty($data['description']), function ($query) use ($data) {
                                            return $query->whereHas('langs', function($query) use ($data) {
                                                $query->ofCurrent()
                                                      ->where('key', 'description')
                                                      ->where('value', 'LIKE', "%".$data['description']."%");
                                            });
                                        })
                                        ->unless(empty($data['categories']), function ($query) use ($data) {
                                            return $query->whereHas('categories', function($query) use ($data) {
                                                $query->ofEnabled()
                                                      ->whereIn('id', $data['categories']);
                                            });
                                        })
                                        ->unless(empty($data['tags']), function ($query) use ($data) {
                                            return $query->whereHas('tags', function($query) use ($data) {
                                                $query->ofEnabled()
                                                      ->whereIn('id', $data['tags']);
                                            });
                                        })
                                        ->unless(!empty($data['orderBy']) && !empty($data['orderType']), function ($query) use ($data) {
                                            return $query->orderBy($data['orderBy'], $data['orderType']);
                                        }, function ($query) {
                                            return $query->orderBy('updated_at', 'DESC');
                                        });
                            }, function ($query) {
                                return $query->orderBy('updated_at', 'DESC');
                            })
                            ->get()
                            ->when(is_integer($page) && is_integer($nums), function ($query) use ($page, $nums) {
                                return $query->forPage($page, $nums);
                            });
        $list = [];
        foreach ($records as $record) {
            $data = $record->toArray();
            array_push($list,
                array_merge($data, [
                    'name'        => $record->findLangByKey('name'),
                    'alt'         => $record->findLangByKey('alt'),
                    'description' => $record->findLangByKey('description')
                ])
            );
        }

        return $list;
    }

    /**
     * @param Image $entity
     * @param Array|String $code
     * @return Array
     */
    public function show($entity, $code)
    {
        $data = [
            'id'    => $entity ? $entity->id : '',
            'basic' => []
        ];

        if (empty($entity))
            return $data;

        $this->setEntity($entity);

        if (is_string($code)) {
            $data['basic'] = [
                'filename'   => $record->filename,
                'serial'     => $record->serial,
                'identifier' => $record->identifier,
                'name'       => $record->findLang($code, 'name'),
                'alt'        => $record->findLang($code, 'alt'),
                'type'       => $record->type,
                'size'       => $record->size,
                'data'       => $record->data,
                'options'    => $record->options,
                'is_visible' => $record->is_visible,
                'is_enabled' => $record->is_enabled,
                'created_at' => $entity->created_at,
                'updated_at' => $entity->updated_at
            ];

        } elseif (is_array($code)) {
            foreach ($code as $language) {
                $data['basic'][$language] = [
                    'filename'   => $record->filename,
                    'serial'     => $record->serial,
                    'identifier' => $record->identifier,
                    'name'       => $record->findLang($language, 'name'),
                    'alt'        => $record->findLang($language, 'alt'),
                    'type'       => $record->type,
                    'size'       => $record->size,
                    'data'       => $record->data,
                    'options'    => $record->options,
                    'is_visible' => $record->is_visible,
                    'is_enabled' => $record->is_enabled,
                    'created_at' => $entity->created_at,
                    'updated_at' => $entity->updated_at
                ];
            }
        }

        return $data;
    }


    /**
     * @param FormData $fileSource
     * @param Int      $id
     * @param Int      $order
     * @return String
     */
    public function createNewFileName($fileSource = null, $id = null, $order = 0)
    {
        $ext = is_null($fileSource) ? '' : '.'. $fileSource->getClientOriginalExtension();

        if (is_null($id)) {
            if (!empty($this->entity)) {
                return $this->entity->id .'_'. Carbon::now()->timestamp .'_'. $order . $ext;
            }
        } else {
            return $id .'_'. Carbon::now()->timestamp . $ext;
        }
    }

    /**
     * @param String   $directory
     * @param FormData $fileSource
     * @param Int      $id
     * @param Int      $order
     * @return String
     */
    public function uploadImage(String $directory, $fileSource, $id, $order = 0)
    {
        $fileName = $this->createNewFileName($fileSource, $id, $order);
        $path = Storage::putFileAs($directory, $fileSource, $fileName);

        if ($this->find($id)->update(['filename' => $fileName]))
            return $path;
    }

    /**
     * @param Image|Int|Array $data
     * @return Boolean
     */
    public function removeImage($data)
    {
        $class = config('wk-core.class.morph-image.image');
        if ($data instanceOf $class) return (Boolean) $data->delete();
        elseif (is_integer($data))   return (Boolean) $this->deleteByIds([$data]);
        elseif (is_array($data))     return (Boolean) $this->deleteByIds($data);
    }
}
