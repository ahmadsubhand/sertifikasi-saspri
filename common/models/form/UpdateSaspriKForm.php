<?php

namespace common\models\form;

class UpdateSaspriKForm extends RegisterSaspriKForm
{
    public $saspri_k_documents = null;

    public function rules()
    {
        return $this->baseRules();
    }
}