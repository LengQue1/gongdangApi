<?php

namespace App\Libraries\Repositories\Contracts;

interface RepositoryContract
{

    public function former();

    public function paginate($limit = null);

    public function where(array $data);

    public function whereVague($name, $str, $var);

    public function first();

    public function find($id);

    public function create(array $attributes);

    public function update($id, array $attributes);

    public function destroy($id);

    public function orderBy($attr, $sort);

    public function get();
}
