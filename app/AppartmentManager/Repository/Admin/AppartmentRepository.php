<?php
    /**
     * Created by PhpStorm.
     * User: kaso
     * Date: 11/14/2014
     * Time: 12:59 PM
     */

    namespace AppartmentManager\Repository\Admin;


    use AppartmentManager\Models\Appartment;
    use AppartmentManager\Models\Block;
    use AppartmentManager\Repository\CrudRepository;

    class AppartmentRepository implements CrudRepository
    {


        /**
         * @var Appartment
         */
        private $appartmentModel;

        function __construct(Appartment $appartmentModel)
        {
            $this->appartmentModel = $appartmentModel;
        }

        public function create($data = [])
        {
            return $this->appartmentModel->create($data);
        }

        public function delete($id)
        {
            $this->appartmentModel->findOrFail($id)->delete();
        }

        public function update($id, $data)
        {
            $this->appartmentModel->findOrFail($id)->update($data);
        }

        public function allWithCondition($limit = 20, $condition, $value)
        {
            if (is_null($value)) {
                return $this->all($limit);
            }

            return $this->appartmentModel
                ->where($condition, $value)
                ->with(['unit', 'tenant'])
                ->simplePaginate($limit);
        }

        public function all($limit = 20)
        {
            return $this->appartmentModel->with(['unit', 'tenant'])->simplePaginate($limit);
        }

        public function getVacantAppartments($columns = NULL)
        {
            return $this->appartmentModel->where('is_vacant', TRUE)->get($columns);
        }

        public function getVacantAppartmentsList($value, $key)
        {
            return $this->appartmentModel->where('is_vacant', TRUE)->lists($value, $key);
        }

        public function assignTenant($appartment_id, $tenant)
        {
            $appartment = $this->read($appartment_id);
            $appartment->tenant_id = $tenant->id;
            $appartment->is_vacant = FALSE;

            return $appartment->save();
        }

        public function read($id, $data = [])
        {
            if (empty($data)) {
                return $this->appartmentModel->findOrFail($id);
            } else {
                foreach ($data as $key => $value) {
                    $this->appartmentModel->where($key, $value);
                }

                return $this->appartmentModel->get();
            }
        }

        public function getAppartmentByAppartmentID($appartment_id)
        {
            return $this->appartmentModel
                ->where('appartment_id', $appartment_id)
                ->with('tenant')
                ->first();
        }

        public function blocks()
        {
            return Block::get();
        }

        public function allForSlugWithCondition($slug, $limit, $condition, $value)
        {
            if (is_null($value)) {
                return $this->allForSlug($slug, $limit);
            }

            $block_id = $this->getBlockIdForSlug($slug);

            return $this->appartmentModel
                ->whereHas('unit', function ($query) use ($block_id) {
                    $query->where('block_id', $block_id);
                })
                ->where($condition, $value)
                ->with(['unit', 'tenant'])
                ->simplePaginate($limit);
        }

        public function allForSlug($slug, $limit)
        {
            $block_id = $this->getBlockIdForSlug($slug);

            return $this->appartmentModel
                ->whereHas('unit', function ($query) use ($block_id) {
                    $query->where('block_id', $block_id);
                })
                ->with(['unit', 'tenant'])
                ->simplePaginate($limit);
        }

        private function getBlockIdForSlug($slug)
        {
            return Block::where('slug', $slug)->firstOrFail()->id;
        }
    }