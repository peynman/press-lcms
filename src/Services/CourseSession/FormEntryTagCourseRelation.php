<?php

namespace Larapress\LCMS\Services\CourseSession;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Larapress\ECommerce\Models\Product;
use Larapress\Profiles\Models\FormEntry;

class FormEntryTagCourseRelation extends Relation
{
    protected $table = '';
    protected $isReadyToLoad = false;
    public function __construct(Model $parent, Builder $query)
    {
        $this->table = $query->getModel()->getTable();
        parent::__construct(Product::query(), $parent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $this->query
            ->select([$this->table.'.id', $this->table.'.name'])
            ->leftJoin('form_entries', function ($join) {
                $join->on('form_entries.tags', '=', DB::raw('CONCAT(\'course-\', '.$this->table.'.id, \'-presence\')'));
            });
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     *
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query
            ->whereIn('form_entries.id', collect($models)->map(function (FormEntry $model) {
                return $model->id;
            }));

        $this->isReadyToLoad = true;
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param array $models
     * @param string $relation
     *
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array $models
     * @param \Illuminate\Database\Eloquent\Collection $results
     * @param string $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        if ($results->isEmpty()) {
            return $models;
        }

        foreach ($models as $model) {
            $resultset = $results->filter(function (Model $contract) use ($model) {
                return $model->tags === 'course-'.$contract->id.'-presence';
            });
            $model->setRelation(
                $relation,
                $resultset->first(),
            );
        }

        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        if (!$this->isReadyToLoad) {
            $this->addEagerConstraints([$this->parent]);
        }
        return $this->query->first();
    }
}
