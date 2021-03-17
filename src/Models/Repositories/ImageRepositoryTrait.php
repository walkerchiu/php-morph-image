<?php

namespace WalkerChiu\MorphImage\Models\Repositories;

use WalkerChiu\MorphComment\Models\Repositories\CommentRepositoryTrait;

trait ImageRepositoryTrait
{
    use CommentRepositoryTrait;

    /**
     * @param $record
     * @param Array|String $code
     * @param Boolean      $is_frontend
     * @return Array
     */
    public function packRecord($record, $code, $is_frontend = false)
    {
        if (is_array($code)) {
            $list = [];
            foreach ($code as $lang) {
                $list[$lang] = $this->packRecord($record, $lang);
            }

            return $list;

        } elseif (is_string($code)) {
            if ($is_frontend) {
                return [
                    'filename'   => $record->filename,
                    'serial'     => $record->serial,
                    'identifier' => $record->identifier,
                    'name'       => $record->findLang($code, 'name'),
                    'alt'        => $record->findLang($code, 'alt'),
                    'type'       => $record->type,
                    'size'       => $record->size,
                    'options'    => $record->options,
                    'comments'   => config('wk-morph-image.onoff.morph-comment')
                                        ? $this->getlistOfComments($record)
                                        : []
                ];
            } else {
                return [
                    'id'         => $record->id,
                    'filename'   => $record->filename,
                    'serial'     => $record->serial,
                    'identifier' => $record->identifier,
                    'name'       => $record->findLang($code, 'name'),
                    'alt'        => $record->findLang($code, 'alt'),
                    'type'       => $record->type,
                    'size'       => $record->size,
                    'data'       => $record->data,
                    'options'    => $record->options,
                    'comments'   => config('wk-morph-image.onoff.morph-comment')
                                        ? $this->getlistOfComments($record)
                                        : []
                ];
            }
        }
    }

    /**
     * @param $records
     * @param Array|String $code
     * @param Boolean      $is_frontend
     * @return Array
     */
    public function getlist($records, $code, $is_frontend = false)
    {
        $list = [];
        if (is_array($code)) {
            foreach ($records as $record) {
                foreach ($code as $lang) {
                    if ($is_frontend)
                        $list[$lang][] = $this->packRecord($record, $lang, $is_frontend);
                    else
                        $list[$record->id][$lang] = $this->packRecord($record, $lang, $is_frontend);
                }
            }
        } elseif (is_string($code)) {
            foreach ($records as $record) {
                if ($is_frontend)
                    $list[] = $this->packRecord($record, $code, $is_frontend);
                else
                    $list[$record->id] = $this->packRecord($record, $code, $is_frontend);
            }
        }

        return $list;
    }


    /*
    |--------------------------------------------------------------------------
    | Get icon
    |--------------------------------------------------------------------------
    */

    /**
     * @param Array|String $code
     * @param Boolean      $is_enabled
     * @param Entity       $entity
     * @param Boolean      $is_frontend
     * @return Array
     */
    public function getlistOfIcons($code, $is_enabled = null, $entity = null, $is_frontend = false)
    {
        $entity = $entity ? $entity : $this->entity;
        $records = $entity->icons->where('is_visible', 1)
                                 ->where('size', '!=', NULL)
                                 ->when(!is_null($is_enabled), function ($query) use ($is_enabled) {
                                        return $query->where('is_enabled', $is_enabled);
                                      })
                                 ->all();

        return $this->getlist($records, $code, $is_frontend);
    }

    /**
     * @param Array|String $code
     * @param $entity
     * @param $type
     * @param Boolean $is_frontend
     * @return Array
     */
    public function getEnabledIcon($code, $entity, $type = null, $is_frontend = false)
    {
        $records = $entity->icons->where('is_visible', 1)
                                 ->where('is_enabled', 1)
                                 ->sortByDESC('updated_at')
                                 ->all();
        if (is_array($code)) {
            return $this->getlist($records, $code, $is_frontend);
        } elseif (is_string($code)) {
            foreach ($records as $record) {
                if ( is_null($type) || ($type == $record->size) ) {
                    return $this->packRecord($record, $code, $is_frontend);
                }
            }
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Get logo
    |--------------------------------------------------------------------------
    */

    /**
     * @param Array|String $code
     * @param Boolean      $is_enabled
     * @param Entity       $entity
     * @param Boolean      $is_frontend
     * @return Array
     */
    public function getlistOfLogos($code, $is_enabled = null, $entity = null, $is_frontend = false)
    {
        $entity = $entity ? $entity : $this->entity;
        $records = $entity->logos->where('is_visible', 1)
                                 ->where('size', '!=', NULL)
                                 ->when(!is_null($is_enabled), function ($query) use ($is_enabled) {
                                        return $query->where('is_enabled', $is_enabled);
                                      })
                                 ->all();

        return $this->getlist($records, $code, $is_frontend);
    }

    /**
     * @param Array|String $code
     * @param $entity
     * @param $type
     * @param Boolean $is_frontend
     * @return Array
     */
    public function getEnabledLogo($code, $entity, $type = null, $is_frontend = false)
    {
        $records = $entity->logos->where('is_visible', 1)
                                 ->where('is_enabled', 1)
                                 ->sortByDESC('updated_at')
                                 ->all();
        if (is_array($code)) {
            return $this->getlist($records, $code, $is_frontend);
        } elseif (is_string($code)) {
            foreach ($records as $record) {
                if ( is_null($type) || ($type == $record->size) ) {
                    return $this->packRecord($record, $code, $is_frontend);
                }
            }
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Get cover
    |--------------------------------------------------------------------------
    */

    /**
     * @param Array|String $code
     * @param Boolean      $is_enabled
     * @param Entity       $entity
     * @param Boolean      $is_frontend
     * @return Array
     */
    public function getlistOfCovers($code, $is_enabled = null, $entity = null, $is_frontend = false)
    {
        $entity = $entity ? $entity : $this->entity;
        $records = $entity->covers->where('is_visible', 1)
                                  ->where('size', '!=', NULL)
                                  ->when(!is_null($is_enabled), function ($query) use ($is_enabled) {
                                        return $query->where('is_enabled', $is_enabled);
                                      })
                                  ->all();

        return $this->getlist($records, $code, $is_frontend);
    }

    /**
     * @param Array|String $code
     * @param $entity
     * @param $type
     * @param Boolean $is_frontend
     * @return Array
     */
    public function getEnabledCover($code, $entity, $type = null, $is_frontend = false)
    {
        $records = $entity->covers->where('is_visible', 1)
                                  ->where('is_enabled', 1)
                                  ->sortByDESC('updated_at')
                                  ->all();
        if (is_array($code)) {
            return $this->getlist($records, $code, $is_frontend);
        } elseif (is_string($code)) {
            foreach ($records as $record) {
                if ( is_null($type) || ($type == $record->size) ) {
                    return $this->packRecord($record, $code, $is_frontend);
                }
            }
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Get image list to show
    |--------------------------------------------------------------------------
    */

    /**
     * @param Array|String $code
     * @param Boolean      $is_onlySimple
     * @param Boolean      $is_enabled
     * @param Entity       $entity
     * @param Boolean      $is_frontend
     * @return Array
     */
    public function getlistOfImages($code, $is_onlySimple = false, $is_enabled = null, $entity = null, $is_frontend = false)
    {
        $entity = $entity ? $entity : $this->entity;
        $records = $entity->images->where('is_visible', 1)
                                  ->when($is_onlySimple, function ($query, $is_onlySimple) {
                                        return $query->whereNull('type');
                                      })
                                  ->unless(is_null($is_enabled), function ($query) use ($is_enabled) {
                                        return $query->where('is_enabled', $is_enabled);
                                      })
                                  ->all();

        return $this->getlist($records, $code, $is_frontend);
    }

    /*
    |--------------------------------------------------------------------------
    | For Auto Complete
    |--------------------------------------------------------------------------
    */

    /**
     * @param String  $host_type
     * @param String  $host_id
     * @param String  $code
     * @param Any     $value
     * @param Int     $count
     * @param String  $target
     * @param Boolean $target_is_enabled
     * @return Array
     */
    public function autoCompleteAltOfEnabled($host_type, $host_id, String $code, $value, $count = 10, $target = null, $target_is_enabled = null)
    {
        $records = $this->entity->lang()::with('morph')
                                        ->ofEnabled()
                                        ->ofVisible()
                                        ->ofCodeAndKey($code, 'alt')
                                        ->whereHasMorph('morph', $this->entity->morph_type, function($query) use ($host_type, $host_id) {
                                                $query->ofEnabled()
                                                      ->unless(empty($host_type) || empty($host_id), function ($query) use ($host_type, $host_id) {
                                                            return $query->whereHasMorph('host', $host_type, function($query) {
                                                                $query->ofEnabled();
                                                            });
                                                        });
                                           })
                                        ->where('value', 'LIKE', $value .'%')
                                        ->orderBy('updated_at', 'DESC')
                                        ->select('shelf_image_id', 'value')
                                        ->take($count)
                                        ->get();
        $list = [];
        foreach ($records as $record) {
            $list[] = [
                'id'     => $record->host->id,
                'serial' => $record->host->serial,
                'name'   => $record->host->findLang($code, 'name'),
                'alt'    => $record->value
            ];
        }

        return $list;
    }
}
